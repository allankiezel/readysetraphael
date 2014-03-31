<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 3:04 PM
 */

namespace AllanKiezel\ReadySetRaphael;

use AllanKiezel\ReadySetRaphael\Element\ElementFactory;
use AllanKiezel\ReadySetRaphael\ParserInterface;

/**
 * Converts SVG file into Raphael.js generated .js code
 *
 * @package AllanKiezel\ReadySetRaphael
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 * @version 2.0
 */
class Parser implements ParserInterface
{
    /**
     * @var string $container ID name of container to house svg output.
     */
    public $container = 'rsr';

    /**
     * @var string $currentSetName Current set name.
     */
    public $currentSetName = '';

    /**
     * @var array $gradients All gradients residing in SVG.
     */
    public $gradients = array();

    /**
     * @var string $ln Line ending.
     */
    public $ln = ';';

    /**
     * @var mixed $parentSet Previous set name.
     */
    public $parentSetName = null;

    /**
     * @var array $set "set" array.
     */
    public $set = array();

    /**
     * @var \SimpleXMLElement $currentElement Current pointer position element during iteration.
     */
    private $currentElement = null;

    /**
     * @var string $currentVar Character used as name of set if no 'id' attribute exists.
     *     Gets incremented as its used.
     */
    private $currentVar = 'a';

    /**
     * @var boolean $inSet Defines whether draw() adds to a "set" (group).
     */
    private $inSet = false;

    /**
     * @var string $js String holding Javascript output.
     */
    private $js = '';

    /**
     * @var array $setArray Holds multi-dimensional array of "set" elements.
     */
    private $setArray = array();

    /**
     * @var string $setJs String holding Javascript set output.
     */
    private $setJs = '';

    /**
     * @var array $ignored Array of elements to ignore during iteration.
     */
    private $supportedElements = array(
        'g',
        'path',
        'polygon',
        'rect',
        'circle',
        'ellipse',
        'switch',
        'text'
    );

    /**
     * @var string $svg SimpleXML Object representation of SVG file.
     */
    private $svg = '';

    /**
     * @var string $svgName Javascript variable name of Raphael Object.
     */
    private $svgName = 'rsr';

    /**
     * Constructor
     *
     * @param \SimpleXMLElement $svg SVG file contents.
     */
    public function __construct(\SimpleXMLElement $svg = null)
    {
        if ($svg === null) {
            throw new \InvalidArgumentException('You must provide an SVG file string argument.');
        }

        $this->svg = $svg;

    }

    public function __call($method, $arguments)
    {
        if (strstr($method, 'draw') !== FALSE) {
            // throw new Exception('Unsupported element:
            // '.htmlentities('<'.strtolower(str_replace("draw", "", $method)).'
            // />'));
        }
    }

    /**
     * Appends the string to the current js script string
     *
     * @param string $string String to append
     * @param boolean $newLine Boolean to add new line character to string
     * @param boolean $addToSetJs Should the output be concat'd to the setJs string
     */
    public function addToJs($string = '', $newLine = true, $addToSetJs = false)
    {
        $js = '';
        $setJs = '';

        /*
         * If adding to a "set" add the var name to the set and
         * add the element to the js ouput
        */
        if ($this->inSet) {

            $pattern = '/var\s+(.+)\s*=/';

            preg_match_all($pattern, $string, $parts, PREG_SET_ORDER);

            $varName = $parts[0][1];

            $setJs .= "\n\t" . $varName . ",";

        }

        $js .= $string;

        if ($newLine) {
            $js .= "\n";
        }

        if ($addToSetJs === true) {
            $this->setJs .= $js;
        } else {
            $this->js .= $js;
        }

        $this->setJs .= $setJs;
    }

    public function allowedElement()
    {
        // Check if element is in ignored list
        return in_array($this->getCurrentElement()->getName(), $this->supportedElements);
    }

    /**
     * Creates an SVG circle
     */
    public function drawCircle()
    {

        $attrs = $this->getCurrentElement()->attributes();

        $x = round($attrs['cx']);
        $y = round($attrs['cy']);
        $r = round($attrs['r']);

        $varName = $this->generateVar('circle');
        $this->addToJs(
            'var ' . $varName . ' = ' . $this->svgName .
            ".circle($x, $y, $r)" .
            $this->generateAttributes(
                array(
                    'cx',
                    'cy',
                    'r'
                ), $varName) . ".data('id', '$varName')" . ';');
    }

    /**
     * Creates an SVG ellipse
     */
    public function drawEllipse()
    {

        $attrs = $this->getCurrentElement()->attributes();

        $cx = $attrs['cx'];
        $cy = $attrs['cy'];
        $rx = $attrs['rx'];
        $ry = $attrs['ry'];

        $varName = $this->generateVar('ellipse');
        $this->addToJs(
            'var ' . $varName . ' = ' . $this->svgName .
            ".ellipse($cx, $cy, $rx, $ry)" .
            $this->generateAttributes(
                array(
                    'cx',
                    'cy',
                    'rx',
                    'ry'
                ), $varName) . ".data('id', '$varName')" . ';');
    }

    /**
     * Creates an SVG group
     *
     * *Note - Raphael does not have groups; it has "set"s
     */
    public function drawG()
    {
    }

    /**
     * Creates an SVG path
     */
    public function drawPath()
    {
        $attrs = $this->getCurrentElement()->attributes();

        $varName = $this->generateVar('path');

        $this->addToJs(
            'var ' . $varName . ' = ' . $this->svgName . '.path("' .
            $attrs['d'] . '")' .
            $this->generateAttributes(
                array(
                    'd'
                ), $varName) . ".data('id', '$varName')" . ';');
    }

    /**
     * Creates an SVG polygon
     */
    public function drawPolygon()
    {
        $varName = $this->generateVar('path');

        // Emulate polyline and polygon with path
        $f = strpos($this->getAttribute($this->getCurrentElement(), 'points'),
            ' ');

        $d = sprintf("M %s %s",
            $this->getAttribute($this->getCurrentElement(), 'points'), 'z');

        $this->addToJs(
            'var ' . $varName . ' = ' . $this->svgName . '.path("' . $d .
            '")' .
            $this->generateAttributes(
                array(
                    'points'
                ), $varName) . ".data('id', '$varName')" . ';');
    }

    /**
     * Creates an SVG rectangle
     */
    public function drawRect()
    {
        $attrs = $this->getCurrentElement()->attributes();

        $w = $attrs['width'];
        $h = $attrs['height'];
        $x = $attrs['x'] ? $attrs['x'] : 0;
        $y = $attrs['y'] ? $attrs['y'] : 0;

        $varName = $this->generateVar('rect');
        $this->addToJs(
            'var ' . $varName . ' = ' . $this->svgName .
            ".rect($x, $y, $w, $h)" .
            $this->generateAttributes(
                array(
                    'width',
                    'height'
                ), $varName) . ".data('id', '$varName')" . ';');
    }

    /**
     * Draw text on the SVG
     */
    public function drawText()
    {
        $attrs = $this->getCurrentElement()->attributes();

        $x = $attrs['x'] != '' ? $attrs['x'] : 0;
        $y = $attrs['y'] != '' ? $attrs['y'] : 0;
        $text = (string)$this->getCurrentElement();

        if (isset($this->getCurrentElement()->tspan) && $text == "") {

            for ($i = 0, $len = count($this->getCurrentElement()->tspan) - 1; $i <=
            $len; $i++) {
                $text .= $this->getCurrentElement()->tspan[$i] . '\n';
            }

            $text = substr($text, 0, -2);
        }

        if ($text == '') {

            return;
        }

        $varName = $this->generateVar('text');
        $this->addToJs(
            'var ' . $varName . ' = ' . $this->svgName .
            ".text($x, $y, '$text')" .
            $this->generateAttributes(
                array(
                    'y',
                    'x'
                ), $varName) . ".data('id', '$varName')" . ';');
    }

    /**
     * Outputs the generated javascript for raphael and its groups
     */
    public function generateJS()
    {
        echo $this->js;
        echo $this->setJs;
    }

    /**
     * Returns the generated javascript for RaphaelJS and its groups
     */
    public function getJS()
    {
        return $this->js . $this->setJs;
    }

    /**
     * Starts the parsing process.
     */
    public function init()
    {
        $this->initRaphael();

        if (isset($this->svg->defs)) {
            $this->generateGradients();
        }

        $this->parse($this->svg);

        $this->draw();

        $this->setArray = str_replace('"', '', json_encode($this->setArray));

        $this->addToJs(sprintf("\n\nvar rsrGroups = %s;", $this->setArray));
    }

    /**
     * Iterates through SVG and generates a multi-dimensional array of groups
     *
     * @param \SimpleXMLElement $obj SVG to parse.
     */
    private function parse($obj)
    {
        // Loop through svg and create set array
        foreach ($obj->children() as $element) {

            // Update to element that is being parsed
            $this->currentElement = $element;

            // If element has children (ie: group) build a "set" && not "TEXT"
            if (count($element->children()) > 0 && $element->getName() != 'text') {

                if ($this->allowedElement()) {

                    $this->createSet();

                    $this->parse($element);
                }
            }
        }
    }

    /**
     * Creates the initial Raphael object
     */
    public function initRaphael()
    {
        $attrs = $this->svg->attributes();

        if (isset($attrs['viewBox'])) {

            list ($x, $y, $w, $h) = explode(' ', $attrs['viewBox']);
        } else {

            $x = 0;
            $y = 0;

            $w = $attrs['width'];

            $h = $attrs['height'];
        }

        if (strstr($w, 'pt') !== FALSE) {
            $w = ceil(str_replace('pt', '', $w) / 0.75);
            $h = ceil(str_replace('pt', '', $h) / 0.75);
        } elseif (strstr($w, 'px') !== FALSE) {
            $w = str_replace('px', '', $attrs['w']);
            $h = str_replace('px', '', $attrs['h']);
        } else {
            $w = "'$w'";
            $h = "'$h'";
        }

        $this->addToJs(
            sprintf("var %s = Raphael('%s', %s, %s);\n", $this->svgName,
                $this->container, $w, $h));
    }

    public function printSVG()
    {
        print_r($this->svg);
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

    /**
     * Add current group to set array
     */
    private function createSet()
    {
        $id = $this->generateVar('group');

        // Add to javascript object as string
        // This will assist in applying events to groups
        $this->setArray[] = (string)$id;

        $this->getCurrentElement()->addAttribute('name', $id);

        $this->set[] = $this->getCurrentElement();

        $this->generateParent($id);
    }

    /**
     * Loops through the svg and set array and draws each element
     */
    private function draw()
    {

        // Draw all free standing elements not in a set
        foreach ($this->svg as $element) {

            // Update to element that is being parsed
            $this->currentElement = $element;

            if ($this->allowedElement()) {

                // Don't draw groups
                if (count($element->children()) == 0) {

                    $this->drawElement();

                }
            }
        }

        // Loop through sets array and draw each set
        foreach ($this->set as $set) {
            $this->drawSet($set);
        }
    }

    /**
     * Responsible for loading element class and calling draw() method
     */
    private function drawElement()
    {

        $type = ucfirst($this->getCurrentElement()->getName());

        if ($type === 'Polygon' || $type === 'Path') {
            try {
                $element = ElementFactory::create($type, $this->inSet);
                $element->init($this->getCurrentElement());

                $output = $element->draw();

                $this->addToJs($output);
                //echo $output . '<br><br>';
            } catch(\InvalidArgumentException $e) {
                echo $e->getMessage();
            }
        } else {
            $method = 'draw' . $type;
            $this->$method();
        }

    }

    private function drawSet($set)
    {
        $id = $this->getAttribute($set, 'name');

        $this->addToJs('var ' . $id . ' = ' . $this->svgName . '.set();');

        if ($this->setHasElements($set)) {

            $this->addToJs(sprintf('%s.push(', $id), false, true);

            foreach ($set as $element) {

                // Update to element that is being parsed
                $this->currentElement = $element;

                if ($this->allowedElement()) {

                    // Don't draw groups
                    if (count($element->children()) == 0 ||
                        $element->getName() == 'text'
                    ) {

                        // Start drawing!
                        $this->inSet = true;
                        $this->drawElement();
                        $this->inSet = false;
                    }
                }
            }

            if (substr($this->setJs, -1) === ',') {
                // Remove trailing ',' from $this->setJs
                $this->setJs = substr($this->setJs, 0, -1);
            }

            $this->addToJs("\n);", true, true);
        }

        $attrs = $set->attributes();

        $a = "";
        $transform = false;
        foreach ($attrs as $key => $value) {

            if ($key != 'transform') {
                $a .= "'$key': '$value',";
            } else {

                $transform = true;

                preg_match('/^(.+)\((.+)\)/', $value, $m);

                $type = $m[1];
                $args = $m[2];

                switch ($type) {

                    case 'matrix':

                        $t = 'transform("m' . $args . '")';

                        break;

                    case 'translate':

                        $t = 'transform("t' . $args . '")';

                        break;

                    case 'scale':

                        $t = 'transform("s' . $args . '")';

                        break;
                }
            }
        }

        // HACK
        $a = substr($a, 0, -1);

        if ($transform) {
            $this->addToJs($id . '.attr({' . $a . '});' . $id . '.' . $t . ';');
        } else {
            $this->addToJs($id . '.attr({' . $a . '});');
        }
    }

    /**
     * Creates attribute string for current element
     *
     * @param array $ignored Array holding attributes to ignore
     * @param string $var Object getting attributes assigned to
     *
     * @return string
     */
    private function generateAttributes($ignored = array(), $var = '')
    {
        if (isset($this->getCurrentElement()->attributes()->style)) {

            $vals = explode(';',
                $this->getCurrentElement()->attributes()->style);

            foreach ($vals as $sep) {

                list ($k, $v) = explode(":", $sep);
                $this->getCurrentElement()->addAttribute($k, $v);
            }

        }

        $attrs = $this->getCurrentElement()->attributes();
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

                if (strstr($this->getCurrentElement()->attributes()->transformG,
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
     * Generates gradients array to extract gradient fills from
     */
    private function generateGradients()
    {
        foreach ($this->svg->defs->children() as $element) {

            $gradientID = (string)$element->attributes()->id;

            $gradientTemp = array();

            // Create attribute key, value pairs
            foreach ($element->attributes() as $key => $value) {

                $key = (string)$key;
                $value = (string)$value;

                $gradientTemp[$key] = (string)$value;
            }

            // Add gradient type
            $gradientTemp['type'] = substr($element->getName(), 0, -8);

            if (array_key_exists('gradientTransform', $gradientTemp)) {

                if (strstr($gradientTemp['gradientTransform'], 'scale') !== FALSE) {
                    $gradientTemp['scale'] = $gradientTemp['gradientTransform'];
                }
            }

            $stops = array();
            $stopInc = 0;

            // Gradient element has stops
            if (count($element->children()) > 0) {

                // Loop through each stop
                foreach ($element->children() as $key => $value) {

                    foreach ($value->attributes() as $k => $v) {

                        $stops[$stopInc][$k] = (string)$v;
                    }

                    $stopInc++;
                }
            }

            $stopInc = 0;

            $this->gradients[$gradientID] = $gradientTemp;
            $this->gradients[$gradientID]['stops'] = $stops;

            unset($gradientTemp);
            unset($stops);
        }

    }

    /**
     * Generates parent attribute for set children
     */
    private function generateParent($name)
    {
        if (count($this->getCurrentElement()->children()) > 0) {

            $this->parentSetName = $name;

            foreach ($this->getCurrentElement()->children() as $element) {

                // Build group attributes to be applied to children
                $ignoredAttrs = array(
                    'id',
                    'name'
                );
                $groupAttrs = array();

                foreach ($this->getCurrentElement()->attributes() as $a => $b) {

                    if (!in_array($a, $ignoredAttrs)) {

                        if ($a == 'transform') {
                            $groupAttrs['transformG'] = $b;
                        } else {
                            $groupAttrs[$a] = $b;
                        }
                    }
                }

                foreach ($groupAttrs as $a => $b) {

                    $element->addAttribute($a, $b);
                }

                /** @var \SimpleXMLObject $parentName Element's current parent name. */
                $currentParentName = $element->attributes()['parent'];

                if (!empty($currentParentName)) {
                    $element->addAttribute('parent', $this->parentSetName);
                }
            }
        }
    }

    /**
     * Generates unique id for elements
     */
    private function generateVar($prefix = '')
    {
        // Check if ID value was passed in for current element
        $attrs = $this->getCurrentElement()->attributes();

        $id = '';

        if (isset($attrs['id'])) {

            $id = $attrs['id'];
            $id = str_replace('.', '', $id);
            $id = str_replace('-', '', $id);
        } else {

            $id = $prefix . '_' . $this->currentVar++;
        }

        return $id;
    }

    /**
     * Returns attribute from current element
     *
     * @param  $attr Requested attribute
     *
     * @return string
     */
    private function getAttribute($obj, $attr)
    {
        $attrs = $obj->attributes();

        return $attrs[$attr];
    }

    private function getCurrentElement()
    {
        return $this->currentElement;
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
     * Retrieves gradient information for attribute and
     * builds the gradient fill attribute
     */
    private function processGradient($id)
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

    /**
     * Checks to see if set had children
     */
    private function setHasElements($set)
    {
        return count($set->children()) > 0;
    }

}