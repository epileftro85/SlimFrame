<?php
namespace App\Utils;

class Response
{
    /**
     * Send a JSON response
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send a success response
     */
    public static function success(string $message = 'Success', array $data = [], int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send an error response
     */
    public static function error(string $message = 'Error', array $errors = [], int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Send validation errors
     */
    public static function validationErrors(array $errors, string $message = 'Validation failed'): void
    {
        self::error($message, $errors, 422);
    }

    /**
     * Redirect with optional flash message
     */
    public static function redirect(string $url, string $message = null, string $type = 'success'): void
    {
        if ($message) {
            // Use Flash system for consistent message handling
            $flashClass = 'App\\Utils\\Flash';
            if (class_exists($flashClass)) {
                $flashClass::set($message, $type);
            } else {
                // Fallback to session if Flash class not available
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['flash'] = [
                    'message' => $message,
                    'type' => $type
                ];
            }
        }

        header("Location: {$url}");
        exit;
    }

    /**
     * Send HTML error page
     */
    public static function htmlError(string $message, int $statusCode = 400, array $errors = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/html');

        $errorHtml = self::buildErrorHtml($message, $errors);
        echo $errorHtml;
        exit;
    }

    /**
     * Check if request expects JSON response
     */
    public static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return strpos($accept, 'application/json') !== false ||
               strpos($contentType, 'application/json') !== false ||
               isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Build HTML error page
     */
    private static function buildErrorHtml(string $message, array $errors = []): string
    {
        // Extract variables for the view
        $viewMessage = $message;
        $viewErrors = $errors;

        // Start output buffering
        ob_start();

        // Include the error view
        $errorViewPath = __DIR__ . '/../Views/error.php';
        if (file_exists($errorViewPath)) {
            include $errorViewPath;
        } else {
            // Fallback to inline HTML if view file doesn't exist
            echo self::getFallbackErrorHtml($message, $errors);
        }

        // Get the buffered content
        return ob_get_clean();
    }

    /**
     * Fallback HTML error page (original inline version)
     */
    private static function getFallbackErrorHtml(string $message, array $errors = []): string
    {
        $errorList = '';
        if (!empty($errors)) {
            $errorList = '<ul class="error-list">';
            foreach ($errors as $field => $fieldErrors) {
                if (is_array($fieldErrors)) {
                    foreach ($fieldErrors as $error) {
                        $errorList .= "<li><strong>{$field}:</strong> {$error}</li>";
                    }
                } else {
                    $errorList .= "<li><strong>{$field}:</strong> {$fieldErrors}</li>";
                }
            }
            $errorList .= '</ul>';
        }

        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #e74c3c; margin-bottom: 20px; }
                .error-message { color: #333; margin-bottom: 20px; }
                .error-list { background: #f8f8f8; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c; }
                .error-list li { margin-bottom: 5px; }
                .back-link { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
                .back-link:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1 class='error-title'>Error</h1>
                <div class='error-message'>{$message}</div>
                {$errorList}
                <a href='/' class='back-link'>← Back to Home</a>
            </div>
        </body>
        </html>
        ";
    }
}
