<?php
/**
 * @author <a href="mailto:mr.ljaime@gmail.com">José Jaime Ramírez Calvo</a>
 * @version 1
 * @since 2019-06-02
 */

namespace App\Util;

/**
 * Class ArrayUtil
 * @package App\Util
 */
class ArrayUtil
{
    /**
     * Safe retriever
     *
     * @param $from
     * @param $key
     * @param $safe
     * @return mixed|null
     */
    public static function safe($from, $key, $safe = null)
    {
        if (!array_key_exists($key, $from)) {
            return $safe;
        }

        return $from[$key];
    }
}