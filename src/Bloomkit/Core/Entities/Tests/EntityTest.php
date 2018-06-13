<?php

namespace Bloomkit\Core\Entities\Tests;

use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Entities\Fields\Field;
use Bloomkit\Core\Entities\Fields\FieldType;
use Bloomkit\Core\Entities\Entity;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    protected $descriptor;

    protected function setUp()
    {
        $field = new Field(FieldType::PDynFTString, 'fieldid', 'fieldname');
        $this->descriptor = new EntityDescriptor('test');
        $this->descriptor->addField($field);
    }

    public function testSettingValueForUnknownField()
    {
        $this->expectException(\Bloomkit\Core\Entities\Exceptions\FieldNotFoundException::class);
        $entity = new Entity($this->descriptor);
        $entity->unknown = 1234;
    }

    public function testSettingValueForField()
    {
        $entity = new Entity($this->descriptor);
        $entity->fieldid = 1234;
    }

    public function testGettingValueForField()
    {
        $entity = new Entity($this->descriptor);
        $entity->fieldid = 1234;
        $this->assertEquals(1234, $entity->fieldid);
    }

    public function testGettingValueForUnknownField()
    {
        $this->expectException(\Bloomkit\Core\Entities\Exceptions\FieldNotFoundException::class);
        $entity = new Entity($this->descriptor);
        $value = $entity->unknown;
    }
}
