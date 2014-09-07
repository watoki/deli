<?php
namespace watoki\deli\router;

use watoki\deli\Request;
use watoki\deli\Router;
use watoki\deli\Target;
use watoki\deli\target\TargetFactory;

class DynamicRouter implements Router {

    /** @var array|TargetFactory[] */
    private $factories = array();

    /**
     * @param Request $request
     * @return Target
     */
    public function route(Request $request) {
        return $this->factories[$request->getTarget()->toString()]->create($request);
    }


    public function set($pattern, TargetFactory $factory) {
        $this->factories[$pattern] = $factory;
    }
}