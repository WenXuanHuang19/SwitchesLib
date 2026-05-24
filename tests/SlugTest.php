<?php

test('Slug::make lowercases and hyphenates words', function () {
    assertSame('oil-king-r2', Slug::make('Oil King R2'));
});

test('Slug::make strips special characters and collapses separators', function () {
    assertSame('cream-pro', Slug::make('  Cream!!  Pro  '));
    assertSame('gateron-milky-yellow', Slug::make('Gateron / Milky Yellow'));
});
