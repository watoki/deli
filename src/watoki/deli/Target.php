<?php
namespace watoki\deli;

abstract class Target {

    /** @var Request */
    protected $request;

    function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * @return Response
     */
    abstract function respond();

} 