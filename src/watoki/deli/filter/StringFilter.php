<?php
namespace watoki\deli\filter;

use watoki\factory\Filter;

class StringFilter implements Filter {

    public function filter($value) {
        return strval($value);
    }
}