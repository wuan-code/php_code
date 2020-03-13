<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | author    武安y<yaobin24@126.com>
// +----------------------------------------------------------------------
// | note
// +----------------------------------------------------------------------
// | Date       2020-03-12 17:35
// +----------------------------------------------------------------------


namespace Exception;


use ReflectionClass;
use RuntimeException;

class BaseException extends RuntimeException{
    /**
     * new exception
     *
     * @param $name
     * @param $arguments
     * @return static
     * @throws \ReflectionException
     */
    public static function __callStatic ($name, $arguments) {
        static $exceptionMap = [];
        static $regex = '/@method[ ]*static[ ]*([A-Z0-9a-z_]?[A-Za-z0-9_]*)[ ]*([A-Z0-9_]*)[ ]*\([ ]*\$(code|codeOrText)[ ]*=[ ]*(0x[0-9A-F]{9})[ ]*,[ ]*\$(text)[ ]*=[ ]*[\'"](.*)[\'"][ ]*\)/m';

        $className = static::class;
        $curExceptionMap = $exceptionMap[$className] ?? [];
        if (!isset($exceptionMap[$className])) {
            $rClass = new ReflectionClass(static::class);

            foreach (explode("\n", $rClass->getDocComment()) as $line) {
                $line = trim($line, " \t\n\r\0\x0B*");

                if (preg_match_all($regex, $line, $matches, PREG_SET_ORDER, 0)) {
                    [$_, $exceptionName, $exceptionMethodName, $firstParamName, $exceptionCode, $secondParamName, $exceptionText] = $matches[0];
                    if ($exceptionName != $rClass->getShortName()) {
                        throw new RuntimeException("doc: {$line} return name is not equals class {$rClass->getName()}", 500);
                    }
                    $curExceptionMap[$exceptionMethodName] = [
                        $firstParamName === "codeOrText", intval(substr($exceptionCode, 2), 16), $exceptionText,
                    ];
                }
            }

            $exceptionMap[$className] = $curExceptionMap;
        }

        $allowText = false;
        $exceptionCode = 500;
        $exceptionText = "unknown exception {$name}";

        if (isset($curExceptionMap[$name])) {
            [$allowText, $exceptionCode, $exceptionText] = $curExceptionMap[$name];
        }

        if (count($arguments) == 1) {
            if ($allowText && is_string($arguments[0])) {
                $exceptionText = $arguments[0];
            } elseif (is_numeric($arguments[0])) {
                $exceptionCode = (int)$arguments[0];
            } else {
                var_dump('unknown code type');die;
            }
        } else if (count($arguments) == 2) {
            $exceptionCode = (int)$arguments[0];
            $exceptionText = $arguments[1];
        }

        return new static($exceptionText, $exceptionCode);
    }
}