<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 * @version 1
 * @since 1
 */

namespace App\View;

/**
 * Class AbstractView
 * @package App\View
 */
abstract class AbstractView
{
    /**
     * @var array
     */
    protected $data;

    /**
     * AbstractView constructor.
     * @param array $data
     */
    private function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public abstract function buildView();

    /**
     * @param $type
     * @param $data
     * @return DefaultView
     */
    public static function factory($type, $data)
    {
        $class = '\\App\\View\\' . ucfirst($type) . 'View';
        if (class_exists($class)) {
            return new $class($data);
        }

        return new DefaultView($data);
    }
}

