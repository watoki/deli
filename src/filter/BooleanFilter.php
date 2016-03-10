<?php
namespace watoki\deli\filter;

class BooleanFilter implements Filter {

    public function filter($value) {
        return strtolower($value) == 'false' ? false : !!$value;
    }
}