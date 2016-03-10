<?php
namespace watoki\deli\filter;

class DateTimeFilter implements Filter {

    public function filter($value) {
        if (!$value) {
            return null;
        }
        return new \DateTime($value);
    }
}