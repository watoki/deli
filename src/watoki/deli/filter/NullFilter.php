<?php
namespace watoki\deli\filter;

use watoki\factory\Filter;

class NullFilter implements Filter {

    public function filter($value) {
        return $value;
    }
}
 