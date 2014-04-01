<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 4:35 PM
 */

namespace AllanKiezel\ReadySetRaphael\Element;

use AllanKiezel\ReadySetRaphael\Element\ElementInterface;
use AllanKiezel\ReadySetRaphael\SVG;

/**
 * Abstract base element class.
 *
 * @package AllanKiezel\ReadySetRaphael\Element
 */
abstract class AbstractElement implements ElementInterface
{
    /**
     * @var \SimpleXMLElement $element SVG element.
     */
    protected $element = null;

    /**
     * @var array $gradients All gradients residing in SVG.
     */
    protected $gradients = array();

    /** @var object $svg Entire SVG Object. */
    protected $svg;

    /**
     * @var bool $inSet Defines whether draw() adds to a "set" (group).
     */
    private $inSet = false;

    /**
     * Constructs.
     *
     * @param bool $inSet Is element a part of a set?
     */
    public function __construct($inSet = false)
    {
        $this->svg = SVG::getSVG();
        $this->gradients = SVG::getGradients();
        $this->svgName = SVG::getName();

        if ($inSet) {
            $this->inSet = true;
        }
    }

    /**
     * Returns attribute value of current element.
     *
     * @param string $attribute Attribute value to retrieve.
     *
     * @return mixed Attribute value.
     */
    public function getAttribute($attribute)
    {
        $attributes = $this->getAttributes();

        return $attributes[$attribute];
    }

    /**
     * Returns all attributes of current element.
     *
     * @return \SimpleXMLElement Element attributes.
     */
    public function getAttributes()
    {
        return $this->element->attributes();
    }

    /**
     * Element getter.
     *
     * @return \SimpleXMLElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Element setter.
     *
     * @param \SimpleXMLElement $element
     */
    public function setElement($element)
    {
        $this->element = $element;
    }

    /**
     * Initiate element
     *
     * @param \SimpleXMLElement $element Element to draw.
     */
    public function init(\SimpleXMLElement $element)
    {
        $this->setElement($element);
    }

    /**
     * Creates attribute string for current element
     *
     * @param array $ignored Array holding attributes to ignore
     * @param string $var Object getting attributes assigned to
     *
     * @return string
     */
    protected function generateAttributes($ignored = array(), $var = '')
    {
        if (isset($this->getAttributes()->style)) {

            $vals = explode(';',
                $this->getAttributes()->style);

            foreach ($vals as $sep) {

                list ($k, $v) = explode(":", $sep);
                $this->getElement()->addAttribute($k, $v);
            }

        }

        $attrs = $this->getAttributes();
        unset($attrs['style']);

        $a = "";
        $transform = false;
        $t = "";

        foreach ($attrs as $key => $value) {

            $value = str_replace("'", '', $value);
            if (($key != 'transform') && ($key != 'transformG')) {

                // CHECK TO MAKE SURE WE CAN USE THIS ATTRIBUTE
                if (!in_array($key, $ignored)) {

                    $k = $key;
                    $v = $value;

                    if (strstr($k, '-') !== FALSE) {

                        if ($k == 'stroke-width') {
                            $v = round($v, 2);
                        }
                        $a .= '"' . $k . '"' . ": '$v',";

                        // Check if we have a fill attribute and needs gradient
                    } elseif (($k == 'fill') &&
                        (preg_match('/^url\(#(.+)\)/', $v, $m) >= 1)
                    ) {

                        $gID = $m[1];

                        $fill = $this->processGradient($gID);
                        $f = explode('__', $fill);
                        $a .= "fill: '" . $f[0] . "',";
                        $a .= "'fill-opacity': '" . $f[1] . "',";

                        if (isset($this->gradients[$gID]['scale'])) {

                            $scale = $this->gradients[$gID]['scale'];
                            $scale = str_replace('scale(', '', $scale);
                            $scale = str_replace(')', '', $scale);

                        }
                    } elseif ($k == 'stroke' && $v == 'none') {

                        $a .= "stroke: 'none',";

                        if (strstr($value, 'stroke-width') === FALSE) {

                            $a .= "'stroke-width':'1',";
                        }

                        if (strstr($value, 'stroke-opacity') === FALSE) {

                            $a .= "'stroke-opacity':'1',";
                        }
                    } else {

                        $a .= $k . ": '$v',";
                    }
                }
            } else {

                $transform = true;

                preg_match('/^(.+)\((.+)\)/', $value, $m);

                $type = $m[1];
                $args = $m[2];

                switch ($type) {

                    case 'matrix':

                        $form = 'm' . $args . ' ';

                        break;

                    case 'translate':

                        $form = 't' . $args . ' ';

                        break;
                }

                if (strstr($this->getAttributes()->transformG,
                        $type) !== FALSE
                ) {

                    $t = $form . $t;
                } else {

                    $t .= $form;
                }
            } // END IF TRANSFORM
        }

        if (strlen($t) > 0) {
            $t = substr($t, 0, -1);
        }

        if (strstr($a, 'stroke-width') === FALSE) {

            // Raphael adds a default stroke to paths
            // If there is none add a stroke-width:0 hack
            $a .= "'stroke-width': '0',";
        }

        if (strstr($a, 'stroke-opacity') === FALSE) {

            // Raphael adds a default stroke to paths
            // If there is none add a stroke-width:0 hack
            $a .= "'stroke-opacity': '1',";
        }

        if (!isset($attrs['fill'])) {

            $a .= "'fill': '#000000',";
        }

        $a = substr($a, 0, -1);

        if ($this->inSet) {
            if (!$transform) {
                return sprintf(".attr({%s})", $a);
            } else {
                return sprintf(".attr({%s}).transform(\"%s\")", $a, $t);
            }
        } else {
            if (!$transform) {
                return sprintf(";\n%s.attr({%s})", $var, $a);
            } else {
                return sprintf(";\n%s.attr({%s});\n%s.transform(\"%s\")", $var,
                    $a, $var, $t);
            }
        }
    }

    /**
     * Generates unique id for elements
     */
    protected function generateVar($prefix = '')
    {
        // Check if ID value was passed in for current element
        $attrs = $this->getAttributes();

        $id = '';

        if (isset($attrs['id'])) {
            $id = $attrs['id'];
            $id = str_replace('.', '', $id);
            $id = str_replace('-', '', $id);
        } else {
            $id = $prefix . '_' . $this->generateRandomString(10);
        }

        return $id;
    }

    /**
     * Retrieves gradient information for attribute and
     * builds the gradient fill attribute
     */
    protected function processGradient($id)
    {

        // Raphael sample
        /*
         * linear gradient: “‹angle›-‹colour›[-‹colour›[:‹offset›]]*-‹colour›”,
         * “90-#fff-#000” – 90° gradient from white to black or
         * “0-#fff-#f00:20-#000” – 0° gradient from white via red (at 20%) to
         * black
         */

        // Check if it uses an inkscape setup and we should
        // grab values from another gradient element
        if (array_key_exists('xlink-href', $this->gradients[$id])) {

            $second = str_replace('#', '', $this->gradients[$id]['xlink-href']);

            $gradient = array_merge($this->gradients[$id],
                $this->gradients[$second]);
        } else {

            $gradient = $this->gradients[$id];
        }

        switch ($gradient['type']) {

            case 'linear':

                $angle = $this->angle($gradient['x1'], $gradient['y1'],
                    $gradient['x2'], $gradient['y2']);

                $g = "$angle-";

                foreach ($gradient['stops'] as $stop) {

                    $s = explode(';', $stop['style']);
                    $style = explode(':', $s[0]);

                    // Check if stio is already a percentage
                    if (strstr($stop['offset'], '%') !== FALSE) {

                        $offset = str_replace('%', '', $stop['offset']);
                    } else {

                        $offset = $stop['offset'] * 100;
                    }

                    if (preg_match('/rgb\((.+)\)/', $style[1], $m) != 0) {

                        $g .= $this->rgb2hex(explode(',', $m[1])) . ':' . $offset .
                            '-';
                    } else {

                        $g .= $style[1] . ':' . $offset . '-';
                    }

                    /*
                     * if ( preg_match('/rgb\((.+)\)/', $style[1], $m) != 0 ) {
                     * //print_r($m); //$g .= $this->rgb2hex(explode(",",
                     * $m[1])) . ":" . $offset . "-"; $hex =
                     * $this->rgb2hex(explode(",", $m[1])); } else { //$g .=
                     * $style[1] . ":" . $offset . "-"; $hex = $style[1]; } $op
                     * = explode(":", $s[1]); $opacity = $op[1]; $rgb =
                     * $this->hexToRGB($hex); $rgba = sprintf("rgba(%s, %s, %s,
                     * %s)", $rgb['r'], $rgb['g'], $rgb['b'], $opacity); $g .=
                     * $rgba . ":" . $offset . "-";
                     */
                    $op = explode(':', $s[1]);
                    $opacity = $op[1];
                }

                $g = substr($g, 0, -1) . '__' . $opacity;

                break;

            case 'radial':

                $g = 'r(' . (str_replace('%', '', $gradient['fx']) / $gradient['cx']) . ', ' .
                    (str_replace('%', '', $gradient['fy']) / $gradient['cy']) . ')';

                foreach ($gradient['stops'] as $stop) {

                    $s = explode(';', $stop['style']);
                    $style = explode(':', $s[0]);

                    // Check if stio is already a percentage
                    if (strstr($stop['offset'], '%') !== FALSE) {

                        $offset = str_replace('%', '', $stop['offset']);
                    } else {

                        $offset = ($stop['offset'] * 100);
                    }

                    if (preg_match('/rgb\((.+)\)/', $style[1], $m) != 0) {

                        $g .= $this->rgb2hex(explode(',', $m[1])) . ':' . $offset .
                            "-";
                    } else {

                        $g .= $style[1] . ':' . $offset . '-';

                    }

                    $op = explode(':', $s[1]);
                    $opacity = $op[1];
                }

                $g = substr($g, 0, -1) . '__' . $opacity;

                break;
        }

        return $g;
    }

    /**
     * Returns the angle between 2 sets of x & y coordinates
     * Useful for generating angle for fill gradients
     */
    private function angle($x1, $y1, $x2, $y2)
    {
        $y = $y2 - $y1;
        $x = $x2 - $x1;

        if ($x == 0)
            $x = 1 / 10000;

        $deg = rad2deg(atan(abs($y / $x)));

        // Looks like RaphaelJS uses the reverse so lets flip it!
        // Normally we would use ($y >= 0)
        if ($y <= 0) {

            $deg = $x < 0 ? 180 - $deg : $deg;
        } else {

            $deg = $x < 0 ? 180 + $deg : 360 - $deg;
        }

        return $deg;
    }

    private function generateRandomString($length)
    {
        $str = '';

        $chars = "abcdefghijklmnopqrstuvwxyz";

        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }

        return $str;
    }

    /**
     * Convert a hexadecimal color code to its RGB equivalent
     *
     * @param string $hexStr (hexadecimal color value)
     * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
     * @param string $separator (to separate RGB values. Applicable only if second parameter is true.)
     *
     * @return array or string (depending on second parameter. Returns False if invalid hex color value)
     */
    private function hexToRGB($hexStr, $returnAsString = false, $separator = ',')
    {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['r'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['g'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['b'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray['r'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['g'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['b'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false; //Invalid hex color code
        }

        return $returnAsString ? implode($separator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }

    /**
     * Converts RGB values into hexidecimal values
     */
    private function rgb2hex($r, $g = -1, $b = -1)
    {
        if (is_array($r) && sizeof($r) == 3)
            list ($r, $g, $b) = $r;

        $r = intval($r);
        $g = intval($g);
        $b = intval($b);

        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color = (strlen($r) < 2 ? '0' : '') . $r;
        $color .= (strlen($g) < 2 ? '0' : '') . $g;
        $color .= (strlen($b) < 2 ? '0' : '') . $b;

        return '#' . $color;
    }

}