<?php
/**
 * @author <a href="mailto:mr.ljaime@gmail.com">José Jaime Ramírez Calvo</a>
 * @version 1
 * @since 2019-02-24
 */

namespace App\Util;

/**
 * Class DateTimeUtil
 * @package App\Util
 */
class DateTimeUtil
{
    /**
     * @return \DateTime
     * @throws \Exception
     */
    public static function getDateTime(): \DateTime
    {
        return new \DateTime("now", new \DateTimeZone("America/Mexico_City"));
    }

    /**
     * To json response standard
     *
     * @param \DateTime $dateTime
     * @return string|null
     */
    public static function formatForJsonResponse(\DateTime $dateTime): ?string
    {
        return $dateTime->format("Y-m-d H:i:s");
    }
}