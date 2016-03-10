<?php
namespace watoki\deli\router;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\Router;
use watoki\deli\router\dynamic\Matcher;
use watoki\deli\router\dynamic\PathMatcher;
use watoki\deli\Target;
use watoki\deli\target\ObjectTarget;
use watoki\deli\target\TargetFactory;
use watoki\factory\Factory;

class DynamicRouter implements Router {

    /** @var array|TargetFactory[] */
    private $factories = array();

    /** @var array|Matcher[] */
    private $matchers = array();

    /**
     * @param Request $request
     * @throws TargetNotFoundException If no matching Target can be found
     * @return Target
     */
    public function route(Request $request) {
        foreach ($this->matchers as $i => $matcher) {
            $nextRequest = $matcher->matches($request);
            if ($nextRequest) {
                return $this->factories[$i]->create($nextRequest);
            }
        }
        throw new TargetNotFoundException("Could not find a path matching [{$request->getTarget()->toString()}]");
    }

    public function add(Matcher $matcher, TargetFactory $factory) {
        $this->matchers[] = $matcher;
        $this->factories[] = $factory;
    }

    public function addPath($pathString, TargetFactory $factory) {
        $this->add(new PathMatcher(Path::fromString($pathString)), $factory);
    }

    public function addObjectPath($pathString, $class, Factory $factory) {
        $this->addPath($pathString, ObjectTarget::factory($factory, $factory->getInstance($class)));
    }
}