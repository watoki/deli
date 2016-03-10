<?php

use spec\watoki\deli\fixtures\TestDelivererStub;
use watoki\deli\Delivery;
use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\NoneRouter;
use watoki\deli\target\CallbackTarget;

require_once __DIR__ . '/../../vendor/autoload.php';

error_reporting(0);

$router = new NoneRouter(CallbackTarget::factory(function () {
    /** @noinspection PhpUndefinedFunctionInspection */
    causeFatalError();
}));

$test = new TestDelivererStub(new Request(new Path(), Path::fromString('some/target')));
$test->onDeliver(function ($response) {
    echo $response;
});
$delivery = new Delivery($router, $test, $test);
$delivery->run();