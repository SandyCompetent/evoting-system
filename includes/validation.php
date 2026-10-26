<?php
declare(strict_types=1);
/**
 * Server-side validation helpers shared by the public and dashboard forms.
 */

/**
 * Parse an HTML datetime-local value into a MySQL DATETIME string, or null when
 * it is empty or malformed. The strict format check prevents strtotime() from
 * silently turning garbage into the 1970 epoch.
 */
function parse_datetime_local(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s'] as $format) {
        $parsed = \DateTimeImmutable::createFromFormat('!' . $format, $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($parsed instanceof \DateTimeImmutable
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
        ) {
            return $parsed->format('Y-m-d H:i:s');
        }
    }

    return null;
}

/**
 * True when a stored DATETIME string is in the future.
 */
function is_future_datetime(string $value): bool
{
    $timestamp = strtotime($value);

    return $timestamp !== false && $timestamp > time();
}

/**
 * Enforce a maximum length for a trimmed UTF-8 string without requiring mbstring.
 */
function text_within_length(string $value, int $max): bool
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8') <= $max;
    }

    return strlen($value) <= $max;
}
