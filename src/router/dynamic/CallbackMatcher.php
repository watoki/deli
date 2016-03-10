<?php
namespace watoki\deli\router\dynamic;

use watoki\deli\Request;

class CallbackMatcher implements Matcher {

    /** @var callable */
    private $callback;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    /**
     * @param \watoki\deli\Request $request
     * @return \watoki\deli\Request|null
     */
    public function matches(Request $request) {
        return call_user_func($this->callback, $request);
    }
}