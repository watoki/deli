<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\filter\DefaultFilterRegistry;
use watoki\deli\filter\FilterRegistry;
use watoki\deli\target\ObjectTarget;
use watoki\scrut\ExceptionFixture;
use watoki\scrut\Specification;

/**
 * The `ObjectTarget` can use any plain old PHP object (POPO) as source. The method or the Request is
 * then mapped to a method of the object by prefixing "do" (e.g. "something" => "doSomething").
 *
 * A TargetFactory for the ObjectTarget can be created with an instance of Factory and the object
 * `ObjectTarget::factory($factory, $object)`
 *
 * @property RequestFixture request <-
 * @property ExceptionFixture try <-
 */
class RouteToObjectsTest extends Specification {

    function testMapMethodName() {
        $this->givenTheClass_WithTheBody('MapMethodName', '
            public function doSomething() {
                return "Something";
            }
        ');

        $this->request->givenTheRequestHasTheMethod('something');
        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('Something');
    }

    function testMapNamedArguments() {
        $this->givenTheClass_WithTheBody('MapNamedArguments', '
            public function doThis($uno) {
                return "This " . $uno;
            }
        ');

        $this->request->givenTheRequestHasTheMethod('this');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('uno', 'one');
        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('This one');
    }

    function testIncompleteMixedArguments() {
        $this->givenTheClass_WithTheBody('IncompleteMixedArguments', '
            public function doThat($uno, $dos="two", $tres="three") {
                return $uno . $dos. $tres;
            }
        ');
        $this->request->givenTheRequestHasTheMethod('that');
        $this->request->givenTheRequestHasTheArgument_WithTheValue(0, 'one');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('tres', '3');
        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('onetwo3');
    }

    function testInjectRequest() {
        $this->givenTheClass_WithTheBody('InjectRequest', '
            /**
             * @param $foo <-
             * @param $bar <-
             */
            public function doDa(\watoki\deli\Request $foo, \watoki\deli\Request $bar) {
                return $foo->getMethod() . "ba" . $bar->getMethod();
            }
        ');
        $this->request->givenTheRequestHasTheMethod('da');
        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('dabada');
    }

    function testInvokeHooks() {
        $this->givenTheClass_WithTheBody('InvokeHooks', '
            public function before($request) {
                return new \\watoki\\deli\\Request(
                    new \\watoki\\deli\\Path(),
                    new \\watoki\\deli\\Path(),
                    "else"
                );
            }
            public function doElse() {
                return "found";
            }
            public function after($response, $request) {
                return "Something " . $request->getMethod() . " " . $response;
            }
        ');
        $this->request->givenTheRequestHasTheMethod('something');

        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('Something else found');
    }

    function testEdgeCaseEmptyHooks() {
        $this->givenTheClass_WithTheBody('EmptyHooks', '
            public function before() {}
            public function after() {}
            public function doSomething() {
                return "Something";
            }
        ');
        $this->request->givenTheRequestHasTheMethod('something');

        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('Something');
    }

    function testFilterArguments() {
        $this->givenTheClass_WithTheBody('FilterArguments', '
            /**
             * @param array $array
             * @param boolean $boolean
             * @param float $float
             * @param integer $int
             * @param \DateTime $dateTime1
             */
            public function doSomething($array, $boolean, $float, $int, $dateTime1, \DateTime $dateTime2) {
                return json_encode(array($array, $boolean, $float, $int, $dateTime1->format("c"), $dateTime2->format("c")));
            }
        ');
        $this->request->givenTheRequestHasTheMethod('something');

        $this->request->givenTheRequestHasTheArgument_WithTheValue('array', 'this');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('boolean', 'false');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('float', '1.4');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('int', '1.5');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('dateTime1', '2001-12-31 12:00');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('dateTime2', '31.12.2012');

        $this->whenIGetTheResponseFromTheTarget();

        $this->thenTheResponseShouldBe('[["this"],false,1.4,1,"2001-12-31T12:00:00+00:00","2012-12-31T00:00:00+00:00"]');
    }

    function testInvalidTypeHint() {
        $this->givenTheClass_WithTheBody('InvalidTypeHint', '
            /**
             * @param invalid $one
             */
            function doThis($one) {
                return $one;
            }
        ');
        $this->request->givenTheRequestHasTheMethod('this');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('one', 'not');

        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('not');
    }

    function testDoNotInflateNullAsDateTime() {
        $this->givenTheClass_WithTheBody('DoNotInflateNullAsDateTime', '
            function doThis(\DateTime $d = null) {
                return $d;
            }');
        $this->request->givenTheRequestHasTheMethod('this');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('d', null);

        $this->whenIGetTheResponseFromTheTarget();

        $this->thenTheResponseShouldBe(null);
    }

    function testMissingRequestArgument() {
        $this->givenTheClass_WithTheBody('MissingRequestArgument', '
            function doThis(\DateTime $missing) {
                return $missing;
            }');
        $this->request->givenTheRequestHasTheMethod('this');

        $this->whenITryToGetTheResponseFromTheTarget();

        $this->try->thenTheException_ShouldBeThrown(
                'Cannot fill parameter [missing] of [MissingRequestArgument::doThis]: ' .
                'Argument not given and not injectable.');
    }

    ################ SET-UP ##################

    private $object;

    private $response;

    protected function setUp() {
        parent::setUp();
        date_default_timezone_set('UTC');
    }

    private function givenTheClass_WithTheBody($className, $body) {
        eval("class $className { $body }");
        $this->object = new $className();
    }

    private function whenIGetTheResponseFromTheTarget() {
        $this->factory->setSingleton(new DefaultFilterRegistry(), FilterRegistry::class);
        $target = ObjectTarget::factory($this->factory, $this->object)->create($this->request->request);
        $this->response = $target->respond();
    }

    private function whenITryToGetTheResponseFromTheTarget() {
        $this->try->tryTo(function () {
            $this->whenIGetTheResponseFromTheTarget();
        });
    }

    private function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

} 