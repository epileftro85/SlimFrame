<?php
namespace App\Requests;

abstract class Request
{
    protected array $data = [];
    protected array $errors = [];
    protected array $validated = [];

    public function __construct(array $data = null)
    {
        $this->data = $data ?? $_POST;
        $this->sanitize();
    }

    /**
     * Define validation rules for this request
     */
    abstract public function rules(): array;

    /**
     * Define custom error messages
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Validate the request data
     */
    public function validate(): bool
    {
        $this->errors = [];
        $this->validated = [];

        foreach ($this->rules() as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($fieldRules as $rule) {
                if (!$this->validateRule($field, $value, $rule)) {
                    break; // Stop validating this field if one rule fails
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get specific input value
     */
    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if field has errors
     */
    public function hasErrors(string $field = null): bool
    {
        return $field ? isset($this->errors[$field]) : !empty($this->errors);
    }

    /**
     * Get error message for field
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Sanitize input data
     */
    protected function sanitize(): void
    {
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes and normalize line endings
                $this->data[$key] = str_replace(["\0", "\r\n", "\r"], ["", "\n", "\n"], trim($value));
            }
        }
    }

    /**
     * Validate a single rule
     */
    protected function validateRule(string $field, $value, string $rule): bool
    {
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $ruleParam = $ruleParts[1] ?? null;

        $method = 'validate' . ucfirst($ruleName);
        if (method_exists($this, $method)) {
            $result = $this->$method($field, $value, $ruleParam);
            if (!$result) {
                return false;
            }
        } else {
            // Unknown rule
            $this->addError($field, "Unknown validation rule: {$ruleName}");
            return false;
        }

        // If validation passed, add to validated data
        if (!isset($this->validated[$field])) {
            $this->validated[$field] = $value;
        }

        return true;
    }

    /**
     * Add an error message
     */
    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    // Validation rule methods

    protected function validateRequired(string $field, $value, $param): bool
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, $this->getMessage($field, 'required', 'This field is required'));
            return false;
        }
        return true;
    }

    protected function validateEmail(string $field, $value, $param): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $this->getMessage($field, 'email', 'Please enter a valid email address'));
            return false;
        }
        return true;
    }

    protected function validateMin(string $field, $value, $param): bool
    {
        $min = (int)$param;
        if (is_string($value) && strlen($value) < $min) {
            $this->addError($field, $this->getMessage($field, 'min', "Must be at least {$min} characters"));
            return false;
        }
        if (is_numeric($value) && $value < $min) {
            $this->addError($field, $this->getMessage($field, 'min', "Must be at least {$min}"));
            return false;
        }
        return true;
    }

    protected function validateMax(string $field, $value, $param): bool
    {
        $max = (int)$param;
        if (is_string($value) && strlen($value) > $max) {
            $this->addError($field, $this->getMessage($field, 'max', "Must not exceed {$max} characters"));
            return false;
        }
        if (is_numeric($value) && $value > $max) {
            $this->addError($field, $this->getMessage($field, 'max', "Must not exceed {$max}"));
            return false;
        }
        return true;
    }

    protected function validateUrl(string $field, $value, $param): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $this->getMessage($field, 'url', 'Please enter a valid URL'));
            return false;
        }
        return true;
    }

    protected function validateRegex(string $field, $value, $param): bool
    {
        if (!preg_match($param, $value)) {
            $this->addError($field, $this->getMessage($field, 'regex', 'Invalid format'));
            return false;
        }
        return true;
    }

    protected function validateIn(string $field, $value, $param): bool
    {
        $allowed = explode(',', $param);
        if (!in_array($value, $allowed)) {
            $this->addError($field, $this->getMessage($field, 'in', 'Invalid value'));
            return false;
        }
        return true;
    }

    /**
     * Get custom message or default
     */
    protected function getMessage(string $field, string $rule, string $default): string
    {
        $messages = $this->messages();
        $key = "{$field}.{$rule}";
        return $messages[$key] ?? $default;
    }
}
