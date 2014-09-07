<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Response;
use watoki\deli\Target;
use watoki\factory\Factory;
use watoki\factory\FilterFactory;
use watoki\factory\Injector;

class ObjectTarget extends Target {

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
     * @return Response
     */
    function respond() {
        $arguments = $this->request->getArguments()->toArray();

        $injector = new Injector($this->factory);
        $reflection = new \ReflectionMethod($this->object, $this->getMethodName());
        $args = $injector->injectMethodArguments($reflection, $arguments, $this->filterFactory);

        return $reflection->invokeArgs($this->object, $args);
    }

    private function getMethodName() {
        return 'do' . ucfirst($this->request->getMethod());
    }
}