<?php

namespace Perfbase\SDK\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;

abstract class BaseTest extends TestCase
{
    /**
     * Retrieves the value of a private or protected property from an object.
     *
     * @param object $object The object containing the private property.
     * @param string $propertyName The name of the property to access.
     * @return mixed The value of the private property.
     * @throws ReflectionException If the property does not exist.
     */
    protected function getPrivateFieldValue(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Sets the value of a private or protected property on an object.
     *
     * @param object $object The object containing the private property.
     * @param string $propertyName The name of the property to modify.
     * @param mixed $value The new value to set.
     * @throws ReflectionException If the property does not exist.
     */
    protected function setPrivateField(object $object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }


    /**
     * Invokes a private or protected method on an object.
     *
     * @param object $object The object containing the private method.
     * @param string $methodName The name of the method to invoke.
     * @param array $parameters The parameters to pass to the method.
     * @return mixed The result of the method invocation.
     * @throws \ReflectionException If the method does not exist.
     */
    protected function invokePrivateMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
