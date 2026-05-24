<?php

/**
 * Tiny zero-dependency test framework (plain PHP).
 *
 * Register tests with test('name', fn). Assert with assertSame / assertTrue.
 * Run them all with run_tests(), which prints results and returns an exit code.
 */

$GLOBALS['__TESTS'] = [];

function test(string $name, callable $fn): void
{
    $GLOBALS['__TESTS'][$name] = $fn;
}

function assertSame($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            ($message !== '' ? $message : 'assertSame failed')
            . "\n  expected: " . var_export($expected, true)
            . "\n  actual:   " . var_export($actual, true)
        );
    }
}

function assertTrue($condition, string $message = ''): void
{
    if ($condition !== true) {
        throw new RuntimeException($message !== '' ? $message : 'assertTrue failed');
    }
}

function run_tests(): int
{
    $passed = 0;
    $failed = 0;

    foreach ($GLOBALS['__TESTS'] as $name => $fn) {
        try {
            $fn();
            echo "  \u{2713} {$name}\n";
            $passed++;
        } catch (Throwable $e) {
            echo "  \u{2717} {$name}\n";
            echo "      " . str_replace("\n", "\n      ", $e->getMessage()) . "\n";
            $failed++;
        }
    }

    echo "\n{$passed} passed, {$failed} failed\n";
    return $failed === 0 ? 0 : 1;
}
