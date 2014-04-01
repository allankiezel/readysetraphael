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
 * SVG Circle Element Output
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class CircleElement extends AbstractElement
{
    /**
     * Generates the output string of current element.
     *
     * @return string Generated element JS string.
     */
    public function draw()
    {
        $varName = $this->generateVar('circle');

        $cx = round($this->getAttribute('cx'));
        $cy = round($this->getAttribute('cy'));
        $r = round($this->getAttribute('r'));

        $format = 'var %s = %s.circle(%s, %s, %s)%s.data("id", "%1$s");';

        $js = sprintf($format, $varName, $this->svgName, $cx, $cy, $r, $this->generateAttributes(array('cx', 'cy', 'r'), $varName));

        return $js;
    }
}