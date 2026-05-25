<?php

namespace app\models;

use yii\db\ActiveRecord;

class Airport extends ActiveRecord
{
    public static function tableName(): string { return 'airport'; }

    public function attributeLabels(): array
    {
        return [
            'id'          => 'ID',
            'timezone_id' => 'Часовой пояс',
            'name'        => 'Название',
            'city'        => 'Город',
            'country'     => 'Страна',
        ];
    }

    public static function getList(): array
    {
        return self::find()
            ->select(["CONCAT(name, ', ', city) as label", 'id'])
            ->indexBy('id')
            ->column();
    }

    public function getTimezone()
    {
        return $this->hasOne(Timezone::class, ['id' => 'timezone_id']);
    }
}
