<?php
namespace watoki\deli\filter;

use watoki\factory\Filter;

class FloatFilter implements Filter {

    public function filter($value) {
        return floatval($value);
    }
}