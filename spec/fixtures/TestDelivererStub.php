<?php
namespace spec\watoki\deli\fixtures;

use watoki\deli\Request;
use watoki\deli\RequestBuilder;
use watoki\deli\ResponseDeliverer;

class TestDelivererStub implements ResponseDeliverer, RequestBuilder {

    public $request;

    public $response;

    private $onDeliver;

    function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * @param mixed $response
     * @return null
     */
    public function deliver($response) {
        $this->response = $response;
        if ($this->onDeliver) {
            call_user_func($this->onDeliver, $response);
        }
    }

    /**
     * @return Request
     */
    public function build() {
        return $this->request;
    }

    /**
     * @param callable $callback
     */
    public function onDeliver($callback) {
        $this->onDeliver = $callback;
    }
}