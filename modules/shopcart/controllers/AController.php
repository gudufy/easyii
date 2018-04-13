<?php
namespace yii\easyii\modules\shopcart\controllers;

use Yii;
use yii\data\ActiveDataProvider;

use yii\easyii\components\Controller;
use yii\easyii\modules\shopcart\models\Good;
use yii\easyii\modules\shopcart\models\Order;

class AController extends Controller
{
    public $all = 0;
    public $pending = 0;
    public $processed = 0;
    public $sent = 0;
    public $completed = 0;
    public $fails = 0;
    public $refund = 0;

    public function init()
    {
        parent::init();

        $this->all = Order::find()->where(['>','user_id',0])->count();
        $this->pending = Order::find()->status(Order::STATUS_PENDING)->count();
        $this->processed = Order::find()->status(Order::STATUS_PROCESSED)->count();
        $this->sent = Order::find()->status(Order::STATUS_SENT)->count();
        $this->completed = Order::find()->status(Order::STATUS_COMPLETED)->count();
        $this->fails = Order::find()->where(['in', 'status', [Order::STATUS_DECLINED, Order::STATUS_ERROR, Order::STATUS_RETURNED]])->count();
        $this->refund = Order::find()->where(['pay'=>1,'is_refund'=>0,'status'=>Order::STATUS_DECLINED])->count();
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->where(['>','user_id',0])->desc(),
            ])
        ]);
    }

    public function actionPending()
    {
        $this->setReturnUrl();
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->status(Order::STATUS_PENDING)->asc(),
            ])
        ]);
    }

    public function actionProcessed()
    {
        $this->setReturnUrl();
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->status(Order::STATUS_PROCESSED)->asc(),
            ])
        ]);
    }

    public function actionSent()
    {
        $this->setReturnUrl();
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->status(Order::STATUS_SENT)->asc(),
            ])
        ]);
    }

    public function actionCompleted()
    {
        $this->setReturnUrl();
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->status(Order::STATUS_COMPLETED)->desc()
            ])
        ]);
    }

    public function actionFails()
    {
        $this->setReturnUrl();
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->where(['in', 'status', [Order::STATUS_DECLINED, Order::STATUS_ERROR, Order::STATUS_RETURNED]])->desc()
            ])
        ]);
    }

    public function actionRefund()
    {
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->where(['pay'=>1,'is_refund'=>0,'status'=>Order::STATUS_DECLINED])->desc(),
            ])
        ]);
    }

    public function actionBlank()
    {
        $this->setReturnUrl();
        return $this->render('index', [
            'data' => new ActiveDataProvider([
                'query' => Order::find()->with('goods')->status(Order::STATUS_BLANK)->desc()
            ])
        ]);
    }

    public function actionView($id)
    {
        $request = Yii::$app->request;
        $order = Order::findOne($id);

        if($order === null){
            $this->flash('error', Yii::t('easyii', 'Not found'));
            return $this->redirect(['/admin/'.$this->module->id]);
        }


        if($request->post('status')){
            $newStatus = $request->post('status');
            $oldStatus = $order->status;

            $order->status = $newStatus;
            $order->remark = filter_var($request->post('remark'), FILTER_SANITIZE_STRING);
            if($order->status === 4 && $order->delivery === 0){
                $order->delivery = 1;
                $order->delivery_time = time();
            }

            if($order->save()){
                if($newStatus != $oldStatus && $request->post('notify')){
                    $order->notifyUser();
                }
                $this->flash('success', Yii::t('easyii/shopcart', 'Order updated'));
            }
            else {
                $this->flash('error', Yii::t('easyii', 'Update error. {0}', $order->formatErrors()));
            }
            return $this->refresh();
        }
        else {
            if ($order->new > 0) {
                $order->new = 0;
                $order->update();
            }

            $goods = Good::find()->where(['order_id' => $order->primaryKey])->with('item')->asc()->all();

            return $this->render('view', [
                'order' => $order,
                'goods' => $goods
            ]);
        }
    }

    public function actionDelete($id)
    {
        if(($model = Order::findOne($id))){
            $model->delete();
        } else {
            $this->error = Yii::t('easyii', 'Not found');
        }
        return $this->formatResponse(Yii::t('easyii/shopcart', 'Order deleted'));
    }

    public function actionExport($type='')
    {
        $allModels = Order::find()->with('goods')->where(['>','user_id',0])->desc()->all();

        \moonland\phpexcel\Excel::export([
            'models' => $allModels, 
            'columns' => ['name',[
                    'attribute' => 'address',
                    'format' => 'text',
                    'value' => function($model) {
                        return $model->getFullRegion().$model->address;
                    },
                ],'phone:text','cost',[
                    'attribute' => 'time',
                    'format' => 'date',
                ],[
                    'attribute' => 'pay',
                    'format' => 'text',
                    'value' => function($model) {
                        return $model->pay === 1 ? '已付款' : '';
                    },
                ],[
                    'attribute' => 'status',
                    'format' => 'text',
                    'value' => function($model) {
                        if($model->status === 3 && $model->is_refund===1){
                            return '已退款';
                        }
                        else{
                            return Order::statusName($model->status);
                        }
                    },
                ],[
                    'attribute' => 'goods',
                    'format' => 'text',
                    'value' => function($model) {
                        $goodsText = '';
                        foreach($model->goods as $good){
                            $goodsText = $goodsText. ''.$good->item->title.'×'.$good->count.'；';   
                        }

                        return $goodsText;
                    },
                ]
            ], 
            'headers' => [
                'name' => Yii::t('easyii', 'Name'),
                'phone' => Yii::t('easyii', 'Phone'),
                'address' => Yii::t('easyii/shopcart', 'Address'), 
                'cost' => Yii::t('easyii/shopcart', 'Cost'),
                'time' => Yii::t('easyii', 'Date'),
                'pay' => Yii::t('easyii/shopcart', 'Pay Status'),
                'status' => Yii::t('easyii', 'Status'),
                'goods' => '订单内容'
            ],
        ]);
    }
}