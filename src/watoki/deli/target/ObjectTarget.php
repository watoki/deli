<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Target;
use watoki\factory\Factory;
use watoki\factory\FilterFactory;
use watoki\factory\Injector;

class ObjectTarget extends Target {

    const BEFORE_METHOD = 'before';

    const AFTER_METHOD = 'after';

    private $object;

    /** @var Factory */
    private $factory;

    /** @var FilterFactory <- */
    public $filterFactory;

    function __construct(Request $request, $object, Factory $factory) {
        parent::__construct($request);
        $this->object = $object;
        $this->factory = $factory;
    }

    public static function factory(Factory $factory, $object) {
        return new TargetFactory($factory, __CLASS__, array($object));
    }

    /**
     * @return mixed
     */
    function respond() {
        $class = new \ReflectionClass($this->object);

        if ($class->hasMethod(self::BEFORE_METHOD)) {
            $this->request = $class->getMethod(self::BEFORE_METHOD)->invokeArgs($this->object, array($this->request));
        }

        $response = $this->invoke($this->getMethodName());

        if ($class->hasMethod(self::AFTER_METHOD)) {
            $response = $class->getMethod(self::AFTER_METHOD)->invokeArgs($this->object, array($response));
        }

        return $response;
    }

    private function getMethodName() {
        return 'do' . ucfirst($this->request->getMethod());
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function invoke($name) {
        $arguments = $this->request->getArguments()->toArray();
        if (!array_key_exists('request', $arguments)) {
            $arguments['request'] = $this->request;
        }

        $injector = new Injector($this->factory);
        $reflection = new \ReflectionMethod($this->object, $name);
        $args = $injector->injectMethodArguments($reflection, $arguments, $this->filterFactory);

        return $reflection->invokeArgs($this->object, $args);
    }
}