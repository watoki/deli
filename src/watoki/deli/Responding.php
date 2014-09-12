<?php
namespace watoki\deli;
 
interface Responding {

    /**
     * @param Request $request
     * @return mixed
     */
    public function respond(Request $request);

}
 