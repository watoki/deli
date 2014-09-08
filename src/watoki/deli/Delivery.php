<?php
namespace watoki\deli;

abstract class Delivery {

    /** @var Router */
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function run() {
        try {
            $request = $this->fetch();
            $this->catchErrors($request);

            $target = $this->router->route($request);
            $response = $target->respond();
        } catch (\Exception $e) {
            $response = $this->error($e);
        }

        $this->deliver($response);
    }

    private function catchErrors() {
        error_reporting(0);
        ini_set('display_errors', false);
        register_shutdown_function(array($this, 'handleError'));
    }

    public function handleError() {
        $error = error_get_last();
        if (in_array($error['type'], array(E_ERROR, E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR))) {
            $message = "{$error['message']} in {$error['file']}:{$error['line']}";
            $this->deliver($this->error(new \Exception($message)));
        }
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
     * @return mixed|Response
     */
    abstract protected function error(\Exception $exception);

} 