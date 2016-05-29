<?php
namespace Timber\Tests\Entities;

use Timber\Entity;
use Timber\EntityCollection;
use DOMXpath;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public $_entityNS = 'urn:Timber:Tests';

    public function setup()
    {

    }

    /**
     *
     * @todo check for expected exception
     */
    public function test1dArray()
    {
        $content = [
            'foo',
            'bar',
            'grah',
        ];

        $entity = new BarEntity($content);
        $xml = $entity->__toXML();

        // days should be 1 right ?
        $this->assertNotNull($xml);

        $xpath = [
            '/ent:BarEntity/ent:item/@position' => '0',
            '/ent:BarEntity/ent:item' => 'foo'
        ];
        $this->checkNS($xml, $xpath);


    }

    public function testKVArray()
    {
        $content = [
            'foo'  => 'foo1',
            'bar'  => 'foo2',
            'grah' => 'foo3',
        ];

        $entity = new BarEntity($content);
        $xml = $entity->__toXML();

        $xpath = [
            '/ent:BarEntity' => '',
            '/ent:BarEntity/ent:foo' => 'foo1'
        ];
        $this->checkNS($xml, $xpath);

        // days should be 1 right ?
        $this->assertNotNull($xml);
    }

    public function testObject()
    {
        $content = new \stdClass;
        $content->foo  = 'foo1';
        $content->bar  = 'foo2';
        $content->grah = 'foo3';

        $entity = new BarEntity($content);
        $xml = $entity->__toXML();


        $xpath = [
            '/ent:BarEntity' => '',
            '/ent:BarEntity/ent:foo' => 'foo1'
        ];
        $this->checkNS($xml, $xpath);

        // days should be 1 right ?
        $this->assertNotNull($xml);
    }

    public function testNested()
    {
        $content = new \stdClass;
        $content->foo  = 'foo1';
        $content->bar  = [
            'apple'   => 'green',
            'coconut' => 'brown',
            'strawberry' => 'red'
        ];
        $content->grah = 'foo3';

        $entity = new BarEntity($content);
        $xml = $entity->__toXML();

        $xpath = [
            '/ent:BarEntity' => '',
            '/ent:BarEntity/ent:foo' => 'foo1'
        ];
        $this->checkNS($xml, $xpath);

        // days should be 1 right ?
        $this->assertNotNull($xml);
    }

    public function testString()
    {

        $entity = new BarEntity("foo");
        $xml = $entity->__toXML();
        $xpath = [
            '/ent:BarEntity' => 'foo',
        ];
        $this->checkNS($xml, $xpath);

        // days should be 1 right ?
        $this->assertNotNull($xml);
    }

    public function testEntityCollection()
    {
        $content = new \stdClass;
        $content->foo  = 'foo1';
        $content->bar  = [
            'apple'   => 'green',
            'coconut' => 'brown',
            'strawberry' => 'red'
        ];
        $content->grah = 'foo3';

        $list = new BarCollection([
            new BarEntity($content),
            new BarEntity($content),
            new BarEntity($content)
        ]);

        $xml = $list->__toXML();

        $xpath = [
            '/ent:BarEntity' => '',
            '/ent:BarEntity/ent:foo' => 'foo1'
        ];

        // days should be 1 right ?
        $this->assertNotNull($xml);
    }

    public function testGetNS()
    {
        $entity = new BarEntity("foo");
        $ns = $entity->getNS();
        $this->assertNotNull($ns);
    }


    public function testGetName()
    {
        $entity = new BarEntity("foo");
        $ns = $entity->getName();
        $this->assertNotNull($ns);
    }

    public function testToString()
    {
        $entity = new BarEntity("foo");
        $string = sprintf("%s", $entity);
        $this->assertNotNull($string);

        $entity = new BarEntity(new \stdClass);

        $string = sprintf("%s", $entity);
        $this->assertNotNull($string);
    }

    public function testSet()
    {
        $entity = new BarEntity(['feh']);
        $entity->rar = "blah";

        $this->assertEquals("blah", $entity->rar);

        $xml = $entity->__toXML();
        $this->assertNotNull($xml);

        $xpath = [
            '/ent:BarEntity/ent:item/@position' => '0',
            '/ent:BarEntity/ent:item' => 'feh'
        ];
        $this->checkNS($xml, $xpath);

        $x = new \stdClass();

        $entity = new BarEntity($x);
        $entity->rar = "foo";
        $this->assertEquals("foo", $entity->rar);

        $xml = $entity->__toXML();

        $xpath = [
            '/ent:BarEntity/ent:rar' => 'foo',
        ];
        $this->checkNS($xml, $xpath);

        $entity = new BarEntity();
        $this->assertEquals(false, $entity->rar);

        $this->assertNotNull($xml);
    }

    /**
     *
     * @expectedException \ErrorException
     */
    public function testSetException()
    {
        $entity = new BarEntity('blah');
        $entity->rar = "foo";
    }

    protected function checkNS($xml, array $xpaths)
    {
        $dom = new \Timber\XML\DOMDocument();
        $dom->loadXML($xml);

        $proc = new DOMXPath($dom);

        $proc->registerNamespace('ent', $this->_entityNS);

        // get the ns
        foreach($xpaths as $xpath => $val) {
            $node = $proc->query($xpath);

            $msg = sprintf(
                'Query: %s returned null ',
                $xpath
            );
            $this->assertNotNull($node, $msg);

            if (!empty($val)) {
                $this->assertEquals($val, $node->item(0)->nodeValue);
            }

        }
    }
}
