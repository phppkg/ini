<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Parser;

use Inhere\Kite\Lib\Parser\IniParser;
use PhpPkg\Ini\Ini;
use PhpPkg\IniTest\IniTestCase;
use function strtoupper;
use function vdump;

/**
 * class IniParserTest
 */
class IniParserTest extends IniTestCase
{
    public function testParseIni_full(): void
    {
        $ini = <<<'INI'
; comments line
// comments line
# comments line

int = 23
float = 34.5
str=ab cd
bool=true
empty-str = 

# support multi-line
multi-line = '''
this is
  a multi
 line string
 '''

# simple inline list array
inlineList = [ab, 23, 34.5]

# simple multi-line list array, equals the 'simpleList'
simpleList[] = 567
simpleList[] = "some value"

# simple multi-line list array
[simpleList1]
[] = 567
[] = "some value"

# simple k-v map
[simpleMap]
val_one = 567
val_two = 'some value'

# multi level k-v map
[simpleMap2]
[val_one] = 567
[val_two] = "some value"

# multi level list array
[array]
arr_sub_key[] = "arr_elem_one"
arr_sub_key[] = "arr_elem_two"
arr_sub_key[] = "arr_elem_three"

# multi level k-v map sub array
[array_keys]
val_arr_two[6] = "key_6"
val_arr_two[some_key] = "some_key_value"
INI;
        $data = IniParser::new($ini)->parse();
        vdump($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('inlineList', $data);
        $this->assertEquals(23, $data['int']);
        $this->assertEquals(34.5, $data['float']);
        $this->assertEquals('', $data['empty-str']);
        $this->assertEquals("this is
  a multi
 line string", $data['multi-line']);

        $this->assertEquals(['ab', 23, 34.5], $data['inlineList']);

        $this->assertArrayHasKey('simpleList', $data);
        $this->assertEquals([567, 'some value'], $data['simpleList']);
        $this->assertArrayHasKey('simpleList1', $data);
        $this->assertEquals([567, 'some value'], $data['simpleList1']);

        $this->assertArrayHasKey('simpleMap', $data);
        $this->assertEquals(['val_one' => 567, 'val_two' => 'some value'], $data['simpleMap']);
    }

    public function testParseIni_1levelList_inline(): void
    {
        $ini = '
# simple inline list array
inlineList = [ab, 23, 34.5]
';
        $data = Ini::decode($ini);
        // vdump($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('inlineList', $data);
        $this->assertEquals(['ab', 23, 34.5], $data['inlineList']);
    }

    public function testParseIni_1levelList_m_line(): void
    {
        $ini = '
# simple multi-line list array
simpleList[] = 567
simpleList[] = "some value"
';
        $data = Ini::decode($ini);
        // vdump($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('simpleList', $data);
        $this->assertEquals([567, 'some value'], $data['simpleList']);

        $ini = '
# simple multi-line list array
[simpleList]
[] = 567
[] = "some value"
';
        $data = Ini::decode($ini);
        // vdump($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('simpleList', $data);
        $this->assertEquals([567, 'some value'], $data['simpleList']);
    }

    public function testParseIni_setInterceptors(): void
    {
        $ini = '
# comments
someKey = value
someKey2 = value2
';
        $p = IniParser::new($ini);
        $data = $p->parse();
        $this->assertNotEmpty($data);
        $this->assertEquals('value', $data['someKey']);
        $this->assertEquals('value2', $data['someKey2']);

        // use interceptor
        $p->setInterceptors(function ($val) {
            if ($val === 'value') {
                return strtoupper($val);
            }
            return $val;
        });
        $data = $p->parse();

        // vdump($data);
        $this->assertNotEmpty($data);
        $this->assertEquals('VALUE', $data['someKey']);
        $this->assertEquals('value2', $data['someKey2']);
    }
}
