<?php declare(strict_types=1);

namespace PhpPkg\IniTest;

use PhpPkg\Ini\Ini;
use function vdump;

/**
 * class IniEncoderTest
 *
 * @author inhere
 * @date 2022/8/7
 */
class IniEncoderTest extends IniTestCase
{
    public function testEncode_simple(): void
    {
        $data = [
            'key0' => 'val0',
            'key1' => 'val1',
            'key2' => 'val2',
            'key3' => 'val3',
            'key4' => 'val4',
            'arrKey' => [
                'abc',
                'def',
            ],
        ];

        $ini = Ini::encode($data);

        $this->assertNotEmpty($ini);
        $this->assertStringContainsString('arrKey = [', $ini);
        vdump($ini);
    }

    public function testEncode_full(): void
    {
        $data = [
            'int'         => 23,
            'float'       => 34.5,
            'str'         => 'ab cd',
            'bool'        => true,
            'empty-str'   => '',
            'multi-line'  => 'this is
  a multi
 line string',
            'inlineList'  => [
                'ab',
                23,
                34.5,
            ],
            'simpleList'  => [
                567,
                'some value',
            ],
            'simpleList1' => [
                567,
                'some value',
            ],
            'simpleMap'   => [
                'val_one' => 567,
                'val_two' => 'some value',
            ],
            // 'simpleMap2'  => [
            //     0 => [
            //         'val_one' => 567,
            //     ],
            //     1 => [
            //         'val_two' => 'some value',
            //     ],
            // ],
            'array'       => [
                'arr_sub_key' => [
                    'arr_elem_one',
                    'arr_elem_two',
                    'arr_elem_three',
                ],
            ],
            'array_keys'  => [
                'val_arr_two' => [
                    6          => 'key_6',
                    'some_key' => 'some_key_value',
                ],
            ],
        ];

        $ini = Ini::encode($data);
        $this->assertNotEmpty($ini);

        vdump($ini);
    }
}
