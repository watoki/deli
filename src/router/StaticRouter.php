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
use watoki\stores\Store;

class StaticRouter implements Router {

    const DEFAULT_SUFFIX = 'Class';

    const DEFAULT_INDEX = 'index';

    /** @var Store */
    protected $store;

    /** @var string */
    private $namespace;

    /** @var string */
    protected $index;

    /** @var string */
    protected $suffix;

    /** @var Factory */
    protected $factory;

    function __construct(Factory $factory, Store $store, $namespace,
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

        foreach ($request->getTarget()->getElements() as $nodeName) {
            $target = $this->findIndexNode($request, $currentContext);
            if ($target) {
                return $target;
            }
            $currentContext = $currentContext->appended($nodeName);
        }
        return $this->findNode($request, $currentContext);
    }

    protected function findIndexNode(Request $request, Path $currentContext) {
        return $this->createTargetFromClassPath(ucfirst($this->index) . $this->suffix, $request, $currentContext);
    }

    private function findNode(Request $request, Path $currentContext) {
        $path = $currentContext->getElements();
        $target = ucfirst(array_pop($path)) . $this->suffix;

        return $this->createTargetFromClassPath($target, $request, $currentContext->with($path));
    }

    private function createTargetFromClassPath($target, Request $request, Path $currentContext) {
        $path = $currentContext->appended($target);

        if ($this->store->has($path . '.php')) {
            $fullClassName = implode('\\', $path->getElements());
            if ($this->namespace) {
                $fullClassName = rtrim($this->namespace, '\\') . '\\' . trim($fullClassName, '\\');
            }

            if (class_exists($fullClassName)) {
                return $this->createTargetFromClass($fullClassName, $request, $currentContext);
            }
        }
        return null;
    }

    private function createTargetFromClass($fullClassName, Request $request, Path $context) {
        $object = $this->factory->getInstance($fullClassName);

        $nextRequest = $request->withContext($request->getContext()->appendedAll($context->getElements()));
        $nextRequest = $nextRequest->withTarget(new Path(array_slice($request->getTarget()->getElements(), count($context->getElements()))));

        if ($object instanceof Responding) {
            return new RespondingTarget($nextRequest, $object);
        } else {
            return new ObjectTarget($nextRequest, $object, $this->factory);
        }
    }
}