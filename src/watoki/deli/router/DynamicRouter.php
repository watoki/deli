<?php
namespace watoki\deli\router;

use watoki\deli\Path;
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
        foreach ($this->factories as $path => $factory) {
            $nextRequest = $this->match(Path::fromString($path), $request);
            if ($nextRequest) {
                return $this->factories[$path]->create($nextRequest);
            }
        }
        throw new \InvalidArgumentException("Could not find a path matching [{$request->getTarget()->toString()}]");
    }

    public function set(Path $path, TargetFactory $factory) {
        $this->factories[$path->toString()] = $factory;
        uksort($this->factories, function ($a, $b) {
            $pattern = '/{[^}]+}/';
            $a = preg_replace($pattern, '', $a);
            $b = preg_replace($pattern, '', $b);
            return strlen($b) - strlen($a);
        });
    }

    /**
     * @param Path $path
     * @param Request $request
     * @return null|Request Null if no match
     */
    private function match(Path $path, Request $request) {
        $target = $request->getTarget();
        $arguments = $request->getArguments()->copy();

        for ($i = 0; $i < $path->count(); $i++) {
            $p = $path->get($i);
            if (substr($p, 0, 1) == '{' && substr($p, -1) == '}') {
                $key = substr($p, 1, -1);
                $value = $target->get($i);
                $arguments->set($key, $value);
            } else if ($target->get($i) != $p) {
                return null;
            }
        }

        return new Request(
            new Path($target->slice(0, $i)->toArray()),
            new Path($target->slice($i)->toArray()),
            $request->getMethod(),
            $arguments);
    }
}