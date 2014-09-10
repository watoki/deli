<?php
namespace watoki\deli\router;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\Responding;
use watoki\deli\Router;
use watoki\deli\Target;
use watoki\deli\target\CallbackTarget;
use watoki\deli\target\ObjectTarget;
use watoki\deli\target\RespondingTarget;
use watoki\factory\Factory;
use watoki\stores\file\FileStore;

class StaticRouter implements Router {

    const DEFAULT_SUFFIX = 'Class';

    const PLACEHOLDER_PREFIX = 'xx';

    /** @var FileStore */
    private $store;

    /** @var string */
    private $namespace;

    /** @var string */
    private $suffix;

    /** @var \watoki\factory\Factory */
    private $factory;

    /** @var callable */
    private $fileTargetCreator;

    function __construct(Factory $factory, FileStore $store, $namespace, $suffix = self::DEFAULT_SUFFIX) {
        $this->factory = $factory;
        $this->store = $store;
        $this->namespace = $namespace;
        $this->suffix = $suffix;
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

        if ($this->fileTargetCreator && $this->store->exists($request->getTarget()->toString())) {
            return $this->createTargetFromFile($request);
        }

        throw new \Exception("Could not route [{$request->getTarget()}]");
    }

    /**
     * @param Request $request
     * @return null|Target
     * @throws \Exception
     */
    private function findTarget(Request $request) {
        $currentTarget = new Path();

        foreach ($request->getTarget() as $nodeName) {
            $currentTarget->append($nodeName);

            $node = $currentTarget->copy();
            $className = ucfirst($node->pop()) . $this->suffix;
            $node->append($className);

            $filePath = $node . '.php';

            if ($this->store->exists($filePath)) {
                $fullClassName = $this->namespace . '\\' . implode('\\', $node->toArray());
                return $this->createTargetFromClass($fullClassName, $currentTarget, $request);
            }

            $node = $currentTarget->copy();
            $currentNode = $node->pop();
            $node->append(self::PLACEHOLDER_PREFIX . '*.php');
            $pattern = $node->toString();
            $matching = $this->store->find($pattern);

            if (count($matching) > 1) {
                throw new \Exception('Too many placeholders: [' . implode(', ', $matching) . ']');
            }

            if ($matching) {
                $path = explode('/', substr($matching[0], 0, -4));
                $fullClassName = $this->namespace . '\\' . implode('\\', $path);

                $key = lcfirst(substr(end($path), strlen(self::PLACEHOLDER_PREFIX), -strlen($this->suffix)));

                $arguments = $request->getArguments()->copy();
                $arguments->set($key, $currentNode);
                $subRequest = new Request($request->getContext(), $request->getTarget(), $request->getMethod(), $arguments);

                return $this->createTargetFromClass($fullClassName, $currentTarget, $subRequest);
            }
        }

        return null;
    }

    private function createTargetFromClass($fullClassName, Path $currentTarget, Request $request) {
        $object = $this->factory->getInstance($fullClassName);

        $nextRequest = new Request(
            $currentTarget,
            new Path($request->getTarget()->slice($currentTarget->count())->toArray()),
            $request->getMethod(),
            $request->getArguments()->copy()
        );

        if ($object instanceof Responding) {
            return new RespondingTarget($nextRequest, $object);
        } else if ($currentTarget->count() == $request->getTarget()->count()) {
            return new ObjectTarget($nextRequest, $object, $this->factory);
        } else {
            throw new \Exception("[$fullClassName] needs to implement Responding");
        }
    }

    /**
     * @param callable $targetCreator Returns a Target given the Request and a File
     */
    public function setFileTargetCreator($targetCreator) {
        $this->fileTargetCreator = $targetCreator;
    }

    /**
     * @param Request $request
     * @return CallbackTarget|ObjectTarget|RespondingTarget
     */
    private function createTargetFromFile(Request $request) {
        $nextRequest = new Request(
            $request->getTarget()->copy(),
            new Path(),
            $request->getMethod(),
            $request->getArguments()->copy()
        );

        $callable = $this->fileTargetCreator;
        return $callable($nextRequest, $this->store->read($request->getTarget()->toString()));
    }
}