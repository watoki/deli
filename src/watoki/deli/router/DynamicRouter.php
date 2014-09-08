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
     * @throws \InvalidArgumentException If no matching Target can be found
     * @return Target
     */
    public function route(Request $request) {
        $key = $request->getTarget()->toString();
        if (!array_key_exists($key, $this->factories)) {
            throw new \InvalidArgumentException('Could not find a path matching [not/existing]');
        }
        return $this->factories[$key]->create($request);
    }

    public function set($pattern, TargetFactory $factory) {
        $this->factories[$pattern] = $factory;
    }
}