<?php
namespace spec\watoki\deli;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\Router;
use watoki\deli\target\CallbackTarget;
use watoki\scrut\Specification;

class RespondToRequestTest extends Specification {

    function testStringAsResponse() {
        $this->router->set('path/to/target', CallbackTarget::factory(function () {
            return 'Hello World';
        }));

        $this->whenIGetTheResponseFor('path/to/target');
        $this->thenTheResponseShouldBe('Hello World');
    }

    /** @var DynamicRouter */
    private $router;

    /** @var mixed */
    private $response;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    private function whenIGetTheResponseFor($path) {
        $request = new Request(new Path(), Path::fromString($path));
        $target = $this->router->route($request);
        $this->response = $target->respond();
    }

    private function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

} 