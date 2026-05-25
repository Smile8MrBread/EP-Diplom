<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $owner_user_id
 * @property int    $airline_status_id
 * @property string $name
 * @property string $legal_name
 * @property string $country
 * @property string $support_email
 * @property string $support_phone
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class Airline extends ActiveRecord
{
    const STATUS_PENDING = 1;
    const STATUS_ACTIVE  = 2;
    const STATUS_BLOCKED = 3;

    public static function tableName(): string { return 'airline'; }

    public function rules(): array
    {
        return [
            [['owner_user_id', 'airline_status_id', 'name', 'legal_name', 'country', 'support_email', 'support_phone', 'description'], 'required'],
            [['owner_user_id', 'airline_status_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'legal_name', 'country', 'support_email', 'support_phone'], 'string', 'max' => 255],
            ['support_email', 'email'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'                => 'ID',
            'owner_user_id'     => 'Владелец',
            'airline_status_id' => 'Статус',
            'name'              => 'Название авиакомпании',
            'legal_name'        => 'Юридическое название',
            'country'           => 'Страна',
            'support_email'     => 'Email поддержки',
            'support_phone'     => 'Телефон поддержки',
            'description'       => 'Описание',
            'created_at'        => 'Дата регистрации',
        ];
    }

    public function getIsActive(): bool  { return (int)$this->airline_status_id === self::STATUS_ACTIVE; }
    public function getIsPending(): bool { return (int)$this->airline_status_id === self::STATUS_PENDING; }
    public function getIsBlocked(): bool { return (int)$this->airline_status_id === self::STATUS_BLOCKED; }

    public function getAverageRating(): float
    {
        $avg = AirlineRating::find()
            ->leftJoin('airline_review', 'airline_review.rating_id = airline_rating.id')
            ->where(['airline_rating.airline_id' => $this->id])
            ->andWhere(['or',
                ['airline_review.id' => null],
                ['airline_review.deleted_at' => null],
            ])
            ->average('airline_rating.rating_value');
        return round((float)$avg, 1);
    }

    // --- Relations ---

    public function getOwnerUser()
    {
        return $this->hasOne(User::class, ['id' => 'owner_user_id']);
    }

    public function getAirlineStatus()
    {
        return $this->hasOne(AirlineStatus::class, ['id' => 'airline_status_id']);
    }

    public function getFlights()
    {
        return $this->hasMany(Flight::class, ['airline_id' => 'id']);
    }

    public function getRatings()
    {
        return $this->hasMany(AirlineRating::class, ['airline_id' => 'id']);
    }

    public function getReviews()
    {
        return $this->hasMany(AirlineReview::class, ['rating_id' => 'id'])
            ->via('ratings')
            ->andWhere(['airline_review.deleted_at' => null]);
    }
}
