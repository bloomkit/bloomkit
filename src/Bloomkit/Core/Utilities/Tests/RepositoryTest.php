<?php
namespace Bloomkit\Tests\Utilities;

use Bloomkit\Core\Utilities\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    protected $repository;

    protected $config;
    
    public function setUp()
    {
        $this->config = [
            'foo' => 'bar',
            'bar' => 'baz',
            'null' => null,
            'associate' => [
                'x' => 'xxx',
                'y' => 'yyy',
            ],
            'array' => [
                'aaa',
                'zzz',
            ],
        ];
        $this->repository = new Repository($this->config);
        parent::setUp();
    }
    
    public function testConstruct()
    {
        $this->assertInstanceOf(Repository::class, $this->repository);
    }
    
    public function testGet()
    {
        $this->assertSame('bar', $this->repository->get('foo'));
    }
    
    public function testGetWithDefault()
    {
        $this->assertSame('default', $this->repository->get('not-exist', 'default'));
    }
    
    public function testHas()
    {
        $this->assertTrue($this->repository->has('foo'));
    }
    
    public function testHasNot()
    {
        $this->assertFalse($this->repository->has('not-exist'));
    }
    
    public function testPrepend()
    {
        $this->repository->prepend('array', 'xxx');
        $this->assertSame('xxx', $this->repository->get('array.0'));
    }
    
    public function testPush()
    {
        $this->repository->push('array', 'xxx');
        $this->assertSame('xxx', $this->repository->get('array.2'));
    }
    
    public function testRemove()
    {
        $config = [
            'foo' => 'bar',
            'associate1' => ['x' => 'xxx', 'y' => 'yyy'],
            'associate2' => ['x' => 'xxx', 'y' => 'yyy'],
            'associate3' => ['a' => ['x' => 'xxx', 'y' => 'yyy'], 'b' => ['x' => 'xxx', 'y' => 'yyy']],
        ];
        
        $repository = new Repository($config);
        
        $this->assertTrue($repository->has('foo'));
        $this->assertTrue($repository->has('associate1'));
        $this->assertTrue($repository->has('associate2.x'));
        
        $repository->remove('foo');
        $this->assertFalse($repository->has('foo'));
        
        $repository->remove('associate1');
        $this->assertFalse($repository->has('associate1'));
        $this->assertFalse($repository->has('associate1.x'));
        
        $repository->remove('associate2.x');
        $this->assertTrue($repository->has('associate2'));
        $this->assertFalse($repository->has('associate2.x'));
        $this->assertTrue($repository->has('associate2.y'));
        
        $repository->remove('associate3.b.x');
        $this->assertTrue($repository->has('associate3'));
        $this->assertTrue($repository->has('associate3.a'));
        $this->assertTrue($repository->has('associate3.b'));
        $this->assertTrue($repository->has('associate3.b.y'));
        $this->assertFalse($repository->has('associate3.b.x'));        
    }
    
    public function testSet()
    {
        $this->repository->set('key', 'value');
        $this->assertSame('value', $this->repository->get('key'));
    }
    
    public function testSetArray()
    {
        $this->repository->set([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $this->assertSame('value1', $this->repository->get('key1'));
        $this->assertSame('value2', $this->repository->get('key2'));
    }
}
