<?php
namespace spec\watoki\deli\fixtures;

use watoki\deli\Delivery;
use watoki\deli\Request;
use watoki\deli\Response;
use watoki\deli\Router;

class TestDelivery extends Delivery {

    /** @var \watoki\deli\Request */
    public $request;

    public $response = false;

    private $echoResponse;

    public function __construct(Router $router, Request $request) {
        parent::__construct($router);
        $this->request = $request;
    }

    public function echoResponse() {
        $this->echoResponse = true;
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
        if ($this->echoResponse && is_string($response)) {
            echo $response;
        }
        $this->response = $response;
    }

    /**
     * Is called if an error is caught while running the delivery
     *
     * @param \Exception $exception
     * @return mixed|Response
     */
    protected function error(\Exception $exception) {
        return 'Error: ' . $exception->getMessage();
    }
}