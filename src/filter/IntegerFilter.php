<?php
namespace watoki\deli\filter;

class IntegerFilter implements Filter {

    public function filter($value) {
        return intval($value);
    }
}