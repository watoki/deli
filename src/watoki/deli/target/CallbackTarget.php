<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Target;
use watoki\factory\Factory;

class CallbackTarget extends Target {

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

    public static function factory($callback) {
        return new TargetFactory(new Factory(), __CLASS__, array('callback' => $callback));
    }

    /**
     * @return mixed
     */
    function respond() {
        return call_user_func($this->callback, $this->request);
    }
}