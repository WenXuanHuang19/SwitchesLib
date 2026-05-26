<?php

/**
 * Unit tests for the Slug generator.
 *
 * Pure-function tests (Slug::make) run with no DB access.
 * Collision-suffix tests drive through SwitchRepository::create() because
 * uniqueSlug() is private and its observable output is the stored slug.
 */
class SlugGeneratorTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Slug::make() — pure function, no DB
    // -----------------------------------------------------------------------

    public function test_make_lowercases_and_hyphenates(): void
    {
        $this->assertSame('cherry-mx-red', Slug::make('Cherry MX Red'));
    }

    public function test_make_collapses_multiple_spaces_and_symbols(): void
    {
        $this->assertSame('gateron-g-pro-3-0', Slug::make('Gateron G Pro 3.0'));
    }

    public function test_make_strips_special_characters(): void
    {
        // Parentheses, slashes, ampersands → treated as non-alphanumeric separators.
        $this->assertSame('top-clicky-v2', Slug::make('Top (Clicky) v2!'));
    }

    public function test_make_strips_non_ascii_characters(): void
    {
        // Chinese characters and other non-latin glyphs are dropped entirely.
        $result = Slug::make('茶轴 Brown');
        // Only the ASCII part should survive.
        $this->assertSame('brown', $result);
    }

    public function test_make_trims_leading_and_trailing_hyphens(): void
    {
        $this->assertSame('hello-world', Slug::make('  --hello world--  '));
    }

    public function test_make_returns_empty_string_for_all_special_chars(): void
    {
        $this->assertSame('', Slug::make('!!!---!!!'));
    }

    // -----------------------------------------------------------------------
    // uniqueSlug collision suffix — exercised via SwitchRepository::create()
    // -----------------------------------------------------------------------

    private function makeSwitch(array $overrides = []): int
    {
        $repo = new SwitchRepository($this->pdo);
        return $repo->create(array_merge([
            'name'        => 'Test Switch',
            'switch_type' => 'Linear',
        ], $overrides));
    }

    public function test_first_switch_gets_base_slug(): void
    {
        $repo = new SwitchRepository($this->pdo);
        $id   = $this->makeSwitch(['name' => 'Cherry MX Red']);
        $row  = $repo->findById($id);

        $this->assertSame('cherry-mx-red', $row['slug']);
    }

    public function test_second_switch_with_same_name_gets_dash_2_suffix(): void
    {
        $repo = new SwitchRepository($this->pdo);

        $this->makeSwitch(['name' => 'Gateron Yellow']);
        $id2 = $this->makeSwitch(['name' => 'Gateron Yellow']);

        $row2 = $repo->findById($id2);
        $this->assertSame('gateron-yellow-2', $row2['slug']);
    }

    public function test_third_switch_with_same_name_gets_dash_3_suffix(): void
    {
        $repo = new SwitchRepository($this->pdo);

        $this->makeSwitch(['name' => 'Akko CS Jelly']);
        $this->makeSwitch(['name' => 'Akko CS Jelly']);
        $id3 = $this->makeSwitch(['name' => 'Akko CS Jelly']);

        $row3 = $repo->findById($id3);
        $this->assertSame('akko-cs-jelly-3', $row3['slug']);
    }
}
