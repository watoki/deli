<?php

use spec\watoki\deli\fixtures\TestDelivery;
use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\target\CallbackTarget;

require_once __DIR__ . '/../../../../bootstrap.php';

$router = new DynamicRouter();
$router->set('', CallbackTarget::factory(function () {
    causeFatalError();
}));

$delivery = new TestDelivery($router, new Request(new Path(), new Path()));
$delivery->echoResponse();
$delivery->run();