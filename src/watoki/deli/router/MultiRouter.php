<?php
namespace watoki\deli\router;

use watoki\deli\Request;
use watoki\deli\Router;
use watoki\deli\Target;

class MultiRouter implements Router {

    /** @var array|Router[] */
    private $routers = array();

    public function add(Router $router) {
        $this->routers[] = $router;
    }

    /**
     * @param Request $request
     * @throws \Exception If no router can route the request
     * @return Target
     */
    public function route(Request $request) {
        foreach ($this->routers as $router) {
            try {
                return $router->route($request);
            } catch (TargetNotFoundException $e) {
                // Try next one
            }
        }

        throw new TargetNotFoundException("Could not route [{$request->getTarget()}]");
    }
}