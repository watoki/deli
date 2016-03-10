<?php
namespace watoki\deli;

use watoki\collections\Map;

class Request {

    public static $REQUEST_CLASS = __CLASS__;

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
        return $this->arguments->copy();
    }

    /**
     * @param Map $arguments
     * @return Request
     */
    public function withArguments(Map $arguments) {
        $newUrl = $this->copy();
        $newUrl->arguments = $arguments;
        return $newUrl;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Request
     */
    public function withArgument($key, $value) {
        $newUrl = $this->copy();
        $newUrl->arguments->set($key, $value);
        return $newUrl;
    }

    /**
     * @param \watoki\deli\Path $context
     * @return static
     */
    public function withContext(Path $context) {
        $copy = $this->copy();
        $copy->context = $context;
        return $copy;
    }

    /**
     * @param string $method
     * @return static
     */
    public function withMethod($method) {
        $copy = $this->copy();
        $copy->method = $method;
        return $copy;
    }

    /**
     * @param \watoki\deli\Path $target
     * @return static
     */
    public function withTarget($target) {
        $copy = $this->copy();
        $copy->target = $target;
        return $copy;
    }

    /**
     * @return static
     */
    protected function copy() {
        return new Request(
                $this->context,
                $this->target,
                $this->method,
                $this->arguments->copy()
        );
    }

    public function toString() {
        return json_encode(array(
                'context' => $this->context->toString(),
                'target' => $this->target->toString(),
                'method' => $this->method,
                'arguments' => $this->arguments->toArray()
        ));
    }

    function __toString() {
        return $this->toString();
    }

}
