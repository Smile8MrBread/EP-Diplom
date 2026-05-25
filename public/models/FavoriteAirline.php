<?php

namespace app\models;

use yii\db\ActiveRecord;

class FavoriteAirline extends ActiveRecord
{
    public static function tableName(): string { return 'favorite_airline'; }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAirline()
    {
        return $this->hasOne(Airline::class, ['id' => 'airline_id']);
    }
}
