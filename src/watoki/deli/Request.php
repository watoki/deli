<?php
namespace watoki\deli;

use watoki\collections\Map;

class Request {

    /** @var Path */
    private $context;

    /** @var Path */
    private $target;

    /** @var string */
    private $method;

    /** @var Map */
    private $arguments;

    function __construct(Path $context, Path $target, $method = null, Map $arguments = null) {
        $this->context = $context;
        $this->target = $target;
        $this->method = $method;
        $this->arguments = $arguments ? : new Map();
    }

    /**
     * @return \watoki\deli\Path
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @return \watoki\deli\Path
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return \watoki\collections\Map
     */
    public function getArguments() {
        return $this->arguments;
    }

}
