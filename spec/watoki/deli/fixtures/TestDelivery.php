<?php
namespace spec\watoki\deli\fixtures;

use watoki\deli\Delivery;
use watoki\deli\Request;
use watoki\deli\Response;
use watoki\deli\Router;

class TestDelivery extends Delivery {

    /** @var \watoki\deli\Request */
    public $request;

    public $response;

    public function __construct(Router $router, Request $request) {
        parent::__construct($router);
        $this->request = $request;
    }

    /**
     * @return Request
     */
    protected function fetch() {
        return $this->request;
    }

    /**
     * @param mixed|Response $response
     * @return null
     */
    protected function deliver($response) {
        $this->response = $response;
    }
}