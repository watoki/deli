<?php

use spec\watoki\deli\fixtures\TestDelivery;
use watoki\deli\Delivery;
use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\target\CallbackTarget;

require_once __DIR__ . '/../../../../bootstrap.php';

$router = new DynamicRouter();
$router->set(new Path(), CallbackTarget::factory(function () {
    causeFatalError();
}));

$test = new TestDelivery(new Request(new Path(), new Path()));
$test->onDeliver(function ($response) {
    echo $response;
});
$delivery = new Delivery($router, $test, $test);
$delivery->run();