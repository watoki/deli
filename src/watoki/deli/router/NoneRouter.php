<?php
namespace watoki\deli\router;

use watoki\deli\Request;
use watoki\deli\Router;
use watoki\deli\Target;
use watoki\deli\target\TargetFactory;

class NoneRouter implements Router {

    /** @var TargetFactory */
    private $targetFactory;

    /**
     * @param TargetFactory $factory
     */
    function __construct(TargetFactory $factory) {
        $this->targetFactory = $factory;
    }

    /**
     * @param Request $request
     * @return Target
     */
    public function route(Request $request) {
        return $this->targetFactory->create($request);
    }
}