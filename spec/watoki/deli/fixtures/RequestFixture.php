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

    public function givenTheRequestHasTheTarget($pathString) {
        $this->request = new Request(
            $this->request->getContext(),
            Path::fromString($pathString),
            $this->request->getMethod(),
            $this->request->getArguments()
        );
    }

    public function givenTheRequestHasTheMethod($string) {
        $this->request = new Request(
            $this->request->getContext(),
            $this->request->getTarget(),
            $string,
            $this->request->getArguments()
        );
    }

    public function givenTheRequestHasTheArgument_WithTheValue($key, $value) {
        $this->request->getArguments()->set($key, $value);
    }

} 