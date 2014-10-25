<?php
namespace watoki\deli\router\dynamic;

use watoki\deli\Path;
use watoki\deli\Request;
use watoki\deli\router\dynamic\Matcher;

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
        $nextRequest = $request->copy();
        $target = $request->getTarget();

        for ($i = 0; $i < $this->path->count(); $i++) {
            $p = $this->path->get($i);

            if ($i >= $target->count()) {
                return null;
            } else if (substr($p, 0, 1) == '{' && substr($p, -1) == '}') {
                $key = substr($p, 1, -1);
                $value = $target->get($i);
                $nextRequest->getArguments()->set($key, $value);
            } else if ($target->get($i) != $p) {
                return null;
            }
        }

        $nextContext = $request->getContext()->copy();
        foreach (new Path($target->slice(0, $i)->toArray()) as $part) {
            $nextContext->append($part);
        }

        $nextRequest->setContext($nextContext);
        $nextRequest->setTarget(new Path($target->slice($i)->toArray()));

        return $nextRequest;
    }
}