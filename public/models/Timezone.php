<?php

namespace app\models;

use yii\db\ActiveRecord;

class Timezone extends ActiveRecord
{
    public static function tableName() { return 'timezone'; }
}
