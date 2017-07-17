<?php

namespace AKlump\LoftLib\Testing;


class PhpUnitTestCase extends \PHPUnit_Framework_TestCase
{

    public function assertProtectedPropertySame($control, $instance, $property)
    {
        $reflection = new \ReflectionClass($instance);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $this->assertSame($control, $property->getValue($instance));
    }

    //
    //
    // XML Assertions
    //

    public function assertXMLEquals($control, $xml)
    {
        if (is_array($control)) {
            $subject = (array) $xml;
        }
        else {
            $subject = (string) $xml;
        }
        return $this->assertEquals($control, $subject);
    }

    public function assertXMLHasAttribute($attribute, $xml)
    {
        $attr = (array) $xml->attributes();
        if (!array_key_exists('@attributes', $attr)) {
            $this->fail('No attributes exist.');
        }
        $this->assertArrayHasKey($attribute, $attr['@attributes']);
    }

    public function assertXMLHasChild($child, $xml)
    {
        $this->assertArrayHasKey($child, (array) $xml->children());
    }
}
