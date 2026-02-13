<?php

namespace r3pt1s\mysql\util;

use pmmp\thread\ThreadSafeArray;

final class ThreadedHelper {

    public static function toThreadSafeArray(array $array): ThreadSafeArray {
        return ThreadSafeArray::fromArray($array);
    }

    public static function toNormalArray(ThreadSafeArray $array): array {
        $array = (array) $array;
        foreach ($array as &$value) {
            if ($value instanceof ThreadSafeArray) $value = self::toNormalArray($value);
        }

        return $array;
    }
}