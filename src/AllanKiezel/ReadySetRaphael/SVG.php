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
    protected static $gradients = array();

    /** @var string $name JavaScript RaphaelJS variable. */
    protected static $name = 'raphael';

    /**
     * @return string
     */
    public static function getName()
    {
        return self::$name;
    }

    /**
     * @return array
     */
    public static function getGradients()
    {
        return self::$gradients;
    }

    /** @var \SimpleXMLElement */
    private static $svg;

    /**
     * Protected constructor to prevent creating a new instance of the
     * *SVG* via the `new` operator from outside of this class.
     */
    private function __construct($svg, $name)
    {
        if (empty($svg)) {
            throw new \InvalidArgumentException('You must provide an SVG file string argument.');
        }

        if (!empty($name)) {
            self::$name = $name;
        }

        // Account for attributes with ':' in gradients
        $svg = str_replace('xlink:', 'xlink-', $svg);

        self::$svg = simplexml_load_string($svg);

        if (isset(static::$svg->defs)) {
            $this->generateGradients();
        }

    }

    /**
     * Initializes the *SVG* instance of this class.
     *
     * @var string $svg SVG file contents.
     * @var string $name JavaScript variable name to use in output.
     *
     * @static
     */
    public static function init($svg = '', $name = '')
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static($svg, $name);
        }
    }

    /**
     * @return \SimpleXMLElement
     */
    public static function getSVG()
    {
        return static::$svg;
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
        $svg = self::getSVG();

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

            self::$gradients[$gradientID] = $gradientTemp;
            self::$gradients[$gradientID]['stops'] = $stops;

            unset($gradientTemp);
            unset($stops);
        }

    }

}