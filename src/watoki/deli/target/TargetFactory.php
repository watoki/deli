<?php
namespace watoki\deli\target;

use watoki\deli\Request;

class TargetFactory {

    private $targetClass;

    private $arguments = array();

    public function __construct($targetClass, $constructorArguments = array()) {
        $this->targetClass = $targetClass;
        $this->arguments = $constructorArguments;
    }

    public function create(Request $request) {
        $class = new \ReflectionClass($this->targetClass);
        return $class->newInstanceArgs(array_merge(array($request), $this->arguments));
    }

} 