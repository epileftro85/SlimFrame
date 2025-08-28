<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <title><?php echo $title ?? 'SlimFrame'; ?></title>

    <?php if (isset($stylesheets) && is_array($stylesheets)): ?>
        <?php foreach ($stylesheets as $stylesheet): ?>
            <link rel="stylesheet" href="<?php echo $stylesheet; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-gray-900">
                        SlimFrame
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($user) && $user): ?>
                        <span class="text-sm text-gray-700">Welcome, <?php echo htmlspecialchars($user->name); ?></span>
                        <a href="/dashboard" class="text-blue-600 hover:text-blue-800">Dashboard</a>
                        <form method="POST" action="/logout" class="inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf ?? ''; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="/" class="text-blue-600 hover:text-blue-800">Home</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php include __DIR__ . '/partials/flash_messages.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> SlimFrame. Built with simplicity in mind.</p>
            </div>
        </div>
    </footer>

    <?php if (isset($javascript) && is_array($javascript)): ?>
        <?php foreach ($javascript as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>