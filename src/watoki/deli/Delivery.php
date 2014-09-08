<?php
namespace watoki\deli;

abstract class Delivery {

    /** @var Router */
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function run() {
        $request = null;
        try {
            $request = $this->fetch();
            $target = $this->router->route($request);
            $response = $target->respond();
        } catch (\Exception $e) {
            $response = $this->error($e, $request);
        }
        $this->deliver($response);
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

    /**
     * Is called if an error is caught while running the delivery
     *
     * @param \Exception $exception
     * @param Request|null $request Null if error occurred while fetching the Request
     * @return mixed|Response
     */
    abstract protected function error(\Exception $exception, Request $request = null);

} 