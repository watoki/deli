<?php
namespace watoki\deli;

interface ResponseDeliverer {

    /**
     * @param mixed $response
     * @return null
     */
    public function deliver($response);

} 