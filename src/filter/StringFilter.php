<?php
namespace watoki\deli\filter;

class StringFilter implements Filter {

    public function filter($value) {
        return strval($value);
    }
}