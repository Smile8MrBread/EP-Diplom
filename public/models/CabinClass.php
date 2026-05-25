<?php

namespace app\models;

use yii\db\ActiveRecord;

class CabinClass extends ActiveRecord
{
    public static function tableName(): string { return 'cabin_class'; }

    public static function getList(): array
    {
        return self::find()->select(['class', 'id'])->indexBy('id')->column();
    }
}
