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
 * @Inject parser
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

        $js = 'var ' . $varName . ' = ' . $this->svgName . '.path("' . $d . '")' . $this->generateAttributes(array('d'), $varName) . ".data('id', '$varName')" . ';';

        return $js;
    }
}