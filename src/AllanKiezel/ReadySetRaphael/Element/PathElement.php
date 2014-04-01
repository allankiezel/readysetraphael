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
 * Class PathElement
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class PathElement extends AbstractElement
{
    /**
     * Generates the output string of current element.
     *
     * @return string Generated element JS string.
     */
    public function draw()
    {
        $varName = $this->generateVar('path');

        $d = $this->getAttribute('d');

        $format  = 'var %s = %s.path("%s")%s.data("id", "%1$s");';

        $js = sprintf($format, $varName, $this->svgName, $d, $this->generateAttributes(array('d'), $varName));

        return $js;
    }
}