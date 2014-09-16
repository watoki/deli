<?php
namespace watoki\deli\filter;

class NullFilter implements Filter {

    public function filter($value) {
        return $value;
    }
}
 