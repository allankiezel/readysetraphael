<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 4:47 PM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

use AllanKiezel\ReadySetRaphael\AbstractFactory;

/**
 * Factory loader for SVG Element classes.
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 */
class ElementFactory extends AbstractFactory
{
    /**
     * @var string $factorySuffix Suffix of requested parser class.
     */
    protected static $factorySuffix = 'Element';
}