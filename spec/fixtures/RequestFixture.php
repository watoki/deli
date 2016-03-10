<?php
namespace spec\watoki\deli\fixtures;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\scrut\Fixture;

class RequestFixture extends Fixture {

    /** @var Request */
    public $request;

    public function setUp() {
        parent::setUp();
        $this->request = new Request(new Path(), new Path());
    }

    public function givenTheRequestHasTheContext($pathString) {
        $this->request = $this->request->withContext(Path::fromString($pathString));
    }

    public function givenTheRequestHasTheTarget($pathString) {
        $this->request = $this->request->withTarget(Path::fromString($pathString));
    }

    public function givenTheRequestHasTheMethod($string) {
        $this->request = $this->request->withMethod($string);
    }

    public function givenTheRequestHasTheArgument_WithTheValue($key, $value) {
        $this->request = $this->request->withArgument($key, $value);
    }

} 