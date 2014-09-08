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
        $this->given_IsRoutedToTheCallback('path/to/target', function (Request $r) {
            return 'Hello ' . $r->getArguments()->get('name');
        });
        $this->request->givenTheRequestHasTheTarget('path/to/target');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('name', 'Homer');

        $this->whenIRunTheDelivery();
        $this->thenTheResponseShouldBe('Hello Homer');
    }

    /**
     * A class that implements the Responding interface is handled by the RespondingTarget.
     */
    function testRespondingTarget() {
        $this->given_IsRoutedToARespondingClass_ThatRespondsWith('path/to/responding', 'TestResponding',
            'return "Hello " . $request->getArguments()->get("name");');
        $this->request->givenTheRequestHasTheTarget('path/to/responding');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('name', 'Bart');

        $this->whenIRunTheDelivery();
        $this->thenTheResponseShouldBe('Hello Bart');
    }

    function testCatchExceptions() {
        $this->given_IsRoutedToARespondingClass_ThatRespondsWith('my/path', 'CatchExceptions',
            'throw new \Exception("Something went wrong");');
        $this->request->givenTheRequestHasTheTarget('my/path');

        $this->whenIRunTheDelivery();
        $this->thenTheResponseShouldBe('Error: Something went wrong');
    }

    function testCatchFatalErrors() {
        $this->whenIExecute('fixtures/fatal.php');
        $this->thenTheOutputShouldStartWith('Error: Call to undefined function causeFatalError()');
    }

    ######################### SET-UP #########################

    /** @var DynamicRouter */
    private $router;

    /** @var TestDelivery */
    private $delivery;

    private $outputString;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    private function given_IsRoutedToTheCallback($path, $callback) {
        $this->router->set($path, CallbackTarget::factory($callback));
    }

    private function given_IsRoutedToARespondingClass_ThatRespondsWith($path, $className, $methodBody) {
        eval('class ' . $className . ' implements \\watoki\\deli\\Responding {
            public function respond(\\watoki\\deli\\Request $request) {
                ' . $methodBody . '
            }
        }');
        $this->router->set($path, RespondingTarget::factory($this->factory, new $className()));
    }

    public function whenIRunTheDelivery() {
        $this->delivery = new TestDelivery($this->router, $this->request->request);
        $this->delivery->run();
    }

    public function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->delivery->response);
    }

    private function whenIExecute($file) {
        $output = array();
        $file = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $file);
        exec('php ' . __DIR__ . DIRECTORY_SEPARATOR . $file, $output);
        $this->outputString = implode("\n", $output);
    }

    private function thenTheOutputShouldStartWith($string) {
        $this->assertStringStartsWith($string, $this->outputString);
    }

} 