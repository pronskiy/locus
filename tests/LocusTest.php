<?php

use Locus\PhpPlugin;

test('example', function (string $prettyConstraint, string $expectedPhpVersion) {
    expect(PhpPlugin::normalizePhpVersion($prettyConstraint))->toEqual($expectedPhpVersion);
})->with([
    // @TODO Dynamic versions
   ['^8.3', '8.3.4'], 
   ['^8.1.0', '8.3.4'], 
   ['8.1.*', '8.1.27'], 
]);
