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
 * SVG Ellipse Element Output
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class EllipseElement extends AbstractElement
{
    /**
     * Generates the output string of current element.
     *
     * @return string Generated element JS string.
     */
    public function draw()
    {
        $varName = $this->generateVar('ellipse');

        $cx = $this->getAttribute('cx');
        $cy = $this->getAttribute('cy');
        $rx = $this->getAttribute('rx');
        $ry = $this->getAttribute('ry');

        $format = 'var %s = %s.ellipse(%s, %s, %s, %s)%s.data("id", "%1$s");';

        $js = sprintf($format, $varName, $this->svgName, $cx, $cy, $rx, $ry, $this->generateAttributes(array('cx', 'cy', 'rx', 'ry'), $varName));

        return $js;
    }
}