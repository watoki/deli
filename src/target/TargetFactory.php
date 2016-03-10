<?php
namespace watoki\deli\target;

use watoki\deli\Request;
use watoki\deli\Target;
use watoki\factory\Factory;

class TargetFactory {

    /** @var Factory */
    private $factory;

    private $targetClass;

    private $arguments = array();

    /**
     * @param Factory $factory <-
     * @param string $targetClass
     * @param array $constructorArguments
     */
    public function __construct(Factory $factory, $targetClass, $constructorArguments = array()) {
        $this->factory = $factory;
        $this->targetClass = $targetClass;
        $this->arguments = $constructorArguments;
    }

    /**
     * @param Request $request
     * @return Target
     */
    public function create(Request $request) {
        return $this->factory->getInstance($this->targetClass, array_merge(array('request' => $request), $this->arguments));
    }

} 