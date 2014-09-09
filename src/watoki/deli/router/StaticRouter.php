<?php
namespace watoki\deli\router;

use watoki\deli\Request;
use watoki\deli\Responding;
use watoki\deli\Router;
use watoki\deli\Target;
use watoki\deli\target\ObjectTarget;
use watoki\deli\target\RespondingTarget;
use watoki\factory\Factory;
use watoki\stores\file\FileStore;

class StaticRouter implements Router {

    const DEFAULT_SUFFIX = 'Class';

    /** @var FileStore */
    private $store;

    /** @var string */
    private $namespace;

    /** @var string */
    private $suffix;

    /** @var \watoki\factory\Factory */
    private $factory;

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
        $target = $request->getTarget()->copy();
        $className = ucfirst($target->pop()) . $this->suffix;

        $target->append($className);

        $filePath = $target . '.php';

        if ($this->store->exists($filePath)) {
            $fullClassName = $this->namespace . '\\' . implode('\\', $target->toArray());
            $object = $this->factory->getInstance($fullClassName);

            if ($object instanceof Responding) {
                return new RespondingTarget($request, $object);
            } else {
                return new ObjectTarget($request, $object, $this->factory);
            }
        }

        throw new \Exception("Could not route [{$request->getTarget()}]");
    }
}