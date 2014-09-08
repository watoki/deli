<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use spec\watoki\deli\fixtures\TestDelivery;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\Router;
use watoki\deli\target\CallbackTarget;
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
class DeliverRequestAndResponseTest extends Specification {

    /**
     * The CallbackTarget requires no infrastructure since it simply calls the callable it's given.
     */
    function testCallbackTarget() {
        $this->router->set('path/to/target', CallbackTarget::factory(function (Request $r) {
            return 'Hello ' . $r->getArguments()->get('name');
        }));
        $this->request->givenTheRequestHasTheTarget('path/to/target');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('name', 'Homer');

        $this->whenIRunTheDelivery();
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

        $this->whenIRunTheDelivery();
        $this->thenTheResponseShouldBe('Hello Bart');
    }

    ######################### SET-UP #########################

    /** @var DynamicRouter */
    private $router;

    /** @var TestDelivery */
    private $delivery;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    public function whenIRunTheDelivery() {
        $this->delivery = new TestDelivery($this->router, $this->request->request);
        $this->delivery->run();
    }

    public function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->delivery->response);
    }

} 