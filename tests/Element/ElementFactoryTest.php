<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/30/14
 * Time: 1:58 AM
 */

namespace tests\Element;

use tests\TestCase;
use AllanKiezel\ReadySetRaphael\Element\ElementFactory;

class ElementFactoryTest extends TestCase
{
    private $namespace;

    private $element;

    protected function setUp()
    {
        parent::setUp();

        $this->setElementNamespace();

        $this->element = ElementFactory::create('Polygon');
    }

    public function testShouldReturnInstanceOfElementInterface()
    {

        $this->assertInstanceOf($this->namespace . '\\ElementInterface', $this->element);
    }

    public function testShouldReturnInstanceOfAbstractElement()
    {

        $this->assertInstanceOf($this->namespace . '\\AbstractElement', $this->element);
    }

    public function testShouldReturnInstanceOfPolygonElement()
    {
        $this->assertInstanceOf($this->namespace . '\\PolygonElement', $this->element);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage You must pass in a factory type.
     */
    public function testShouldThrowInvalidArgumentException()
    {
        $element = ElementFactory::create('');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Class DontExistElement not found.
     */
    public function testExceptionHasRightMessage()
    {
        $parser = ElementFactory::create('DontExist');
    }

    /**
     * Sets element namespace to avoid repetition
     */
    protected function setElementNamespace()
    {
        $this->namespace = 'AllanKiezel\\ReadySetRaphael\\Element';
    }
}
