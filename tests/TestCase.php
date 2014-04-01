<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/30/14
 * Time: 12:31 AM
 */

namespace tests;

use AllanKiezel\ReadySetRaphael\SVG;

/**
 * Base test case class for all RSR test cases.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $sampleSVGContents;

    protected function setUp()
    {
        parent::setUp();

        $this->sampleSVGContents = file_get_contents(__DIR__  . '/_data/sample.svg');

        $svg = SVG::getInstance($this->sampleSVGContents, 'rsr');

    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}