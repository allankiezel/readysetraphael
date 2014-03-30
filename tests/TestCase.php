<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/30/14
 * Time: 12:31 AM
 */

namespace tests;

/**
 * Base testcase class for all Doctrine testcases.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}