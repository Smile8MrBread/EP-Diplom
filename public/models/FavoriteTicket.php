<?php

namespace app\models;

use yii\db\ActiveRecord;

class FavoriteTicket extends ActiveRecord
{
    public static function tableName(): string { return 'favorite_ticket'; }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }
}
