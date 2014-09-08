<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\target\CallbackTarget;
use watoki\deli\Target;
use watoki\scrut\Specification;

/**
 * The DynamicRouter allows partial paths and patterns which are matched against the
 * target of the Request.
 *
 * @property RequestFixture request <-
*/
class RouteWithPatternsTest extends Specification {

    function testNonExistingTarget() {
        $this->givenISetATargetForThePath('foo/bar');
        $this->request->givenTheRequestHasTheTarget('not/existing');

        $this->whenITryToRouteTheRequest();
        $this->thenAnException_ShouldBeThrown('Could not find a path matching [not/existing]');
    }

    function testPartialPath() {
        $this->givenISetATargetForThePath('foo/baz');
        $this->request->givenTheRequestHasTheTarget('foo/baz/bar/me');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldBeFound();
        $this->thenTheRequestShouldHaveTheContext('foo/baz');
        $this->thenTheRequestShouldHaveTheTarget('bar/me');
    }

    function testPatternWithPlaceholder() {
        $this->givenISetATargetForThePath('foo/{name}/bar');
        $this->request->givenTheRequestHasTheTarget('foo/baz/bar');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldBeFound();
        $this->thenTheRequestShouldHaveTheContext('foo/baz/bar');
        $this->thenTheRequestArgument_ShouldBe('name', 'baz');
    }

    ############### SET-UP #################

    /** @var DynamicRouter */
    private $router;

    /** @var Target|null */
    private $target;

    /** @var Request */
    private $targetRequest;

    /** @var null|\Exception */
    private $caught;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    private function givenISetATargetForThePath($path) {
        $this->router->set($path, CallbackTarget::factory(function (Request $r) {
            return $r;
        }));
    }

    private function whenIRouteTheRequest() {
        $this->target = $this->router->route($this->request->request);
        $this->targetRequest = $this->target->respond();
    }

    private function whenITryToRouteTheRequest() {
        try {
            $this->whenIRouteTheRequest();
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    private function thenTheTargetShouldBeFound() {
        $this->assertNotNull($this->target);
    }

    private function thenTheRequestArgument_ShouldBe($key, $value) {
        $this->assertEquals($value, $this->targetRequest->getArguments()->get($key));
    }

    private function thenTheRequestShouldHaveTheTarget($string) {
        $this->assertEquals($string, $this->targetRequest->getTarget()->toString());
    }

    private function thenTheRequestShouldHaveTheContext($string) {
        $this->assertEquals($string, $this->targetRequest->getContext()->toString());
    }

    private function thenAnException_ShouldBeThrown($string) {
        $this->assertNotNull($this->caught);
        $this->assertEquals($string, $this->caught->getMessage());
    }

}