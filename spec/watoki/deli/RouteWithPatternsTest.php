<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\Path;
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
        $this->thenTheRoutedRequestShouldHaveTheContext('foo/baz');
        $this->thenTheRoutedRequestShouldHaveTheTarget('bar/me');
    }

    function testRootPath() {
        $this->givenISetATargetForThePath('');
        $this->request->givenTheRequestHasTheTarget('some/thing');

        $this->whenIRouteTheRequest();
        $this->thenTheRoutedRequestShouldHaveTheTarget('some/thing');
    }

    function testPatternWithPlaceholder() {
        $this->givenISetATargetForThePath('foo/{name}/bar');
        $this->request->givenTheRequestHasTheTarget('foo/baz/bar');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldBeFound();
        $this->thenTheRoutedRequestShouldHaveTheContext('foo/baz/bar');
        $this->thenTheRoutedRequestArgument_ShouldBe('name', 'baz');
    }

    function testSpecificOverGeneral() {
        $this->givenISetATargetForThePath_Responding('foo/bar', 'first');
        $this->givenISetATargetForThePath_Responding('foo/{bar}/baz', 'second');
        $this->givenISetATargetForThePath_Responding('foo/bar/baz', 'third');
        $this->request->givenTheRequestHasTheTarget('foo/bar/baz');

        $this->whenIRouteTheRequest();
        $this->thenResponseShouldBe('third');
    }

    function testNotMatching() {
        $this->givenISetATargetForThePath_Responding('foo/bar', 'not');
        $this->request->givenTheRequestHasTheTarget('foo/baz');

        $this->whenITryToRouteTheRequest();
        $this->thenAnException_ShouldBeThrown('Could not find a path matching [foo/baz]');
    }

    ############### SET-UP #################

    /** @var DynamicRouter */
    private $router;

    /** @var Target|null */
    private $target;

    /** @var mixed|Request */
    private $response;

    /** @var null|\Exception */
    private $caught;

    protected function setUp() {
        parent::setUp();
        $this->router = new DynamicRouter();
    }

    private function givenISetATargetForThePath($path) {
        $this->router->set(Path::fromString($path), CallbackTarget::factory(function (Request $r) {
            return $r;
        }));
    }

    private function  givenISetATargetForThePath_Responding($path, $return) {
        $this->router->set(Path::fromString($path), CallbackTarget::factory(function () use ($return) {
            return $return;
        }));
    }

    private function whenIRouteTheRequest() {
        $this->target = $this->router->route($this->request->request);
        $this->response = $this->target->respond();
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

    private function thenTheRoutedRequestArgument_ShouldBe($key, $value) {
        $this->assertEquals($value, $this->response->getArguments()->get($key));
    }

    private function thenTheRoutedRequestShouldHaveTheTarget($string) {
        $this->assertEquals($string, $this->response->getTarget()->toString());
    }

    private function thenTheRoutedRequestShouldHaveTheContext($string) {
        $this->assertEquals($string, $this->response->getContext()->toString());
    }

    private function thenAnException_ShouldBeThrown($string) {
        $this->assertNotNull($this->caught);
        $this->assertEquals($string, $this->caught->getMessage());
    }

    private function thenResponseShouldBe($string) {
        $this->assertEquals($string, $this->response);
    }

}