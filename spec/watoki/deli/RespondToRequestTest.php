<?php
namespace spec\watoki\deli;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\Router;
use watoki\deli\target\CallbackTarget;
use watoki\deli\target\RespondingTarget;
use watoki\scrut\Specification;

class RespondToRequestTest extends Specification {

    function testRespondingTarget() {
        $className = 'TestObject';
        eval('class ' . $className . ' implements \\watoki\\deli\\Responding {
            public function respond(\\watoki\\deli\\Request $request) {
                return "Hello " . $request->getArguments()->get("name");
            }
        }');
        $this->router->set('path/to/responding', RespondingTarget::factory(new $className()));

        $this->givenARequestWithTheTarget('path/to/responding');
        $this->givenTheRequestHasTheArgument_WithTheValue('name', 'Bart');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello Bart');
    }

    function testCallbackTarget() {
        $this->router->set('path/to/target', CallbackTarget::factory(function (Request $r) {
            return 'Hello ' . $r->getArguments()->get('name');
        }));

        $this->givenARequestWithTheTarget('path/to/target');
        $this->givenTheRequestHasTheArgument_WithTheValue('name', 'Homer');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello Homer');
    }

    ######################### SET-UP #########################

    /** @var DynamicRouter */
    private $router;

    /** @var mixed */
    private $response;

    /** @var Request */
    private $request;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    private function givenARequestWithTheTarget($pathString) {
        $this->request = new Request(new Path(), Path::fromString($pathString));
    }

    private function whenIGetTheResponseForTheRequest() {
        $target = $this->router->route($this->request);
        $this->response = $target->respond();
    }

    private function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

    private function givenTheRequestHasTheArgument_WithTheValue($key, $value) {
        $this->request->getArguments()->set($key, $value);
    }

} 