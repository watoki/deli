<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\Router;
use watoki\deli\target\CallbackTarget;
use watoki\deli\target\ObjectTarget;
use watoki\deli\target\RespondingTarget;
use watoki\scrut\Specification;

/**
 * This describes the basic set-up which gets the Response from a Target, found using a
 * Router which uses a TargetFactory to create the Target. There are different kind of
 * Targets with different sources for the Response (e.g. closure, objects implementing the
 * Responding interface or plain objects).
 *
 * @property RequestFixture request <-
*/
class RespondToRequestTest extends Specification {

    /**
     * The CallbackTarget requires no infrastructure since it simply calls the callable it's given.
     */
    function testCallbackTarget() {
        $this->router->set('path/to/target', CallbackTarget::factory(function (Request $r) {
            return 'Hello ' . $r->getArguments()->get('name');
        }));

        $this->request->givenTheRequestHasTheTarget('path/to/target');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('name', 'Homer');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello Homer');
    }

    /**
     * A class that implements the Responding interface is handled by the RespondingTarget.
     */
    function testRespondingTarget() {
        $className = 'TestResponding';
        eval('class ' . $className . ' implements \\watoki\\deli\\Responding {
            public function respond(\\watoki\\deli\\Request $request) {
                return "Hello " . $request->getArguments()->get("name");
            }
        }');
        $this->router->set('path/to/responding', RespondingTarget::factory($this->factory, new $className()));

        $this->request->givenTheRequestHasTheTarget('path/to/responding');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('name', 'Bart');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello Bart');
    }

    /**
     * A target can use any plain old PHP object (POPO) as source. The method or the Request is
     * then mapped to a method of the object by prefixing "do" (e.g. "something" => "doSomething")
     */
    function testObjectTarget() {
        $className = 'TestObjectEmptyMethod';
        eval('class ' . $className . ' {
            public function doTheMethod() {
                return "Hello World";
            }
        }');
        $this->router->set('path/to/object', ObjectTarget::factory($this->factory, new $className()));

        $this->request->givenTheRequestHasTheTarget('path/to/object');
        $this->request->givenTheRequestHasTheMethod('theMethod');
        $this->whenIGetTheResponseForTheRequest();
        $this->thenTheResponseShouldBe('Hello World');
    }

    ######################### SET-UP #########################

    /** @var DynamicRouter */
    private $router;

    /** @var mixed */
    private $response;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    public function whenIGetTheResponseForTheRequest() {
        $target = $this->router->route($this->request->request);
        $this->response = $target->respond();
    }

    public function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

} 