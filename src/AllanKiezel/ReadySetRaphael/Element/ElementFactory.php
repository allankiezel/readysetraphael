<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 4:47 PM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

/**
 * Factory loader for SVG Element classes.
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 */
class ElementFactory
{
    /**
     * @var string $factorySuffix Suffix of requested parser class.
     */
    protected static $factorySuffix = 'Element';

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Responsible for initiating and returning elements.
     *
     * @param string $type Type of parser class to initiate and return.
     *
     * @throws \Exception if no $type argument is passed.
     * @throws \Exception if class doesn't exist.
     *
     * @return mixed New class instance.
     */
    public static function create($type, $inSet = false)
    {
        if (!empty($type)) {
            $className = $type . static::$factorySuffix;
        } else {
            throw new \InvalidArgumentException('You must pass in a factory type.');
        }

        $reflector = new \ReflectionClass(get_called_class());
        $namespace = $reflector->getNamespaceName();

        $class = $namespace . '\\' . $className;

        if (class_exists($class)) {
            return new $class($inSet);
        } else {
            throw new \Exception('Class ' . $className . ' not found.');
        }
    }
}