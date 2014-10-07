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
        $currentTarget = new Path();

        foreach ($request->getTarget() as $nodeName) {
            $currentTarget->append($nodeName);

            $target = $this->findNodeTarget($request, $currentTarget);
            if ($target) {
                return $target;
            }
        }
        return $this->findLeafTarget($request, $currentTarget);
    }

    private function findNodeTarget(Request $request, Path $currentTarget) {
        $node = $currentTarget->copy();
        $node->append(ucfirst($node->last()) . $this->suffix);
        return $this->createTargetFromClassFile($node, $request, $currentTarget);
    }

    private function findLeafTarget(Request $request, Path $currentTarget) {
        $node = $currentTarget->copy();
        $className = ucfirst($node->pop()) . $this->suffix;
        $node->append($className);
        return $this->createTargetFromClassFile($node, $request, $currentTarget);
    }

    private function createTargetFromClassFile(Path $node, Request $request, Path $currentTarget) {
        $filePath = $node . '.php';
        if ($this->store->exists($filePath)) {
            $fullClassName = $this->namespace . '\\' . implode('\\', $node->toArray());
            return $this->createTargetFromClass($fullClassName, $request, $currentTarget);
        }
        return null;
    }

    private function createTargetFromClass($fullClassName, Request $request, Path $currentTarget) {
        $fullClassName = str_replace('\\\\', '\\', $fullClassName);
        $object = $this->factory->getInstance($fullClassName);

        $nextRequest = $request->copy();
        $nextContext = $request->getContext()->copy();
        foreach ($currentTarget as $targetPart) {
            $nextContext->append($targetPart);
        }
        $nextRequest->setContext($nextContext);
        $nextRequest->setTarget(new Path($request->getTarget()->slice($currentTarget->count())->toArray()));

        if ($object instanceof Responding) {
            return new RespondingTarget($nextRequest, $object);
        } else if ($currentTarget->count() == $request->getTarget()->count()) {
            return new ObjectTarget($nextRequest, $object, $this->factory);
        } else {
            throw new \Exception("[$fullClassName] needs to implement Responding");
        }
    }

    private function createTargetFromFile(Request $request, $file) {
        $nextRequest = $request->copy();
        $nextRequest->setContext($request->getTarget()->copy());
        $nextRequest->setTarget(new Path());

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