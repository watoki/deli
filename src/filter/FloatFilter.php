<?php
namespace watoki\deli\filter;

class FloatFilter implements Filter {

    public function filter($value) {
        return floatval($value);
    }
}