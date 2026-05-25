<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RegForm extends Model
{
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password;
    public $birth_date;

    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'email', 'phone', 'password'], 'required', 'message' => 'Поле обязательно'],

            // Email
            ['email', 'email', 'message' => 'Введите корректный email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже зарегистрирован'],

            // Имя и фамилия
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 50,
                'tooShort' => 'Минимум 2 символа', 'tooLong' => 'Максимум 50 символов'],
            [['first_name', 'last_name'], 'match',
                'pattern' => '/^[a-zA-Zа-яёА-ЯЁ][a-zA-Zа-яёА-ЯЁ\s\-]*$/u',
                'message' => 'Только буквы, пробелы и дефис'],

            // Телефон
            ['phone', 'string', 'max' => 30],
            ['phone', 'match',
                'pattern' => '/^(\+7|7|8)[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/',
                'message' => 'Формат: +7 (999) 123-45-67'],

            // Пароль
            ['password', 'string', 'min' => 6, 'max' => 128,
                'tooShort' => 'Минимум 6 символов', 'tooLong' => 'Максимум 128 символов'],
            ['password', 'match', 'pattern' => '/[a-zA-Zа-яёА-ЯЁ]/u',
                'message' => 'Пароль должен содержать хотя бы одну букву'],
            ['password', 'match', 'pattern' => '/\d/',
                'message' => 'Пароль должен содержать хотя бы одну цифру'],

            // Дата рождения
            ['birth_date', 'date', 'format' => 'php:Y-m-d', 'message' => 'Введите корректную дату'],
            ['birth_date', 'validateAge'],
        ];
    }

    public function validateAge(string $attribute): void
    {
        if (!$this->$attribute) return;
        $age = (new \DateTime($this->$attribute))->diff(new \DateTime())->y;
        if ($age < 14) {
            $this->addError($attribute, 'Возраст должен быть не менее 14 лет');
        } elseif ($age > 120) {
            $this->addError($attribute, 'Введите корректную дату рождения');
        }
    }

    public function attributeLabels(): array
    {
        return [
            'first_name' => 'Имя',
            'last_name'  => 'Фамилия',
            'email'      => 'Email',
            'phone'      => 'Телефон',
            'password'   => 'Пароль',
            'birth_date' => 'Дата рождения',
        ];
    }

    public function register(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->load($this->attributes, "");
        $user->role_id        = User::ROLE_USER;
        $user->user_status_id = User::STATUS_ACTIVE;
        $user->password_hash  = Yii::$app->security->generatePasswordHash($this->password);
        $user->auth_key       = Yii::$app->security->generateRandomString(32);

        return $user->save() ? $user : null;
    }
}
