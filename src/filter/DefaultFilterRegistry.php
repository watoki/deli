<?php
namespace watoki\deli\filter;
 
use watoki\collections\Map;

class DefaultFilterRegistry extends FilterRegistry {

    function __construct() {
        $this->registerFilter('array', new ArrayFilter());
        $this->registerFilter('boolean', new BooleanFilter());
        $this->registerFilter('float', new FloatFilter());
        $this->registerFilter('integer', new IntegerFilter());
        $this->registerFilter('string', new StringFilter());
        $this->registerFilter('DateTime', new DateTimeFilter());
        $this->registerFilter(Map::$CLASSNAME, new MapFilter());
    }

    /**
     * @param string $type
     * @return NullFilter|Filter
     */
    public function getFilter($type) {
        try {
            return parent::getFilter($type);
        } catch (\InvalidArgumentException $e) {
            return new NullFilter();
        }
    }

}
 