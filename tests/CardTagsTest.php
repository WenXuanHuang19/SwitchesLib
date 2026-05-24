<?php

require_once dirname(__DIR__) . '/app/helpers.php';

test('card tags are ordered: switch type, then sound profile, then recommended use', function () {
    $switch = [
        'switch_type'     => 'Linear',
        'sound_profile'   => 'Creamy',
        'recommended_use' => 'Budget Build',
    ];

    assertSame(['Linear', 'Creamy', 'Budget Build'], switch_card_tags($switch));
});

test('card tags skip values that are Unknown', function () {
    $switch = [
        'switch_type'     => 'Tactile',
        'sound_profile'   => 'Unknown',
        'recommended_use' => 'Office',
    ];

    assertSame(['Tactile', 'Office'], switch_card_tags($switch));
});
