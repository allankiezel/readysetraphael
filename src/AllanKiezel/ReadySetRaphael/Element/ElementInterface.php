<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/29/14
 * Time: 10:25 AM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

use AllanKiezel\ReadySetRaphael\Parser;

/**
 * Interface for elements.
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
interface ElementInterface
{
    /**
     * Initiate element
     *
     * @param \SimpleXMLElement $element Element to draw.
     */
    public function init(\SimpleXMLElement $element);

    /**
     * Generate and return the output string of the element.
     *
     * @return string Generated element JS string.
     */
    public function draw();
}