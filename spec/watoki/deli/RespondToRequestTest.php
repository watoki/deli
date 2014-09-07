<?php
namespace spec\watoki\deli;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\Router;
use watoki\deli\target\CallbackTarget;
use watoki\deli\target\ObjectTarget;
use watoki\deli\target\RespondingTarget;
use watoki\scrut\Specification;

class RespondToRequestTest extends Specification {

    function testCallbackTarget() {
        $this->router->set('path/to/target', CallbackTarget::factory(function (Request $r) {
            return 'Hello ' . $r->getArguments()->get('name');
        }));

        $this->givenARequestWithTheTarget('path/to/target');
        $this->givenTheRequestHasTheArgument_WithTheValue('name', 'Homer');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello Homer');
    }

    function testRespondingTarget() {
        $className = 'TestResponding';
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

    function testObjectTarget() {
        $className = 'TestObjectEmptyMethod';
        eval('class ' . $className . ' {
            public function doTheMethod() {
                return "Hello World";
            }
        }');
        $this->router->set('path/to/object', ObjectTarget::factory(new $className()));

        $this->givenARequestWithTheTarget('path/to/object');
        $this->givenTheRequestHasTheMethod('theMethod');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello World');
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

    private function givenTheRequestHasTheMethod($string) {
        $this->request = new Request(new Path(), $this->request->getTarget(), $string);
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