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

    /**
     * Is called if an error is caught while running the delivery
     *
     * @param \Exception $exception
     * @param Request|null $request Null if error occurred while fetching the Request
     * @return mixed|Response
     */
    protected function error(\Exception $exception, Request $request = null) {
        return 'Error in ' . $request->getTarget() . ': ' . $exception->getMessage();
    }
}