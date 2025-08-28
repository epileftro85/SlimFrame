# Improved View System Documentation

## Overview

The SlimFrame view system has been enhanced with a lightweight template engine that maintains simplicity while providing better organization and security.

## Key Improvements

### 1. **Separation of Concerns**
- Views are now separate from business logic
- Template helpers for common operations
- Consistent data passing

### 2. **Security**
- Automatic HTML escaping
- XSS protection built-in
- Safe variable extraction

### 3. **Maintainability**
- Reusable layout system
- Template inheritance
- Organized file structure

### 4. **Developer Experience**
- Helper functions for common tasks
- Clear error messages
- Easy debugging

## Usage Examples

### Basic View Rendering

```php
// In your controller
use App\Utils\View;

class ExampleController extends Controller
{
    public function index()
    {
        View::make()
            ->with(['title' => 'My Page', 'users' => $users])
            ->display('users/index', 'layout');
    }
}
```

### Using Helper Functions

```php
// Include helpers in your bootstrap or controller
require_once 'app/Utils/ViewHelpers.php';

// Simple rendering
function index()
{
    display('users/index', [
        'title' => 'Users',
        'users' => User::all()
    ]);
}
```

### View Data

```php
// Pass data to views
view('user/profile', [
    'user' => $user,
    'posts' => $user->posts,
    'is_admin' => $user->isAdmin()
]);
```

### In Views

```php
<!-- app/Views/user/profile.php -->
<div class="profile">
    <h1>Welcome, <?php echo escape($user->name); ?>!</h1>

    <?php if ($is_admin): ?>
        <div class="admin-panel">
            <p>You have admin privileges.</p>
        </div>
    <?php endif; ?>

    <h2>Recent Posts</h2>
    <?php foreach ($posts as $post): ?>
        <article>
            <h3><?php echo escape($post->title); ?></h3>
            <p><?php echo escape($post->content); ?></p>
        </article>
    <?php endforeach; ?>
</div>
```

## File Structure

```
app/Views/
├── layout.php          # Main layout template
├── error.php           # Error page template
├── home.php            # Home page
├── users/
│   ├── index.php       # Users list
│   └── profile.php     # User profile
└── partials/
    ├── header.php      # Reusable header
    └── footer.php      # Reusable footer
```

## Template Helpers

### Escaping
```php
<?php echo escape($variable); ?>
```

### Assets
```php
<link rel="stylesheet" href="<?php echo asset('css/app.css'); ?>">
<script src="<?php echo asset('js/app.js'); ?>"></script>
```

### URLs
```php
<a href="<?php echo url('users/profile'); ?>">Profile</a>
```

## Flash Messages

### Setting Flash Messages

```php
// In your controller
use App\Utils\Flash;

// Direct usage
Flash::success('User created successfully!');
Flash::error('Failed to save data');
Flash::warning('Session expiring soon');
Flash::info('Welcome back!');

// Or using helper functions
flash_success('Profile updated!');
flash_error('Validation failed');
flash_warning('Please review your input');
flash_info('New feature available');
```

### Displaying Flash Messages

Flash messages are automatically displayed in the layout:

```php
<!-- This is automatically included in layout.php -->
<?php include __DIR__ . '/partials/flash_messages.php'; ?>
```

### Features

- **Multiple message types**: success, error, warning, info
- **Styled components**: Consistent Tailwind CSS styling
- **Dismissible**: Click to close messages
- **Icons**: Appropriate icons for each message type
- **Accessible**: Proper ARIA attributes
- **Auto-expiry**: Messages are cleared after display

### Controller Examples

```php
class UserController extends Controller
{
    public function store()
    {
        // Process form...
        if ($success) {
            flash_success('User created successfully!');
            return redirect('/users');
        } else {
            flash_error('Failed to create user');
            return redirect()->back();
        }
    }

    public function update()
    {
        // Update logic...
        flash_success('Profile updated!');
        return redirect('/profile');
    }
}
```

## Layout System

### Basic Layout Usage

```php
// layout.php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'App'; ?></title>
</head>
<body>
    <nav>
        <!-- Navigation content -->
    </nav>

    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <footer>
        <!-- Footer content -->
    </footer>
</body>
</html>
```

### Advanced Layout with Sections

```php
// In a view file
<?php $this->startSection('head'); ?>
<style>
    .custom { color: red; }
</style>
<?php $this->endSection('head'); ?>

<?php $this->startSection('scripts'); ?>
<script>
    console.log('Page specific script');
</script>
<?php $this->endSection('scripts'); ?>

<div class="content">
    <!-- Page content -->
</div>
```

## Migration Guide

### From Old System

**Old way:**
```php
// Controller
$child_view = ROOT_PATH . 'app/Views/home.php';
$title = "Home";
include ROOT_PATH . 'app/Views/layout.php';
```

**New way:**
```php
// Controller
display('home', ['title' => 'Home']);
```

### View File Changes

**Old view:**
```php
<div>
    <h1><?php echo $title; ?></h1>
    <p>Welcome to <?php echo $site_name; ?>!</p>
</div>
```

**New view:**
```php
<div>
    <h1><?php echo escape($title); ?></h1>
    <p>Welcome to <?php echo escape($site_name); ?>!</p>
</div>
```

## Best Practices

### 1. Always Escape Output
```php
<!-- Good -->
<h1><?php echo escape($title); ?></h1>

<!-- Bad -->
<h1><?php echo $title; ?></h1>
```

### 2. Use Meaningful Variable Names
```php
// Good
display('user/profile', ['user' => $user, 'posts' => $posts]);

// Bad
display('user/profile', ['data' => $user, 'list' => $posts]);
```

### 3. Keep Views Simple
```php
// Good - Simple presentation logic
<?php foreach ($users as $user): ?>
    <div class="user"><?php echo escape($user->name); ?></div>
<?php endforeach; ?>

// Bad - Business logic in views
<?php
$activeUsers = array_filter($users, function($u) { return $u->isActive(); });
foreach ($activeUsers as $user) {
    // ...
}
?>
```

### 4. Use Partials for Reusable Components
```php
// In a view
<?php $this->include('partials/user_card', ['user' => $user]); ?>
```

## Performance Considerations

- Views are cached in memory during request
- Template helpers are lightweight
- No external dependencies
- Minimal overhead compared to full template engines

## Security Features

- Automatic HTML escaping
- XSS protection
- Safe variable extraction
- Path traversal protection in view loading

This system provides a good balance between simplicity and functionality without adding complexity to your application.
