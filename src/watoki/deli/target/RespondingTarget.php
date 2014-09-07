<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Responding;
use watoki\deli\Response;
use watoki\deli\Target;

class RespondingTarget extends Target {

    private $object;

    function __construct(Request $request, Responding $object) {
        parent::__construct($request);
        $this->object = $object;
    }

    public static function factory(Responding $object) {
        return new TargetFactory(__CLASS__, array($object));
    }

    /**
     * @return Response
     */
    function respond() {
        return $this->object->respond($this->request);
    }
}