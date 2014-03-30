<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 4:35 PM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

use AllanKiezel\ReadySetRaphael\Element\ElementInterface;

/**
 * Abstract base element class.
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 */
class AbstractElement implements ElementInterface
{
    /**
     * Generates the output string of current element.
     */
    public function draw()
    {
        echo "draw() method called.";
    }
}