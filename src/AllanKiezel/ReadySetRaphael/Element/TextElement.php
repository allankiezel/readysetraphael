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
 * SVG Text Element Output
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class TextElement extends AbstractElement
{
    /**
     * Generates the output string of current element.
     *
     * @return string Generated element JS string.
     */
    public function draw()
    {
        $varName = $this->generateVar('text');

        $x = $this->getAttribute('x');
        $x = !empty($x) ? $x : 0;

        $y = $this->getAttribute('y');
        $y = !empty($y) ? $y : 0;

        $text = (string)$this->getElement();

        if (isset($this->getElement()->tspan) && $text === '') {

            for ($i = 0, $len = count($this->getElement()->tspan) - 1; $i <= $len; $i++) {
                $text .= $this->getElement()->tspan[$i] . '\n';
            }

            $text = substr($text, 0, -2);
        }

        if ($text === '') {

            return false;
        }

        $format = 'var %s = %s.text(%s, %s, \'%s\')%s.data("id", "%1$s");';

        $js = sprintf($format, $varName, $this->svgName, $x, $y, $text, $this->generateAttributes(array('x', 'y'), $varName));

        return $js;
    }
}