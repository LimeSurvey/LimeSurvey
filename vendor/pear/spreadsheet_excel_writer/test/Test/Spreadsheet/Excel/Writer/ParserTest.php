<?php

namespace Test\Spreadsheet\Excel\Writer;

use Spreadsheet_Excel_Writer_Parser;

use function is_string;

class ParserTest extends \LegacyPHPUnit\TestCase
{
    /**
     * Test that _convertFunction returns a value for all code paths
     */
    public function testConvertFunctionReturnsValue()
    {
        $parser = new Spreadsheet_Excel_Writer_Parser(0, 0x0500);

        $method = new \ReflectionMethod($parser, '_convertFunction');
        $method->setAccessible(true);

        // Fixed args (TIME=3) should return without issue
        $result = $method->invoke($parser, 'TIME', 3);
        $this->assertNotEmpty($result);
        $this->assertTrue(is_string($result));

        // Variable args (SUM=-1) should return without issue
        $result = $method->invoke($parser, 'SUM', 2);
        $this->assertNotEmpty($result);
        $this->assertTrue(is_string($result));

        // Array structure: [function_number, arg_count, unknown, volatile_flag]
        $parser->_functions['INVALID'] = [999, -2, 0, 0]; // -2 is not valid

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument count -2 for function INVALID');
        $method->invoke($parser, 'INVALID', 1);
    }

    /**
     * Test that duplicate PTG entries have the correct final values
     * This ensures backward compatibility is maintained
     *
     * Background: In the original code, these PTG names were duplicated with different values:
     * - ptgMemNoMemN appeared at 0x2F, 0x4F, and 0x6F
     * - ptgAreaErr3d appeared at 0x3D, 0x5D, and 0x7D
     *
     * In PHP arrays, duplicate keys result in the last value overwriting earlier ones.
     * This test confirms that behavior is preserved.
     */
    public function testDuplicatePtgValues()
    {
        $parser = new Spreadsheet_Excel_Writer_Parser(0, 0x0500);

        $property = new \ReflectionProperty($parser, 'ptg');
        $property->setAccessible(true);
        $ptg = $property->getValue($parser);

        // ptgMemNoMemN: last duplicate at 0x6F wins (0x2F, 0x4F were overwritten)
        $this->assertArrayHasKey('ptgMemNoMemN', $ptg);
        $this->assertSame(0x6F, $ptg['ptgMemNoMemN']);

        // ptgAreaErr3d: last duplicate at 0x7D wins (0x3D, 0x5D were overwritten)
        $this->assertArrayHasKey('ptgAreaErr3d', $ptg);
        $this->assertSame(0x7D, $ptg['ptgAreaErr3d']);

        // ptgMemNoMem base variant at 0x28 (0x48, 0x68 duplicates removed per Excel spec)
        $this->assertSame(0x28, $ptg['ptgMemNoMem']);
    }
}
