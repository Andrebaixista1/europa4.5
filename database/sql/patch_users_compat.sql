IF COL_LENGTH('dbo.users', 'remember_token') IS NULL
BEGIN
    ALTER TABLE dbo.users ADD remember_token NVARCHAR(100) NULL;
END;

IF COL_LENGTH('dbo.users', 'email_verified_at') IS NULL
BEGIN
    ALTER TABLE dbo.users ADD email_verified_at DATETIME2 NULL;
END;
