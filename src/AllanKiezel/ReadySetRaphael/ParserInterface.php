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
     * Starts the parsing process.
     */
    public function init();
}