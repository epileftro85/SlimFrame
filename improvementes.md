# SlimFrame Improvement Plan

Based on a comprehensive review of the SlimFrame project, this document outlines critical issues, security concerns, and enhancement opportunities. The project is a lightweight PHP MVC framework with minimal dependencies, but several improvements are needed to ensure reliability, security, and maintainability.

## Critical Issues (Must Fix First)

### 1. [X] Missing Migration for `user_sessions` Table
**Problem**: The `UserSession` model references a `user_sessions` table, but no migration exists. This will cause database errors during login/logout.

**Evidence**:
```php
// app/Models/UserSession.php
protected static string $table = 'user_sessions';
```

**Flow Issue**:
- User logs in → `Auth::login()` creates UserSession record → Fails if table doesn't exist
- Auth flow: Login → Create session → Store in DB → Set cookie/session

**Fix**: Create migration `003_create_user_sessions_table.sql`:
```sql
CREATE TABLE IF NOT EXISTS user_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  ip VARCHAR(45),
  user_agent VARCHAR(255),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_token (user_id, token_hash),
  INDEX idx_expires (expires_at)
);
```

### 2. [X] Missing Fields in Users Table
**Problem**: Registration collects `company` and `website` but users table lacks these columns.

**Evidence**:
```php
// app/Controllers/AuthController.php
$data = ['name', 'last_name', 'company', 'website', 'email', 'password']
```

**Fix**: Add migration `004_add_user_fields.sql`:
```sql
ALTER TABLE users ADD COLUMN company VARCHAR(255), ADD COLUMN website VARCHAR(255);
```

## Security Improvements

### 3. [X] Hardcoded Secrets and Weak Defaults
**Problems**:
- `APP_KEY` defaults to 'dev-only-change-me' in `Auth.php`
- DB credentials default to 'seoanchor' (easily guessable)
- No environment file support

**Evidence**:
```php
// app/Config/Auth.php
private static function secret(): string {
    return getenv('APP_KEY') ?: 'dev-only-change-me';
}
```

**Fix**:
- Add `.env` support with `vlucas/phpdotenv`
- Generate secure random `APP_KEY` in production
- Use stronger default credentials or require explicit setup

### 4. [] Input Validation and Sanitization
**Problem**: No validation in controllers - direct use of `$_POST` data.

**Evidence**:
```php
// app/Controllers/AuthController.php
$email = $_POST['email'] ?? '';
```

**Fix**: Add validation layer:
- Email format validation
- Password strength requirements
- XSS protection beyond `htmlspecialchars`
- SQL injection prevention (already handled by PDO, but add input filtering)

### 5. [] Session Security
**Problems**:
- Session fixation possible (though `session_regenerate_id` is used)
- No session timeout enforcement
- Remember tokens don't rotate

**Fix**:
- Implement session timeout
- Rotate remember tokens on each use
- Add session metadata validation

## Code Quality and Architecture

### 6. [] Global State in Router
**Problem**: `$ROUTES` global array makes testing and modularity difficult.

**Evidence**:
```php
// app/Routes/Router.php
global $ROUTES;
$ROUTES = ['GET' => [], 'POST' => [], ...];
```

**Fix**: Refactor to use a Router class with dependency injection.

### 7. [] Error Handling
**Problems**:
- Raw PHP errors in views
- No centralized error handling
- No logging

**Evidence**:
```php
// app/Views/home.php
<?php echo $csrf; ?>
```

**Fix**:
- Add error templates
- Implement logging (Monolog)
- Use try-catch blocks in controllers

### 8. [] Model Improvements
**Problems**:
- No relationships
- Potential N+1 queries
- No eager loading

**Evidence**:
```php
// app/Models/Model.php - basic query methods
public static function where(array $where, ...): array
```

**Fix**:
- Add relationship methods (belongsTo, hasMany)
- Implement query builder for complex queries
- Add caching layer

### 9. [] View Layer
**Problems**:
- PHP in HTML (hard to maintain)
- No template inheritance beyond basic include
- No asset management

**Fix**:
- Introduce Twig or Blade templating
- Add asset pipeline
- Implement flash messages for user feedback

## Performance Optimizations

### 10. [] Database Optimizations
**Problems**:
- No indexes on frequently queried fields
- No connection pooling
- No query caching

**Evidence**:
```sql
-- users table lacks index on email
CREATE TABLE users (
  email VARCHAR(255) NOT NULL UNIQUE,
  -- No INDEX on email for fast lookups
```

**Fix**:
- Add indexes: `INDEX idx_email (email)`
- Implement Redis for session/caching
- Add database query profiling

### 11. [] Caching Strategy
**Problems**:
- No caching for views, queries, or config
- Static file caching not optimized

**Fix**:
- Add PSR-16 cache interface
- Cache user sessions and config
- Implement HTTP caching headers

## Feature Enhancements

### 12. [] Middleware Expansion
**Problems**:
- Only basic auth middleware
- No CORS, rate limiting, or logging middleware

**Fix**:
- Add CORS middleware
- Implement rate limiting
- Add request logging middleware

### 13. [] API Support
**Problem**: Only web routes, no JSON API endpoints.

**Fix**:
- Add API routes with JSON responses
- Implement content negotiation
- Add API authentication (Bearer tokens)

### 14. [] Testing and Quality Assurance
**Problems**:
- No tests
- No code quality tools

**Fix**:
- Add PHPUnit for unit tests
- Implement integration tests for routes
- Add PHPStan for static analysis
- Set up CI/CD pipeline

## DevOps and Deployment

### 15. [] Docker and Environment
**Problems**:
- No multi-stage builds
- No health checks for PHP
- Environment variables not properly managed

**Fix**:
- Optimize Dockerfile with multi-stage builds
- Add health checks
- Implement proper .env handling

### 16. [] Logging and Monitoring
**Problems**:
- No centralized logging
- No error tracking

**Fix**:
- Add structured logging
- Implement error tracking (Sentry)
- Add performance monitoring

## Implementation Priority

1. **High Priority (Critical)**: Fix missing migrations, security issues
2. **Medium Priority**: Input validation, error handling, basic caching
3. **Low Priority**: Advanced features, testing, monitoring

## Example Improved Auth Flow

**Current Flow**:
```
User submits login form
→ AuthController::doLogin()
→ Verify CSRF
→ Query User::where(['email' => $email])
→ Verify password
→ Auth::login() → Create UserSession
→ Redirect to /dashboard
```

**Improved Flow**:
```
User submits login form
→ Validate input (email format, password length)
→ AuthController::doLogin()
→ Verify CSRF
→ Query User::where(['email' => $email]) with caching
→ Verify password with rate limiting
→ Auth::login() → Create UserSession with IP/user-agent validation
→ Log successful login
→ Redirect to /dashboard with flash message
```

This plan addresses the core issues while maintaining the project's lightweight philosophy. Start with critical fixes, then layer on security and quality improvements.
