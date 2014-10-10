<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use spec\watoki\stores\FileStoreFixture;
use watoki\deli\Request;
use watoki\deli\router\StaticRouter;
use watoki\deli\Target;
use watoki\deli\target\CallbackTarget;
use watoki\scrut\ExceptionFixture;
use watoki\scrut\Specification;
use watoki\stores\file\raw\File;

/**
 * The `StaticRouter` maps Paths to classes relative to a root directory.
 *
 * It finds Responding classes on the way or at the target and any calls the "do" method of
 * the target if it does not implement `Responding`.
 *
 * @property RequestFixture request <-
 * @property ExceptionFixture try <-
 * @property FileStoreFixture file <-
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
                return "Found " . $request->getTarget() . " at " . $request->getContext();
            }
        ');
        $this->request->givenTheRequestHasTheContext('my/context');
        $this->request->givenTheRequestHasTheTarget('foo/bar/target');
        $this->request->givenTheRequestHasTheMethod('this');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Found target at my/context/foo/bar");
    }

    function testTargetIsARespondingClass() {
        $this->givenTheBaseNamespaceIs('respond');
        $this->givenARespondingClass_In_Returning('respond\foo\RespondingNode', 'foo', '"Hello {$request->getContext()} {$request->getTarget()}"');
        $this->request->givenTheRequestHasTheTarget('foo/responding');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Hello foo responding");
    }

    function testNodeOnTheWay() {
        $this->givenTheBaseNamespaceIs('node');
        $this->givenARespondingClass_In_Returning('node\foo\HereNode', 'foo', '$request->getContext() . " " . $request->getTarget()');
        $this->request->givenTheRequestHasTheTarget('foo/here/some/where');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith('foo here/some/where');
    }

    function testNonRespondingClassOnTheWay() {
        $this->givenTheBaseNamespaceIs('not');
        $this->givenAClass_In_WithTheBody('not\foo\HereNode', 'foo', '');
        $this->request->givenTheRequestHasTheTarget('foo/here/target');

        $this->whenITryToRouteTheRequest();
        $this->try->thenTheException_ShouldBeThrown('[not\foo\HereNode] needs to implement Responding');
    }

    function testTargetIsFile() {
        $this->givenAnObjectFromAFileIsCreatedWith(function (Request $r, File $f) {
            return new CallbackTarget($r, function () use ($r, $f) {
                return $r->getContext() . ' -> ' . $f->content;
            });
        });

        $this->file->givenAFile_WithContent('file/foo/bar', 'Hello again');
        $this->request->givenTheRequestHasTheTarget('file/foo/bar');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith('file/foo -> Hello again');
    }

    function testTargetIsFileAndClass() {
        $this->givenAnObjectFromAFileIsCreatedWith(function (Request $r, File $f) {
            return new CallbackTarget($r, function () use ($f) {
                return $f->content;
            });
        });

        $this->file->givenAFile_WithContent('foo/bar', 'The file');

        $this->givenTheBaseNamespaceIs('both');
        $this->givenARespondingClass_In_Returning('both\foo\BarNode', 'foo', '"The class"');

        $this->request->givenTheRequestHasTheTarget('foo/bar');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith('The class');
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

    /** @var callable */
    private $fileObject;

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
        $this->file->givenAFile_WithContent($fileName, '<?php ' . $code);
    }

    private function givenAnObjectFromAFileIsCreatedWith($callable) {
        $this->fileObject = $callable;
    }

    public function whenIRouteTheRequest() {
        $router = new StaticRouter($this->factory, $this->file->store, $this->namespace, $this->suffix);
        $router->setFileTargetCreator($this->fileObject);
        $this->target = $router->route($this->request->request);
    }

    private function whenITryToRouteTheRequest() {
        $this->try->tryTo(array($this, 'whenIRouteTheRequest'));
    }

    private function thenTheTargetShouldRespondWith($string) {
        $this->assertEquals($string, $this->target->respond());
    }

} 