<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/30/14
 * Time: 12:14 AM
 */

namespace tests;

use tests\TestCase;
use AllanKiezel\ReadySetRaphael\Parser;

class ParserTest extends TestCase {

    private $parser;

    protected function setUp()
    {
        parent::setUp();

        $this->parser = new Parser();
    }

    public function testShouldReturnInstanceOfParserInterface()
    {
        $this->assertInstanceOf('AllanKiezel\\ReadySetRaphael\\ParserInterface', $this->parser);
    }

    public function testShouldReturnInstanceOfSVGParser()
    {
        $this->assertInstanceOf('AllanKiezel\\ReadySetRaphael\\Parser', $this->parser);
    }

}
