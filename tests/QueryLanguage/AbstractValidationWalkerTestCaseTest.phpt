--TEST--
Abstract validation walker test cases
--FILE--
<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/phpunit.xml';

require __DIR__ . '/../../vendor/autoload.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTREGEX--
(?s)\A(?:PHP Deprecated: .*\R)*PHPUnit .+ by Sebastian Bergmann and contributors\.\R\RRuntime:\s+PHP .+\RConfiguration: .+phpunit\.xml\R\RFNN\s+3 \/ 3 \(100%\)\R\RTime: .+, Memory: .+\R\RThere was 1 failure:\R\R1\) FailureTest::testFoo\R0 violation expected\. Got 1\.\RFailed asserting that 1 is identical to 0\.\R\R.*AbstractValidationWalkerTestCase\.php:\d+\R.*FailureTest\.php:\d+\R\RFAILURES!\RTests: 3, Assertions: 4, Failures: 1.*\z
