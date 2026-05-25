<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Airline;
use app\models\Flight;
use app\models\Ticket;
use app\models\Order;
use app\models\AirlineReview;
use app\models\OrderItem;

class AdminController extends Controller
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

        if (!Yii::$app->user->identity?->isAdmin) {
            throw new \yii\web\ForbiddenHttpException('Доступ запрещён.');
        }
        return true;
    }

    public function actionIndex(): string
    {
        $stats = [
            'users'    => User::find()->where(['role_id' => User::ROLE_USER])->count(),
            'airlines' => Airline::find()->count(),
            'pending'  => Airline::find()->where(['airline_status_id' => Airline::STATUS_PENDING])->count(),
            'orders'   => Order::find()->count(),
        ];

        return $this->render('index', ['stats' => $stats]);
    }

    // --- Users ---

    public function actionUsers(): string
    {
        $search       = trim(Yii::$app->request->get('search', ''));
        $statusFilter = Yii::$app->request->get('status', '');

        $query = User::find()->where(['role_id' => User::ROLE_USER])->orderBy(['id' => SORT_DESC]);

        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'email', $search],
                ['like', 'first_name', $search],
                ['like', 'last_name', $search],
                ['like', 'phone', $search],
            ]);
        }
        if ($statusFilter === 'active') {
            $query->andWhere(['user_status_id' => User::STATUS_ACTIVE]);
        } elseif ($statusFilter === 'blocked') {
            $query->andWhere(['user_status_id' => User::STATUS_BLOCKED]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('users', ['dataProvider' => $provider, 'search' => $search, 'statusFilter' => $statusFilter]);
    }

    public function actionUserView(int $id): string
    {
        $user = User::findOne($id);
        if (!$user) throw new NotFoundHttpException();

        return $this->render('user-view', ['user' => $user]);
    }

    public function actionUserBlock(int $id): \yii\web\Response
    {
        $user = User::findOne($id);
        if (!$user) throw new NotFoundHttpException();

        $user->user_status_id = ($user->user_status_id === User::STATUS_ACTIVE)
            ? User::STATUS_BLOCKED
            : User::STATUS_ACTIVE;
        $user->save();

        $msg = $user->user_status_id === User::STATUS_BLOCKED ? 'заблокирован' : 'разблокирован';
        Yii::$app->session->setFlash('success', "Пользователь {$msg}.");

        return $this->redirect(['/admin/users']);
    }

    // --- Airlines ---

    public function actionAirlines(): string
    {
        $statusFilter = Yii::$app->request->get('status', '');
        $search       = trim(Yii::$app->request->get('search', ''));

        $query = Airline::find()->with('ownerUser')->orderBy(['id' => SORT_DESC]);

        if ($statusFilter === 'pending') {
            $query->andWhere(['airline_status_id' => Airline::STATUS_PENDING]);
        } elseif ($statusFilter === 'active') {
            $query->andWhere(['airline_status_id' => Airline::STATUS_ACTIVE]);
        } elseif ($statusFilter === 'blocked') {
            $query->andWhere(['airline_status_id' => Airline::STATUS_BLOCKED]);
        }
        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'name', $search],
                ['like', 'support_email', $search],
            ]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('airlines', ['dataProvider' => $provider, 'statusFilter' => $statusFilter, 'search' => $search]);
    }

    public function actionAirlineView(int $id): string
    {
        $airline = Airline::find()
            ->where(['id' => $id])
            ->with(['flights.departureAirport', 'flights.arrivalAirport', 'flights.flightStatus'])
            ->one();
        if (!$airline) throw new NotFoundHttpException();

        $reviews = AirlineReview::find()
            ->innerJoinWith('rating')
            ->where(['airline_rating.airline_id' => $id])
            ->with(['rating.user', 'moderatedBy'])
            ->orderBy(['airline_review.created_at' => SORT_DESC])
            ->all();

        return $this->render('airline-view', ['airline' => $airline, 'reviews' => $reviews]);
    }

    public function actionAirlineApprove(int $id): \yii\web\Response
    {
        $airline = Airline::findOne($id);
        if (!$airline) throw new NotFoundHttpException();

        $airline->airline_status_id = Airline::STATUS_ACTIVE;
        $airline->save();
        Yii::$app->session->setFlash('success', 'Авиакомпания подтверждена.');

        return $this->redirect(['/admin/airlines']);
    }

    public function actionAirlineBlock(int $id): \yii\web\Response
    {
        $airline = Airline::findOne($id);
        if (!$airline) throw new NotFoundHttpException();

        $wasActive = $airline->airline_status_id === Airline::STATUS_ACTIVE;
        $airline->airline_status_id = $wasActive ? Airline::STATUS_BLOCKED : Airline::STATUS_ACTIVE;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $airline->save(false);

            if ($wasActive) {
                $flightIds = Flight::find()
                    ->select('id')
                    ->where(['airline_id' => $airline->id])
                    ->andWhere(['!=', 'flight_status_id', Flight::STATUS_CANCELLED])
                    ->andWhere(['>', 'departure_time', date('Y-m-d H:i:s')])
                    ->column();

                if ($flightIds) {
                    Flight::updateAll(['flight_status_id' => Flight::STATUS_CANCELLED], ['id' => $flightIds]);

                    $ticketIds = Ticket::find()->select('id')->where(['flight_id' => $flightIds])->column();
                    if ($ticketIds) {
                        $orderIds = OrderItem::find()->select('order_id')->where(['ticket_id' => $ticketIds])->column();
                        if ($orderIds) {
                            Order::updateAll(
                                ['order_status_id' => Order::STATUS_CANCELLED, 'comment_text' => 'Авиакомпания заблокирована'],
                                ['id' => $orderIds, 'order_status_id' => [Order::STATUS_PENDING, Order::STATUS_PAID]]
                            );
                        }
                    }
                }
            }

            $transaction->commit();
        } catch (\Exception) {
            $transaction->rollBack();
        }

        $msg = $airline->airline_status_id === Airline::STATUS_BLOCKED ? 'заблокирована' : 'разблокирована';
        Yii::$app->session->setFlash('success', "Авиакомпания {$msg}.");

        return $this->redirect(['/admin/airlines']);
    }

    // --- Reviews ---

    public function actionReviews(): string
    {
        $provider = new ActiveDataProvider([
            'query' => AirlineReview::find()->where(['deleted_at' => null])->with(['rating.user', 'rating.airline'])->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('reviews', ['dataProvider' => $provider]);
    }

    public function actionDeleteReview(int $id): \yii\web\Response
    {
        $review = AirlineReview::findOne($id);
        if (!$review) throw new NotFoundHttpException();

        $review->delete_reason        = Yii::$app->request->post('reason', '');
        $review->moderated_by_user_id = Yii::$app->user->id;
        $now = date('Y-m-d H:i:s');
        $review->moderated_at         = $now;
        $review->deleted_at           = $now;
        $review->save(false);

        Yii::$app->session->setFlash('success', 'Отзыв удалён.');
        $ref = Yii::$app->request->post('ref', '');
        if ($ref === 'airline') {
            $airlineId = Yii::$app->request->post('airline_id', 0);
            return $this->redirect(['/catalog/airline', 'id' => $airlineId]);
        }
        return $this->redirect(['/admin/reviews']);
    }

    // --- Orders ---

    public function actionOrders(): string
    {
        $statusFilter  = (int) Yii::$app->request->get('order_status_id', 0);
        $userFilter    = trim(Yii::$app->request->get('user', ''));
        $airlineFilter = (int) Yii::$app->request->get('airline_id', 0);

        $query = Order::find()
            ->with(['user', 'orderStatus', 'payType',
                'orderItems.ticket.flight.departureAirport',
                'orderItems.ticket.flight.arrivalAirport',
                'orderItems.ticket.flight.airline',
            ])
            ->orderBy(['order.id' => SORT_DESC]);

        if ($statusFilter) {
            $query->andWhere(['order.order_status_id' => $statusFilter]);
        }
        if ($userFilter !== '') {
            $query->joinWith('user')
                ->andWhere(['or',
                    ['like', 'order.contact_email', $userFilter],
                    ['like', 'user.email', $userFilter],
                    ['like', 'user.first_name', $userFilter],
                    ['like', 'user.last_name', $userFilter],
                ]);
        }
        if ($airlineFilter) {
            $sub = (new \yii\db\Query())
                ->select('oi.order_id')
                ->from('order_item oi')
                ->innerJoin('ticket t', 't.id = oi.ticket_id')
                ->innerJoin('flight f', 'f.id = t.flight_id')
                ->where(['f.airline_id' => $airlineFilter]);
            $query->andWhere(['order.id' => $sub]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $airlines = Airline::find()->orderBy(['name' => SORT_ASC])->all();

        return $this->render('orders', [
            'dataProvider'  => $provider,
            'statusFilter'  => $statusFilter,
            'userFilter'    => $userFilter,
            'airlineFilter' => $airlineFilter,
            'airlines'      => $airlines,
        ]);
    }

    public function actionUpdateOrderStatus(int $id): \yii\web\Response
    {
        $order = Order::findOne($id);
        if (!$order) throw new NotFoundHttpException();

        $statusId = (int) Yii::$app->request->post('status_id');
        $reason   = trim(Yii::$app->request->post('reason', ''));
        $ref      = Yii::$app->request->post('ref', 'list');
        $back     = $ref === 'view' ? ['/admin/order-view', 'id' => $id] : ['/admin/orders'];

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

    public function actionOrderView(int $id): string
    {
        $order = Order::find()
            ->with(['user', 'orderStatus', 'payType', 'orderItems.ticket.flight.departureAirport', 'orderItems.ticket.flight.arrivalAirport', 'orderItems.ticket.cabinClass', 'orderItems.ticket.flight.airline'])
            ->where(['id' => $id])
            ->one();
        if (!$order) throw new NotFoundHttpException();

        return $this->render('order-view', ['order' => $order]);
    }

    // --- Flights / Tickets ---

    public function actionCancelFlight(int $id): \yii\web\Response
    {
        $flight = Flight::findOne($id);
        if (!$flight) throw new NotFoundHttpException();

        if ($flight->flight_status_id === Flight::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('danger', 'Рейс уже отменён.');
            return $this->redirect(['/catalog/index']);
        }

        if (!$flight->canCancelByRule()) {
            Yii::$app->session->setFlash('danger', 'Отмена невозможна: есть активные заказы и до вылета менее 3 месяцев.');
            return $this->redirect(['/catalog/index']);
        }

        $reason = trim(Yii::$app->request->post('reason', ''));
        if ($reason === '') {
            Yii::$app->session->setFlash('danger', 'Укажите причину отмены.');
            return $this->redirect(['/catalog/index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $flight->flight_status_id = Flight::STATUS_CANCELLED;
            $flight->save(false);

            $ticketIds = Ticket::find()->select('id')->where(['flight_id' => $id])->column();
            if ($ticketIds) {
                $orderIds = OrderItem::find()->select('order_id')->where(['ticket_id' => $ticketIds])->column();
                if ($orderIds) {
                    Order::updateAll(
                        ['order_status_id' => Order::STATUS_CANCELLED, 'comment_text' => $reason],
                        ['id' => $orderIds, 'order_status_id' => [Order::STATUS_PENDING, Order::STATUS_PAID]]
                    );
                }
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Рейс отменён.');
        } catch (\Exception) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', 'Ошибка при отмене рейса.');
        }

        return $this->redirect(['/catalog/index']);
    }

    public function actionFlight(int $id): string
    {
        $flight = Flight::find()
            ->where(['id' => $id])
            ->with(['departureAirport', 'arrivalAirport', 'flightStatus', 'airline', 'tickets.cabinClass', 'tickets.ticketStatus'])
            ->one();
        if (!$flight) throw new NotFoundHttpException();

        return $this->render('flight', ['flight' => $flight]);
    }

    public function actionCancelTicket(int $id): \yii\web\Response
    {
        $ticket = Ticket::findOne($id);
        if (!$ticket) throw new NotFoundHttpException();

        if ($ticket->ticket_status_id === Ticket::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('danger', 'Билет уже отменён.');
            return $this->redirect(['/admin/flight', 'id' => $ticket->flight_id]);
        }

        if (strtotime($ticket->flight->departure_time) <= time() + 7 * 24 * 3600) {
            Yii::$app->session->setFlash('danger', 'Отмена билета невозможна менее чем за 7 дней до вылета.');
            return $this->redirect(['/admin/flight', 'id' => $ticket->flight_id]);
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
                    ['order_status_id' => Order::STATUS_CANCELLED, 'comment_text' => $reason ?: 'Билет отменён администратором'],
                    ['id' => $orderIds, 'order_status_id' => [Order::STATUS_PENDING, Order::STATUS_PAID]]
                );
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Билет отменён. Все активные заказы по этому билету отменены.');
        } catch (\Exception) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', 'Ошибка при отмене билета.');
        }

        return $this->redirect(['/admin/flight', 'id' => $ticket->flight_id]);
    }
}
