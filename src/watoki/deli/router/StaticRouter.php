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

    /** @var RawFileStore */
    protected $store;

    /** @var string */
    private $namespace;

    /** @var string */
    private $suffix;

    /** @var Factory */
    private $factory;

    /** @var callable */
    private $fileTargetCreator;

    function __construct(Factory $factory, RawFileStore $store, $namespace, $suffix = self::DEFAULT_SUFFIX) {
        $this->factory = $factory;
        $this->store = $store;
        $this->namespace = $namespace;
        $this->suffix = $suffix;
    }

    /**
     * @param callable $targetCreator Returns a Target given the Request, the File and the key of the File
     */
    public function setFileTargetCreator($targetCreator) {
        $this->fileTargetCreator = $targetCreator;
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

        if ($this->fileTargetCreator) {
            $found = $this->existingFile($request);
            if ($found) {
                return $this->createTargetFromFile($request, $found);
            }
        }

        throw new TargetNotFoundException("Could not route [{$request->getTarget()}]");
    }

    private function findTarget(Request $request) {
        $currentContext = new Path();

        foreach ($request->getTarget() as $nodeName) {
            $target = $this->findNode($request, $currentContext, $nodeName);
            if ($target) {
                return $target;
            }

            $currentContext->append($nodeName);
        }
        return null;
    }

    private function findNode(Request $request, Path $currentContext, $nodeName) {
        $className = ucfirst($nodeName) . $this->suffix;
        return $this->createTargetFromClassFile($request, $currentContext, $className);
    }

    private function createTargetFromClassFile(Request $request, Path $currentContext, $className) {
        $path = $currentContext->copy();
        $path->append($className);

        if ($this->store->exists($path . '.php')) {
            $fullClassName = $path->join('\\');
            if ($this->namespace) {
                $fullClassName = $this->namespace . '\\' . $fullClassName;
            }

            return $this->createTargetFromClass($fullClassName, $request, $currentContext);
        }
        return null;
    }

    private function createTargetFromClass($fullClassName, Request $request, Path $currentContext) {
        $object = $this->factory->getInstance($fullClassName);

        $nextRequest = $request->copy();
        $nextRequest->getContext()->appendAll($currentContext);
        $nextRequest->setTarget(new Path($request->getTarget()->slice($currentContext->count())->toArray()));

        if ($object instanceof Responding) {
            return new RespondingTarget($nextRequest, $object);
        } else if ($nextRequest->getTarget()->count() == 1) {
            return new ObjectTarget($nextRequest, $object, $this->factory);
        } else {
            throw new \Exception("[$fullClassName] needs to implement Responding");
        }
    }

    private function createTargetFromFile(Request $request, $file) {
        $nextRequest = $request->copy();
        $nextRequest->setContext($request->getTarget()->copy());
        $nextRequest->setTarget(new Path(array($nextRequest->getContext()->pop())));

        $callable = $this->fileTargetCreator;
        return $callable($nextRequest, $this->store->read($file), $file);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function existingFile(Request $request) {
        $file = $request->getTarget()->toString();
        if ($this->store->exists($file)) {
            return $file;
        }
        return null;
    }
}