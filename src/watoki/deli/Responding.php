<?php
namespace watoki\deli;
 
interface Responding {

    /**
     * @param Request $request
     * @return Response
     */
    public function respond(Request $request);

}
 