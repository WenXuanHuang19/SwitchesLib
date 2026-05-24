<?php

/** A minimal valid submission form payload. */
function valid_submission_input(array $overrides = []): array
{
    return array_merge([
        'name'        => 'Oil King',
        'designer_id' => '7',
        'switch_type' => 'Linear',
    ], $overrides);
}

test('validate returns normalized data and no errors for valid input', function () {
    $result = Submission::validate(valid_submission_input());

    assertTrue(!isset($result['errors']));
    assertSame('Oil King', $result['data']['name']);
    assertSame(7, $result['data']['designer_id']);
    assertSame('Linear', $result['data']['switch_type']);
});

test('validate reports an error when name is empty', function () {
    $result = Submission::validate(valid_submission_input(['name' => '   ']));

    assertTrue(isset($result['errors']['name']));
});

test('validate reports an error when designer_id is missing', function () {
    $result = Submission::validate(valid_submission_input(['designer_id' => '']));

    assertTrue(isset($result['errors']['designer_id']));
});

test('validate reports an error when switch_type is not an allowed value', function () {
    $result = Submission::validate(valid_submission_input(['switch_type' => 'Bogus']));

    assertTrue(isset($result['errors']['switch_type']));
});

test('validate defaults empty spec text fields to Unknown', function () {
    $result = Submission::validate(valid_submission_input([
        'series'        => '',
        'manufacturer'  => '   ',
        'stem_material' => '',
    ]));

    assertSame('Unknown', $result['data']['series']);
    assertSame('Unknown', $result['data']['manufacturer']);
    assertSame('Unknown', $result['data']['stem_material']);
});

test('validate maps empty or Unknown numeric fields to null', function () {
    $result = Submission::validate(valid_submission_input([
        'bottom_out_force' => '',
        'actuation_force'  => 'Unknown',
        'pin_count'        => '5',
    ]));

    assertSame(null, $result['data']['bottom_out_force']);
    assertSame(null, $result['data']['actuation_force']);
    assertSame('5', (string) $result['data']['pin_count']);
});

test('validate reports an error when a numeric field is not a number', function () {
    $result = Submission::validate(valid_submission_input(['bottom_out_force' => 'heavy']));

    assertTrue(isset($result['errors']['bottom_out_force']));
});
