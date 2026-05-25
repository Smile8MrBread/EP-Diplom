<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\Order;
use app\models\OrderItem;
use app\models\Ticket;
use app\models\AccountOrderSearch;
use app\models\FavoriteAirline;
use app\models\FavoriteTicket;
use app\models\PayType;
use app\models\User;

class AccountController extends Controller
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

        if (Yii::$app->user->identity?->isUser === false) {
            Yii::$app->session->setFlash('danger', 'Этот раздел только для пассажиров.');
            $this->redirect(Yii::$app->homeUrl);
            return false;
        }
        return true;
    }

    public function actionIndex(): string
    {
        $searchModel  = new AccountOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOrder(int $id): string
    {
        $order = Order::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->with([
                'orderStatus', 'payType',
                'orderItems.ticket.cabinClass',
                'orderItems.ticket.flight.airline',
                'orderItems.ticket.flight.departureAirport',
                'orderItems.ticket.flight.arrivalAirport',
            ])
            ->one();
        if (!$order) throw new NotFoundHttpException();

        return $this->render('order', ['order' => $order]);
    }

    public function actionBuy(int $ticketId): \yii\web\Response|string
    {
        $ticket = Ticket::find()
            ->where(['ticket.id' => $ticketId, 'ticket.ticket_status_id' => Ticket::STATUS_AVAILABLE])
            ->joinWith('flight')
            ->with(['flight.airline', 'flight.departureAirport', 'flight.arrivalAirport', 'cabinClass'])
            ->andWhere(['>', 'flight.departure_time', date('Y-m-d H:i:s')])
            ->one();
        if (!$ticket || $ticket->available_quantity < 1) {
            Yii::$app->session->setFlash('danger', 'Билет недоступен для покупки.');
            return $this->redirect(['/catalog/index']);
        }

        $payTypes = PayType::getList();

        if (Yii::$app->request->isPost) {
            $qty      = (int)Yii::$app->request->post('quantity', 1);
            $payType  = (int)Yii::$app->request->post('pay_type_id');
            $email    = Yii::$app->request->post('contact_email', Yii::$app->user->identity->email);
            $phone    = Yii::$app->request->post('contact_phone', Yii::$app->user->identity->phone);
            $comment  = Yii::$app->request->post('comment_text', '');

            $qty = max(1, min($qty, $ticket->available_quantity));

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $order = new Order();
                $order->user_id         = Yii::$app->user->id;
                $order->order_status_id = Order::STATUS_PENDING;
                $order->total_amount    = $ticket->price * $qty;
                $order->currency_code   = $ticket->currency_code;
                $order->contact_email   = $email;
                $order->contact_phone   = $phone;
                $order->pay_type_id     = $payType;
                $order->comment_text    = $comment;

                if (!$order->save()) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('danger', 'Ошибка создания заказа.');
                    return $this->refresh();
                }

                $item              = new OrderItem();
                $item->order_id    = $order->id;
                $item->ticket_id   = $ticket->id;
                $item->quantity    = $qty;
                $item->unit_price  = $ticket->price;
                $item->line_total  = $ticket->price * $qty;
                if (!$item->save()) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('danger', 'Ошибка создания заказа.');
                    return $this->refresh();
                }

                $ticket->available_quantity -= $qty;
                if ($ticket->available_quantity === 0) {
                    $ticket->ticket_status_id = Ticket::STATUS_SOLD_OUT;
                }
                if (!$ticket->save(false)) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('danger', 'Ошибка при обновлении билета.');
                    return $this->refresh();
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Заказ #' . $order->id . ' успешно оформлен!');
                return $this->redirect(['/account/order', 'id' => $order->id]);
            } catch (\Exception) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('danger', 'Произошла ошибка. Попробуйте снова.');
            }
        }

        return $this->render('buy', ['ticket' => $ticket, 'payTypes' => $payTypes]);
    }

    public function actionCancel(int $id): \yii\web\Response
    {
        $order = Order::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->with('orderItems.ticket')
            ->one();
        if (!$order) throw new NotFoundHttpException();

        if ($order->order_status_id === Order::STATUS_PENDING) {
            $reason      = trim(Yii::$app->request->post('reason', ''));
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $order->order_status_id = Order::STATUS_CANCELLED;
                if ($reason !== '') {
                    $order->comment_text = $reason;
                }
                if (!$order->save(false)) {
                    throw new \RuntimeException();
                }

                foreach ($order->orderItems as $item) {
                    $ticket = $item->ticket;
                    $ticket->available_quantity += $item->quantity;
                    if ($ticket->ticket_status_id === Ticket::STATUS_SOLD_OUT) {
                        $ticket->ticket_status_id = Ticket::STATUS_AVAILABLE;
                    }
                    if (!$ticket->save(false)) {
                        throw new \RuntimeException();
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Заказ отменён.');
            } catch (\Exception) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('danger', 'Ошибка при отмене заказа.');
            }
        }

        return $this->redirect(['/account/index']);
    }

    public function actionFavorites(): string
    {
        $uid = Yii::$app->user->id;

        $airlinesProvider = new \yii\data\ActiveDataProvider([
            'query' => FavoriteAirline::find()->where(['user_id' => $uid])->with('airline'),
            'pagination' => ['pageSize' => 5, 'pageParam' => 'pageA'],
            'sort' => false,
        ]);
        $ticketsProvider = new \yii\data\ActiveDataProvider([
            'query' => FavoriteTicket::find()->where(['user_id' => $uid])->with([
                'ticket.flight.airline',
                'ticket.flight.departureAirport',
                'ticket.flight.arrivalAirport',
            ]),
            'pagination' => ['pageSize' => 5, 'pageParam' => 'pageT'],
            'sort' => false,
        ]);

        return $this->render('favorites', [
            'airlinesProvider' => $airlinesProvider,
            'ticketsProvider'  => $ticketsProvider,
        ]);
    }

    public function actionProfile(): \yii\web\Response|string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if (Yii::$app->request->isPost) {
            $user->first_name = Yii::$app->request->post('first_name', $user->first_name);
            $user->last_name  = Yii::$app->request->post('last_name', $user->last_name);
            $user->phone      = Yii::$app->request->post('phone', $user->phone);
            $user->birth_date = Yii::$app->request->post('birth_date') ?: null;
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Профиль обновлён.');
            }
        }
        return $this->render('profile', ['user' => $user]);
    }
}
