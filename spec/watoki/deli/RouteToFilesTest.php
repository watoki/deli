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

    function testTargetIsPlainClass() {
        $this->givenTheClassSuffixIs('Class');
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

    ###################### SET-UP #########################

    private $namespace;

    private $suffix;

    /** @var null|Target */
    private $target;

    /** @var FileStore */
    private $file;

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
        $spaceAndName = explode('\\', $fullName);
        $name = array_pop($spaceAndName);
        $space = implode('\\', $spaceAndName);

        $code = "namespace $space;
            class $name {
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

    private function thenTheTargetShouldRespondWith($string) {
        $this->assertEquals($string, $this->target->respond());
    }

} 