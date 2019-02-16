<?php

namespace Bloomkit\Core\Entities\Tests;

use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Entities\Fields\Field;
use Bloomkit\Core\Entities\Fields\FieldType;
use PHPUnit\Framework\TestCase;

class EntityDescriptorTest extends TestCase
{
    public function testSetTableName()
    {
        $descriptor = new EntityDescriptor('FooBar');
        $this->assertEquals('FooBar', $descriptor->getEntityName());
        $this->assertEquals('foobar', $descriptor->getTableName());
        $descriptor->setTableName('BarFoo');
        $this->assertEquals('barfoo', $descriptor->getTableName());
    }

    public function testRecoveryMode()
    {
        $descriptor = new EntityDescriptor('FooBar');
        $this->assertEquals(false, $descriptor->getRecoveryMode());
        $descriptor->setRecoveryMode(true);
        $this->assertEquals(true, $descriptor->getRecoveryMode());
    }

    public function testIdType()
    {
        $descriptor = new EntityDescriptor('FooBar');
        $this->assertEquals(EntityDescriptor::IDTYPE_SERIAL, $descriptor->getIdType());
        $descriptor = new EntityDescriptor('FooBar', EntityDescriptor::IDTYPE_UUID);
        $this->assertEquals(EntityDescriptor::IDTYPE_UUID, $descriptor->getIdType());
    }

    public function testAddAndCount()
    {
        $descriptor = new EntityDescriptor('test');
        $this->assertEquals(0, $descriptor->getFieldCount());
        $field1 = new Field(FieldType::PDynFTString, 'fieldid1', 'fieldname1');
        $field2 = new Field(FieldType::PDynFTInteger, 'fieldid2', 'fieldname2');
        $descriptor->addField($field1);
        $descriptor->addField($field2);
        $this->assertEquals(2, $descriptor->getFieldCount());
        $this->assertEquals(true, $descriptor->hasField('fieldid1'));
        $this->assertEquals(true, $descriptor->hasField('fieldid2'));
        $this->assertEquals(false, $descriptor->hasField('fieldid3'));
    }

    public function testDoubleAdd()
    {
        $descriptor = new EntityDescriptor('test');
        $this->assertEquals(0, $descriptor->getFieldCount());
        $field1 = new Field(FieldType::PDynFTString, 'fieldid', 'fieldname');
        $field2 = new Field(FieldType::PDynFTInteger, 'fieldid', 'fieldname');
        $descriptor->addField($field1);
        $descriptor->addField($field2);
        $this->assertEquals(1, $descriptor->getFieldCount());
    }

    public function testGetField()
    {
        $descriptor = new EntityDescriptor('test');
        $fieldIn = new Field(FieldType::PDynFTString, 'fieldid', 'fieldname');
        $descriptor->addField($fieldIn);
        $fieldOut = $descriptor->getField('fieldid');
        $this->assertEquals($fieldIn, $fieldOut);
    }

    public function testRemove()
    {
        $descriptor = new EntityDescriptor('test');
        $this->assertEquals(0, $descriptor->getFieldCount());
        $field1 = new Field(FieldType::PDynFTString, 'fieldid1', 'fieldname1');
        $field2 = new Field(FieldType::PDynFTInteger, 'fieldid2', 'fieldname2');
        $descriptor->addField($field1);
        $descriptor->addField($field2);
        $this->assertEquals(2, $descriptor->getFieldCount());
        $descriptor->removeField('fieldid1');
        $this->assertEquals(1, $descriptor->getFieldCount());
    }

    public function testGetFields()
    {
        $descriptor = new EntityDescriptor('test');
        $this->assertEquals(0, $descriptor->getFieldCount());
        $field1 = new Field(FieldType::PDynFTString, 'fieldid1', 'fieldname1');
        $field2 = new Field(FieldType::PDynFTInteger, 'fieldid2', 'fieldname2');
        $descriptor->addField($field1);
        $descriptor->addField($field2);
        $compare = array(
            'fieldid1' => $field1,
            'fieldid2' => $field2,
        );
        $this->assertEquals($compare, $descriptor->getFields());
    }

    public function testSetLogging()
    {
        $descriptor = new EntityDescriptor('test');
        $this->assertEquals(true, $descriptor->getCreationDateLogging());
        $this->assertEquals(true, $descriptor->getModificationDateLogging());
        $descriptor->setCreationDateLogging(false);
        $descriptor->setModificationDateLogging(false);
        $this->assertEquals(false, $descriptor->getCreationDateLogging());
        $this->assertEquals(false, $descriptor->getModificationDateLogging());
    }
}
