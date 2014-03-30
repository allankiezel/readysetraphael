<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/29/14
 * Time: 3:46 PM
 */

namespace AllanKiezel\ReadySetRaphael;

/**
 * Interface for parsers.
 *
 * @package AllanKiezel\ReadySetRaphael
 * @author Allan Kiezel <allan.kiezel@gmail.com>
 */
interface ParserInterface
{
    /**
     * Setup the parsing process.
     *
     * @param \SimpleXMLElement $obj SVG to parse.
     */
    public function init($obj);
}