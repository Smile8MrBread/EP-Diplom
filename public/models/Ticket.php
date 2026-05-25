<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $flight_id
 * @property int    $cabin_class_id
 * @property string $fare_type
 * @property string $baggage_info
 * @property float  $price
 * @property string $currency_code
 * @property int    $available_quantity
 * @property int    $ticket_status_id
 */
class Ticket extends ActiveRecord
{
    const STATUS_AVAILABLE = 1;
    const STATUS_SOLD_OUT  = 2;
    const STATUS_CANCELLED = 3;

    const SCENARIO_UPDATE = 'update';

    public static function tableName(): string { return 'ticket'; }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['cabin_class_id', 'fare_type', 'baggage_info', 'price', 'currency_code', 'available_quantity'];
        return $scenarios;
    }

    public function rules(): array
    {
        return [
            [['flight_id', 'cabin_class_id', 'fare_type', 'baggage_info', 'price', 'currency_code', 'available_quantity', 'ticket_status_id'], 'required'],
            [['flight_id', 'cabin_class_id', 'available_quantity', 'ticket_status_id'], 'integer'],
            [['price'], 'number', 'min' => 0],
            [['fare_type', 'baggage_info'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'                 => 'ID',
            'flight_id'          => 'Рейс',
            'cabin_class_id'     => 'Класс салона',
            'fare_type'          => 'Тип тарифа',
            'baggage_info'       => 'Багаж',
            'price'              => 'Цена',
            'currency_code'      => 'Валюта',
            'available_quantity' => 'Кол-во мест',
            'ticket_status_id'   => 'Статус',
        ];
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->price, 0, '.', ' ') . ' ' . $this->currency_code;
    }

    // --- Relations ---

    public function getFlight()
    {
        return $this->hasOne(Flight::class, ['id' => 'flight_id']);
    }

    public function getCabinClass()
    {
        return $this->hasOne(CabinClass::class, ['id' => 'cabin_class_id']);
    }

    public function getTicketStatus()
    {
        return $this->hasOne(TicketStatus::class, ['id' => 'ticket_status_id']);
    }

    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['ticket_id' => 'id']);
    }
}
