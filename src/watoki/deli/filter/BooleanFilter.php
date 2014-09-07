<?php
namespace watoki\deli\filter;

use watoki\factory\Filter;

class BooleanFilter implements Filter {

    public function filter($value) {
        return strtolower($value) == 'false' ? false : !!$value;
    }
}