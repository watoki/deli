<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Response;
use watoki\deli\Target;
use watoki\factory\Factory;
use watoki\factory\Injector;

class ObjectTarget extends Target {

    private $object;

    /** @var Factory */
    private $factory;

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
        $injector = new Injector($this->factory);
        $arguments = $this->request->getArguments()->toArray();
        return $injector->injectMethod($this->object, $this->getMethodName(), $arguments);
    }

    private function getMethodName() {
        return 'do' . ucfirst($this->request->getMethod());
    }
}