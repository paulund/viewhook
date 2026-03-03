# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-03-01

### Added

- **Webhook URL management** — create, update, and delete webhook endpoints identified by UUID or custom slug
- **Request capture** — capture all HTTP methods (GET, POST, PUT, PATCH, DELETE, etc.) with full headers, body, and query parameters
- **Real-time updates** — live request feed via WebSockets (Laravel Reverb) without polling
- **Request inspection** — view full request details including headers, body, query params, and metadata
- **Webhook forwarding** — forward captured requests to any target URL with configurable method and headers
- **Slack notifications** — send a Slack message for every captured request via webhook
- **Email notifications** — receive an email for every captured request
- **Export** — export captured requests as CSV or JSON
- **Custom slugs** — give your webhook URL a memorable name (e.g. `/catch/my-project`)
- **Rate limiting** — configurable per-URL hourly request limits (`VIEWHOOK_RATE_LIMIT`)
- **Data retention** — automatic cleanup of old requests (`VIEWHOOK_RETENTION_HOURS`)
- **URL limits** — configurable maximum URLs per user (`VIEWHOOK_MAX_URLS`)
- **Email/password authentication** — standard Laravel Breeze auth (register, login, password reset)
- **SQLite by default** — zero external database dependency for self-hosting
- **Docker support** — single-container deployment with `docker compose up`
- **100% test coverage** — Pest PHP feature and unit tests with PHPStan level 8 static analysis
