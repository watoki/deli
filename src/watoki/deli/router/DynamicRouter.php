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

        if ($path->isEmpty()) {
            return new Request(
                new Path(),
                $request->getTarget()->copy(),
                $request->getMethod(),
                $arguments);
        }

        $i = 0;
        foreach ($path as $i => $p) {
            if (substr($p, 0, 1) == '{' && substr($p, -1) == '}') {
                $key = substr($p, 1, -1);
                $value = $target->get($i);
                $arguments->set($key, $value);
            } else if ($target->get($i) != $p) {
                if ($i == 0) {
                    return null;
                }
                break;
            }
        }

        return new Request(
            new Path($target->slice(0, $i + 1)->toArray()),
            new Path($target->slice($i + 1)->toArray()),
            $request->getMethod(),
            $arguments);
    }
}