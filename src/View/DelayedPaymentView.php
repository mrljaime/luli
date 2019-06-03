<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 * @version 1
 * @since 1
 */

namespace App\View;

use App\Entity\Movement;

/**
 * Class DelayedPaymentView
 * @package App\View
 */
class DelayedPaymentView extends AbstractView
{
    /**
     * @return array
     */
    public function buildView()
    {
        /** @var array $info */ /* amount - comment */
        $info = json_decode($this->data['info'], true);

        return [
            'id'        => $this->data['id'],
            'type'      => Movement::REVERSE_TYPES[$this->data['type']],
            'amount'    => $info['amount'],
            'comment'   => $info['comment']
        ];
    }
}