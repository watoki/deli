<?php
namespace watoki\deli;
 
use watoki\collections\Liste;

class Path extends Liste {

    const SEPARATOR = '/';

    public static function fromString($string) {
        if ($string === '') {
            return new Path();
        }
        $string = self::resolve($string);
        return new Path(Liste::split(self::SEPARATOR, $string)->elements);
    }

    private static function resolve($path) {
        $re = array(
            '#/\./#' => '/',
            '#^\./#' => '',
            '#/(?!\.\.)[^/]+/\.\.#' => '',
            '#^(?!\.\.)[^/]+/\.\./#' => '');
        for ($n = 1; $n > 0;) {
            $path = preg_replace(array_keys($re), array_values($re), $path, -1, $n);
        }
        return $path;
    }

    public function isAbsolute() {
        return !$this->isEmpty() && $this->first() == '';
    }

    public function toString() {
        return self::resolve($this->join(self::SEPARATOR));
    }

    function __toString() {
        return $this->toString();
    }

}
