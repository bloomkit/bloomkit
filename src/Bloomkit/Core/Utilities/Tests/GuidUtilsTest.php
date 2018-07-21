<?php

namespace Bloomkit\Core\Utilities\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Utilities\GuidUtils;

class GuidUtilsTest extends TestCase
{
    public function testGenerateUncompressedGuid()
    {
        $guid = GuidUtils::generateGuid();
        $this->assertEquals(strlen($guid), 38);
    }

    public function testGenerateCompressedGuid()
    {
        $guid = GuidUtils::generateGuid(true);
        $this->assertEquals(strlen($guid), 32);
    }

    public function testCompressGuid()
    {
        $guid = '{XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}';
        $compressed = GuidUtils::compressGuid($guid);
        $this->assertEquals($compressed, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
    }

    public function testDecompressGuid()
    {
        $guid = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
        $decompressed = GuidUtils::decompressGuid($guid);
        $this->assertEquals($decompressed, '{XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}');
    }
}
