<?php
namespace watoki\deli;

class Delivery {

    private static $errorReporting = 0;

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
        $request = $this->builder->build();

        try {
            $this->catchErrors($request);
            $response = $this->getResponse($request);
        } catch (\Exception $e) {
            $response = $this->error($request, $e);
        }

        $this->deliverer->deliver($response);
    }

    protected function getResponse(Request $request) {
        $target = $this->router->route($request);
        return $target->respond();
    }

    public static function errorReporting($level) {
        if ($level === true) {
            $level = E_ALL;
        }
        self::$errorReporting = $level;
        error_reporting($level);
        ini_set('display_errors', !!$level);
    }

    private function catchErrors(Request $request) {
        self::errorReporting(self::$errorReporting);
        register_shutdown_function(array($this, 'handleError'), $request);
    }

    public function handleError(Request $request) {
        $error = error_get_last();
        if (in_array($error['type'], array(E_ERROR, E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR))) {
            $message = "{$error['message']} in {$error['file']}:{$error['line']}";
            $this->deliverer->deliver($this->error($request, new \Exception($message)));
        }
    }

    /**
     * Is called if an error is caught while running the delivery
     *
     * @param Request $request
     * @param \Exception $exception
     * @return mixed
     */
    protected function error(Request $request, \Exception $exception) {
        return $request->getTarget() . ' threw '
        . get_class($exception) . ': '
        . $exception->getMessage() . "\n"
        . $exception->getTraceAsString();
    }

} 