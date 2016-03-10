<?php
namespace watoki\deli\filter;

use watoki\collections\Map;

class MapFilter implements Filter {

    public function filter($value) {
        return new Map  ((array)$value);
    }
}