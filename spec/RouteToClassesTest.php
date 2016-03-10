<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\router\StaticRouter;
use watoki\deli\Target;
use watoki\scrut\ExceptionFixture;
use watoki\scrut\Specification;
use watoki\stores\stores\MemoryStore;

/**
 * The `StaticRouter` maps Paths to classes relative to a root directory.
 *
 * It finds Responding classes on the way or at the target and any calls the "do" method of
 * the target if it does not implement `Responding`.
 *
 * @property RequestFixture request <-
 * @property ExceptionFixture try <-
 * @property MemoryStore store <-
 */
class RouteToClassesTest extends Specification {

    public function background() {
        $this->givenTheClassSuffixIs('Node');
    }

    function testTargetIsPlainClass() {
        $this->givenTheBaseNamespaceIs('some\space');
        $this->givenAClass_In_WithTheBody('some\space\foo\bar\TargetNode', 'foo/bar', '
            /** @param $request <- */
            function doThis(\watoki\deli\Request $request) {
                return "Found it at " . $request->getTarget() . " in " . $request->getContext();
            }
        ');
        $this->request->givenTheRequestHasTheContext('my/context');
        $this->request->givenTheRequestHasTheTarget('foo/bar/target');
        $this->request->givenTheRequestHasTheMethod('this');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Found it at target in my/context/foo/bar");
    }

    function testTargetIsARespondingClass() {
        $this->givenTheBaseNamespaceIs('respond');
        $this->givenARespondingClass_In_Returning('respond\foo\RespondingNode', 'foo',
            '"Hello {$request->getContext()}:{$request->getTarget()}"');
        $this->request->givenTheRequestHasTheTarget('foo/responding');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Hello foo:responding");
    }

    function testIndexNodeOnTheWay() {
        $this->givenTheBaseNamespaceIs('node');
        $this->givenARespondingClass_In_Returning('node\foo\here\IndexNode', 'foo/here',
            '$request->getContext() . ":" . $request->getTarget()');
        $this->request->givenTheRequestHasTheTarget('foo/here/some/where');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith('foo/here:some/where');
    }

    function testIndexNodeNotResponding() {
        $this->givenTheBaseNamespaceIs('not');
        $this->givenAClass_In_WithTheBody('not\foo\here\IndexNode', 'foo/here', '
            /** @param $request <- */
            function doThis(\watoki\deli\Request $request) {
                return "Found it at " . $request->getTarget() . " in " . $request->getContext();
            }
        ');
        $this->request->givenTheRequestHasTheTarget('foo/here/target');
        $this->request->givenTheRequestHasTheMethod('this');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Found it at target in foo/here");
    }

    function testTargetIsIndexClassImplicitly() {
        $this->givenTheBaseNamespaceIs('index');
        $this->givenARespondingClass_In_Returning('index\foo\IndexNode', 'foo',
                '"Hello " . $request->getContext() . ":" . $request->getTarget();');
        $this->request->givenTheRequestHasTheTarget('foo/');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Hello foo:");
    }

    function testTargetIsIndexClassExplicitly() {
        $this->givenTheBaseNamespaceIs('explicit');
        $this->givenARespondingClass_In_Returning('explicit\foo\IndexNode', 'foo',
                '"Hello " . $request->getContext() . ":" . $request->getTarget();');
        $this->request->givenTheRequestHasTheTarget('foo/index');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Hello foo:index");
    }

    function testTargetDoesNotExist() {
        $this->request->givenTheRequestHasTheTarget('non/existing');
        $this->whenITryToRouteTheRequest();
        $this->try->thenTheException_ShouldBeThrown('Could not route [non/existing]');
    }

    ###################### SET-UP #########################

    private $namespace;

    private $suffix;

    /** @var null|Target */
    private $target;

    private function givenTheClassSuffixIs($string) {
        $this->suffix = $string;
    }

    private function givenTheBaseNamespaceIs($string) {
        $this->namespace = $string;
    }

    private function givenAClass_In_WithTheBody($fullName, $folder, $body) {
        $this->givenAClass_Implementing_In_WithTheBody($fullName, null, $folder, $body);
    }

    private function givenARespondingClass_In_Returning($fullName, $folder, $expression) {
        $body = '
            function respond(\\watoki\\deli\\Request $request) {
                return ' . $expression . ';
            }
        ';
        $this->givenAClass_Implementing_In_WithTheBody($fullName, '\watoki\deli\Responding', $folder, $body);
    }

    private function givenAClass_Implementing_In_WithTheBody($fullName, $interface, $folder, $body) {
        $spaceAndName = explode('\\', $fullName);
        $name = array_pop($spaceAndName);
        $space = implode('\\', $spaceAndName);

        $implements = $interface ? 'implements ' . $interface : '';

        $code = "namespace $space;
            class $name $implements {
                $body
            }";
        eval($code);

        $fileName = ($folder ? $folder . '/' : '') . $name .'.php';
        $this->store->write('<?php ' . $code, $fileName);
    }

    public function whenIRouteTheRequest() {
        $router = new StaticRouter($this->factory, $this->store, $this->namespace, $this->suffix);
        $this->target = $router->route($this->request->request);
    }

    private function whenITryToRouteTheRequest() {
        $this->try->tryTo(array($this, 'whenIRouteTheRequest'));
    }

    private function thenTheTargetShouldRespondWith($string) {
        $this->assertEquals($string, $this->target->respond());
    }

} 