<?php

namespace app\models;

use yii\db\ActiveRecord;

class AirlineStatus extends ActiveRecord
{
    public static function tableName(): string { return 'airline_status'; }

    public static function getList(): array
    {
        return self::find()->select(['status', 'id'])->indexBy('id')->column();
    }
}
