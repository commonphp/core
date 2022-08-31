<?php

/**
 * Validator Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Contracts\ValidatorContract;
use CommonPHP\Core\Exceptions\CoreException;
use CommonPHP\Core\Exceptions\ValidatorException;
use ReflectionException;

/**
 * Validate the properties of an object
 */
#[Service]
final class Validator
{
    /** @var Inspector The Inspector component */
    private Inspector $inspector;

    /**
     * Instantiate this class
     *
     * @param Inspector $inspector The Inspector component
     */
    public function __construct(Inspector $inspector)
    {
        $this->inspector = $inspector;
    }

    /**
     * Validate an object
     *
     * @param object $source The object to validate
     * @return void
     * @throws ValidatorException
     * @throws CoreException
     */
    public function validate(object $source): void
    {
        $result = [];
        try {
            $properties = $this->inspector->iterateProperties($source);
        } catch (ReflectionException $e) {
            throw new CoreException($e->getMessage(), 0, $e);
        }
        foreach ($properties as $property) {
            /** @var ValidatorContract[] $attributes */
            $attributes = $this->inspector->getMultipleReflectedAttributes($property, ValidatorContract::class);
            foreach ($attributes as $attribute) {
                $errors = [];
                if (!$attribute->check($property->getName(), ($property->isInitialized($source) ? $property->getValue($source) : null), $errors)) {
                    if (!array_key_exists($property->getName(), $result)) {
                        $result[$property->getName()] = [];
                    }
                    $result[$property->getName()] = array_merge($result[$property->getName()], $errors);
                }
            }
        }
        if (count($result) > 0) {
            throw new ValidatorException($result);
        }
    }
}