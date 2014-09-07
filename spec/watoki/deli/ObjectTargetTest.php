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

    ################ SET-UP ##################

    private $object;

    private $response;

    private function givenTheClass_WithTheBody($className, $body) {
        eval("class $className { $body }");
        $this->object = new $className();
    }

    private function whenIGetTheResponseFromTheTarget() {
        $target = new ObjectTarget($this->request->request, $this->object);
        $this->response = $target->respond();
    }

    private function thenTheResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

} 