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
 * SVG Rect Element Output
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class RectElement extends AbstractElement
{
    /**
     * Generates the output string of current element.
     *
     * @return string Generated element JS string.
     */
    public function draw()
    {
        $varName = $this->generateVar('rect');

        $x = $this->getAttribute('x');
        $x = $x ? $x : 0;

        $y = $this->getAttribute('y');
        $y = $y ? $y : 0;

        $w = $this->getAttribute('width');
        $h = $this->getAttribute('height');

        $format = 'var %s = %s.rect(%s, %s, %s, %s)%s.data("id", "%1$s");';

        $js = sprintf($format, $varName, $this->svgName, $x, $y, $w, $h, $this->generateAttributes(array('width', 'height'), $varName));

        return $js;
    }
}