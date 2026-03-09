import crypto from 'node:crypto';
import bcrypt from 'bcryptjs';
import sql from 'mssql';

const poolConfig = {
  user: process.env.BRIDGE_DB_USERNAME || process.env.DB_USERNAME,
  password: process.env.BRIDGE_DB_PASSWORD || process.env.DB_PASSWORD,
  server: process.env.BRIDGE_DB_HOST || process.env.DB_HOST,
  database: process.env.BRIDGE_DB_DATABASE || process.env.DB_DATABASE,
  port: Number(process.env.BRIDGE_DB_PORT || process.env.DB_PORT || 1433),
  options: {
    encrypt: toBool(process.env.BRIDGE_DB_ENCRYPT ?? process.env.DB_ENCRYPT, true),
    trustServerCertificate: toBool(
      process.env.BRIDGE_DB_TRUST_SERVER_CERTIFICATE ?? process.env.DB_TRUST_SERVER_CERTIFICATE,
      true
    ),
    enableArithAbort: true,
  },
  pool: {
    max: 5,
    min: 0,
    idleTimeoutMillis: 30_000,
  },
};

function toBool(value, fallback) {
  if (typeof value === 'boolean') return value;
  if (typeof value === 'number') return value !== 0;
  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase();
    if (['1', 'true', 'yes', 'on'].includes(normalized)) return true;
    if (['0', 'false', 'no', 'off'].includes(normalized)) return false;
  }
  return fallback;
}

function normalizeLogin(login) {
  return String(login || '').trim().toLowerCase();
}

async function parseJsonBody(req) {
  if (req.body && typeof req.body === 'object') {
    return req.body;
  }

  if (typeof req.body === 'string' && req.body !== '') {
    try {
      return JSON.parse(req.body);
    } catch {
      return {};
    }
  }

  const chunks = [];
  for await (const chunk of req) {
    chunks.push(Buffer.isBuffer(chunk) ? chunk : Buffer.from(chunk));
  }

  const raw = Buffer.concat(chunks).toString('utf8').trim();
  if (!raw) return {};

  try {
    return JSON.parse(raw);
  } catch {
    return {};
  }
}

function getBridgeToken(req) {
  const header = req.headers['x-bridge-token'];
  return Array.isArray(header) ? String(header[0] || '') : String(header || '');
}

function getPoolPromise() {
  if (!globalThis.__europaSqlPoolPromise) {
    const pool = new sql.ConnectionPool(poolConfig);
    globalThis.__europaSqlPoolPromise = pool.connect();
  }

  return globalThis.__europaSqlPoolPromise;
}

async function findUserByLogin(login) {
  const pool = await getPoolPromise();
  const request = pool.request();
  request.input('login', sql.VarChar(191), login);

  const result = await request.query(`
    SELECT TOP 1
      u.id,
      u.login,
      u.nome,
      u.email,
      u.password,
      u.equipe_id,
      u.role_id,
      u.ativo,
      u.remember_token,
      r.slug AS role_slug,
      r.nome AS role_nome,
      r.nivel AS role_nivel,
      e.nome AS team_name
    FROM dbo.users u
    LEFT JOIN dbo.roles r ON r.id = u.role_id
    LEFT JOIN dbo.equipes e ON e.id = u.equipe_id
    WHERE LOWER(login) = @login
  `);

  return result.recordset?.[0] ?? null;
}

async function findUserById(id) {
  const numericId = Number(id || 0);
  if (!Number.isFinite(numericId) || numericId <= 0) {
    return null;
  }

  const pool = await getPoolPromise();
  const request = pool.request();
  request.input('id', sql.Int, numericId);

  const result = await request.query(`
    SELECT TOP 1
      u.id,
      u.login,
      u.nome,
      u.email,
      u.password,
      u.equipe_id,
      u.role_id,
      u.ativo,
      u.remember_token,
      r.slug AS role_slug,
      r.nome AS role_nome,
      r.nivel AS role_nivel,
      e.nome AS team_name
    FROM dbo.users u
    LEFT JOIN dbo.roles r ON r.id = u.role_id
    LEFT JOIN dbo.equipes e ON e.id = u.equipe_id
    WHERE u.id = @id
  `);

  return result.recordset?.[0] ?? null;
}

async function updateLastLogin(userId) {
  const numericId = Number(userId || 0);
  if (!Number.isFinite(numericId) || numericId <= 0) return;

  const pool = await getPoolPromise();
  const request = pool.request();
  request.input('id', sql.Int, numericId);
  await request.query(`
    UPDATE dbo.users
    SET last_login_at = SYSUTCDATETIME()
    WHERE id = @id
  `);
}

async function passwordMatches(plainPassword, dbHash) {
  const hash = String(dbHash || '');
  if (!hash) return false;

  const normalizedHash = hash.startsWith('$2y$')
    ? `$2b$${hash.slice(4)}`
    : hash;

  try {
    return await bcrypt.compare(String(plainPassword || ''), normalizedHash);
  } catch {
    return false;
  }
}

function mapUser(user) {
  if (!user) return null;

  return {
    id: Number(user.id),
    login: String(user.login || ''),
    name: String(user.nome || user.login || ''),
    email: String(user.email || `${String(user.login || '')}@europa.local`),
    equipe_id: user.equipe_id === null || user.equipe_id === undefined ? null : Number(user.equipe_id),
    role_id: user.role_id === null || user.role_id === undefined ? null : Number(user.role_id),
    role_slug: String(user.role_slug || ''),
    role_nome: String(user.role_nome || ''),
    role_nivel: user.role_nivel === null || user.role_nivel === undefined ? null : Number(user.role_nivel),
    team_name: String(user.team_name || ''),
    ativo: Number(user.ativo ?? 1),
    remember_token: user.remember_token ?? null,
  };
}

function computeScopeMode(roleSlug) {
  const normalizedRole = String(roleSlug || '').trim().toLowerCase();

  if (normalizedRole === 'master') {
    return 'all';
  }

  if (['administrador', 'administrator', 'admin', 'supervisor'].includes(normalizedRole)) {
    return 'team';
  }

  return 'self';
}

async function ensureSettingsPermissionsCatalog() {
  const pool = await getPoolPromise();

  await pool.request().query(`
    IF EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta_cliente.view')
      UPDATE dbo.permissions
      SET nome = 'Ver', modulo = 'consulta_cliente'
      WHERE slug = 'consulta_cliente.view';
    ELSE
      INSERT INTO dbo.permissions (nome, slug, modulo)
      VALUES ('Ver', 'consulta_cliente.view', 'consulta_cliente');
  `);
}

async function hasPermission(roleId, roleSlug, permissionSlug) {
  const normalizedRole = String(roleSlug || '').trim().toLowerCase();
  const normalizedPermission = String(permissionSlug || '').trim().toLowerCase();

  if (normalizedRole === 'master') {
    return true;
  }

  const numericRoleId = Number(roleId || 0);
  if (!Number.isFinite(numericRoleId) || numericRoleId <= 0 || !normalizedPermission) {
    return false;
  }

  const pool = await getPoolPromise();
  const request = pool.request();
  request.input('role_id', sql.Int, numericRoleId);
  request.input('permission_slug', sql.VarChar(191), normalizedPermission);

  const result = await request.query(`
    SELECT TOP 1 1 AS allowed
    FROM dbo.role_permissions rp
    INNER JOIN dbo.permissions p ON p.id = rp.permission_id
    WHERE rp.role_id = @role_id
      AND rp.allowed = 1
      AND LOWER(p.slug) = @permission_slug
  `);

  return (result.recordset?.length ?? 0) > 0;
}

function formatActionLabel(permissionSlug, fallbackLabel) {
  const normalized = String(permissionSlug || '').trim().toLowerCase();

  if (normalized.endsWith('.view')) return 'Ver';
  if (normalized.endsWith('.create')) return 'Criar';
  if (normalized.endsWith('.edit')) return 'Editar';
  if (normalized.endsWith('.delete')) return 'Excluir';
  if (normalized.endsWith('.batch.send')) return 'Enviar lote';
  if (normalized.endsWith('.batch.delete')) return 'Excluir lote';

  return String(fallbackLabel || permissionSlug || '').trim();
}

function buildPermissionNodes(modulePermissions) {
  return [...modulePermissions]
    .sort((a, b) => Number(a.id || 0) - Number(b.id || 0))
    .map((permission) => ({
      key: `perm-${Number(permission.id || 0)}`,
      label: formatActionLabel(permission.slug, permission.nome),
      permission_slug: String(permission.slug || '').trim(),
    }));
}

async function buildSettingsIndexPayload(authUser) {
  await ensureSettingsPermissionsCatalog();

  const authUserId = Number(authUser?.id || 0);
  const authRoleId = Number(authUser?.role_id || 0);
  const authTeamId = authUser?.team_id === null || authUser?.team_id === undefined || authUser?.team_id === ''
    ? null
    : Number(authUser.team_id);
  const authRoleSlug = String(authUser?.role_slug || '').trim().toLowerCase();
  const scopeMode = computeScopeMode(authRoleSlug);

  const pool = await getPoolPromise();

  let authAllowedPermissionSlugs = [];
  if (authRoleSlug === 'master') {
    authAllowedPermissionSlugs = ['*'];
  } else if (authRoleId > 0) {
    const authPermissionsResult = await pool.request()
      .input('role_id', sql.Int, authRoleId)
      .query(`
        SELECT p.slug
        FROM dbo.role_permissions rp
        INNER JOIN dbo.permissions p ON p.id = rp.permission_id
        WHERE rp.role_id = @role_id
          AND rp.allowed = 1
      `);

    authAllowedPermissionSlugs = (authPermissionsResult.recordset || [])
      .map((row) => String(row.slug || '').trim().toLowerCase())
      .filter(Boolean);
  }

  let usersQuery = `
    SELECT
      u.id,
      u.nome,
      u.login,
      u.equipe_id,
      u.role_id,
      u.ativo
    FROM dbo.users u
  `;

  const usersRequest = pool.request();
  if (scopeMode === 'team') {
    if (authTeamId === null) {
      usersQuery += ` WHERE u.equipe_id IS NULL `;
    } else {
      usersQuery += ` WHERE u.equipe_id = @team_id `;
      usersRequest.input('team_id', sql.Int, authTeamId);
    }
  } else if (scopeMode === 'self') {
    if (authUserId > 0) {
      usersQuery += ` WHERE u.id = @user_id `;
      usersRequest.input('user_id', sql.Int, authUserId);
    } else {
      usersQuery += ` WHERE 1 = 0 `;
    }
  }

  usersQuery += ` ORDER BY u.nome, u.login `;
  const users = (await usersRequest.query(usersQuery)).recordset || [];

  const roles = (await pool.request().query(`
    SELECT id, nome, slug, nivel
    FROM dbo.roles
    ORDER BY nivel DESC, nome ASC
  `)).recordset || [];

  const rolesById = Object.fromEntries(
    roles.map((role) => [
      Number(role.id),
      {
        nome: String(role.nome || '').trim(),
        slug: String(role.slug || '').trim(),
        nivel: role.nivel === null || role.nivel === undefined ? null : Number(role.nivel),
      },
    ])
  );

  let teams = (await pool.request().query(`
    SELECT id, nome
    FROM dbo.equipes
    ORDER BY nome ASC
  `)).recordset || [];

  if (scopeMode !== 'all') {
    const visibleTeamIds = [...new Set(
      users
        .map((user) => user.equipe_id)
        .filter((id) => id !== null && id !== undefined)
        .map((id) => Number(id))
    )];

    teams = teams.filter((team) => visibleTeamIds.includes(Number(team.id)));
  }

  if (teams.length === 0) {
    teams = [...new Set(
      users
        .map((user) => user.equipe_id)
        .filter((id) => id !== null && id !== undefined)
        .map((id) => Number(id))
    )].sort((a, b) => a - b).map((id) => ({ id, nome: `Equipe #${id}` }));
  }

  const dbTeams = teams.map((team) => ({
    key: `team-${String(team.id).replace(/[^a-zA-Z0-9_-]/g, '-')}`,
    label: String(team.nome || '').trim() || `Equipe #${team.id}`,
    team_id: team.id ?? null,
  }));

  const buildUserLabel = (user, index) => {
    const name = String(user.nome || '').trim();
    const login = String(user.login || '').trim();

    if (name && login && name.toLowerCase() !== login.toLowerCase()) {
      return `${name} (${login})`;
    }

    return name || login || `Usuario #${user.id || index + 1}`;
  };

  const buildUserKey = (user, index) => {
    const login = String(user.login || '').trim();
    const keySource = user.id !== null && user.id !== undefined ? String(user.id) : (login || `idx-${index}`);

    return `user-${keySource.replace(/[^a-zA-Z0-9_-]/g, '-')}`;
  };

  const teamKeyById = Object.fromEntries(dbTeams.map((team) => [String(team.team_id ?? ''), team.key]));
  const teamLabelById = Object.fromEntries(dbTeams.map((team) => [String(team.team_id ?? ''), team.label]));

  const buildMemberPayload = (memberUsers) => memberUsers.map((user, index) => {
    const roleData = user.role_id !== null && user.role_id !== undefined ? rolesById[Number(user.role_id)] : null;
    const roleName = String(roleData?.nome || '').trim();
    const roleLevel = roleData?.nivel ?? null;

    let permissionLevel = 'Sem nivel';
    if (roleName && roleLevel !== null) {
      permissionLevel = `${roleName} (Nivel ${roleLevel})`;
    } else if (roleName) {
      permissionLevel = roleName;
    } else if (user.role_id !== null && user.role_id !== undefined) {
      permissionLevel = `Role #${user.role_id}`;
    }

    return {
      key: `member-${user.id ?? `idx-${index}`}`,
      userKey: buildUserKey(user, index),
      label: buildUserLabel(user, index),
      permissionLevel,
    };
  });

  const teamMembersByTeam = {};
  for (const team of dbTeams) {
    const members = users.filter((user) => String(user.equipe_id ?? '') === String(team.team_id ?? ''));
    teamMembersByTeam[team.key] = buildMemberPayload(members);
  }

  const usersWithoutTeam = users.filter((user) => user.equipe_id === null || user.equipe_id === undefined);
  const dbTeamsWithNoTeam = [...dbTeams];
  if (usersWithoutTeam.length > 0) {
    const noTeamKey = 'team-sem-equipe';
    dbTeamsWithNoTeam.push({
      key: noTeamKey,
      label: 'Sem equipe',
      team_id: null,
    });
    teamMembersByTeam[noTeamKey] = buildMemberPayload(usersWithoutTeam);
  }

  const permissions = (await pool.request().query(`
    SELECT id, nome, slug, modulo
    FROM dbo.permissions
    ORDER BY modulo ASC, id ASC
  `)).recordset || [];

  const rolePermissions = (await pool.request().query(`
    SELECT role_id, permission_id, allowed
    FROM dbo.role_permissions
  `)).recordset || [];

  const allowedPermissionsByRole = {};
  const permissionsById = Object.fromEntries(permissions.map((permission) => [Number(permission.id), permission]));
  for (const row of rolePermissions) {
    if (Number(row.allowed || 0) !== 1) continue;
    const permission = permissionsById[Number(row.permission_id || 0)];
    if (!permission || !String(permission.slug || '').trim()) continue;

    const roleId = Number(row.role_id || 0);
    if (!allowedPermissionsByRole[roleId]) {
      allowedPermissionsByRole[roleId] = {};
    }
    allowedPermissionsByRole[roleId][String(permission.slug || '').trim()] = true;
  }

  const permissionRoles = roles.map((role) => ({
    key: `role-${Number(role.id || 0)}`,
    label: String(role.nome || '').trim(),
    role_id: Number(role.id || 0),
    slug: String(role.slug || '').trim(),
    nivel: Number(role.nivel || 0),
  }));

  const permissionsByModule = {};
  for (const permission of permissions) {
    const moduleKey = String(permission.modulo || '').trim();
    if (!permissionsByModule[moduleKey]) {
      permissionsByModule[moduleKey] = [];
    }
    permissionsByModule[moduleKey].push(permission);
  }

  const permissionsTree = [];

  if (permissionsByModule.dashboard) {
    const dashboardChildren = buildPermissionNodes(permissionsByModule.dashboard);
    if (dashboardChildren.length > 0) {
      permissionsTree.push({ key: 'module-dashboard', label: 'Painel', children: dashboardChildren });
    }
  }

  const consultasChildren = [];
  for (const [moduleKey, moduleLabel] of Object.entries({ consulta_cliente: 'Consulta Cliente' })) {
    if (!permissionsByModule[moduleKey]) continue;
    consultasChildren.push({
      key: `module-${moduleKey}`,
      label: moduleLabel,
      children: buildPermissionNodes(permissionsByModule[moduleKey]),
    });
  }
  if (consultasChildren.length > 0) {
    permissionsTree.push({ key: 'module-consultas', label: 'Consultas', children: consultasChildren });
  }

  const settingsChildren = [];
  for (const [moduleKey, moduleLabel] of Object.entries({ config: 'Permissoes', users: 'Usuarios', equipes: 'Equipes' })) {
    if (!permissionsByModule[moduleKey]) continue;
    settingsChildren.push({
      key: `module-${moduleKey}`,
      label: moduleLabel,
      children: buildPermissionNodes(permissionsByModule[moduleKey]),
    });
  }

  const apiChildren = [];
  for (const [moduleKey, moduleLabel] of Object.entries({ consulta_v8: 'Consulta V8', consulta_presenca: 'Consulta Presenca' })) {
    if (!permissionsByModule[moduleKey]) continue;
    apiChildren.push({
      key: `module-${moduleKey}`,
      label: moduleLabel,
      children: buildPermissionNodes(permissionsByModule[moduleKey]),
    });
  }
  if (apiChildren.length > 0) {
    settingsChildren.push({
      key: 'module-cadastro-api',
      label: 'Cadastro API',
      children: apiChildren,
    });
  }
  if (settingsChildren.length > 0) {
    permissionsTree.push({
      key: 'module-configuracoes',
      label: 'Configuracoes',
      children: settingsChildren,
    });
  }

  for (const moduleKey of Object.keys(permissionsByModule)) {
    if (['dashboard', 'config', 'users', 'equipes', 'consulta_v8', 'consulta_presenca', 'consulta_cliente'].includes(moduleKey)) {
      continue;
    }

    const modulePermissions = buildPermissionNodes(permissionsByModule[moduleKey]);
    if (modulePermissions.length === 0) continue;

    permissionsTree.push({
      key: `module-${String(moduleKey).replace(/[^a-zA-Z0-9_-]/g, '-')}`,
      label: String(moduleKey).replace(/[_\.]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase()),
      children: modulePermissions,
    });
  }

  const permissionKeyToSlug = Object.fromEntries(
    permissions.map((permission) => [`perm-${Number(permission.id || 0)}`, String(permission.slug || '').trim()])
  );

  const permissionsStateByRole = {};
  for (const role of permissionRoles) {
    const selectionKey = `permissions:${role.key}`;
    const allowedBySlug = allowedPermissionsByRole[Number(role.role_id || 0)] || {};

    permissionsStateByRole[selectionKey] = {};
    for (const [permissionKey, permissionSlug] of Object.entries(permissionKeyToSlug)) {
      permissionsStateByRole[selectionKey][permissionKey] = Boolean(allowedBySlug[permissionSlug] || false);
    }

    if (String(role.slug || '').trim().toLowerCase() === 'master') {
      for (const permissionKey of Object.keys(permissionsStateByRole[selectionKey])) {
        permissionsStateByRole[selectionKey][permissionKey] = true;
      }
    }
  }

  const dbUsers = users.map((user, index) => {
    const roleData = user.role_id !== null && user.role_id !== undefined ? rolesById[Number(user.role_id)] : null;

    return {
      key: buildUserKey(user, index),
      label: buildUserLabel(user, index),
      user_id: user.id !== null && user.id !== undefined ? Number(user.id) : null,
      name: String(user.nome || '').trim(),
      login: String(user.login || '').trim(),
      team_id: user.equipe_id !== null && user.equipe_id !== undefined ? Number(user.equipe_id) : null,
      team_key: teamKeyById[String(user.equipe_id ?? '')] || '',
      team_label: String(teamLabelById[String(user.equipe_id ?? '')] || '').trim(),
      role_id: user.role_id !== null && user.role_id !== undefined ? Number(user.role_id) : null,
      role_label: String(roleData?.nome || '').trim(),
      role_nivel: roleData?.nivel ?? null,
      is_active: Number(user.ativo ?? 1) === 1,
    };
  });

  return {
    dbUsers,
    dbTeams: dbTeamsWithNoTeam,
    teamMembersByTeam,
    permissionRoles,
    permissionsTree,
    permissionsStateByRole,
    authUserId,
    authUserTeamId: authTeamId,
    authRoleSlug,
    authScopeMode: scopeMode,
    authAllowedPermissionSlugs,
  };
}

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ ok: false, message: 'method_not_allowed' });
  }

  const expectedToken = String(process.env.SQL_BRIDGE_TOKEN || '').trim();
  const receivedToken = getBridgeToken(req).trim();

  if (!expectedToken || !receivedToken || !timingSafeEqual(expectedToken, receivedToken)) {
    return res.status(401).json({ ok: false, message: 'unauthorized' });
  }

  try {
    const body = await parseJsonBody(req);
    const action = String(body?.action || '').trim().toLowerCase();

    if (!action) {
      return res.status(422).json({ ok: false, message: 'invalid_action' });
    }

    if (action === 'user_by_login') {
      const login = normalizeLogin(body?.login);
      if (!login) return res.status(422).json({ ok: false, message: 'invalid_login' });

      const user = await findUserByLogin(login);
      if (!user) return res.status(200).json({ ok: false, code: 'not_found' });

      return res.status(200).json({ ok: true, user: mapUser(user) });
    }

    if (action === 'user_by_id') {
      const user = await findUserById(body?.id);
      if (!user) return res.status(200).json({ ok: false, code: 'not_found' });

      return res.status(200).json({ ok: true, user: mapUser(user) });
    }

    if (action === 'login') {
      const login = normalizeLogin(body?.login);
      const password = String(body?.password || '');

      if (!login || !password) {
        return res.status(422).json({ ok: false, message: 'invalid_credentials' });
      }

      const user = await findUserByLogin(login);
      if (!user) {
        return res.status(200).json({ ok: false, code: 'invalid_credentials' });
      }

      if (Number(user.ativo ?? 1) !== 1) {
        return res.status(200).json({ ok: false, code: 'inactive_user' });
      }

      const validPassword = await passwordMatches(password, user.password);
      if (!validPassword) {
        return res.status(200).json({ ok: false, code: 'invalid_credentials' });
      }

      await updateLastLogin(user.id);

      return res.status(200).json({ ok: true, user: mapUser(user) });
    }

    if (action === 'has_permission') {
      const allowed = await hasPermission(body?.role_id, body?.role_slug, body?.permission_slug);
      return res.status(200).json({ ok: true, allowed });
    }

    if (action === 'ensure_permissions_catalog') {
      await ensureSettingsPermissionsCatalog();
      return res.status(200).json({ ok: true });
    }

    if (action === 'settings_index') {
      const payload = await buildSettingsIndexPayload(body?.auth_user || {});
      return res.status(200).json({ ok: true, payload });
    }

    return res.status(422).json({ ok: false, message: 'unsupported_action' });
  } catch (error) {
    console.error('BRIDGE_AUTH_ERROR', {
      message: error?.message || 'unknown_error',
      stack: error?.stack || null,
    });

    return res.status(500).json({ ok: false, message: 'bridge_error' });
  }
}

function timingSafeEqual(a, b) {
  const left = Buffer.from(String(a));
  const right = Buffer.from(String(b));

  if (left.length !== right.length) return false;
  return crypto.timingSafeEqual(left, right);
}
