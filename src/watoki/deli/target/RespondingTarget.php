<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Responding;
use watoki\deli\Target;
use watoki\factory\Factory;

class RespondingTarget extends Target {

    private $object;

    function __construct(Request $request, Responding $object) {
        parent::__construct($request);
        $this->object = $object;
    }

    public static function factory(Factory $factory, Responding $object) {
        return new TargetFactory($factory, __CLASS__, array($object));
    }

    /**
     * @return mixed
     */
    function respond() {
        return $this->object->respond($this->request);
    }
}