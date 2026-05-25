<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\Airline;
use app\models\Flight;
use app\models\Ticket;
use app\models\CatalogSearch;
use app\models\FavoriteAirline;
use app\models\FavoriteTicket;
use app\models\AirlineRating;
use app\models\AirlineReview;

class CatalogController extends Controller
{
    public function actionIndex(): string
    {
        $searchModel = new CatalogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $airlines = Airline::find()
            ->where(['airline_status_id' => Airline::STATUS_ACTIVE])
            ->limit(6)
            ->all();

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'airlines'     => $airlines,
        ]);
    }

    public function actionAirline(int $id): string
    {
        $airline = Airline::find()
            ->where(['id' => $id, 'airline_status_id' => Airline::STATUS_ACTIVE])
            ->with([
                'flights.departureAirport',
                'flights.arrivalAirport',
                'flights.flightStatus',
                'flights.tickets.cabinClass',
            ])
            ->one();

        if (!$airline) {
            throw new NotFoundHttpException('Авиакомпания не найдена.');
        }

        $isFavorite = false;
        $userRating = null;
        if (!Yii::$app->user->isGuest) {
            $uid = Yii::$app->user->id;
            $isFavorite = FavoriteAirline::find()->where(['user_id' => $uid, 'airline_id' => $id])->exists();
            $userRating = AirlineRating::findOne(['user_id' => $uid, 'airline_id' => $id]);
        }

        return $this->render('airline', [
            'airline'    => $airline,
            'isFavorite' => $isFavorite,
            'userRating' => $userRating,
        ]);
    }

    public function actionFlight(int $id): string
    {
        $flight = Flight::find()
            ->where(['flight.id' => $id])
            ->joinWith(['airline', 'departureAirport', 'arrivalAirport'])
            ->andWhere(['!=', 'flight.flight_status_id', Flight::STATUS_CANCELLED])
            ->andWhere(['airline.airline_status_id' => Airline::STATUS_ACTIVE])
            ->andWhere(['>', 'flight.departure_time', date('Y-m-d H:i:s')])
            ->with(['flightStatus', 'tickets' => function ($q) {
                $q->where(['ticket_status_id' => Ticket::STATUS_AVAILABLE])
                  ->andWhere(['>', 'available_quantity', 0])
                  ->with('cabinClass');
            }])
            ->one();

        if (!$flight) {
            throw new NotFoundHttpException('Рейс не найден.');
        }

        $favoriteIds = [];
        if (!Yii::$app->user->isGuest) {
            $ticketIds = array_column($flight->tickets, 'id');
            if ($ticketIds) {
                $favoriteIds = FavoriteTicket::find()
                    ->select('ticket_id')
                    ->where(['user_id' => Yii::$app->user->id, 'ticket_id' => $ticketIds])
                    ->column();
            }
        }

        return $this->render('flight', ['flight' => $flight, 'favoriteIds' => $favoriteIds]);
    }

    public function actionTicket(int $id): string
    {
        $ticket = Ticket::find()
            ->where(['ticket.id' => $id, 'ticket.ticket_status_id' => Ticket::STATUS_AVAILABLE])
            ->joinWith('flight')
            ->andWhere(['>', 'flight.departure_time', date('Y-m-d H:i:s')])
            ->andWhere(['!=', 'flight.flight_status_id', \app\models\Flight::STATUS_CANCELLED])
            ->with(['flight.departureAirport', 'flight.arrivalAirport', 'flight.airline', 'cabinClass'])
            ->one();
        if (!$ticket) {
            throw new NotFoundHttpException('Билет не найден.');
        }

        $isFavorite = false;
        if (!Yii::$app->user->isGuest) {
            $isFavorite = FavoriteTicket::find()->where(['user_id' => Yii::$app->user->id, 'ticket_id' => $id])->exists();
        }

        return $this->render('ticket', [
            'ticket'     => $ticket,
            'isFavorite' => $isFavorite,
        ]);
    }

    public function actionToggleFavoriteAirline(int $id): Response|array
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Войдите в аккаунт'];
        }

        $uid = Yii::$app->user->id;
        $fav = FavoriteAirline::findOne(['user_id' => $uid, 'airline_id' => $id]);
        if ($fav) {
            $fav->delete();
            return ['success' => true, 'active' => false];
        }

        $fav = new FavoriteAirline();
        $fav->user_id    = $uid;
        $fav->airline_id = $id;
        $fav->save();
        return ['success' => true, 'active' => true];
    }

    public function actionToggleFavoriteTicket(int $id): Response|array
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Войдите в аккаунт'];
        }

        $uid = Yii::$app->user->id;
        $fav = FavoriteTicket::findOne(['user_id' => $uid, 'ticket_id' => $id]);
        if ($fav) {
            $fav->delete();
            return ['success' => true, 'active' => false];
        }

        $fav = new FavoriteTicket();
        $fav->user_id   = $uid;
        $fav->ticket_id = $id;
        $fav->save();
        return ['success' => true, 'active' => true];
    }

    public function actionRate(int $airlineId): array
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->user->identity?->isUser) {
            return ['success' => false];
        }

        $value = (int)Yii::$app->request->post('value', 0);
        if ($value < 1 || $value > 5) {
            return ['success' => false];
        }

        $uid    = Yii::$app->user->id;
        $rating = AirlineRating::findOne(['user_id' => $uid, 'airline_id' => $airlineId]);
        if (!$rating) {
            $rating              = new AirlineRating();
            $rating->user_id    = $uid;
            $rating->airline_id = $airlineId;
        }
        $rating->rating_value = $value;
        $rating->save();

        $airline = Airline::findOne($airlineId);
        $avg = $airline ? $airline->averageRating : 0;
        return ['success' => true, 'avg' => $avg];
    }

    public function actionReview(int $airlineId): \yii\web\Response
    {
        if (!Yii::$app->user->identity?->isUser) {
            return $this->redirect(['/site/login']);
        }

        $uid    = Yii::$app->user->id;
        $rating = AirlineRating::findOne(['user_id' => $uid, 'airline_id' => $airlineId]);
        if (!$rating) {
            Yii::$app->session->setFlash('danger', 'Сначала поставьте оценку авиакомпании.');
            return $this->redirect(['/catalog/airline', 'id' => $airlineId]);
        }

        $text = Yii::$app->request->post('text', '');
        if (trim($text)) {
            $review = AirlineReview::findOne(['rating_id' => $rating->id]);
            if ($review && $review->deleted_at) {
                Yii::$app->session->setFlash('danger', 'Ваш отзыв был удалён администратором и не может быть изменён.');
                return $this->redirect(['/catalog/airline', 'id' => $airlineId]);
            }
            if (!$review) {
                $review            = new AirlineReview();
                $review->rating_id = $rating->id;
            }
            $review->text = trim($text);
            $review->save();
        }

        return $this->redirect(['/catalog/airline', 'id' => $airlineId]);
    }
}
