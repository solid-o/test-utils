--TEST--
Abstract validation walker test cases
--FILE--
<?php

declare(strict_types=1);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/phpunit.xml';

require __DIR__ . '/../../vendor/autoload.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.
%a

F.. %w 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) FailureTest::testFoo
0 violation expected. Got 1.
Failed asserting that 1 is identical to 0.

%sAbstractValidationWalkerTestCase.php:%d
%sFailureTest.php:%d

FAILURES!
Tests: 3, Assertions: 4, Failures: 1.
