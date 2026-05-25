<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $rating_id
 * @property string $text
 * @property int    $moderated_by_user_id
 * @property string $moderated_at
 * @property string $deleted_at
 * @property string $delete_reason
 * @property string $created_at
 * @property string $updated_at
 */
class AirlineReview extends ActiveRecord
{
    public static function tableName(): string { return 'airline_review'; }

    public function rules(): array
    {
        return [
            [['rating_id', 'text'], 'required'],
            [['rating_id', 'moderated_by_user_id'], 'integer'],
            [['text'], 'string'],
            [['delete_reason'], 'string', 'max' => 255],
            [['moderated_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'                   => 'ID',
            'rating_id'            => 'Оценка',
            'text'                 => 'Текст отзыва',
            'moderated_by_user_id' => 'Модератор',
            'moderated_at'         => 'Дата модерации',
            'delete_reason'        => 'Причина удаления',
            'created_at'           => 'Дата',
        ];
    }

    public function getRating()
    {
        return $this->hasOne(AirlineRating::class, ['id' => 'rating_id']);
    }

    public function getModeratedBy()
    {
        return $this->hasOne(User::class, ['id' => 'moderated_by_user_id']);
    }
}
