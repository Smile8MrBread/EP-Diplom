<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $airline_id
 * @property string $flight_number
 * @property int    $departure_airport_id
 * @property int    $arrival_airport_id
 * @property string $departure_time
 * @property string $arrival_time
 * @property int    $flight_status_id
 * @property string $created_at
 * @property string $updated_at
 */
class Flight extends ActiveRecord
{
    public $min_price;

    const STATUS_SCHEDULED    = 1;
    const STATUS_CHECKIN      = 2;
    const STATUS_BOARDING     = 3;
    const STATUS_DEPARTED     = 4;
    const STATUS_ARRIVED      = 5;
    const STATUS_DELAYED      = 6;
    const STATUS_CANCELLED    = 7;

    public static function tableName(): string { return 'flight'; }

    public function rules(): array
    {
        return [
            [['airline_id', 'flight_number', 'departure_airport_id', 'arrival_airport_id', 'departure_time', 'arrival_time', 'flight_status_id'], 'required'],
            [['airline_id', 'departure_airport_id', 'arrival_airport_id', 'flight_status_id'], 'integer'],
            [['departure_time', 'arrival_time'], 'safe'],
            [['flight_number'], 'string', 'max' => 30],
            ['arrival_time', 'compare', 'compareAttribute' => 'departure_time', 'operator' => '>', 'message' => 'Время прилёта должно быть позже вылета'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'                   => 'ID',
            'airline_id'           => 'Авиакомпания',
            'flight_number'        => 'Номер рейса',
            'departure_airport_id' => 'Аэропорт вылета',
            'arrival_airport_id'   => 'Аэропорт прилёта',
            'departure_time'       => 'Время вылета',
            'arrival_time'         => 'Время прилёта',
            'flight_status_id'     => 'Статус',
        ];
    }

    public function afterFind(): void
    {
        parent::afterFind();
        if (
            $this->arrival_time &&
            strtotime($this->arrival_time) < time() &&
            $this->flight_status_id !== self::STATUS_CANCELLED
        ) {
            $this->flight_status_id = self::STATUS_ARRIVED;
        }
    }

    public function isArrived(): bool
    {
        return $this->flight_status_id !== self::STATUS_CANCELLED
            && $this->arrival_time
            && strtotime($this->arrival_time) < time();
    }

    public function getDurationMinutes(): int
    {
        $dep = strtotime($this->departure_time);
        $arr = strtotime($this->arrival_time);
        return (int)(($arr - $dep) / 60);
    }

    public function getDurationFormatted(): string
    {
        $mins = $this->durationMinutes;
        return sprintf('%dч %02dмин', intdiv($mins, 60), $mins % 60);
    }

    // --- Relations ---

    public function getAirline()
    {
        return $this->hasOne(Airline::class, ['id' => 'airline_id']);
    }

    public function getDepartureAirport()
    {
        return $this->hasOne(Airport::class, ['id' => 'departure_airport_id']);
    }

    public function getArrivalAirport()
    {
        return $this->hasOne(Airport::class, ['id' => 'arrival_airport_id']);
    }

    public function getFlightStatus()
    {
        return $this->hasOne(FlightStatus::class, ['id' => 'flight_status_id']);
    }

    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['flight_id' => 'id']);
    }

    public function hasActiveOrders(): bool
    {
        return (new \yii\db\Query())
            ->from('order_item oi')
            ->innerJoin('`order` o', 'o.id = oi.order_id')
            ->innerJoin('ticket t', 't.id = oi.ticket_id')
            ->where(['t.flight_id' => $this->id])
            ->andWhere(['o.order_status_id' => [Order::STATUS_PENDING, Order::STATUS_PAID]])
            ->exists();
    }

    public function canCancelByRule(): bool
    {
        return strtotime($this->departure_time) >= strtotime('+3 months')
            || !$this->hasActiveOrders();
    }
}
