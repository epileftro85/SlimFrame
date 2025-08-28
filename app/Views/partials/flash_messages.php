<?php
/**
 * Flash Message Component
 *
 * Displays flash messages in a clean, styled format
 * Supports multiple message types with appropriate styling
 */

// Get flash messages if Flash class exists
$flashMessages = [];
if (class_exists('App\\Utils\\Flash')) {
    $flashClass = 'App\\Utils\\Flash';
    if ($flashClass::has()) {
        $flash = $flashClass::get();
        if ($flash) {
            $flashMessages[] = $flash;
        }
    }
}

// If no flash messages, don't render anything
if (empty($flashMessages)) {
    return;
}

// Define styles for different message types
$messageStyles = [
    'success' => [
        'container' => 'bg-green-50 border-green-200 text-green-800',
        'icon' => 'text-green-400',
        'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z'
    ],
    'error' => [
        'container' => 'bg-red-50 border-red-200 text-red-800',
        'icon' => 'text-red-400',
        'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z'
    ],
    'warning' => [
        'container' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'icon' => 'text-yellow-400',
        'iconPath' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z'
    ],
    'info' => [
        'container' => 'bg-blue-50 border-blue-200 text-blue-800',
        'icon' => 'text-blue-400',
        'iconPath' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z'
    ]
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-3">
    <?php foreach ($flashMessages as $flash): ?>
        <?php
        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';
        $style = $messageStyles[$type] ?? $messageStyles['info'];
        ?>

        <div class="<?php echo $style['container']; ?> border px-4 py-3 rounded-lg shadow-sm" role="alert">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 <?php echo $style['icon']; ?>" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="<?php echo $style['iconPath']; ?>" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button"
                                class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 hover:bg-opacity-20 transition-colors duration-200 <?php echo $style['icon']; ?> hover:bg-current"
                                onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
