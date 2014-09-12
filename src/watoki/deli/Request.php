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

    /**
     * @param \watoki\deli\Path $context
     */
    public function setContext($context) {
        $this->context = $context;
    }

    /**
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * @param \watoki\deli\Path $target
     */
    public function setTarget($target) {
        $this->target = $target;
    }

    /**
     * @return static
     */
    public function copy() {
        return new Request(
            $this->context->copy(),
            $this->target->copy(),
            $this->method,
            $this->arguments->copy()
        );
    }

}
