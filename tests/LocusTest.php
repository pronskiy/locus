<?php

use Locus\PhpPlugin;

test('example', function (string $prettyConstraint, string $expectedPhpVersion) {
    expect(PhpPlugin::normalizePhpVersion($prettyConstraint))->toEqual($expectedPhpVersion);
})->with([
    // @TODO Dynamic last version
   ['^8.3', '8.3.6'], 
//   ['^8.1.0', '8.1.28'], 
   ['8.1.*', '8.1.28'], 
]);
