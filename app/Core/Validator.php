<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Rule-string validator.
 *
 * Rules are declared per field as a list, e.g. ['required', 'max:120', 'url'].
 * Content types in config/content_types.php reuse the same vocabulary, which
 * is what lets one generic controller validate every resource.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /**
     * @param array<string, mixed>            $data
     * @param array<string, array<int,string>> $rules
     * @param array<string, string>           $labels
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly array $labels = []
    ) {
    }

    public function passes(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

                // Only 'required' cares about an absent value; every other rule
                // treats blank as "nothing to check" so optional fields pass.
                if ($name !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

                $error = $this->check($name, $field, $value, $parameter);

                if ($error !== null) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    private function check(string $rule, string $field, mixed $value, ?string $parameter): ?string
    {
        $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

        return match ($rule) {
            'required' => (is_array($value) ? $value === [] : trim((string) $value) === '')
                ? "{$label} is required."
                : null,

            'max' => mb_strlen((string) $value) > (int) $parameter
                ? "{$label} may not be longer than {$parameter} characters."
                : null,

            'min' => mb_strlen((string) $value) < (int) $parameter
                ? "{$label} must be at least {$parameter} characters."
                : null,

            'email' => !filter_var((string) $value, FILTER_VALIDATE_EMAIL)
                ? "{$label} must be a valid email address."
                : null,

            'url' => !filter_var((string) $value, FILTER_VALIDATE_URL)
                ? "{$label} must be a valid URL."
                : null,

            'integer' => !filter_var((string) $value, FILTER_VALIDATE_INT) && (string) $value !== '0'
                ? "{$label} must be a whole number."
                : null,

            'numeric' => !is_numeric((string) $value)
                ? "{$label} must be a number."
                : null,

            'between' => $this->between($label, $value, $parameter),

            'in' => !in_array((string) $value, explode(',', (string) $parameter), true)
                ? "{$label} is not a valid selection."
                : null,

            'confirmed' => ($this->data[$field . '_confirmation'] ?? null) !== $value
                ? "{$label} confirmation does not match."
                : null,

            default => null,
        };
    }

    private function between(string $label, mixed $value, ?string $parameter): ?string
    {
        [$min, $max] = array_pad(explode(',', (string) $parameter, 2), 2, null);
        $number = (float) $value;

        if ($number < (float) $min || $number > (float) $max) {
            return "{$label} must be between {$min} and {$max}.";
        }

        return null;
    }
}
