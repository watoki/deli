<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Response;
use watoki\deli\Target;

class CallbackTarget extends Target {

    public static $CLASS = __CLASS__;

    /** @var callable */
    private $callback;

    /**
     * @param Request $request
     * @param callable $callback
     */
    function __construct(Request $request, $callback) {
        parent::__construct($request);
        $this->callback = $callback;
    }

    /**
     * @param callable $callback
     * @return TargetFactory
     */
    public static function factory($callback) {
        return new TargetFactory(self::$CLASS, array($callback));
    }

    /**
     * @return Response
     */
    function respond() {
        return call_user_func($this->callback, $this->request);
    }
}