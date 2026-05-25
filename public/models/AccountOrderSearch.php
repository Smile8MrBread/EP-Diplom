<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class AccountOrderSearch extends Model
{
    public ?int $order_status_id = null;

    public function rules(): array
    {
        return [
            [['order_status_id'], 'integer'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Order::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->with(['orderStatus', 'payType', 'orderItems.ticket.flight'])
            ->orderBy(['id' => SORT_DESC]);

        $this->load($params, '');

        if ($this->order_status_id) {
            $query->andWhere(['order_status_id' => $this->order_status_id]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);
    }
}
