<?php
namespace noFlash\Waffer;

use BadFunctionCallException;
use BadMethodCallException;
use LogicException;

/**
 * Light configuration blackbox
 * If you need only simple configuration object use DietWaffer, but also look at full-blown Waffer
 *
 * @package noFlash\Waffer
 */
class DietWaffer
{
    public $storage = array();

    /**
     * @param array $baseConfiguration Default configuration
     */
    public function __construct($baseConfiguration = array())
    {
        $this->storage = array_replace_recursive($this->storage, $baseConfiguration);
    }

    /**
     * You can get property either by $dietWafferInstance->storage->propertyName or $dietWafferInstance->propertyName
     *
     * @param string $propertyName Name of configuration option to fetch
     *
     * @return mixed
     * @throws LogicException
     */
    public function __get($propertyName)
    {
        if (!isset($this->storage[$propertyName])) {
            throw new LogicException("There's no \"$propertyName\" configuration option");
        }

        return $this->storage[$propertyName];
    }

    /**
     * You can set property either by assiging to $dietWafferInstance->storage['propertyName'] or
     * $dietWafferInstance->propertyName
     *
     * @param string $propertyName Name of configuration option to set
     * @param mixed $propertyValue Value of configuration property
     */
    function __set($propertyName, $propertyValue)
    {
        $this->storage[$propertyName] = $propertyValue;
    }

    /**
     * Magic method which verifies if configuration property exists
     *
     * @param string $propertyName Name of configuration option to set
     *
     * @return bool
     */
    public function __isset($propertyName)
    {
        return isset($this->storage[$propertyName]);
    }

    /**
     * Removes configuration option
     *
     * @param string $propertyName Name of configurtion property to delete
     */
    public function __unset($propertyName)
    {
        unset($this->storage[$propertyName]);
    }

    /**
     * Waffer also provides automatic getters and setters
     * To retrive property value invoke $dietWafferInstance->getPropertyName(), to set it use setPropertyName(value)
     *
     * @param $methodName
     * @param $methodArguments
     *
     * @return mixed|void
     * @throws BadMethodCallException
     */
    public function __call($methodName, $methodArguments)
    {
        $propertyName = substr($methodName, 3);
        $methodName = substr($methodName, 0, 3);

        if ($methodName !== "get" && $methodName !== "set") {
            throw new BadMethodCallException("There's no such method");
        }

        if ($methodName === "get") {
            if (!isset($this->storage[$propertyName]) && !isset($methodArguments[0], $this->storage[$methodArguments[0]][$propertyName])) {
                throw new BadMethodCallException("There's no \"$propertyName\" configuration option");
            }

            return (isset($methodArguments[0])) ? //Namespace support
                $this->storage[$methodArguments[0]][$propertyName] :
                $this->storage[$propertyName];
        }

        if ($methodName === "set") {
            if (!isset($methodArguments[0])) {
                throw new BadMethodCallException("$methodName require value as argument");
            }

            if(isset($methodArguments[1])) { //Namespace support
                $this->storage[$methodArguments[1]][$propertyName] = $methodArguments[0];

            } else {
                $this->storage[$propertyName] = $methodArguments[0];
            }
        }
    }

    /**
     * You're structural programming fan? Waffer covers it too!
     * Just call class name as function to retrive full configuration, eg. $myConfig = $dietWafferInstance();
     *
     * @param null $namespace
     *
     * @return array
     * @throws BadFunctionCallException
     */
    public function __invoke($namespace = null)
    {
        if(isset($namespace)) {
            if(!isset($this->storage[$namespace])) {
                throw new BadFunctionCallException("There's no namespace $namespace");
            }

            return $this->storage[$namespace];
        }

        return $this->storage;
    }
}