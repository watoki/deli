<?php
namespace watoki\deli\filter;

class ArrayFilter implements Filter {

    public function filter($value) {
        return (array) $value;
    }
}