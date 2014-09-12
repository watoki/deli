<?php
namespace watoki\deli;

class Delivery {

    /** @var Router */
    private $router;

    /** @var RequestBuilder */
    private $builder;

    /** @var ResponseDeliverer */
    private $deliverer;

    public function __construct(Router $router, RequestBuilder $builder, ResponseDeliverer $deliverer) {
        $this->router = $router;
        $this->builder = $builder;
        $this->deliverer = $deliverer;
    }

    public function run() {
        try {
            $request = $this->builder->build();
            $this->catchErrors($request);

            $target = $this->router->route($request);
            $response = $target->respond();
        } catch (\Exception $e) {
            $response = $this->error($e);
        }

        $this->deliverer->deliver($response);
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
            $this->deliverer->deliver($this->error(new \Exception($message)));
        }
    }

    /**
     * Is called if an error is caught while running the delivery
     *
     * @param \Exception $exception
     * @return mixed
     */
    protected function error(\Exception $exception) {
        return get_class($exception) . ': ' . $exception->getMessage() . "\n" . $exception->getTraceAsString();
    }

} 