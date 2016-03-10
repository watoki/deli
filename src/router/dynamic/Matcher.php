<?php
namespace watoki\deli\router\dynamic;

use watoki\deli\Request;

interface Matcher {

    /**
     * @param Request $request
     * @return Request|null
     */
    public function matches(Request $request);

} 