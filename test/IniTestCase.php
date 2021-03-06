<?php declare(strict_types=1);

namespace PhpPkg\IniTest;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;

/**
 * Class BaseTestCase
 *
 * @package PhpPkg\IniTest
 */
abstract class IniTestCase extends TestCase
{
    /**
     * get method for test protected and private method
     *
     * usage:
     *
     * ```php
     * $rftMth = $this->method(SomeClass::class, $protectedOrPrivateMethod)
     *
     * $obj = new SomeClass();
     * $ret = $rftMth->invokeArgs($obj, $invokeArgs);
     * ```
     *
     * @param object|string $class
     * @param string $method
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected static function getMethod(object|string $class, string $method): ReflectionMethod
    {
        $refMth = new ReflectionMethod($class, $method);
        $refMth->setAccessible(true);

        return $refMth;
    }

    /**
     * @param callable $cb
     *
     * @return Throwable
     */
    protected function runAndGetException(callable $cb): Throwable
    {
        try {
            $cb();
        } catch (Throwable $e) {
            return $e;
        }

        return new RuntimeException('NO ERROR', -1);
    }
}
