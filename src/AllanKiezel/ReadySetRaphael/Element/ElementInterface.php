<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/29/14
 * Time: 10:25 AM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

/**
 * Interface for elements.
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
interface ElementInterface
{
    /**
     * Should generate and return the output string of the element
     */
    public function draw();

}