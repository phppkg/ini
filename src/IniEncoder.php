<?php declare(strict_types=1);

namespace PhpPkg\Ini;

use Traversable;

/**
 * class IniEncoder
 *
 * @author inhere
 */
class IniEncoder
{
    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * @param array|Traversable $data
     * @param int $flags
     *
     * @return string
     */
    public function encode(array|Traversable $data, int $flags = 0): string
    {
        return 'TODO';
    }
}
