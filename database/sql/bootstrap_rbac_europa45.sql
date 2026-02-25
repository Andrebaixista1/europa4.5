SET NOCOUNT ON;
SET XACT_ABORT ON;

BEGIN TRAN;

IF OBJECT_ID(N'dbo.roles', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.roles (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_roles PRIMARY KEY,
        nome NVARCHAR(100) NOT NULL,
        slug NVARCHAR(100) NOT NULL,
        nivel INT NOT NULL,
        is_system BIT NOT NULL CONSTRAINT DF_roles_is_system DEFAULT (1),
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_roles_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_roles_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_roles_slug' AND object_id = OBJECT_ID(N'dbo.roles'))
    CREATE UNIQUE INDEX UX_roles_slug ON dbo.roles(slug);

IF OBJECT_ID(N'dbo.permissions', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.permissions (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_permissions PRIMARY KEY,
        nome NVARCHAR(150) NOT NULL,
        slug NVARCHAR(150) NOT NULL,
        modulo NVARCHAR(100) NOT NULL,
        descricao NVARCHAR(255) NULL,
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_permissions_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_permissions_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_permissions_slug' AND object_id = OBJECT_ID(N'dbo.permissions'))
    CREATE UNIQUE INDEX UX_permissions_slug ON dbo.permissions(slug);

IF OBJECT_ID(N'dbo.equipes', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.equipes (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_equipes PRIMARY KEY,
        nome NVARCHAR(150) NOT NULL,
        descricao NVARCHAR(255) NULL,
        supervisor_user_id BIGINT NULL,
        ativo BIT NOT NULL CONSTRAINT DF_equipes_ativo DEFAULT (1),
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_equipes_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_equipes_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_equipes_nome' AND object_id = OBJECT_ID(N'dbo.equipes'))
    CREATE UNIQUE INDEX UX_equipes_nome ON dbo.equipes(nome);

IF OBJECT_ID(N'dbo.users', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.users (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_users PRIMARY KEY,
        nome NVARCHAR(150) NOT NULL,
        login NVARCHAR(100) NOT NULL,
        email NVARCHAR(190) NULL,
        [password] NVARCHAR(255) NOT NULL,
        equipe_id BIGINT NULL,
        role_id BIGINT NULL,
        ativo BIT NOT NULL CONSTRAINT DF_users_ativo DEFAULT (1),
        last_login_at DATETIME2(0) NULL,
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_users_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_users_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_users_login' AND object_id = OBJECT_ID(N'dbo.users'))
    CREATE UNIQUE INDEX UX_users_login ON dbo.users(login);

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_users_email_not_null' AND object_id = OBJECT_ID(N'dbo.users'))
    CREATE UNIQUE INDEX UX_users_email_not_null ON dbo.users(email) WHERE email IS NOT NULL;

IF OBJECT_ID(N'dbo.role_permissions', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.role_permissions (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_role_permissions PRIMARY KEY,
        role_id BIGINT NOT NULL,
        permission_id BIGINT NOT NULL,
        allowed BIT NOT NULL CONSTRAINT DF_role_permissions_allowed DEFAULT (1),
        scope NVARCHAR(20) NOT NULL CONSTRAINT DF_role_permissions_scope DEFAULT ('none'),
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_role_permissions_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_role_permissions_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_role_permissions_role_permission' AND object_id = OBJECT_ID(N'dbo.role_permissions'))
    CREATE UNIQUE INDEX UX_role_permissions_role_permission ON dbo.role_permissions(role_id, permission_id);

IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_role_permissions_scope')
    ALTER TABLE dbo.role_permissions ADD CONSTRAINT CK_role_permissions_scope CHECK (scope IN ('all','team','self','none'));

IF OBJECT_ID(N'dbo.user_permissions', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.user_permissions (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_user_permissions PRIMARY KEY,
        user_id BIGINT NOT NULL,
        permission_id BIGINT NOT NULL,
        allowed BIT NOT NULL CONSTRAINT DF_user_permissions_allowed DEFAULT (1),
        scope NVARCHAR(20) NOT NULL CONSTRAINT DF_user_permissions_scope DEFAULT ('none'),
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_permissions_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_permissions_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_user_permissions_user_permission' AND object_id = OBJECT_ID(N'dbo.user_permissions'))
    CREATE UNIQUE INDEX UX_user_permissions_user_permission ON dbo.user_permissions(user_id, permission_id);

IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_user_permissions_scope')
    ALTER TABLE dbo.user_permissions ADD CONSTRAINT CK_user_permissions_scope CHECK (scope IN ('all','team','self','none'));

IF OBJECT_ID(N'dbo.audit_logs', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.audit_logs (
        id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_audit_logs PRIMARY KEY,
        user_id BIGINT NULL,
        acao NVARCHAR(120) NOT NULL,
        entidade NVARCHAR(120) NOT NULL,
        entidade_id NVARCHAR(100) NULL,
        payload_json NVARCHAR(MAX) NULL,
        ip_address NVARCHAR(45) NULL,
        created_at DATETIME2(0) NOT NULL CONSTRAINT DF_audit_logs_created_at DEFAULT (SYSUTCDATETIME()),
        updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_audit_logs_updated_at DEFAULT (SYSUTCDATETIME())
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_audit_logs_user_id' AND object_id = OBJECT_ID(N'dbo.audit_logs'))
    CREATE INDEX IX_audit_logs_user_id ON dbo.audit_logs(user_id);

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_users_roles')
    ALTER TABLE dbo.users ADD CONSTRAINT FK_users_roles FOREIGN KEY (role_id) REFERENCES dbo.roles(id);

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_users_equipes')
    ALTER TABLE dbo.users ADD CONSTRAINT FK_users_equipes FOREIGN KEY (equipe_id) REFERENCES dbo.equipes(id);

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_equipes_supervisor_user')
    ALTER TABLE dbo.equipes ADD CONSTRAINT FK_equipes_supervisor_user FOREIGN KEY (supervisor_user_id) REFERENCES dbo.users(id);

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_role_permissions_role')
    ALTER TABLE dbo.role_permissions ADD CONSTRAINT FK_role_permissions_role FOREIGN KEY (role_id) REFERENCES dbo.roles(id) ON DELETE CASCADE;

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_role_permissions_permission')
    ALTER TABLE dbo.role_permissions ADD CONSTRAINT FK_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES dbo.permissions(id) ON DELETE CASCADE;

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_user_permissions_user')
    ALTER TABLE dbo.user_permissions ADD CONSTRAINT FK_user_permissions_user FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE;

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_user_permissions_permission')
    ALTER TABLE dbo.user_permissions ADD CONSTRAINT FK_user_permissions_permission FOREIGN KEY (permission_id) REFERENCES dbo.permissions(id) ON DELETE CASCADE;

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_audit_logs_user')
    ALTER TABLE dbo.audit_logs ADD CONSTRAINT FK_audit_logs_user FOREIGN KEY (user_id) REFERENCES dbo.users(id);

-- Roles base
IF NOT EXISTS (SELECT 1 FROM dbo.roles WHERE slug = 'master')
    INSERT INTO dbo.roles (nome, slug, nivel, is_system) VALUES ('Master', 'master', 100, 1);
IF NOT EXISTS (SELECT 1 FROM dbo.roles WHERE slug = 'supervisor')
    INSERT INTO dbo.roles (nome, slug, nivel, is_system) VALUES ('Supervisor', 'supervisor', 50, 1);
IF NOT EXISTS (SELECT 1 FROM dbo.roles WHERE slug = 'operador')
    INSERT INTO dbo.roles (nome, slug, nivel, is_system) VALUES ('Operador', 'operador', 10, 1);

-- Permissoes base (menu + acoes)
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'dashboard.view')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Ver dashboard', 'dashboard.view', 'dashboard', 'Acessar dashboard');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'users.view')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Ver usuarios', 'users.view', 'users', 'Listar usuarios');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'users.create')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Criar usuarios', 'users.create', 'users', 'Criar usuarios');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'users.edit')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Editar usuarios', 'users.edit', 'users', 'Editar usuarios');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'users.delete')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Excluir usuarios', 'users.delete', 'users', 'Excluir usuarios');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'equipes.view')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Ver equipes', 'equipes.view', 'equipes', 'Listar equipes');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'equipes.edit')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Editar equipes', 'equipes.edit', 'equipes', 'Editar equipes');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'config.view')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Ver configuracoes', 'config.view', 'config', 'Acessar configuracoes');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'config.edit')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Editar configuracoes', 'config.edit', 'config', 'Editar configuracoes');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta.v8.view')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Ver consulta V8', 'consulta.v8.view', 'consulta_v8', 'Acessar modulo V8');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta.v8.batch.send')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Enviar lote V8', 'consulta.v8.batch.send', 'consulta_v8', 'Enviar consulta em lote V8');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta.v8.batch.delete')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Excluir lote V8', 'consulta.v8.batch.delete', 'consulta_v8', 'Excluir lote V8');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta.presenca.view')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Ver consulta Presenca', 'consulta.presenca.view', 'consulta_presenca', 'Acessar modulo Presenca');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta.presenca.batch.send')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Enviar lote Presenca', 'consulta.presenca.batch.send', 'consulta_presenca', 'Enviar consulta em lote Presenca');
IF NOT EXISTS (SELECT 1 FROM dbo.permissions WHERE slug = 'consulta.presenca.batch.delete')
    INSERT INTO dbo.permissions (nome, slug, modulo, descricao) VALUES ('Excluir lote Presenca', 'consulta.presenca.batch.delete', 'consulta_presenca', 'Excluir lote Presenca');

-- Equipe padrao
IF NOT EXISTS (SELECT 1 FROM dbo.equipes WHERE nome = 'Equipe Geral')
    INSERT INTO dbo.equipes (nome, descricao, ativo) VALUES ('Equipe Geral', 'Equipe inicial padrao do sistema', 1);

DECLARE @roleMasterId BIGINT = (SELECT TOP 1 id FROM dbo.roles WHERE slug = 'master');
DECLARE @roleSupervisorId BIGINT = (SELECT TOP 1 id FROM dbo.roles WHERE slug = 'supervisor');
DECLARE @roleOperadorId BIGINT = (SELECT TOP 1 id FROM dbo.roles WHERE slug = 'operador');
DECLARE @equipeGeralId BIGINT = (SELECT TOP 1 id FROM dbo.equipes WHERE nome = 'Equipe Geral');

-- Usuario inicial (bcrypt de 899605)
IF NOT EXISTS (SELECT 1 FROM dbo.users WHERE login = 'andrefelipe')
BEGIN
    INSERT INTO dbo.users (nome, login, email, [password], equipe_id, role_id, ativo)
    VALUES ('ANDREFELIPE', 'andrefelipe', 'andrefelipe@europa45.local', '$2y$10$9A8mw2645HNL2FyoNy0ZR.f6YxucDgkIkwYubMkEOH9K3PQDOPMmK', @equipeGeralId, @roleMasterId, 1);
END
ELSE
BEGIN
    UPDATE dbo.users
       SET [password] = '$2y$10$9A8mw2645HNL2FyoNy0ZR.f6YxucDgkIkwYubMkEOH9K3PQDOPMmK',
           role_id = COALESCE(role_id, @roleMasterId),
           equipe_id = COALESCE(equipe_id, @equipeGeralId),
           ativo = 1,
           updated_at = SYSUTCDATETIME()
     WHERE login = 'andrefelipe';
END;

DECLARE @andrefelipeId BIGINT = (SELECT TOP 1 id FROM dbo.users WHERE login = 'andrefelipe');
IF @andrefelipeId IS NOT NULL
BEGIN
    UPDATE dbo.equipes
       SET supervisor_user_id = COALESCE(supervisor_user_id, @andrefelipeId),
           updated_at = SYSUTCDATETIME()
     WHERE id = @equipeGeralId;
END;

-- Grants Master = tudo/all
INSERT INTO dbo.role_permissions (role_id, permission_id, allowed, scope)
SELECT @roleMasterId, p.id, 1, 'all'
FROM dbo.permissions p
WHERE @roleMasterId IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dbo.role_permissions rp
      WHERE rp.role_id = @roleMasterId AND rp.permission_id = p.id
  );

-- Grants Supervisor = gestao da equipe + consultas (sem config global)
INSERT INTO dbo.role_permissions (role_id, permission_id, allowed, scope)
SELECT @roleSupervisorId, p.id, 1,
       CASE WHEN p.slug IN ('dashboard.view') THEN 'team'
            WHEN p.slug LIKE 'consulta.%' THEN 'team'
            WHEN p.slug LIKE 'users.%' OR p.slug LIKE 'equipes.%' THEN 'team'
            ELSE 'team'
       END
FROM dbo.permissions p
WHERE @roleSupervisorId IS NOT NULL
  AND p.slug IN (
      'dashboard.view',
      'users.view','users.create','users.edit',
      'equipes.view','equipes.edit',
      'consulta.v8.view','consulta.v8.batch.send','consulta.v8.batch.delete',
      'consulta.presenca.view','consulta.presenca.batch.send','consulta.presenca.batch.delete'
  )
  AND NOT EXISTS (
      SELECT 1 FROM dbo.role_permissions rp
      WHERE rp.role_id = @roleSupervisorId AND rp.permission_id = p.id
  );

-- Grants Operador = usa consultas e dashboard, sem configuracao/gestao
INSERT INTO dbo.role_permissions (role_id, permission_id, allowed, scope)
SELECT @roleOperadorId, p.id, 1,
       CASE WHEN p.slug = 'dashboard.view' THEN 'self' ELSE 'self' END
FROM dbo.permissions p
WHERE @roleOperadorId IS NOT NULL
  AND p.slug IN (
      'dashboard.view',
      'consulta.v8.view','consulta.v8.batch.send',
      'consulta.presenca.view','consulta.presenca.batch.send'
  )
  AND NOT EXISTS (
      SELECT 1 FROM dbo.role_permissions rp
      WHERE rp.role_id = @roleOperadorId AND rp.permission_id = p.id
  );

COMMIT;

SELECT 
    (SELECT COUNT(*) FROM dbo.roles) AS roles_count,
    (SELECT COUNT(*) FROM dbo.permissions) AS permissions_count,
    (SELECT COUNT(*) FROM dbo.equipes) AS equipes_count,
    (SELECT COUNT(*) FROM dbo.users) AS users_count,
    (SELECT COUNT(*) FROM dbo.role_permissions) AS role_permissions_count;

SELECT TOP 10 id, nome, login, email, role_id, equipe_id, ativo, created_at
FROM dbo.users
ORDER BY id;
