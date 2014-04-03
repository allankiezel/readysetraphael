<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/31/14
 * Time: 10:16 AM
 */
namespace AllanKiezel\ReadySetRaphael;

/**
 * Base SVG class acting as global SVG object
 *
 * @todo Use dependency inject instead!
 *
 * @package AllanKiezel\ReadySetRaphael
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
class SVG
{
    /** @var array $gradients All gradients residing in SVG. */
    protected $gradients = array();

    /** @var string $name JavaScript RaphaelJS variable. */
    protected $name = 'raphael';

    /** @var \SimpleXMLElement */
    private $svg;

    protected static $instance = null;

    /**
     * @param string $svg
     */
    public function setSVG($svg)
    {
        if (empty($svg)) {
            throw new \InvalidArgumentException('You must provide an SVG file string argument.');
        }

        // Account for attributes with ':' in gradients
        $svg = str_replace('xlink:', 'xlink-', $svg);

        $this->svg = simplexml_load_string($svg);

        if (isset($this->svg->defs)) {
            $this->generateGradients();
        }

    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *SVG* via the `new` operator from outside of this class.
     */
    private function __construct($name)
    {
        if (!empty($name)) {
            $this->name = $name;
        }

    }

    /**
     * Initializes the *SVG* instance of this class.
     *
     * @var string $name JavaScript variable name to use in output.
     *
     * @static
     */
    public static function getInstance($name = '')
    {
        if (null === self::$instance) {
            self::$instance = new self($name);
        }

        return self::$instance;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getSVG()
    {
        return $this->svg;
    }

    /**
     * @return array
     */
    public function getGradients()
    {
        return $this->gradients;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *SVG* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *SVG*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * Generates gradients array to extract gradient fills from
     */
    private function generateGradients()
    {
        $svg = $this->getSVG();

        foreach ($svg->defs->children() as $element) {

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

}