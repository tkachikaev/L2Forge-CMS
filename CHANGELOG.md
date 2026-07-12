# Changelog

## 0.2.0 - 2026-07-12

### Added

- Separate administrator model, database table, session guard and protected `/admin` area.
- Administrator login and logout with session regeneration and invalidation.
- `php artisan l2cms:admin-create` command for secure creation of the first administrator.
- Rate limiting by normalized email and IP address.
- Security log for successful, failed, inactive and throttled login attempts.
- Argon2id as the default CMS password hashing driver.
- Security headers and `noindex` metadata for the administration area.
- Initial administration dashboard and responsive administration design.
- Automated feature tests for administrator authentication.

### Security

- No default administrator account or password is included in seed data.
- Authentication errors do not reveal whether an administrator email exists.
- Admin pages are non-cacheable and protected from framing.

## 0.1.1 - 2026-07-12

- Fixed clean Windows installation, PowerShell encoding, required directories and Laravel 13 dependencies.
