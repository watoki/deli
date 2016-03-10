<?php
namespace watoki\deli;

interface Router {

    /**
     * @param Request $request
     * @return Target
     */
    public function route(Request $request);

} 