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
    SELECT TOP 1 id, login, nome, email, password, equipe_id, role_id, ativo, remember_token
    FROM dbo.users
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
    SELECT TOP 1 id, login, nome, email, password, equipe_id, role_id, ativo, remember_token
    FROM dbo.users
    WHERE id = @id
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
    ativo: Number(user.ativo ?? 1),
    remember_token: user.remember_token ?? null,
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
