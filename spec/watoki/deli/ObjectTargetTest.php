<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\filter\DefaultFilterFactory;
use watoki\deli\target\ObjectTarget;
use watoki\factory\FilterFactory;
use watoki\scrut\Specification;

/**
 * @property RequestFixture request <-
 */
class ObjectTargetTest extends Specification {

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
            public function doThis(\\watoki\\deli\\Request $request) {
                return $request->getMethod();
            }
        ');
        $this->request->givenTheRequestHasTheMethod('this');
        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('this');
    }

    function testArgumentWithNameRequest() {
        $this->givenTheClass_WithTheBody('ArgumentWithNameRequest', '
            public function doThis($request) {
                return $request;
            }
        ');
        $this->request->givenTheRequestHasTheMethod('this');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('request', 'that');
        $this->whenIGetTheResponseFromTheTarget();
        $this->thenTheResponseShouldBe('that');
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

        $this->thenTheResponseShouldBe('[["this"],false,1.4,1,"2001-12-31T12:00:00+01:00","2012-12-31T00:00:00+01:00"]');
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

    public function testDoNotInflateNullAsDateTime() {
        $this->givenTheClass_WithTheBody('DoNotInflateNullAsDateTime', '
            function doThis(\DateTime $d = null) {
                return $d;
            }');
        $this->request->givenTheRequestHasTheMethod('this');
        $this->request->givenTheRequestHasTheArgument_WithTheValue('d', null);

        $this->whenIGetTheResponseFromTheTarget();

        $this->thenTheResponseShouldBe(null);
    }

    ################ SET-UP ##################

    private $object;

    private $response;

    private function givenTheClass_WithTheBody($className, $body) {
        eval("class $className { $body }");
        $this->object = new $className();
    }

    private function whenIGetTheResponseFromTheTarget() {
        $this->factory->setSingleton(FilterFactory::$CLASS, new DefaultFilterFactory());
        $target = ObjectTarget::factory($this->factory, $this->object)->create($this->request->request);
        $this->response = $target->respond();
    }

    private function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

} 