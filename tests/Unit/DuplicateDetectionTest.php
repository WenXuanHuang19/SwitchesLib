<?php

/**
 * Unit tests for SwitchRepository::existsByNameAndDesigner().
 *
 * The duplicate-detection guard prevents the same switch (name + designer
 * combination) from being added twice. Tests verify the three cases from the
 * PRD: same designer, different designer, and case sensitivity.
 */
class DuplicateDetectionTest extends TestCase
{
    private SwitchRepository $repo;
    private int              $designerA;
    private int              $designerB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new SwitchRepository($this->pdo);

        // Two distinct designers used as FK anchors.
        $this->pdo->exec(
            "INSERT INTO designers (name) VALUES ('Designer A'), ('Designer B')"
        );
        $this->designerA = (int) $this->pdo
            ->query("SELECT id FROM designers WHERE name = 'Designer A'")
            ->fetchColumn();
        $this->designerB = (int) $this->pdo
            ->query("SELECT id FROM designers WHERE name = 'Designer B'")
            ->fetchColumn();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function insertSwitch(string $name, ?int $designerId): void
    {
        $this->repo->create([
            'name'        => $name,
            'switch_type' => 'Linear',
            'designer_id' => $designerId,
        ]);
    }

    // -----------------------------------------------------------------------
    // existsByNameAndDesigner()
    // -----------------------------------------------------------------------

    public function test_returns_true_when_same_name_and_same_designer_exist(): void
    {
        $this->insertSwitch('Gateron Yellow', $this->designerA);

        $this->assertTrue(
            $this->repo->existsByNameAndDesigner('Gateron Yellow', $this->designerA)
        );
    }

    public function test_returns_false_when_same_name_but_different_designer(): void
    {
        $this->insertSwitch('Gateron Yellow', $this->designerA);

        $this->assertFalse(
            $this->repo->existsByNameAndDesigner('Gateron Yellow', $this->designerB)
        );
    }

    public function test_returns_false_when_no_switch_exists_yet(): void
    {
        $this->assertFalse(
            $this->repo->existsByNameAndDesigner('Brand New Switch', $this->designerA)
        );
    }

    public function test_case_sensitive_name_match(): void
    {
        // The DB column is varchar with utf8mb4_unicode_ci which is
        // case-INsensitive, so 'gateron yellow' == 'Gateron Yellow'.
        $this->insertSwitch('Gateron Yellow', $this->designerA);

        // Confirm the actual behaviour rather than assuming one way or the other.
        $lowerMatch = $this->repo->existsByNameAndDesigner('gateron yellow', $this->designerA);

        // Document the result. utf8mb4_unicode_ci → case-insensitive → true.
        $this->assertTrue(
            $lowerMatch,
            'Expected case-insensitive duplicate detection (utf8mb4_unicode_ci collation)'
        );
    }

    public function test_null_designer_id_is_treated_as_its_own_group(): void
    {
        // Switches without a designer (designer_id = NULL) form their own group.
        // The query uses the NULL-safe operator (<=>) so two NULLs do match.
        $this->insertSwitch('Generic Switch', null);

        $this->assertTrue(
            $this->repo->existsByNameAndDesigner('Generic Switch', null),
            'Two switches with NULL designer_id and the same name should be duplicates'
        );
    }
}
