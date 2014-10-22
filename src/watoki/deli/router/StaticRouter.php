<?php
namespace watoki\deli\router;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\Responding;
use watoki\deli\Router;
use watoki\deli\target\ObjectTarget;
use watoki\deli\Target;
use watoki\deli\target\RespondingTarget;
use watoki\factory\Factory;
use watoki\stores\file\raw\RawFileStore;

class StaticRouter implements Router {

    const DEFAULT_SUFFIX = 'Class';

    const DEFAULT_INDEX = 'index';

    /** @var RawFileStore */
    protected $store;

    /** @var string */
    private $namespace;

    /** @var string */
    protected $index;

    /** @var string */
    protected $suffix;

    /** @var Factory */
    protected $factory;

    function __construct(Factory $factory, RawFileStore $store, $namespace,
                         $suffix = self::DEFAULT_SUFFIX, $index = self::DEFAULT_INDEX) {
        $this->factory = $factory;
        $this->store = $store;
        $this->namespace = $namespace;
        $this->suffix = $suffix;
        $this->index = $index;
    }

    /**
     * @param Request $request
     * @throws \Exception
     * @return Target
     */
    public function route(Request $request) {
        $target = $this->findTarget($request);
        if ($target) {
            return $target;
        }

        throw new TargetNotFoundException("Could not route [{$request->getTarget()}]");
    }

    private function findTarget(Request $request) {
        $currentContext = new Path();

        foreach ($request->getTarget() as $nodeName) {
            $target = $this->findIndexNode($request, $currentContext);
            if ($target) {
                return $target;
            }
            $currentContext->append($nodeName);
        }
        return $this->findNode($request, $currentContext);
    }

    protected function findIndexNode(Request $request, Path $currentContext) {
        $path = $currentContext->copy();
        $path->append(ucfirst($this->index) . $this->suffix);

        return $this->createTargetFromClassPath($path, $request, $currentContext);
    }

    private function findNode(Request $request, Path $currentContext) {
        $path = $currentContext->copy();
        $nodeName = $path->pop();
        $className = ucfirst($nodeName) . $this->suffix;
        $path->append($className);

        return $this->createTargetFromClassPath($path, $request, $currentContext);
    }

    private function createTargetFromClassPath(Path $path, Request $request, Path $currentContext) {
        if ($this->store->exists($path . '.php')) {
            $fullClassName = $path->join('\\');
            if ($this->namespace) {
                $fullClassName = $this->namespace . '\\' . $fullClassName;
            }

            return $this->createTargetFromClass($fullClassName, $request, $currentContext);
        }
        return null;
    }

    private function createTargetFromClass($fullClassName, Request $request, Path $context) {
        $object = $this->factory->getInstance($fullClassName);

        $nextRequest = $request->copy();
        $nextRequest->getContext()->appendAll($context);
        $nextRequest->setTarget(new Path($request->getTarget()->slice($context->count())->toArray()));

        if ($object instanceof Responding) {
            return new RespondingTarget($nextRequest, $object);
        } else if ($nextRequest->getTarget()->isEmpty()) {
            return new ObjectTarget($nextRequest, $object, $this->factory);
        } else {
            throw new \Exception("[$fullClassName] needs to implement Responding");
        }
    }
}