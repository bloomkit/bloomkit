<?php

namespace Bloomkit\Core\Database\PbxQl\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Database\PbxQl\Parser;

class ParserTest extends TestCase
{
    /**
     * @dataProvider provideTokenizerData
     */
    public function testTokenizer($name, $query, $expected)
    {
        $parser = new Parser();
        $tokens = $parser->tokenize($query);
        $this->assertEquals($tokens, $expected, $name);
    }

    /**
     * @dataProvider provideParserData
     */
    public function testParser($name, $query, $expected)
    {
        $parser = new Parser();
        $expression = $parser->parse($query);
        $this->assertEquals($expression, $expected, $name);
    }

    public function provideParserData()
    {
        return array(
            array(
                'Static route',
                'foo = bar and (foo1 > bar1)',
                [
                    ['exprType' => 'colRef', 'expression' => 'foo', 'subExpr' => false],
                    ['exprType' => 'operator', 'expression' => '=', 'subExpr' => false],
                    ['exprType' => 'value', 'expression' => 'bar', 'subExpr' => false],
                    ['exprType' => 'operator', 'expression' => 'and', 'subExpr' => false],
                    ['exprType' => 'expression', 'expression' => '(foo1 > bar1)', 'subExpr' => [
                            ['exprType' => 'colRef', 'expression' => 'foo1', 'subExpr' => false],
                            ['exprType' => 'operator', 'expression' => '>', 'subExpr' => false],
                            ['exprType' => 'value', 'expression' => 'bar1', 'subExpr' => false],
                        ],
                    ],
                ],
            ),
        );
    }

    public function provideTokenizerData()
    {
        return array(
            array(
                'Static route',
                'foo = bar and (foo1 > bar1)',
                ['foo', ' ', '=', ' ', 'bar', ' ', 'and', ' ', '(foo1 > bar1)'],
            ),
            array(
                'Static route2',
                'foo = bar and "foo1 > bar1"',
                ['foo', ' ', '=', ' ', 'bar', ' ', 'and', ' ', '"foo1 > bar1"'],
            ),
        );
    }
}
