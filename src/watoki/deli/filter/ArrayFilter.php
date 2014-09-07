<?php
namespace watoki\deli\filter;

use watoki\factory\Filter;

class ArrayFilter implements Filter {

    public function filter($value) {
        return (array) $value;
    }
}