<?php

/**
 * Unit tests for SwitchRepository filter/sort engine and Similar Switches
 * algorithm.
 *
 * Each test inserts fixture rows inside the inherited transaction; tearDown
 * rolls everything back so tests are fully isolated.
 */
class SwitchRepositoryTest extends TestCase
{
    private SwitchRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new SwitchRepository($this->pdo);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Insert a minimal approved switch and return its id.
     *
     * @param array $overrides Column values that differ from the defaults below.
     */
    private function insertSwitch(array $overrides = []): int
    {
        return $this->repo->create(array_merge([
            'name'             => 'Test Switch ' . uniqid(),
            'switch_type'      => 'Linear',
            'sound_profile'    => 'Unknown',
            'feel_profile'     => 'Unknown',
            'recommended_use'  => 'Unknown',
            'status'           => 'approved',
            'bottom_out_force' => null,
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // filtered() — single filter
    // -----------------------------------------------------------------------

    public function test_filter_by_switch_type_returns_only_matching_rows(): void
    {
        $this->insertSwitch(['name' => 'Linear A', 'switch_type' => 'Linear']);
        $this->insertSwitch(['name' => 'Tactile B', 'switch_type' => 'Tactile']);
        $this->insertSwitch(['name' => 'Linear C', 'switch_type' => 'Linear']);

        $results = $this->repo->filtered(['switch_type' => 'Linear']);

        foreach ($results as $row) {
            $this->assertSame('Linear', $row['switch_type']);
        }
        $this->assertCount(2, $results);
    }

    public function test_filter_by_sound_profile_returns_only_matching_rows(): void
    {
        $this->insertSwitch(['name' => 'Clacky',  'sound_profile' => 'Clacky']);
        $this->insertSwitch(['name' => 'Thocky',  'sound_profile' => 'Thocky']);
        $this->insertSwitch(['name' => 'Clacky2', 'sound_profile' => 'Clacky']);

        $results = $this->repo->filtered(['sound_profile' => 'Thocky']);

        $this->assertCount(1, $results);
        $this->assertSame('Thocky', $results[0]['sound_profile']);
    }

    // -----------------------------------------------------------------------
    // filtered() — multi-filter AND logic
    // -----------------------------------------------------------------------

    public function test_combined_filters_use_and_logic(): void
    {
        // Only this one matches BOTH filters.
        $this->insertSwitch(['name' => 'Match',      'switch_type' => 'Tactile', 'sound_profile' => 'Thocky']);
        $this->insertSwitch(['name' => 'Type Only',  'switch_type' => 'Tactile', 'sound_profile' => 'Clacky']);
        $this->insertSwitch(['name' => 'Sound Only', 'switch_type' => 'Linear',  'sound_profile' => 'Thocky']);

        $results = $this->repo->filtered([
            'switch_type'   => 'Tactile',
            'sound_profile' => 'Thocky',
        ]);

        $this->assertCount(1, $results);
        $this->assertSame('Match', $results[0]['name']);
    }

    // -----------------------------------------------------------------------
    // filtered() — force sort: NULLs always last
    // -----------------------------------------------------------------------

    public function test_lightest_sort_puts_unknown_force_last(): void
    {
        $this->insertSwitch(['name' => 'Medium', 'bottom_out_force' => 55.0]);
        $this->insertSwitch(['name' => 'Light',  'bottom_out_force' => 35.0]);
        $this->insertSwitch(['name' => 'NoForce','bottom_out_force' => null]);

        $results = $this->repo->filtered([], 'lightest');

        // Only look at the last row to confirm NULL is placed last.
        $last = end($results);
        $this->assertNull($last['bottom_out_force']);
        // Confirm sorted order among known-force rows.
        $named = array_filter($results, fn($r) => $r['bottom_out_force'] !== null);
        $named = array_values($named);
        $this->assertLessThan($named[1]['bottom_out_force'], $named[0]['bottom_out_force']);
    }

    public function test_heaviest_sort_puts_unknown_force_last(): void
    {
        $this->insertSwitch(['name' => 'Medium', 'bottom_out_force' => 55.0]);
        $this->insertSwitch(['name' => 'Heavy',  'bottom_out_force' => 75.0]);
        $this->insertSwitch(['name' => 'NoForce','bottom_out_force' => null]);

        $results = $this->repo->filtered([], 'heaviest');

        $last = end($results);
        $this->assertNull($last['bottom_out_force']);
        $named = array_filter($results, fn($r) => $r['bottom_out_force'] !== null);
        $named = array_values($named);
        $this->assertGreaterThan($named[1]['bottom_out_force'], $named[0]['bottom_out_force']);
    }

    // -----------------------------------------------------------------------
    // similarTo()
    // -----------------------------------------------------------------------

    /**
     * Build a full switch row array (as returned by findById) for $id.
     */
    private function row(int $id): array
    {
        return $this->repo->findById($id);
    }

    public function test_similar_returns_at_most_three_results(): void
    {
        // Seed 5 similar switches.
        $target = $this->insertSwitch([
            'name'             => 'Target',
            'switch_type'      => 'Linear',
            'sound_profile'    => 'Thocky',
            'bottom_out_force' => 45.0,
        ]);
        for ($i = 0; $i < 5; $i++) {
            $this->insertSwitch([
                'name'             => "Similar $i",
                'switch_type'      => 'Linear',
                'sound_profile'    => 'Thocky',
                'bottom_out_force' => 45.0 + $i,
            ]);
        }

        $similar = $this->repo->similarTo($this->row($target));
        $this->assertLessThanOrEqual(3, count($similar));
    }

    public function test_similar_excludes_the_target_switch_itself(): void
    {
        $target = $this->insertSwitch([
            'name'          => 'Target',
            'switch_type'   => 'Linear',
            'sound_profile' => 'Thocky',
        ]);
        $this->insertSwitch([
            'name'          => 'Neighbor',
            'switch_type'   => 'Linear',
            'sound_profile' => 'Thocky',
        ]);

        $similar = $this->repo->similarTo($this->row($target));

        $ids = array_column($similar, 'id');
        $this->assertNotContains($target, $ids);
    }

    public function test_similar_only_returns_matching_type_and_sound_profile(): void
    {
        $target = $this->insertSwitch([
            'name'          => 'Target',
            'switch_type'   => 'Tactile',
            'sound_profile' => 'Thocky',
        ]);
        // Different type — should NOT appear.
        $this->insertSwitch([
            'name'          => 'Wrong Type',
            'switch_type'   => 'Linear',
            'sound_profile' => 'Thocky',
        ]);
        // Different sound — should NOT appear.
        $this->insertSwitch([
            'name'          => 'Wrong Sound',
            'switch_type'   => 'Tactile',
            'sound_profile' => 'Clacky',
        ]);
        // Matches both — should appear.
        $match = $this->insertSwitch([
            'name'          => 'Both Match',
            'switch_type'   => 'Tactile',
            'sound_profile' => 'Thocky',
        ]);

        $similar = $this->repo->similarTo($this->row($target));

        $ids = array_column($similar, 'id');
        $this->assertContains($match, $ids);
        $this->assertCount(1, $similar);
    }

    public function test_similar_sorted_by_closest_bottom_out_force(): void
    {
        $target = $this->insertSwitch([
            'name'             => 'Target',
            'switch_type'      => 'Linear',
            'sound_profile'    => 'Thocky',
            'bottom_out_force' => 50.0,
        ]);
        $near = $this->insertSwitch([
            'name'             => 'Near',
            'switch_type'      => 'Linear',
            'sound_profile'    => 'Thocky',
            'bottom_out_force' => 52.0,  // |52-50| = 2
        ]);
        $far = $this->insertSwitch([
            'name'             => 'Far',
            'switch_type'      => 'Linear',
            'sound_profile'    => 'Thocky',
            'bottom_out_force' => 70.0,  // |70-50| = 20
        ]);

        $similar = $this->repo->similarTo($this->row($target));

        $this->assertSame($near, (int) $similar[0]['id']);
        $this->assertSame($far,  (int) $similar[1]['id']);
    }
}
