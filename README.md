# INI

[![License](https://img.shields.io/github/license/phppkg/ini?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/packagist/php-v/phppkg/ini?maxAge=2592000)](https://packagist.org/packages/phppkg/ini)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/phppkg/ini)](https://github.com/phppkg/ini)
[![Actions Status](https://github.com/phppkg/ini/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/ini/actions)

💪 An enhanced `INI` format parser written in PHP.

- auto convert data type, eg: `int, bool, float`
- support encode data to INI string.
- ignores commented lines that start with ";" or "#"
  - ignores broken lines that do not have "="
- supports array values and array value keys
- enhance: supports inline list array value
- enhance: supports multi line string. use `'''` or `"""`
- enhance: supports add interceptor before collect value
- TODO: support parse ENV var. `${SHELL | bash}`

> **[中文说明](README.zh-CN.md)**

## Install

**composer**

```bash
composer require phppkg/ini
```

## Usage

example ini:

```ini
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

# simple multi-line list array, equals the 'simpleList1'
simpleList[] = 567
simpleList[] = "some value"

# simple multi-line list array
[simpleList1]
- = 567
- = "some value"

# simple k-v map
[simpleMap]
val_one = 567
val_two = 'some value'

# multi level list array
[array]
arr_sub_key[] = "arr_elem_one"
arr_sub_key[] = "arr_elem_two"
arr_sub_key[] = "arr_elem_three"

# multi level k-v map sub array
[array_keys]
val_arr_two[6] = "key_6"
val_arr_two[some_key] = "some_key_value"
```

### Decode

Decode from INI string.

```php
use PhpPkg\Ini\Ini;
use Toolkit\Stdlib\Std\Collection;

$data = Ini::decode($ini);
vdump($data);

$cfg = Collection::new($data);
$int = $cfg->get('int'); // 23
```

**Output**:

```text
array(13) {
  ["int"]=> int(23)
  ["float"]=> float(34.5)
  ["str"]=> string(5) "ab cd"
  ["bool"]=> bool(true)
  ["empty-str"]=> string(0) ""
  ["multi-line"]=> string(30) "this is
  a multi
 line string"
  ["inlineList"]=> array(3) {
    [0]=> string(2) "ab"
    [1]=> int(23)
    [2]=> float(34.5)
  }
  ["simpleList"]=> array(2) {
    [0]=> int(567)
    [1]=> string(10) "some value"
  }
  ["simpleList1"]=> array(2) {
    [0]=> int(567)
    [1]=> string(10) "some value"
  }
  ["simpleMap"]=> array(2) {
    ["val_one"]=> int(567)
    ["val_two"]=> string(10) "some value"
  }
  ["array"]=> array(1) {
    ["arr_sub_key"]=> array(3) {
      [0]=> string(12) "arr_elem_one"
      [1]=> string(12) "arr_elem_two"
      [2]=> string(14) "arr_elem_three"
    }
  }
  ["array_keys"]=> array(1) {
    ["val_arr_two"]=> array(2) {
      [6]=> string(5) "key_6"
      ["some_key"]=> string(14) "some_key_value"
    }
  }
}
```

### Decode file

```php
$data = Ini::decodeFile($iniFile);
```

## Encode

Encode data to INI string.

```php
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

$iniString = Ini::encode($data);
echo $iniString;
```

**Output**:

```ini
key0 = val0
key1 = val1
key2 = val2
key3 = val3
key4 = val4
arrKey = [abc, def]
```

## License

[MIT](LICENSE)
