<?php declare(strict_types=1);

namespace PhpPkg\Ini;

use Toolkit\Stdlib\Str;
use function explode;
use function implode;
use function is_array;
use function ltrim;
use function preg_match;
use function rtrim;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * class IniParser
 *
 * @author inhere
 */
class IniParser
{
    /**
     * @var int
     */
    protected int $flags = 0;

    /**
     * current parsed section name.
     *
     * @var string
     */
    private string $sectionName = '';

    /**
     * @var string
     */
    private string $multiLineKey = '';

    /**
     * @var array
     */
    private array $multiLineVal = [];

    /**
     * the source ini string.
     *
     * @var string
     */
    private string $source;

    /**
     * parsed data
     *
     * @var array
     */
    private array $data = [];

    /**
     * allow add interceptors do something before collect value
     *
     * @var array{callable(mixed, bool):mixed}
     * @example an interceptor eg:
     *
     * ```php
     * function (mixed $val, bool $isMultiLine): mixed {
     *    if ($val === 'SPECIAL') {
     *      // do something
     *      return 'Another value';
     *    }
     *
     *    return $val;
     * }
     * ```
     */
    private array $interceptors = [];

    /**
     * @param string $source
     *
     * @return static
     */
    public static function new(string $source = ''): self
    {
        return new self($source);
    }

    /**
     * Class constructor.
     *
     * @param string $source
     */
    public function __construct(string $source = '')
    {
        $this->source = $source;
    }

    /**
     * parse ini string
     *
     * - auto convert data type, eg: int, bool, float
     * - ignores commented lines that start with ";" or "#"
     * - ignores broken lines that do not have "="
     * - supports array values and array value keys
     * - enhance: supports inline array value
     * - enhance: supports multi line string. use `'''` or `"""`
     * - enhance: supports add interceptor before collect value
     *
     * @param int $flags
     *
     * @return array
     * @url https://www.php.net/manual/en/function.parse-ini-string.php#111845
     */
    public function parse(int $flags = 0): array
    {
        $this->flags = $flags;

        if (!$str = trim($this->source)) {
            return [];
        }

        // reset data.
        $this->data = [];

        $lines = explode("\n", $str);
        foreach ($lines as $line) {
            // inside multi line
            if ($this->multiLineKey) {
                $trimmed = trim($line);

                // multi line end.
                if ($trimmed === '"""' || $trimmed === "'''") {
                    $this->collectValue($this->multiLineKey, implode("\n", $this->multiLineVal));
                    // reset tmp data.
                    $this->multiLineKey = '';
                    $this->multiLineVal = [];
                } else {
                    $this->multiLineVal[] = $line;
                }
                continue;
            }

            // empty line
            if (!$line = trim($line)) {
                continue;
            }

            // comments line
            if ($line[0] === "#" || $line[0] === ";" || str_starts_with($line, '//')) {
                continue;
            }

            // section line. eg: [arrayName]
            if (strlen($line) > 3 && $line[0] === '[' && str_ends_with($line, ']')) {
                $this->sectionName = substr($line, 1, -1);
                continue;
            }

            // invalid line
            if (!strpos($line, '=')) {
                continue;
            }

            $tmp = explode('=', $line, 2);
            $key = rtrim($tmp[0]);
            $val = ltrim($tmp[1]);

            // empty value
            if ($val === '') {
                $this->collectValue($key, $val);
                continue;
            }

            // multi line start.
            if ($val === '"""' || $val === "'''") {
                $this->multiLineKey = $key;
                continue;
            }

            // inline array value. eg: tags=[abc, 234]
            if ($val && $val[0] === '[' && str_ends_with($val, ']')) {
                $val = Str::toTypedArray(substr($val, 1, -1));
            } elseif (preg_match("/^\".*\"$/", $val) || preg_match("/^'.*'$/", $val)) {
                // remove quote chars
                $val = mb_substr($val, 1, -1);
            } else {
                // auto convert type
                $val = Str::toTyped($val, true);
            }

            $this->collectValue($key, $val);
        }

        return $this->data;
    }

    /**
     * @param string $key
     * @param mixed $val
     */
    protected function collectValue(string $key, mixed $val): void
    {
        // has interceptors
        if ($this->interceptors) {
            $isMl = $this->multiLineKey !== '';
            foreach ($this->interceptors as $fn) {
                $val = $fn($val, $isMl);
            }
        }

        // not in section
        if (!$this->sectionName) {
            // is list array value. eg `list_name[] = "arr_elem_one"`
            if (str_ends_with($key, '[]') && ($len = strlen($key) > 2)) {
                $section = substr($key, 0, $len-3);

                if (!isset($this->data[$section]) || !is_array($this->data[$section])) {
                    $this->data[$section] = [];
                }

                $this->data[$section][] = $val;
            } else {
                // top field
                $this->data[$key] = $val;
            }
            return;
        }

        // in section. eg: [arrayName] -> $sectionName='arrayName'
        $section = $this->sectionName;

        // is list array value. eg `- = "arr_elem_one"` `[] = "arr_elem_one"`
        if ($key === '-' || $key === '[]') {
            if (!isset($this->data[$section]) || !is_array($this->data[$section])) {
                $this->data[$section] = [];
            }

            $this->data[$section][] = $val;
            return;
        }

        // is map array sub key.
        // eg:
        // val_arr[] = "arr_elem_one"
        // val_arr_two[sub_key] = "some_key_value"
        // [sub_key] = "some_key_value"
        $ok = preg_match("/[\w-]{0,64}\[(.*?)]$/", $key, $matches);
        if ($ok === 1 && isset($matches[0])) {
            [$arrName, $subKey] = explode('[', trim($key, ']'));

            if ($arrName !== '') {
                if (!isset($this->data[$section][$arrName]) || !is_array($this->data[$section][$arrName])) {
                    $this->data[$section][$arrName] = [];
                }

                if ($subKey !== '') { // eg: val_arr[subKey] = "arr_elem_one"
                    $this->data[$section][$arrName][$subKey] = $val;
                } else { // eg: val_arr[] = "arr_elem_one"
                    $this->data[$section][$arrName][] = $val;
                }
            } else {
                if (!isset($this->data[$section]) || !is_array($this->data[$section])) {
                    $this->data[$section] = [];
                }

                // eg: [sub_key] = "arr_elem_one"
                $this->data[$section][][$subKey] = $val;
            }
        } else {
            $this->data[$section][$key] = $val;
        }
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return IniParser
     */
    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param callable $interceptor
     *
     * @return IniParser
     */
    public function addInterceptor(callable $interceptor): self
    {
        $this->interceptors[] = $interceptor;
        return $this;
    }

    /**
     * @param callable[] $interceptors
     *
     * @return IniParser
     */
    public function setInterceptors(callable ...$interceptors): self
    {
        $this->interceptors = $interceptors;
        return $this;
    }

    /**
     * @param int $flags
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    protected function removeQuotes(string $str): string
    {
        if (preg_match("/^\".*\"$/", $str) || preg_match("/^'.*'$/", $str)) {
            return mb_substr($str, 1, -1);
        }

        return $str;
    }
}
