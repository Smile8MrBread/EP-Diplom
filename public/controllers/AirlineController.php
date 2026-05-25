<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\Airline;
use app\models\Flight;
use app\models\FlightStatus;
use app\models\Ticket;
use app\models\Airport;
use app\models\CabinClass;
use app\models\Order;
use app\models\OrderItem;
use app\models\AirlineReview;
use yii\data\ActiveDataProvider;

class AirlineController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) return false;

        if (!Yii::$app->user->identity?->isAirline) {
            Yii::$app->session->setFlash('danger', 'Доступ только для авиакомпаний.');
            $this->redirect(Yii::$app->homeUrl);
            return false;
        }
        return true;
    }

    private function getMyAirline(): ?Airline
    {
        return Yii::$app->user->identity->airline;
    }

    public function actionIndex(): string
    {
        $airline = $this->getMyAirline();
        if (!$airline) throw new NotFoundHttpException('Авиакомпания не найдена.');

        $flights = Flight::find()
            ->where(['airline_id' => $airline->id])
            ->with(['departureAirport', 'arrivalAirport', 'flightStatus'])
            ->orderBy(['departure_time' => SORT_DESC])
            ->all();

        $reviews = AirlineReview::find()
            ->innerJoinWith('rating')
            ->where(['airline_rating.airline_id' => $airline->id])
            ->with(['rating.user'])
            ->orderBy(['airline_review.created_at' => SORT_DESC])
            ->all();

        return $this->render('index', ['airline' => $airline, 'flights' => $flights, 'reviews' => $reviews]);
    }

    public function actionCreateFlight(): \yii\web\Response|string
    {
        $airline = $this->getMyAirline();
        if (!$airline || !$airline->isActive) {
            Yii::$app->session->setFlash('danger', 'Ваша авиакомпания ещё не подтверждена администратором.');
            return $this->redirect(['/airline/index']);
        }

        $model = new Flight();
        $model->airline_id       = $airline->id;
        $model->flight_status_id = Flight::STATUS_SCHEDULED;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Рейс добавлен.');
            return $this->redirect(['/airline/flight', 'id' => $model->id]);
        }

        return $this->render('create-flight', [
            'model'    => $model,
            'airports' => Airport::getList(),
            'statuses' => FlightStatus::getList(),
        ]);
    }

    public function actionFlight(int $id): string
    {
        $airline = $this->getMyAirline();
        $flight  = Flight::find()
            ->where(['id' => $id, 'airline_id' => $airline->id])
            ->with(['departureAirport', 'arrivalAirport', 'flightStatus', 'tickets.cabinClass', 'tickets.ticketStatus'])
            ->one();
        if (!$flight) throw new NotFoundHttpException();

        return $this->render('flight', ['flight' => $flight]);
    }

    public function actionUpdateFlight(int $id): \yii\web\Response|string
    {
        $airline = $this->getMyAirline();
        $model   = Flight::findOne(['id' => $id, 'airline_id' => $airline->id]);
        if (!$model) throw new NotFoundHttpException();

        if ($model->departure_time && strtotime($model->departure_time) < time()) {
            Yii::$app->session->setFlash('danger', 'Нельзя редактировать рейс после вылета.');
            return $this->redirect(['/airline/flight', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Рейс обновлён.');
            return $this->redirect(['/airline/flight', 'id' => $model->id]);
        }

        return $this->render('create-flight', [
            'model'    => $model,
            'airports' => Airport::getList(),
            'statuses' => FlightStatus::getList(),
        ]);
    }

    public function actionCreateTicket(int $flightId): \yii\web\Response|string
    {
        $airline = $this->getMyAirline();
        $flight  = Flight::findOne(['id' => $flightId, 'airline_id' => $airline->id]);
        if (!$flight) throw new NotFoundHttpException();

        $model = new Ticket();
        $model->flight_id        = $flightId;
        $model->ticket_status_id = Ticket::STATUS_AVAILABLE;
        $model->currency_code    = 'RUB';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Билет добавлен.');
            return $this->redirect(['/airline/flight', 'id' => $flightId]);
        }

        return $this->render('create-ticket', [
            'model'        => $model,
            'flight'       => $flight,
            'cabinClasses' => CabinClass::getList(),
        ]);
    }

    public function actionUpdateTicket(int $id): \yii\web\Response|string
    {
        $airline = $this->getMyAirline();
        $model = Ticket::find()->joinWith('flight')->where(['ticket.id' => $id, 'flight.airline_id' => $airline->id])->one();
        if (!$model) throw new NotFoundHttpException();

        $model->scenario = Ticket::SCENARIO_UPDATE;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Билет обновлён.');
            return $this->redirect(['/airline/flight', 'id' => $model->flight_id]);
        }

        return $this->render('create-ticket', [
            'model'        => $model,
            'flight'       => $model->flight,
            'cabinClasses' => CabinClass::getList(),
        ]);
    }

    public function actionOrders(): string
    {
        $airline  = $this->getMyAirline();
        if (!$airline) throw new NotFoundHttpException('Авиакомпания не найдена.');

        $statusFilter = (int) Yii::$app->request->get('order_status_id', 0);
        $userFilter   = trim(Yii::$app->request->get('user', ''));

        $query = Order::find()
            ->innerJoinWith(['orderItems.ticket.flight' => function ($q) use ($airline) {
                $q->andWhere(['flight.airline_id' => $airline->id]);
            }])
            ->with(['user', 'orderStatus', 'payType', 'orderItems.ticket.flight.departureAirport', 'orderItems.ticket.flight.arrivalAirport'])
            ->orderBy(['order.id' => SORT_DESC])
            ->groupBy('order.id');

        if ($statusFilter) {
            $query->andWhere(['order.order_status_id' => $statusFilter]);
        }
        if ($userFilter !== '') {
            $query->joinWith('user', false)
                ->andWhere(['or',
                    ['like', 'order.contact_email', $userFilter],
                    ['like', 'user.email', $userFilter],
                    ['like', 'user.first_name', $userFilter],
                    ['like', 'user.last_name', $userFilter],
                ]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => false,
        ]);

        return $this->render('orders', ['dataProvider' => $provider, 'statusFilter' => $statusFilter, 'userFilter' => $userFilter]);
    }

    public function actionOrderView(int $id): string
    {
        $airline = $this->getMyAirline();

        $order = Order::find()
            ->innerJoinWith(['orderItems.ticket.flight' => function ($q) use ($airline) {
                $q->andWhere(['flight.airline_id' => $airline->id]);
            }])
            ->with(['user', 'orderStatus', 'payType', 'orderItems.ticket.flight.departureAirport', 'orderItems.ticket.flight.arrivalAirport', 'orderItems.ticket.cabinClass'])
            ->where(['order.id' => $id])
            ->one();

        if (!$order) throw new NotFoundHttpException();

        return $this->render('order-view', ['order' => $order]);
    }

    public function actionUpdateOrderStatus(int $id): \yii\web\Response
    {
        $airline  = $this->getMyAirline();
        $statusId = (int) Yii::$app->request->post('status_id');

        $order = Order::find()
            ->innerJoinWith(['orderItems.ticket.flight' => function ($q) use ($airline) {
                $q->andWhere(['flight.airline_id' => $airline->id]);
            }])
            ->where(['order.id' => $id])
            ->one();

        if (!$order) throw new NotFoundHttpException();

        $reason = trim(Yii::$app->request->post('reason', ''));
        $ref    = Yii::$app->request->post('ref', 'list');
        $back   = $ref === 'view' ? ['/airline/order-view', 'id' => $id] : ['/airline/orders'];

        if (!$order->canChangeStatus($statusId)) {
            Yii::$app->session->setFlash('danger', 'Нельзя изменить статус: недопустимый переход или рейс уже отправился.');
            return $this->redirect($back);
        }
        if ($statusId === Order::STATUS_CANCELLED && $reason === '') {
            Yii::$app->session->setFlash('danger', 'Укажите причину отмены.');
            return $this->redirect($back);
        }

        $order->order_status_id = $statusId;
        if ($reason !== '') {
            $order->comment_text = $reason;
        }
        $order->save(false);

        Yii::$app->session->setFlash('success', 'Статус заказа #' . $id . ' обновлён.');
        return $this->redirect($back);
    }

    public function actionCancelFlight(int $id): \yii\web\Response
    {
        $airline = $this->getMyAirline();
        $flight  = Flight::findOne(['id' => $id, 'airline_id' => $airline->id]);
        if (!$flight) throw new NotFoundHttpException();

        if ($flight->flight_status_id === Flight::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('danger', 'Рейс уже отменён.');
            return $this->redirect(['/airline/flight', 'id' => $id]);
        }

        if (!$flight->canCancelByRule()) {
            Yii::$app->session->setFlash('danger', 'Отмена невозможна: есть активные заказы и до вылета менее 3 месяцев.');
            return $this->redirect(['/airline/flight', 'id' => $id]);
        }

        $reason = trim(Yii::$app->request->post('reason', ''));
        if ($reason === '') {
            Yii::$app->session->setFlash('danger', 'Укажите причину отмены.');
            return $this->redirect(['/airline/flight', 'id' => $id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $flight->flight_status_id = Flight::STATUS_CANCELLED;
            $flight->save(false);

            $ticketIds = Ticket::find()->select('id')->where(['flight_id' => $id])->column();
            if ($ticketIds) {
                $orderIds = \app\models\OrderItem::find()
                    ->select('order_id')
                    ->where(['ticket_id' => $ticketIds])
                    ->column();
                if ($orderIds) {
                    Order::updateAll(
                        ['order_status_id' => Order::STATUS_CANCELLED, 'comment_text' => $reason],
                        ['id' => $orderIds, 'order_status_id' => [Order::STATUS_PENDING, Order::STATUS_PAID]]
                    );
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Рейс отменён. Все активные заказы по этому рейсу отменены.');
        } catch (\Exception) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', 'Ошибка при отмене рейса.');
        }

        return $this->redirect(['/airline/flight', 'id' => $id]);
    }

    public function actionCancelTicket(int $id): \yii\web\Response
    {
        $airline = $this->getMyAirline();
        $ticket  = Ticket::find()->joinWith('flight')->where(['ticket.id' => $id, 'flight.airline_id' => $airline->id])->one();
        if (!$ticket) throw new NotFoundHttpException();

        if ($ticket->ticket_status_id === Ticket::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('danger', 'Билет уже отменён.');
            return $this->redirect(['/airline/flight', 'id' => $ticket->flight_id]);
        }

        $reason = trim(Yii::$app->request->post('reason', ''));

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $ticket->ticket_status_id   = Ticket::STATUS_CANCELLED;
            $ticket->available_quantity = 0;
            $ticket->save(false);

            $orderIds = OrderItem::find()
                ->select('order_id')
                ->where(['ticket_id' => $id])
                ->column();
            if ($orderIds) {
                Order::updateAll(
                    ['order_status_id' => Order::STATUS_CANCELLED, 'comment_text' => $reason ?: 'Билет отменён авиакомпанией'],
                    ['id' => $orderIds, 'order_status_id' => [Order::STATUS_PENDING, Order::STATUS_PAID]]
                );
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Билет отменён. Все активные заказы по этому билету отменены.');
        } catch (\Exception) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', 'Ошибка при отмене билета.');
        }

        return $this->redirect(['/airline/flight', 'id' => $ticket->flight_id]);
    }

    public function actionProfile(): \yii\web\Response|string
    {
        $airline = $this->getMyAirline();
        if (!$airline) throw new NotFoundHttpException();

        if ($airline->load(Yii::$app->request->post()) && $airline->save()) {
            Yii::$app->session->setFlash('success', 'Данные авиакомпании обновлены.');
        }

        return $this->render('profile', ['airline' => $airline]);
    }
}
