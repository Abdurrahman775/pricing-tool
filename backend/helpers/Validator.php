<?php
declare(strict_types=1);

class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label = null): self
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            $this->errors[$field][] = ($label ?? $field) . ' is required';
        }
        return $this;
    }

    public function email(string $field, string $value): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Invalid email address';
        }
        return $this;
    }

    public function minLength(string $field, string $value, int $min): self
    {
        if (mb_strlen($value) < $min) {
            $this->errors[$field][] = "Must be at least {$min} characters";
        }
        return $this;
    }

    public function maxLength(string $field, string $value, int $max): self
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = "Must not exceed {$max} characters";
        }
        return $this;
    }

    public function numeric(string $field, mixed $value): self
    {
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field][] = 'Must be a number';
        }
        return $this;
    }

    public function inList(string $field, string $value, array $allowed): self
    {
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field][] = 'Invalid value selected';
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function get(string $field): ?array
    {
        return $this->errors[$field] ?? null;
    }
}
