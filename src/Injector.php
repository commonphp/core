<?php

/**
 * Injector Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use Closure;
use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Contracts\InjectorContract;
use CommonPHP\Core\Exceptions\InjectorException;
use CommonPHP\Core\Injectors\CoreInjector;
use CommonPHP\Core\Injectors\ServiceInjector;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Provides configurable Dependency Injection
 */
#[Service]
final class Injector
{
    /** @var array Collection of injector classes */
    private array $injectors = [];

    /** @var array Class aliases for contract injection */
    private array $aliases = [];

    /** @var Inspector The Inspector Component */
    private Inspector $inspector;

    /**
     * Instantiate this class
     *
     * @throws Exceptions\ServiceException
     * @throws InjectorException
     */
    public function __construct()
    {
        $this->inspector = new Inspector();
        $this->loadInjector(new CoreInjector($this));
        $this->loadInjector(new ServiceInjector(new ServiceManager($this)));
    }

    /**
     * Load an injector class
     *
     * @param InjectorContract $injector The Injector class to load
     * @return void
     * @throws InjectorException
     */
    public function loadInjector(InjectorContract $injector): void
    {
        $injectorContractClass = get_class($injector);
        if (array_key_exists($injectorContractClass, $this->injectors)) {
            throw new InjectorException('The injector ' . $injectorContractClass . ' has already been added');
        }
        $this->injectors[$injectorContractClass] = $injector;
    }

    /**
     * Get the Inspector component
     *
     * @return Inspector
     */
    public function getInspector(): Inspector
    {
        return $this->inspector;
    }

    /**
     * Check if an Injector class has been loaded
     *
     * @param string $injectorClass
     * @return bool
     */
    public function hasInjector(string $injectorClass): bool
    {
        return array_key_exists($injectorClass, $this->injectors);
    }

    /**
     * Get an injector class
     *
     * @param string $injectorClass The injector class to get
     * @return InjectorContract
     * @throws InjectorException
     */
    public function getInjector(string $injectorClass): InjectorContract
    {
        if (!$this->hasInjector($injectorClass))
        {
            throw new InjectorException('Injector has not been added: '.$injectorClass);
        }
        return $this->injectors[$injectorClass];
    }

    /**
     * Add an alias class for contract injection
     *
     * @param string $aliasClass The alias class name
     * @param string $realClass The real class name to use instead
     * @return void
     * @throws InjectorException
     */
    public function addAlias(string $aliasClass, string $realClass): void
    {
        if (!is_a($realClass, $aliasClass, true)) {
            throw new InjectorException($realClass . ' must inherit from ' . $aliasClass);
        }
        if ($this->hasAlias($aliasClass)) {
            if ($this->aliases[$aliasClass] !== $realClass) {
                throw new InjectorException($aliasClass . ' is already an alias of ' . $this->aliases[$aliasClass]);
            }
            return;
        }
        $this->aliases[$aliasClass] = $realClass;
    }

    /**
     * Check if an alias has been added
     *
     * @param string $aliasClass
     * @return bool
     */
    public function hasAlias(string $aliasClass): bool
    {
        return array_key_exists($aliasClass, $this->aliases);
    }

    /**
     * Invoke a method while injecting any known dependencies into the parameters
     *
     * @param string|object $target The object or class name to invoke on
     * @param string $methodName The name of the method
     * @param array $parameters Named parameters to inject
     * @return mixed
     * @throws InjectorException
     */
    public function invoke(string|object $target, string $methodName, array $parameters = []): mixed
    {
        if (is_string($target)) {
            $target = $this->instantiate($target);
        }
        try {
            $method = new ReflectionMethod($target, $methodName);
        } catch (ReflectionException $e) {
            throw new InjectorException($e->getMessage(), 0, $e);
        }
        return $this->parameterize($method, $parameters, function (array $arguments) use ($target, $method) {
            try {
                return $method->invokeArgs($target, $arguments);
            } catch (ReflectionException $e) {
                throw new InjectorException($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Instantiate a new class while injecting any known dependencies into the constructor
     *
     * @param string $className The name of the class to instantiate
     * @param array $parameters Named parameters to include
     * @param bool $bypassInjectors Bypasses injector classes in the event that this call creates a recursive loop
     * @return object
     * @throws InjectorException
     */
    public function instantiate(string $className, array $parameters = [], bool $bypassInjectors = false): object
    {
        if ($this->hasAlias($className)) {
            $className = $this->aliases[$className];
        }

        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new InjectorException($e->getMessage(), 0, $e);
        }

        if (!$bypassInjectors) {
            foreach ($this->injectors as $injector) {
                if ($injector->check($className, !$class->isUserDefined())) {
                    return $injector->get($className, $className);
                }
            }
        }

        if (!$class->isInstantiable()) {
            throw new InjectorException($className . ' cannot be instantiated');
        }

        return $this->parameterize($class->getConstructor(), $parameters, function (array $arguments) use ($class) {
            try {
                return $class->newInstanceArgs($arguments);
            } catch (ReflectionException $e) {
                throw new InjectorException($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Iterate the parameters of a method or function and extract the dependencies
     *
     * @param ReflectionMethod|ReflectionFunction|null $source The source method or function, null will return an empty array
     * @param array $parameters The named parameters to include
     * @param Closure $onInvoke The Closure to invoke, with the array of parameters, on completion
     * @return mixed
     * @throws InjectorException
     */
    private function parameterize(null|ReflectionMethod|ReflectionFunction $source, array $parameters, Closure $onInvoke): mixed
    {
        $arguments = [];
        if ($source !== null && $source->getNumberOfParameters() > 0) {
            foreach ($source->getParameters() as $parameter) {
                $arguments[] = $this->getValueOf(
                    $parameter->getName(),
                    $parameter->getType(),
                    (!array_key_exists($parameter->getName(), $parameters) && $parameter->isDefaultValueAvailable() ? [$parameter->getName() => $parameter->getDefaultValue()] : $parameters),
                    'Parameter ' . $parameter->getName() . ' on ' . ($source instanceof ReflectionMethod ? ($source->getDeclaringClass()->getName() . '->') : '') . $source->getName());
            }
        }
        return $onInvoke($arguments);
    }

    /**
     * Get the injected value for the specified properties
     *
     * @param string $name The name of the parameter or property
     * @param ReflectionType|null $type The ReflectionType of the property
     * @param array $values Other values to include
     * @param string $signature The signature of the parameter or property
     * @return mixed
     * @throws InjectorException
     */
    private function getValueOf(string $name, null|ReflectionType $type, array $values, string $signature): mixed
    {
        if (array_key_exists($name, $values)) {
            return $values[$name];
        }

        $allowsNull = true;

        if ($type !== null) {
            $allowsNull = false;
            foreach ($this->getReflectionNamedTypes($type) as $namedType) {
                if ($namedType->allowsNull() && !$allowsNull) $allowsNull = true;
                if ($namedType->isBuiltin()) continue;
                try {
                    $class = new ReflectionClass($namedType->getName());
                } catch (ReflectionException $e) {
                    throw new InjectorException($e->getMessage(), 0, $e);
                }
                foreach ($this->injectors as $injector) {
                    if ($injector->check($namedType->getName(), !$class->isUserDefined())) {
                        return $injector->get($namedType->getName(), $signature);
                    }
                }
            }
        }

        if ($allowsNull) return null;

        throw new InjectorException('Could not find a set value or injector for ' . $signature);
    }

    /**
     * Convert a ReflectionType to an array of ReflectionNamedTypes
     *
     * @param ReflectionType $type The type to convert
     * @return array
     * @throws InjectorException
     */
    private function getReflectionNamedTypes(ReflectionType $type): array
    {
        if ($type instanceof ReflectionNamedType) {
            return [$type];
        } else if ($type instanceof ReflectionUnionType) {
            return $type->getTypes();
        } else if ($type instanceof ReflectionIntersectionType) {
            return call_user_func_array('array_merge', array_map([$this, 'getReflectionNamedTypes'], $type->getTypes()));
        }
        throw new InjectorException('Unsupported reflection type: ' . get_class($type));
    }

    /**
     * Invoke a function while injecting any known dependencies into the parameters
     *
     * @param string $functionName The name of the function to invoke
     * @param array $parameters The parameters to include in the injection
     * @return mixed
     * @throws InjectorException
     */
    public function call(string $functionName, array $parameters = []): mixed
    {
        try {
            $function = new ReflectionFunction($functionName);
        } catch (ReflectionException $e) {
            throw new InjectorException($e->getMessage(), 0, $e);
        }
        return $this->parameterize($function, $parameters, function (array $arguments) use ($function) {
            try {
                return $function->invokeArgs($arguments);
            } catch (ReflectionException $e) {
                throw new InjectorException($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Inject a class by setting the value of dependency properties
     *
     * @param object $target The target class to inject
     * @return object
     * @throws InjectorException
     */
    public function inject(object $target): object
    {
        try {
            foreach ($this->inspector->iterateProperties($target) as $property) {
                if ($property->isInitialized($target) || !$property->hasType()) continue; // Bypass typeless or initialized properties
                foreach ($this->getReflectionNamedTypes($property->getType()) as $type) {
                    foreach ($this->injectors as $injector) {
                        if ($injector->check($type->getName(), $type->isBuiltin())) {
                            $property->setValue($target, $injector->get($type->getName(), $property->getDeclaringClass()->getName() . '->' . $property->getName()));
                            break 2;
                        }
                    }
                }
            }
        } catch (ReflectionException $e) {
            throw new InjectorException($e->getMessage(), 0, $e);
        }
        return $target;
    }
}