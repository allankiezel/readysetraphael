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
     * @var array $removableVarNameChars Characters to remove from variable names.
     */
    public $removableVarNameChars = array(
        '.',
        '-',
        '+'
    );

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

        $svgInstance = SVG::getInstance();
        $this->svgName = $svgInstance->getName();

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
         * add the element to the js output
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

        $this->parse($this->svg);

        $this->draw();

        $this->setArray = str_replace('"', '', json_encode($this->setArray));

        $this->addToJs(sprintf("\n\nvar rsrGroups = %s;", $this->setArray));
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
     * Add current group to set array
     */
    private function createSet()
    {
        $id = $this->generateVar('group');

        // Add to javascript object as string
        // This will assist in applying events to groups
        $this->setArray[] = (string)$id;

        /** @var \SimpleXMLElement $currentName Element's current name. */
        $currentName = $this->getCurrentElement()->attributes()['name'];

        if (empty($currentName)) {
            $this->getCurrentElement()->addAttribute('name', $id);
        }

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

        try {
            $element = ElementFactory::create($type, $this->inSet);
            $element->init($this->getCurrentElement());

            $output = $element->draw();

            if ($output) {
                $this->addToJs($output);
            }
        } catch (\InvalidArgumentException $e) {
            echo $e->getMessage();
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

                    /** @var \SimpleXMLObject $currentAttr Element's current attr name for key. */
                    $currentAttr = $element->attributes()[$a];

                    if (empty($currentAttr)) {
                        $element->addAttribute($a, $b);
                    }

                }

                /** @var \SimpleXMLObject $parentName Element's current parent name. */
                $currentParentName = $element->attributes()['parent'];

                if (empty($currentParentName)) {
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

            $removable = $this->removableVarNameChars;

            $id = $attrs['id'];
            $id = str_replace($removable, '', $id);

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
     * Checks to see if set had children
     */
    private function setHasElements($set)
    {
        return count($set->children()) > 0;
    }

}