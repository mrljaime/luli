<?php
/**
 * @author <a href="mailto:mr.ljaime@gmail.com">José Jaime Ramírez Calvo</a>
 * @version 1
 * @since 2019-05-26
 */

namespace App\Util;

/**
 * Class StatusUtil
 * @package App\Util
 */
class StatusUtil
{
    const PENDING = 0x1;

    const PAID = 0x2;

    const SENT = 0x4;

    const CLOSED = 0x10;

    /**
     * @param $pending
     * @return bool
     */
    public static function isPending($pending)
    {
        return (0 != (self::PENDING & $pending));
    }

    /**
     * @param $paid
     * @return bool
     */
    public static function isPaid($paid)
    {
        return (0 != (self::PAID & $paid));
    }

    /**
     * @param $sent
     * @return bool
     */
    public static function isSent($sent)
    {
        return (0 != (self::SENT & $sent));
    }

    /**
     * @param $closed
     * @return bool
     */
    public static function isClosed($closed)
    {
        return (0 != (self::CLOSED & $closed));
    }
}