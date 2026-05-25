<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $user_id
 * @property string $date
 * @property int    $order_status_id
 * @property float  $total_amount
 * @property string $currency_code
 * @property string $contact_email
 * @property string $contact_phone
 * @property int    $pay_type_id
 * @property string $comment_text
 */
class Order extends ActiveRecord
{
    const STATUS_PENDING   = 1;
    const STATUS_PAID      = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_REFUND    = 4;

    public static function tableName(): string { return 'order'; }

    public function afterFind(): void
    {
        parent::afterFind();
        if ($this->order_status_id === self::STATUS_PENDING && $this->hasDepartedFlight()) {
            $this->order_status_id = self::STATUS_CANCELLED;
        }
    }

    public function rules(): array
    {
        return [
            [['user_id', 'order_status_id', 'total_amount', 'currency_code', 'contact_email', 'contact_phone', 'pay_type_id'], 'required'],
            [['user_id', 'order_status_id', 'pay_type_id'], 'integer'],
            [['total_amount'], 'number', 'min' => 0],
            [['contact_email'], 'email'],
            [['contact_email', 'contact_phone', 'currency_code'], 'string', 'max' => 255],
            [['comment_text'], 'string'],
            [['date'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'              => 'Номер заказа',
            'user_id'         => 'Пользователь',
            'date'            => 'Дата заказа',
            'order_status_id' => 'Статус',
            'total_amount'    => 'Сумма',
            'currency_code'   => 'Валюта',
            'contact_email'   => 'Email для связи',
            'contact_phone'   => 'Телефон для связи',
            'pay_type_id'     => 'Способ оплаты',
            'comment_text'    => 'Комментарий',
        ];
    }

    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_PENDING => [self::STATUS_PAID, self::STATUS_CANCELLED],
            self::STATUS_PAID    => [self::STATUS_CANCELLED],
        ];
    }

    public function canChangeStatus(int $targetStatus): bool
    {
        $transitions = self::allowedTransitions();
        if (!isset($transitions[$this->order_status_id])) return false;
        if (!in_array($targetStatus, $transitions[$this->order_status_id])) return false;
        return !$this->hasDepartedFlight();
    }

    private function hasDepartedFlight(): bool
    {
        return (new \yii\db\Query())
            ->from('order_item oi')
            ->innerJoin('ticket t', 't.id = oi.ticket_id')
            ->innerJoin('flight f', 'f.id = t.flight_id')
            ->where(['oi.order_id' => $this->id])
            ->andWhere(['<', 'f.departure_time', date('Y-m-d H:i:s')])
            ->exists();
    }

    public function getFormattedTotal(): string
    {
        return number_format($this->total_amount, 0, '.', ' ') . ' ' . $this->currency_code;
    }

    // --- Relations ---

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOrderStatus()
    {
        return $this->hasOne(OrderStatus::class, ['id' => 'order_status_id']);
    }

    public function getPayType()
    {
        return $this->hasOne(PayType::class, ['id' => 'pay_type_id']);
    }

    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }
}
