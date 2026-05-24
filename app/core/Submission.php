<?php

/**
 * Validates and normalizes a Switch submission form payload.
 *
 * Pure with respect to the database (no DB access) so the rules are testable.
 * The duplicate-name check lives in SwitchRepository and runs in the
 * controller; this class only checks the shape of the submitted data.
 *
 * Required (must hold a real value): name, designer_id, switch_type.
 * Spec text/tag fields default to 'Unknown' when left blank.
 * Numeric fields accept a number or blank/'Unknown' (stored as NULL); any
 * other value is an error. description / image_url / release_date are optional.
 */
class Submission
{
    public const SWITCH_TYPES = ['Linear', 'Tactile', 'Clicky', 'Silent Linear', 'Silent Tactile'];

    /** Free-text spec fields that fall back to 'Unknown' when blank. */
    private const TEXT_FIELDS = [
        'series', 'variant', 'manufacturer', 'spring_type',
        'top_housing_material', 'bottom_housing_material', 'stem_material',
        'stem_type', 'contact_material', 'silent_structure',
        'sound_profile', 'feel_profile', 'recommended_use',
    ];

    /** Yes/No/Unknown dropdown fields. */
    private const ENUM_FIELDS = ['led_diffuser', 'rgb_support', 'factory_lubed', 'is_silent'];

    /** Numeric fields: a number, or blank/'Unknown' meaning NULL. */
    private const NUMERIC_FIELDS = [
        'initial_force', 'actuation_force', 'bottom_out_force', 'tactile_force',
        'actuation_travel', 'total_travel', 'spring_length', 'pin_count',
    ];

    /** Optional free-text fields stored as NULL when blank. */
    private const OPTIONAL_FIELDS = ['description', 'image_url', 'release_date'];

    /**
     * @return array ['errors' => [field => message, ...]] when invalid,
     *               ['data'   => [column => value, ...]]   when valid.
     */
    public static function validate(array $input): array
    {
        $errors = [];
        $data   = [];

        // --- Required identity fields ---
        $name = trim($input['name'] ?? '');
        if ($name === '') {
            $errors['name'] = 'Switch name is required.';
        }
        $data['name'] = $name;

        $designerId = trim($input['designer_id'] ?? '');
        if ($designerId === '') {
            $errors['designer_id'] = 'Please choose a Designer or Studio.';
        }
        $data['designer_id'] = $designerId === '' ? null : (int) $designerId;

        $switchType = trim($input['switch_type'] ?? '');
        if (!in_array($switchType, self::SWITCH_TYPES, true)) {
            $errors['switch_type'] = 'Please choose a Switch Type.';
        }
        $data['switch_type'] = $switchType;

        // --- switch_category (defaults to the common case) ---
        $category = trim($input['switch_category'] ?? '');
        $data['switch_category'] = $category === '' ? 'Mechanical MX' : $category;

        // --- Free-text spec / tag fields: blank => Unknown ---
        foreach (self::TEXT_FIELDS as $field) {
            $value = trim($input[$field] ?? '');
            $data[$field] = $value === '' ? 'Unknown' : $value;
        }

        // --- Yes/No/Unknown enums: invalid => Unknown ---
        foreach (self::ENUM_FIELDS as $field) {
            $value = trim($input[$field] ?? '');
            $data[$field] = in_array($value, ['Yes', 'No', 'Unknown'], true) ? $value : 'Unknown';
        }

        // --- Numeric fields: number, or blank/Unknown => NULL ---
        foreach (self::NUMERIC_FIELDS as $field) {
            $value = trim($input[$field] ?? '');
            if ($value === '' || strcasecmp($value, 'Unknown') === 0) {
                $data[$field] = null;
            } elseif (is_numeric($value)) {
                $data[$field] = $value;
            } else {
                $errors[$field] = 'Enter a number, or "Unknown".';
            }
        }

        // --- Optional free-text: blank => NULL ---
        foreach (self::OPTIONAL_FIELDS as $field) {
            $value = trim($input[$field] ?? '');
            $data[$field] = $value === '' ? null : $value;
        }

        return $errors !== [] ? ['errors' => $errors] : ['data' => $data];
    }
}
