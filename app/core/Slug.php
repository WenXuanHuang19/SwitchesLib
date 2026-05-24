<?php

/**
 * Turns a human title into a URL slug. Pure (no DB); uniqueness against
 * existing rows is handled by the repository (ADR-0002).
 */
class Slug
{
    public static function make(string $text): string
    {
        $text = strtolower(trim($text));
        // Replace any run of non-alphanumeric characters with a single hyphen.
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        // Trim leading/trailing hyphens left behind.
        return trim($text, '-');
    }
}
