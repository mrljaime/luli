<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 * @version 1
 * @since 1
 */

namespace App\View;

/**
 * Class DefaultView
 * @package App\View
 */
class DefaultView extends AbstractView
{

    /**
     * @return array
     */
    public function buildView()
    {
        return $this->data;
    }
}