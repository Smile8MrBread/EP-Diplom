<?php

namespace app\models;

use yii\db\ActiveRecord;

class PayType extends ActiveRecord
{
    public static function tableName(): string { return 'pay_type'; }

    public static function getList(): array
    {
        return self::find()->select(['title', 'id'])->indexBy('id')->column();
    }
}
