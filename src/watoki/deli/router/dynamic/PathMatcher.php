<?php
namespace watoki\deli\router\dynamic;

use watoki\deli\Path;
use watoki\deli\Request;

class PathMatcher implements Matcher {

    /** @var Path */
    private $path;

    function __construct(Path $path) {
        $this->path = $path;
    }

    /**
     * @param \watoki\deli\Request $request
     * @return \watoki\deli\Request|null
     */
    public function matches(Request $request) {
        if (!$this->path->getElements()) {
            return $request;
        }

        $target = $request->getTarget()->getElements();
        $elements = $this->path->getElements();

        for ($i = 0; $i < count($elements); $i++) {
            $p = $elements[$i];

            if ($i >= count($target)) {
                return null;
            } else if (substr($p, 0, 1) == '{' && substr($p, -1) == '}') {
                $key = substr($p, 1, -1);
                $value = $target[$i];
                $request = $request->withArgument($key, $value);
            } else if ($target[$i] != $p) {
                return null;
            }
        }

        return $request
            ->withContext($request->getContext()->appendedAll(array_slice($target, 0, $i - 1)))
            ->withTarget(new Path(array_slice($target, $i - 1)));
    }
}