<?php
namespace watoki\deli\filter;
 
class FilterRegistry {

    /** @var array|Filter[] */
    private $filters = array();

    /**
     * @param string $type
     * @throws \Exception if Filter for type can't be found
     * @return Filter
     */
    public function getFilter($type) {
        $originalType = $type;
        while ($type) {
            $normalized = $this->normalizeType($type);
            if (array_key_exists($normalized, $this->filters)) {
                return $this->filters[$normalized];
            }
            $type = is_object($type) ? get_parent_class($type) : null;
        }
        throw new \InvalidArgumentException("Could not find filter for [$originalType]");
    }

    public function registerFilter($type, Filter $filter) {
        $this->filters[$this->normalizeType($type)] = $filter;
    }

    private function normalizeType($type) {
        $normalized = trim(strtolower($type), '\\');

        switch ($normalized) {
            case 'int':
                return 'integer';
            case 'bool':
                return 'boolean';
            default:
                return $normalized;
        }
    }
}
 