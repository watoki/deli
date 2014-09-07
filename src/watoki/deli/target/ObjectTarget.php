<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Response;
use watoki\deli\Target;

class ObjectTarget extends Target {

    private $object;

    function __construct(Request $request, $object) {
        parent::__construct($request);
        $this->object = $object;
    }

    public static function factory($object) {
        return new TargetFactory(__CLASS__, array($object));
    }

    /**
     * @return Response
     */
    function respond() {
        $method = new \ReflectionMethod($this->object, $this->getMethodName());
        return $method->invoke($this->object);
    }

    private function getMethodName() {
        return 'do' . ucfirst($this->request->getMethod());
    }
}