SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS(SELECT *
              FROM sys.objects
              WHERE object_id = OBJECT_ID(N'[#__proofreader_typos]') AND type IN (N'U'))
  BEGIN
    CREATE TABLE [#__proofreader_typos] (
      [id] [BIGINT] IDENTITY (1, 1) NOT NULL,
      [typo_text] [NVARCHAR](MAX) NOT NULL,
      [typo_prefix] [NVARCHAR](MAX) NOT NULL DEFAULT '',
      [typo_suffix] [NVARCHAR](MAX) NOT NULL DEFAULT '',
      [typo_comment] [NVARCHAR](MAX) NOT NULL DEFAULT '',
      [page_url] [NVARCHAR](255) NOT NULL DEFAULT '',
      [page_title] [NVARCHAR](255) NOT NULL DEFAULT '',
      [page_language] [NVARCHAR](255) NOT NULL DEFAULT '',
      [created] DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      [created_by] [BIGINT] NOT NULL DEFAULT '0',
      [created_by_ip] [NVARCHAR](39) NOT NULL DEFAULT '',
      [created_by_name] [NVARCHAR](255) NOT NULL DEFAULT '',
      CONSTRAINT [PK_#__proofreader_id] PRIMARY KEY CLUSTERED
        ([id] ASC)
        WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
    );
  END;

IF NOT EXISTS(SELECT *
              FROM sys.indexes
              WHERE object_id = OBJECT_ID(N'[#__proofreader_typos]') AND name = N'idx_created_by')
  BEGIN
    CREATE NONCLUSTERED INDEX [idx_created_by] ON [#__proofreader_typos] ([created_by] ASC)
      WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
  END;

IF NOT EXISTS(SELECT *
              FROM sys.indexes
              WHERE object_id = OBJECT_ID(N'[#__proofreader_typos]') AND name = N'idx_page_language')
  BEGIN
    CREATE NONCLUSTERED INDEX [idx_language] ON [#__proofreader_typos] ([page_language] ASC)
      WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
  END;
