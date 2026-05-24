<?php

require_once dirname(__DIR__) . '/app/helpers.php';

test('or_unknown shows Unknown for null and empty, and the value otherwise', function () {
    assertSame('Unknown', or_unknown(null));
    assertSame('Unknown', or_unknown(''));
    assertSame('Nylon', or_unknown('Nylon'));
    assertSame('62.0', or_unknown('62.0'));
});
