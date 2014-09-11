<?php
namespace spec\watoki\deli;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\DynamicRouter;
use watoki\deli\router\MultiRouter;
use watoki\deli\target\CallbackTarget;
use watoki\deli\Target;
use watoki\scrut\Specification;

/**
 * Different routers (e.g. dynamic and static) can be combined using the MultiRouter. The added routers
 * will be tried in the order they were added.
 */
class CombineRoutersTest extends Specification {

    protected function background() {
        $this->givenAMultiRouter();
        $this->givenIHaveAddedARouterWhere_RespondsWith('some/path', 'Found one');
        $this->givenIHaveAddedARouterWhere_RespondsWith('some/other', 'Found two');
    }

    function testFirstRouterSucceeds() {
        $this->whenIRoute('some/path');
        $this->thenTheTargetShouldRespond('Found one');
    }

    function testSecondRouterSucceeds() {
        $this->whenIRoute('some/other');
        $this->thenTheTargetShouldRespond('Found two');
    }

    function testNoRouterSucceeds() {
        $this->whenITryToRoute('some/non/existent');
        $this->thenTheException_ShouldBeThrown('Could not route [some/non/existent]');
    }

    ########################### SET-UP ###########################

    /** @var MultiRouter */
    private $router;

    /** @var Target */
    private $target;

    /** @var null|\Exception */
    private $caught;

    private function givenAMultiRouter() {
        $this->router = new MultiRouter();
    }

    private function givenIHaveAddedARouterWhere_RespondsWith($string, $return) {
        $router = new DynamicRouter();
        $router->set(Path::fromString($string), CallbackTarget::factory(function () use ($return) {
            return $return;
        }));
        $this->router->add($router);
    }

    /**
     * @param $path
     */
    private function whenIRoute($path) {
        $this->target = $this->router->route(new Request(new Path(), Path::fromString($path)));
    }

    private function whenITryToRoute($path) {
        try {
            $this->whenIRoute($path);
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    /**
     * @param $str
     */
    private function thenTheTargetShouldRespond($str) {
        $this->assertEquals($str, $this->target->respond());
    }

    private function thenTheException_ShouldBeThrown($message) {
        $this->assertNotNull($this->caught);
        $this->assertEquals($message, $this->caught->getMessage());
    }

} 