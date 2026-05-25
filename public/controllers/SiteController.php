<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;
use app\models\RegForm;
use app\models\AirlineRegForm;
use app\models\Airport;
use app\models\Airline;

class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['logout'],
                'rules' => [
                    ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => ['logout' => ['post']],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'airports' => Airport::getList(),
            'airlines' => Airline::find()
                ->where(['airline_status_id' => Airline::STATUS_ACTIVE])
                ->orderBy(['name' => SORT_ASC])
                ->all(),
        ]);
    }

    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            if ($user->isAdmin) {
                return $this->redirect(['/admin/index']);
            } elseif ($user->isAirline) {
                return $this->redirect(['/airline/index']);
            }
            return $this->redirect(['/account/index']);
        }

        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionReg(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new RegForm();
        if ($model->load(Yii::$app->request->post())) {
            $user = $model->register();
            if ($user && Yii::$app->user->login($user, 3600 * 24 * 30)) {
                Yii::$app->session->setFlash('success', 'Добро пожаловать в AirBook!');
                return $this->redirect(['/account/index']);
            }
        }

        return $this->render('reg', ['model' => $model]);
    }

    public function actionRegAirline(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new AirlineRegForm();
        if ($model->load(Yii::$app->request->post())) {
            $user = $model->register();
            if ($user && Yii::$app->user->login($user, 3600 * 24 * 30)) {
                Yii::$app->session->setFlash('info', 'Заявка отправлена. Ваш аккаунт будет активирован после проверки администратором.');
                return $this->redirect(['/airline/index']);
            }
        }

        return $this->render('reg-airline', ['model' => $model]);
    }
}
