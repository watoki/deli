<?php
namespace watoki\deli\target;

use watoki\deli\filter\FilterRegistry;
use watoki\deli\Request;
use watoki\deli\Target;
use watoki\factory\Factory;
use watoki\factory\providers\DefaultProvider;
use watoki\reflect\MethodAnalyzer;
use watoki\factory\providers\CallbackProvider;

class ObjectTarget extends Target {

    const BEFORE_METHOD = 'before';

    const AFTER_METHOD = 'after';

    private $object;

    /** @var callable */
    private $parameterInjectionFilter;

    /** @var Factory */
    private $factory;

    /** @var FilterRegistry */
    private $filters;

    /**
     * @param Request $request
     * @param object $object
     * @param Factory $factory <-
     */
    function __construct(Request $request, $object, Factory $factory) {
        parent::__construct($request);

        $this->object = $object;
        $this->factory = $factory;
        $this->filters = $factory->getInstance(FilterRegistry::class);

        $this->parameterInjectionFilter = function (\ReflectionParameter $parameter) {
            $pattern = '/@param.+\$' . $parameter->getName() . '.+' . DefaultProvider::INJECTION_TOKEN . '/';
            return preg_match($pattern, $parameter->getDeclaringFunction()->getDocComment());
        };
    }

    public static function factory(Factory $factory, $object) {
        return new TargetFactory($factory, __CLASS__, array('object' => $object));
    }

    /**
     * @return mixed
     */
    function respond() {
        $class = new \ReflectionClass($this->object);

        if ($class->hasMethod(self::BEFORE_METHOD)) {
            $newRequest = $class->getMethod(self::BEFORE_METHOD)->invokeArgs($this->object, array($this->request));
            if ($newRequest) {
                $this->request = $newRequest;
            }
        }

        $response = $this->invoke($this->getMethodName());

        if ($class->hasMethod(self::AFTER_METHOD)) {
            $newResponse = $class->getMethod(self::AFTER_METHOD)->invokeArgs($this->object, array($response, $this->request));
            if ($newResponse) {
                $response = $newResponse;
            }
        }

        return $response;
    }

    private function getMethodName() {
        return 'do' . ucfirst($this->request->getMethod());
    }

    /**
     * @param $name
     * @throws \BadMethodCallException If the method does not exist
     * @return mixed
     */
    protected function invoke($name) {
        try {
            $reflection = new \ReflectionMethod($this->object, $name);
        } catch (\ReflectionException $e) {
            $class = get_class($this->object);
            throw new \BadMethodCallException("Method [$name] does not exist in [{$class}]");
        }

        $this->factory->setProvider(Request::$REQUEST_CLASS, new CallbackProvider(function () {
            return $this->request;
        }));

        $analyzer = new MethodAnalyzer($reflection);

        $arguments = $this->request->getArguments()->toArray();
        $arguments = $this->filter($analyzer, $arguments);

        $factory = $this->factory;
        $arguments = $analyzer->fillParameters($arguments, function ($class) use ($factory) {
            return $factory->getInstance($class);
        }, $this->parameterInjectionFilter);

        return $reflection->invokeArgs($this->object, $arguments);
    }

    private function filter(MethodAnalyzer $analyzer, $arguments) {
        $args = $analyzer->normalize($arguments);
        foreach ($args as $name => $value) {
            $type = $analyzer->getTypeHint($analyzer->getParameter($name));
            if ($type) {
                $args[$name] = $this->filters->getFilter($type)->filter($args[$name]);
            }
        }
        return $args;
    }

    /**
     * @param callable $filter
     */
    public function setParameterInjectionFilter($filter) {
        $this->parameterInjectionFilter = $filter;
    }
}