# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2025-10-29

### 🚀 Major Updates

#### Upgraded to Latest Stable Versions
- **PHP 8.2 → PHP 8.4** (Latest stable release)
- **Symfony 7.0 → Symfony 7.2** (Latest stable release)
- **API Platform 3.3 → API Platform 3.4**
- **Doctrine ORM 2.20 → Doctrine ORM 3.5**
- **PHPUnit 10.5 → PHPUnit 11.5**

### ✅ What Changed

#### Dependencies Updated
- `api-platform/core`: ^3.3.15 → ^3.4.17
- `doctrine/dbal`: 3.10.3 → 4.3.4
- `doctrine/orm`: 2.20.7 → 3.5.3
- `doctrine/persistence`: 3.4.3 → 4.1.1
- `phpunit/phpunit`: 10.5.58 → 11.5.42
- All Symfony components: 7.0.* → 7.2.*

#### Removed Dependencies
- `doctrine/cache` (deprecated)
- `doctrine/common` (deprecated)
- `symfony/polyfill-php72` (no longer needed)
- `symfony/polyfill-php80` (no longer needed)

#### Added Dependencies
- `staabm/side-effects-detector`: 1.0.5
- `symfony/type-info`: 7.2.8

### 🔧 Configuration Updates

#### Dockerfile
- Updated base image from `php:8.2-fpm` to `php:8.4-fpm`

#### composer.json
- Updated PHP requirement: `>=8.2` → `>=8.4`
- Updated all Symfony constraints: `7.0.*` → `7.2.*`
- Updated PHPUnit constraint: `^10.5` → `^11.5`

#### phpunit.xml
- Updated SYMFONY_PHPUNIT_VERSION: `10.5` → `11.5`

### ✅ Verification

All tests passing:
```
PHPUnit 11.5.42 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.14
Configuration: /var/www/html/phpunit.xml

..                                                2 / 2 (100%)

Time: 00:00.869, Memory: 40.50 MB

OK (2 tests, 7 assertions)
```

### 🔐 Security

No security vulnerabilities found after update.

### 📚 Documentation

- Updated README.md with new version numbers
- Updated SUMMARY.md with new version numbers
- Updated technology stack references across all documentation

---

## [1.0.0] - 2025-10-29

### 🎉 Initial Release

#### Features
- Symfony 7.0 API Platform application
- PHP 8.2 with Docker support
- Health check endpoint at `/health`
- Complete PHPUnit test suite
- Helper scripts for common operations:
  - `./php` - Run commands in PHP container
  - `./composer` - Run Composer commands
  - `./console` - Run Symfony console
  - `./test` - Run PHPUnit tests
- Comprehensive documentation

#### Technology Stack
- PHP 8.2
- Symfony 7.0
- API Platform 3.3
- Doctrine ORM 2.20
- PHPUnit 10.5
- PostgreSQL 15
- Docker & Docker Compose

