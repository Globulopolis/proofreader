CREATE TABLE IF NOT EXISTS "#__proofreader_typos" (
  "id" serial NOT NULL,
  "typo_text" text NOT NULL,
  "typo_prefix" text DEFAULT '' NOT NULL,
  "typo_suffix" text DEFAULT '' NOT NULL,
  "typo_comment" text DEFAULT '' NOT NULL,
  "page_url" character varying(255) DEFAULT '' NOT NULL,
  "page_title" character varying(255) DEFAULT '' NOT NULL,
  "page_language" character varying(255) DEFAULT '' NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_ip" character varying(39) DEFAULT '' NOT NULL,
  "created_by_name" character varying(255) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__proofreader_idx_created_by" ON "#__proofreader_typos" ("created_by");
CREATE INDEX "#__proofreader_idx_page_language" ON "#__proofreader_typos" ("page_language");
