<?php declare(strict_types=1);

namespace PhpPkg\Ini;

use Toolkit\FsUtil\File;
use Traversable;

/**
 * class Ini
 *
 * @author inhere
 */
class Ini
{
    /**
     * @param string $ini
     * @param int $flags
     *
     * @return array
     */
    public static function decode(string $ini, int $flags = 0): array
    {
        return (new IniParser($ini))->parse($flags);
    }

    /**
     * @param string $iniFile
     *
     * @return array
     */
    public static function decodeFile(string $iniFile): array
    {
        return self::decode(File::readAll($iniFile));
    }

    /**
     * @param array|Traversable $data
     * @param int $flags
     *
     * @return string
     */
    public static function encode(array|Traversable $data, int $flags = 0): string
    {
        return (new IniEncoder($flags))->encode($data);
    }
}
