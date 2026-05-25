<?php

namespace app\models;

use yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    public static function tableName(): string { return 'role'; }

    public static function getList(): array
    {
        return self::find()->select(['role', 'id'])->indexBy('id')->column();
    }
}
