<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\target\ObjectTarget;
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

    ################ SET-UP ##################

    private $object;

    private $response;

    private function givenTheClass_WithTheBody($className, $body) {
        eval("class $className { $body }");
        $this->object = new $className();
    }

    private function whenIGetTheResponseFromTheTarget() {
        $target = new ObjectTarget($this->request->request, $this->object, $this->factory);
        $this->response = $target->respond();
    }

    private function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

} 