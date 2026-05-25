<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CatalogSearch extends Model
{
    public ?int $from       = null;
    public ?int $to         = null;
    public ?string $date    = null;
    public ?int $airline_id = null;
    public string $sort     = 'date_asc';

    public function rules(): array
    {
        return [
            [['from', 'to', 'airline_id'], 'integer'],
            [['date'], 'safe'],
            [['sort'], 'in', 'range' => ['price_asc', 'price_desc', 'date_asc', 'date_desc']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'from'       => 'Откуда',
            'to'         => 'Куда',
            'date'       => 'Дата вылета',
            'airline_id' => 'Авиакомпания',
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $minPriceSql = '(SELECT MIN(t2.price) FROM ticket t2 WHERE t2.flight_id = flight.id'
            . ' AND t2.ticket_status_id = ' . Ticket::STATUS_AVAILABLE
            . ' AND t2.available_quantity > 0)';

        $query = Flight::find()
            ->select(['flight.*', "$minPriceSql AS min_price"])
            ->joinWith(['departureAirport', 'arrivalAirport', 'airline'])
            ->where(['!=', 'flight.flight_status_id', Flight::STATUS_CANCELLED])
            ->andWhere(['airline.airline_status_id' => Airline::STATUS_ACTIVE])
            ->andWhere(['>', 'flight.departure_time', date('Y-m-d H:i:s')])
            ->andWhere(['EXISTS', (new \yii\db\Query())
                ->from('ticket t_check')
                ->where('t_check.flight_id = flight.id')
                ->andWhere(['t_check.ticket_status_id' => Ticket::STATUS_AVAILABLE])
                ->andWhere(['>', 't_check.available_quantity', 0])])
            ->with(['tickets' => function ($q) {
                $q->where(['ticket_status_id' => Ticket::STATUS_AVAILABLE])
                  ->andWhere(['>', 'available_quantity', 0])
                  ->with('cabinClass');
            }])
            ->groupBy('flight.id');

        $this->load($params, '');

        if ($this->from) {
            $query->andWhere(['flight.departure_airport_id' => $this->from]);
        }
        if ($this->to) {
            $query->andWhere(['flight.arrival_airport_id' => $this->to]);
        }
        if ($this->date) {
            $query->andWhere('DATE(flight.departure_time) = :dt', [':dt' => $this->date]);
        }
        if ($this->airline_id) {
            $query->andWhere(['flight.airline_id' => $this->airline_id]);
        }

        switch ($this->sort) {
            case 'price_asc':  $query->orderBy(new \yii\db\Expression('min_price ASC'));  break;
            case 'price_desc': $query->orderBy(new \yii\db\Expression('min_price DESC')); break;
            case 'date_desc':  $query->orderBy(['flight.departure_time' => SORT_DESC]);   break;
            default:           $query->orderBy(['flight.departure_time' => SORT_ASC]);
        }

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => false,
            'pagination' => ['pageSize' => 10],
        ]);
    }
}
