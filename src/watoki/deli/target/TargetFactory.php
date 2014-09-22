<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\factory\Factory;

class TargetFactory {

    /** @var Factory */
    private $factory;

    private $targetClass;

    private $arguments = array();

    public function __construct(Factory $factory, $targetClass, $constructorArguments = array()) {
        $this->factory = $factory;
        $this->targetClass = $targetClass;
        $this->arguments = $constructorArguments;
    }

    public function create(Request $request) {
        return $this->factory->getInstance($this->targetClass, array_merge(array('request' => $request), $this->arguments));
    }

} 