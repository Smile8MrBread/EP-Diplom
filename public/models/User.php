<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int    $id
 * @property int    $role_id
 * @property int    $user_status_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $password_hash
 * @property string $auth_key
 * @property string $birth_date
 */
class User extends ActiveRecord implements IdentityInterface
{
    const ROLE_ADMIN   = 1;
    const ROLE_AIRLINE = 2;
    const ROLE_USER    = 3;

    const STATUS_ACTIVE  = 1;
    const STATUS_BLOCKED = 2;

    public static function tableName(): string { return 'user'; }

    public function rules(): array
    {
        return [
            [['role_id', 'user_status_id', 'first_name', 'last_name', 'email', 'phone', 'password_hash'], 'required'],
            [['role_id', 'user_status_id'], 'integer'],
            [['first_name', 'last_name', 'email', 'phone', 'password_hash', 'auth_key'], 'string', 'max' => 255],
            ['birth_date', 'safe'],
            ['email', 'email'],
            ['email', 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'             => 'ID',
            'role_id'        => 'Роль',
            'user_status_id' => 'Статус',
            'first_name'     => 'Имя',
            'last_name'      => 'Фамилия',
            'email'          => 'Email',
            'phone'          => 'Телефон',
            'password_hash'  => 'Пароль',
            'birth_date'     => 'Дата рождения',
        ];
    }

    // --- IdentityInterface ---

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    // --- Helpers ---

    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email]);
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // --- Role checks ---

    public function getIsAdmin(): bool    { return (int)$this->role_id === self::ROLE_ADMIN; }
    public function getIsAirline(): bool  { return (int)$this->role_id === self::ROLE_AIRLINE; }
    public function getIsUser(): bool     { return (int)$this->role_id === self::ROLE_USER; }
    public function getIsBlocked(): bool  { return (int)$this->user_status_id === self::STATUS_BLOCKED; }

    // --- Relations ---

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    public function getUserStatus()
    {
        return $this->hasOne(UserStatus::class, ['id' => 'user_status_id']);
    }

    public function getAirline()
    {
        return $this->hasOne(Airline::class, ['owner_user_id' => 'id']);
    }

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    public function getFavoriteAirlines()
    {
        return $this->hasMany(FavoriteAirline::class, ['user_id' => 'id']);
    }

    public function getFavoriteTickets()
    {
        return $this->hasMany(FavoriteTicket::class, ['user_id' => 'id']);
    }
}
