<?php

namespace app\models;

use Yii;
use yii\base\Model;

class AirlineRegForm extends Model
{
    // User fields
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password;

    // Airline fields
    public $airline_name;
    public $legal_name;
    public $country;
    public $support_email;
    public $support_phone;
    public $description;

    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'email', 'phone', 'password', 'airline_name', 'legal_name', 'country', 'support_email', 'support_phone', 'description'], 'required', 'message' => 'Поле обязательно'],

            // Email для входа
            ['email', 'email', 'message' => 'Введите корректный email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже зарегистрирован'],

            // Email поддержки
            ['support_email', 'email', 'message' => 'Введите корректный email поддержки'],
            ['support_email', 'string', 'max' => 255],

            // Имя и фамилия представителя
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 50,
                'tooShort' => 'Минимум 2 символа', 'tooLong' => 'Максимум 50 символов'],
            [['first_name', 'last_name'], 'match',
                'pattern' => '/^[a-zA-Zа-яёА-ЯЁ][a-zA-Zа-яёА-ЯЁ\s\-]*$/u',
                'message' => 'Только буквы, пробелы и дефис'],

            // Телефон представителя и поддержки
            [['phone', 'support_phone'], 'string', 'max' => 30],
            [['phone', 'support_phone'], 'match',
                'pattern' => '/^(\+7|7|8)[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/',
                'message' => 'Формат: +7 (999) 123-45-67'],

            // Пароль
            ['password', 'string', 'min' => 6, 'max' => 128,
                'tooShort' => 'Минимум 6 символов', 'tooLong' => 'Максимум 128 символов'],
            ['password', 'match', 'pattern' => '/[a-zA-Zа-яёА-ЯЁ]/u',
                'message' => 'Пароль должен содержать хотя бы одну букву'],
            ['password', 'match', 'pattern' => '/\d/',
                'message' => 'Пароль должен содержать хотя бы одну цифру'],

            // Название и юр. название
            [['airline_name', 'legal_name'], 'string', 'min' => 2, 'max' => 255,
                'tooShort' => 'Минимум 2 символа', 'tooLong' => 'Максимум 255 символов'],

            // Страна
            ['country', 'string', 'min' => 2, 'max' => 100,
                'tooShort' => 'Минимум 2 символа', 'tooLong' => 'Максимум 100 символов'],

            // Описание
            ['description', 'string', 'min' => 20, 'max' => 2000,
                'tooShort' => 'Минимум 20 символов', 'tooLong' => 'Максимум 2000 символов'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'first_name'    => 'Имя представителя',
            'last_name'     => 'Фамилия представителя',
            'email'         => 'Email для входа',
            'phone'         => 'Телефон представителя',
            'password'      => 'Пароль',
            'airline_name'  => 'Название авиакомпании',
            'legal_name'    => 'Юридическое название',
            'country'       => 'Страна регистрации',
            'support_email' => 'Email поддержки',
            'support_phone' => 'Телефон поддержки',
            'description'   => 'Описание авиакомпании',
        ];
    }

    public function register(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new User();
            $user->load($this->attributes, '');
            $user->role_id        = User::ROLE_AIRLINE;
            $user->user_status_id = User::STATUS_ACTIVE;
            $user->password_hash  = Yii::$app->security->generatePasswordHash($this->password);
            $user->auth_key       = Yii::$app->security->generateRandomString(32);
            if (!$user->save()) {
                $transaction->rollBack();
                return null;
            }

            $airline = new Airline();
            $airline->load($this->attributes, '');
            $airline->owner_user_id     = $user->id;
            $airline->airline_status_id = Airline::STATUS_PENDING;
            if (!$airline->save()) {
                $transaction->rollBack();
                return null;
            }

            $transaction->commit();
            return $user;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return null;
        }
    }
}
