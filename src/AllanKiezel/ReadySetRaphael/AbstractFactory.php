<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/29/14
 * Time: 3:57 PM
 */

namespace AllanKiezel\ReadySetRaphael;

/**
 * Factory loader for parsers.
 *
 * @package AllanKiezel\ReadySetRaphael
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
abstract class AbstractFactory
{

    /**
     * @var string $factorySuffix Suffix of requested parser class.
     */
    protected static $factorySuffix = 'Parser';

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Responsible for initiating and returning parsers.
     *
     * @param string $type Type of parser class to initiate and return.
     *
     * @throws \Exception if no $type argument is passed.
     * @throws \Exception if class doesn't exist.
     *
     * @return mixed New class instance.
     */
    public static function create($type)
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
            return new $class();
        } else {
            throw new \Exception('Class ' . $className . ' not found.');
        }
    }

}