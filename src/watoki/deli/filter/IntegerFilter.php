<?php
namespace watoki\deli\filter;

use watoki\factory\Filter;

class IntegerFilter implements Filter {

    public function filter($value) {
        return intval($value);
    }
}