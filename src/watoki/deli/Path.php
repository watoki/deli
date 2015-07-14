<?php
namespace watoki\deli;
 
class Path {

    const SEPARATOR = '/';

    /**
     * @var array
     */
    protected $elements = array();

    /**
     * @param array|Path|mixed $elements
     */
    public function __construct($elements = array()) {
        if ($elements instanceof Path) {
            $this->elements = $elements->elements;
        } else if (count(func_get_args()) > 1) {
            $this->elements = func_get_args();
        } else {
            $this->elements = $elements;
        }
    }

    /**
     * @param string $string
     * @return Path
     */
    public static function fromString($string) {
        if (!$string) {
            return new Path();
        }

        return new Path(explode(self::SEPARATOR, self::resolve($string)));
    }

    /**
     * @return array
     */
    public function getElements() {
        return $this->elements;
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

    /**
     * @param array|Path|mixed $elements
     * @return static
     */
    public function with($elements) {
        $newPath = $this->copy();

        if ($elements instanceof Path) {
            $newPath->elements = $elements->elements;
        } else if (count(func_get_args()) > 1) {
            $newPath->elements = func_get_args();
        } else {
            $newPath->elements = $elements;
        }

        if ($this->isAbsolute() && !$newPath->isAbsolute()) {
            array_unshift($newPath->elements, '');
        }

        return $newPath;
    }

    /**
     * @param $element
     * @return static
     */
    public function appended($element) {
        $copy = $this->copy();
        $copy->elements[] = $element;
        return $copy;
    }

    public function appendedAll($elements) {
        $copy = $this->copy();
        foreach ($elements as $element) {
            $copy->elements[] = $element;
        }
        return $copy;
    }

    /**
     * @return bool
     */
    public function isAbsolute() {
        return count($this->elements) && $this->elements[0] == '';
    }

    /**+
     * @return string
     */
    public function toString() {
        return self::resolve(implode(self::SEPARATOR, $this->elements));
    }

    function __toString() {
        return $this->toString();
    }

    public function isEmpty() {
        return (!count($this->elements));
    }

    /**
     * @return static
     */
    protected function copy() {
        return new Path($this->elements);
    }

}
