<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 4:48 PM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

use AllanKiezel\ReadySetRaphael\Element\AbstractElement;
use AllanKiezel\ReadySetRaphael\Parser;

/**
 * Class PolygonElement
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class PolygonElement extends AbstractElement
{
    /**
     * Generates the output string of current element.
     *
     * @return string Generated element JS string.
     */
    public function draw()
    {
        //echo $this->getElement()->attributes()['points'];
        $varName = $this->generateVar('path');

        $d = sprintf("M %s %s", $this->getAttribute('points'), 'z');

        $format  = 'var %s = %s.path("%s")%s.data("id", "%1$s");';

        $js = sprintf($format, $varName, $this->svgName, $d, $this->generateAttributes(array('points'), $varName));

        return $js;

    }
}