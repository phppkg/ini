<?php declare(strict_types=1);

namespace PhpPkg\Ini;

use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Str;
use Traversable;
use function get_object_vars;
use function implode;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function method_exists;
use function sprintf;
use function str_contains;
use const PHP_EOL;

/**
 * class IniEncoder
 *
 * @author inhere
 */
class IniEncoder
{
    /**
     * @var int
     */
    protected int $flags;

    /**
     * @param int $flags
     *
     * @return static
     */
    public static function new(int $flags = 0): self
    {
        return new self($flags);
    }

    /**
     * Class constructor.
     *
     * @param int $flags
     */
    public function __construct(int $flags = 0)
    {
        $this->flags = $flags;
    }

    /**
     * @param array|Traversable $data
     * @param int $flags
     *
     * @return string
     */
    public function encode(array|Traversable $data, int $flags = 0): string
    {
        $this->flags = $flags;

        $strings = $defSec = [];
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                if (Arr::isList($val)) {
                    $defSec[$key] = $val;
                    continue;
                }

                $strings[] = $this->encodeSection($key, $val);
            } elseif (is_object($val)) {
                if ($val instanceof Traversable) {
                    $val = (array)$val;
                } elseif (method_exists($val, 'toArray')) {
                    $val = $val->toArray();
                } else {
                    $val = get_object_vars($val);
                }

                $strings[] = $this->encodeSection($key, $val);
            } else {
                $defSec[$key] = $val;
            }
        }

        $defSecIni = '';
        if ($defSec) {
            $defSecIni = $this->encodeSection('', $defSec) . PHP_EOL;
        }

        if (!$strings) {
            return $defSecIni;
        }
        return $defSecIni . implode("\n", $strings) . PHP_EOL;
    }

    /**
     * @param string $section
     * @param array $secData
     *
     * @return string
     */
    protected function encodeSection(string $section, array $secData): string
    {
        $iniLines = [];
        if ($section) {
            $iniLines[] = "[$section]";
        }

        $index = 0;
        foreach ($secData as $key => $val) {
            if (is_string($key)) {
                if (is_array($val)) {
                    // is list
                    if (Arr::isList($val)) {
                        $valString  = $this->listToString($val);
                        $iniLines[] = sprintf('%s = %s', $key, $valString);
                    } else {
                        foreach ($val as $sk => $sv) {
                            $iniLines[] = sprintf('%s[%s] = %s', $key, $sk, $this->valToString($sv));
                        }
                    }
                } else {
                    $valString  = $this->valToString($val);
                    $iniLines[] = sprintf('%s = %s', $key, $valString);
                }
            } elseif (is_int($key)) {
                $valString = $this->valToString($val);
                if ($index === $key) {
                    $iniLines[] = sprintf('[] = %s', $valString);
                } else {
                    $iniLines[] = sprintf('%d = %s', $key, $valString);
                }
            }

            $index++;
        }

        return implode("\n", $iniLines);
    }

    /**
     * @param list<mixed> $list
     *
     * @return string
     */
    protected function listToString(array $list): string
    {
        $strings = [];
        foreach ($list as $item) {
            $strings[] = $this->quoteString((string)$item);
        }

        return '[' . implode(', ', $strings) . ']';
    }

    /**
     * @param mixed $val
     *
     * @return string
     */
    protected function valToString(mixed $val): string
    {
        if (is_array($val)) {
            return $this->listToString($val);
        }

        if (!is_string($val)) {
            return (string)$val;
        }

        return $this->quoteString($val);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    protected function quoteString(string $str): string
    {
        if (str_contains($str, "\n")) {
            return "'''\n$str\n'''";
        }

        return Str::textQuote($str);
    }

    /**
     * @param int $flags
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }
}
