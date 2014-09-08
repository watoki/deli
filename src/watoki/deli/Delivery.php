<?php
namespace watoki\deli;

abstract class Delivery {

    /** @var Router */
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function run() {
        $target = $this->router->route($this->fetch());
        $this->deliver($target->respond());
    }

    /**
     * @return Request
     */
    abstract protected function fetch();

    /**
     * @param mixed|Response $response
     * @return null
     */
    abstract protected function deliver($response);

} 