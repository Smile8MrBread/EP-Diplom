<?php

namespace app\models;

use yii\db\ActiveRecord;

class FlightStatus extends ActiveRecord
{
    public static function tableName(): string { return 'flight_status'; }

    public static function getList(): array
    {
        return self::find()->select(['status', 'id'])->indexBy('id')->column();
    }
}
