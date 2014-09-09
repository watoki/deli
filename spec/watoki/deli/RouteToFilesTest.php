<?php
namespace spec\watoki\deli;

use spec\watoki\deli\fixtures\RequestFixture;
use watoki\deli\router\StaticRouter;
use watoki\deli\Target;
use watoki\scrut\Specification;
use watoki\stores\adapter\FileStoreAdapter;
use watoki\stores\file\FileStore;
use watoki\stores\file\raw\File;
use watoki\stores\memory\MemoryStore;
use watoki\stores\memory\SerializerRepository;

/**
 * @property RequestFixture request <-
 */
class RouteToFilesTest extends Specification {

    protected function background() {
        $this->givenTheClassSuffixIs('Class');
    }

    function testTargetIsPlainClass() {
        $this->givenTheBaseNamespaceIs('some\space');
        $this->givenAClass_In_WithTheBody('some\space\foo\bar\TargetClass', 'foo/bar', '
            function doThis() {
                return "Found me";
            }
        ');
        $this->request->givenTheRequestHasTheTarget('foo/bar/target');
        $this->request->givenTheRequestHasTheMethod('this');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Found me");
    }

    function testTargetIsARespondingClass() {
        $this->givenTheBaseNamespaceIs('respond');
        $this->givenARespondingClass_In_Returning('respond\foo\RespondingClass', 'foo', '"Hello {$request->getContext()}"');
        $this->request->givenTheRequestHasTheTarget('foo/responding');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith("Hello foo/responding");
    }

    function testRespondingClassOnTheWay() {
        $this->givenTheBaseNamespaceIs('in');
        $this->givenARespondingClass_In_Returning('in\foo\HereClass', 'foo', '$request->getTarget()->toString()');
        $this->request->givenTheRequestHasTheTarget('foo/here/some/where');

        $this->whenIRouteTheRequest();
        $this->thenTheTargetShouldRespondWith('some/where');
    }

    function testNonRespondingClassOnTheWay() {
        $this->givenTheBaseNamespaceIs('not');
        $this->givenAClass_In_WithTheBody('not\foo\HereClass', 'foo', '');
        $this->request->givenTheRequestHasTheTarget('foo/here/target');

        $this->whenITryToRouteTheRequest();
        $this->thenThreeException_ShouldBeThrown('[not\foo\HereClass] needs to implement Responding');
    }

    ###################### SET-UP #########################

    private $namespace;

    private $suffix;

    /** @var null|Target */
    private $target;

    /** @var FileStore */
    private $file;

    /** @var null|\Exception */
    private $caught;

    protected function setUp() {
        parent::setUp();
        $this->file = new FileStoreAdapter(new MemoryStore(File::$CLASS, new SerializerRepository()));
    }

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

        $fileName = $folder . '/' . $name .'.php';

        $this->file->create(new File('<?php ' . $code), $fileName);
    }

    private function whenIRouteTheRequest() {
        $router = new StaticRouter($this->factory, $this->file, $this->namespace, $this->suffix);
        $this->target = $router->route($this->request->request);
    }

    private function whenITryToRouteTheRequest() {
        try {
            $this->whenIRouteTheRequest();
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    private function thenTheTargetShouldRespondWith($string) {
        $this->assertEquals($string, $this->target->respond());
    }

    private function thenThreeException_ShouldBeThrown($message) {
        $this->assertNotNull($this->caught);
        $this->assertEquals($message, $this->caught->getMessage());
    }

} 