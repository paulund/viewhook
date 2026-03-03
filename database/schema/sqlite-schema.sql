CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "resource_id" varchar not null,
  "name" varchar not null,
  "email" varchar not null,
  "password" varchar not null,
  "avatar" varchar,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_resource_id_unique" on "users"("resource_id");
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "urls"(
  "id" integer primary key autoincrement not null,
  "resource_id" varchar not null,
  "user_id" integer not null,
  "name" varchar not null,
  "description" text,
  "last_request_at" datetime,
  "forward_to_url" text,
  "forward_method" varchar not null default 'POST',
  "forward_headers" text,
  "notify_email" tinyint(1) not null default '0',
  "notify_slack" tinyint(1) not null default '0',
  "slack_webhook_url" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "urls_user_id_created_at_index" on "urls"(
  "user_id",
  "created_at"
);
CREATE UNIQUE INDEX "urls_resource_id_unique" on "urls"("resource_id");
CREATE TABLE IF NOT EXISTS "requests"(
  "id" integer primary key autoincrement not null,
  "resource_id" varchar not null,
  "url_id" integer not null,
  "method" varchar not null,
  "path" varchar not null default '/',
  "content_type" varchar,
  "content_length" integer not null default '0',
  "headers" text not null,
  "query_params" text,
  "body" text,
  "ip_address" varchar,
  "user_agent" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("url_id") references "urls"("id") on delete cascade
);
CREATE INDEX "requests_url_id_created_at_index" on "requests"(
  "url_id",
  "created_at"
);
CREATE INDEX "requests_method_index" on "requests"("method");
CREATE UNIQUE INDEX "requests_resource_id_unique" on "requests"("resource_id");
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "webhook_forwards"(
  "id" integer primary key autoincrement not null,
  "resource_id" varchar not null,
  "request_id" integer not null,
  "url_id" integer not null,
  "target_url" text not null,
  "method" varchar not null default 'POST',
  "status_code" integer,
  "response_body" text,
  "response_time_ms" integer,
  "error" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("request_id") references "requests"("id") on delete cascade,
  foreign key("url_id") references "urls"("id") on delete cascade
);
CREATE INDEX "webhook_forwards_request_id_index" on "webhook_forwards"(
  "request_id"
);
CREATE INDEX "webhook_forwards_url_id_index" on "webhook_forwards"("url_id");
CREATE INDEX "webhook_forwards_created_at_index" on "webhook_forwards"(
  "created_at"
);
CREATE UNIQUE INDEX "webhook_forwards_resource_id_unique" on "webhook_forwards"(
  "resource_id"
);
CREATE TABLE IF NOT EXISTS "notifications"(
  "id" varchar not null,
  "type" varchar not null,
  "notifiable_type" varchar not null,
  "notifiable_id" integer not null,
  "data" text not null,
  "read_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  primary key("id")
);
CREATE INDEX "notifications_notifiable_type_notifiable_id_index" on "notifications"(
  "notifiable_type",
  "notifiable_id"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_01_31_083342_create_urls_table',1);
INSERT INTO migrations VALUES(5,'2026_01_31_083355_create_requests_table',1);
INSERT INTO migrations VALUES(6,'2026_02_01_104714_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(7,'2026_02_01_110002_create_webhook_forwards_table',1);
INSERT INTO migrations VALUES(8,'2026_02_22_185332_create_notifications_table',1);
INSERT INTO migrations VALUES(9,'2026_03_03_000000_drop_email_verified_at_from_users_table',1);
