<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $airline_id
 * @property int    $rating_value
 * @property string $created_at
 * @property string $updated_at
 */
class AirlineRating extends ActiveRecord
{
    public static function tableName(): string { return 'airline_rating'; }

    public function rules(): array
    {
        return [
            [['user_id', 'airline_id', 'rating_value'], 'required'],
            [['user_id', 'airline_id', 'rating_value'], 'integer'],
            ['rating_value', 'in', 'range' => [1, 2, 3, 4, 5]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'Пользователь',
            'airline_id'   => 'Авиакомпания',
            'rating_value' => 'Оценка',
            'created_at'   => 'Дата',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAirline()
    {
        return $this->hasOne(Airline::class, ['id' => 'airline_id']);
    }

    public function getReview()
    {
        return $this->hasOne(AirlineReview::class, ['rating_id' => 'id']);
    }
}
