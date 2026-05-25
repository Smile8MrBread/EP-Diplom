<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int   $id
 * @property int   $order_id
 * @property int   $ticket_id
 * @property int   $quantity
 * @property float $unit_price
 * @property float $line_total
 */
class OrderItem extends ActiveRecord
{
    public static function tableName(): string { return 'order_item'; }

    public function rules(): array
    {
        return [
            [['order_id', 'ticket_id', 'quantity', 'unit_price', 'line_total'], 'required'],
            [['order_id', 'ticket_id', 'quantity'], 'integer'],
            [['unit_price', 'line_total'], 'number', 'min' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'order_id'   => 'Заказ',
            'ticket_id'  => 'Билет',
            'quantity'   => 'Количество',
            'unit_price' => 'Цена за ед.',
            'line_total' => 'Итого',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }
}
