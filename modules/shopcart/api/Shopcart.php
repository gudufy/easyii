<?php
namespace yii\easyii\modules\shopcart\api;

use Yii;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\shopcart\models\Good;
use yii\easyii\modules\shopcart\models\Order;
use yii\easyii\models\UserAddress;
use yii\easyii\helpers\Utils;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\widgets\LinkPager;
use app\modules\community\api\Community;


/**
 * Shopcart module API
 * @package yii\easyii\modules\shopcart\api
 *
 * @method static GoodObject goods() Get list of added to shopcart goods as GoodObject objects.
 * @method static OrderObject order(int $id) Get an order by id as OrderObject
 * @method static string form(array $options = []) Returns fully worked standalone html form to complete order.
 * @method static array add(int $item_id, int $count = 1, string $options = '', boolean $increaseOnDuplicate = true) Adds item to shopcart, returns GoodObject attributes
 * @method static array remove(int $good_id) Removes good to shopcart
 * @method static void update(array $goods) Update shopcart. Array format [$good_id => $new_count]
 * @method static array send(array $goods) Completes users shopcart order and send to admin.
 * @method static array cost() returns total cost of current shopcart.
 */

class Shopcart extends \yii\easyii\components\API
{
    const SENT_VAR = 'shopcart_sent';

    const ERROR_ITEM_NOT_FOUND = 1;
    const ERROR_CREATE_ORDER = 2;
    const ERROR_GOOD_DUPLICATE = 3;
    const ERROR_CREATE_GOOD = 4;
    const ERROR_ORDER_NOT_FOUND = 5;
    const ERROR_ORDER_EMPTY = 6;
    const ERROR_ORDER_UPDATE = 7;

    private $_order;
    private $_items;
    private $_adp;

    private $_defaultFormOptions = [
        'errorUrl' => '',
        'successUrl' => ''
    ];

    public function api_orders($options = []){
        if(!$this->_items){
            $this->_items = [];

            $query = Order::find()->with(['goods'])->desc();

            if(!empty($options['where'])){
                foreach ($options['where'] as $where) {
                    $query->andFilterWhere($where);
                }
            }

            if(!empty($options['orderBy'])){
                $query->orderBy($options['orderBy']);
            } else {
                $query->sortDate();
            }

            $this->_adp = new ActiveDataProvider([
                'query' => $query,
                'pagination' => !empty($options['pagination']) ? $options['pagination'] : []
            ]);

            foreach($this->_adp->models as $model){
                $this->_items[] = new OrderObject($model);
            }
        }
        return $this->_items;
    }

    public function api_pagination()
    {
        return $this->_adp ? $this->_adp->pagination : null;
    }

    public function api_pages($options = []){
        return $this->_adp ? LinkPager::widget(array_merge($options, ['pagination' => $this->_adp->pagination])) : '';
    }

    public function api_goods()
    {
        return $this->order->goods;
    }

    public function api_order($id)
    {
        $order = Order::findOne($id);
        return $order ? new OrderObject($order) : null;
    }

    public function api_orderByToken($token)
    {
        $order = Order::find()->where(['access_token'=>$token])->one();
        return $order ? new OrderObject($order) : null;
    }

    public function api_form($options = [])
    {
        $model = new Order;
        $model->scenario = 'confirm';
        $settings = Yii::$app->getModule('admin')->activeModules['shopcart']->settings;
        $options = array_merge($this->_defaultFormOptions, $options);

        ob_start();
        $form = ActiveForm::begin([
            'action' => Url::to(['/admin/shopcart/send'])
        ]);

        echo Html::hiddenInput('errorUrl', $options['errorUrl'] ? $options['errorUrl'] : Url::current([self::SENT_VAR => 0]));
        echo Html::hiddenInput('successUrl', $options['successUrl'] ? $options['successUrl'] : Url::current([self::SENT_VAR => 1]));

        echo $form->field($model, 'name');
        echo $form->field($model, 'address');

        if($settings['enableEmail']) echo $form->field($model, 'email');
        if($settings['enablePhone']) echo $form->field($model, 'phone');

        echo $form->field($model, 'comment')->textarea();

        echo Html::submitButton(Yii::t('easyii', 'Send'), ['class' => 'btn btn-primary']);
        ActiveForm::end();

        return ob_get_clean();
    }

    public function api_add($item_id, $count = 1, $options = '', $increaseOnDuplicate = true)
    {
        $item = Item::findOne($item_id);
        if(!$item){
            return ['result' => 'error', 'code' => self::ERROR_ITEM_NOT_FOUND, 'error' => 'Item no found'];
        }

        if(!$this->order->id){
            if(!$this->order->model->save()){
                return ['result' => 'error', 'code' => self::ERROR_CREATE_ORDER, 'error' => 'Cannot create order. '.$this->order->formatErrors()];
            }
            Yii::$app->session->set(Order::SESSION_KEY, $this->order->model->access_token);
        }

        $good = Good::findOne([
            'order_id' => $this->order->id,
            'item_id' => $item->primaryKey,
            'options' => $options
        ]);

        if($good && !$increaseOnDuplicate){
            return ['result' => 'error', 'code' => self::ERROR_GOOD_DUPLICATE, 'error' => 'Dublicate good in order.'];
        }

        if($good) {
            $good->count += $count;
        } else {
            $good = new Good([
                'order_id' => $this->order->id,
                'item_id' => $item->primaryKey,
                'count' => (int)$count,
                'options' => $options,
                'discount' => $item->discount,
                'price' => $item->price
            ]);
        }

        if($good->save()){
            $response = [
                'result' => 'success',
                'order_id' => $this->order->id,
                'good_id' => $good->primaryKey,
                'item_id' => $item->primaryKey,
                'options' => $good->options,
                'discount' => $good->discount,
            ];
            if($response['discount']){
                $response['price'] = round($good->price * (1 - $good->discount / 100));
                $response['old_price'] = $good->price;
            } else {
                $response['price'] = $good->price;
            }
            return $response;
        } else {
            return ['result' => 'error', 'code' => self::ERROR_CREATE_GOOD, 'error' => $good->formatErrors()];
        }
    }

    public function api_remove($good_id)
    {
        $good = Good::findOne($good_id);
        if(!$good){
            return ['result' => 'error', 'code' => 1, 'error' => 'Good not found'];
        }
        if($good->order_id != $this->order->id){
            return ['result' => 'error', 'code' => 2, 'error' => 'Access denied'];
        }

        $good->delete();

        return ['result' => 'success', 'good_id' => $good_id, 'order_id' => $good->order_id];
    }

    public function api_update($goods)
    {
        if(is_array($goods) && count($this->order->goods)) {
            foreach($this->order->goods as $good){
                if(!empty($goods[$good->id]))
                {
                    $count = (int)$goods[$good->id];
                    if($count > 0){
                        $good->model->count = $count;
                        $good->model->update();
                    }
                }
            }
        }
    }

    public function api_send($data,$new_address)
    {
        $model = $this->order->model;
        if(!$this->order->id || $model->status != Order::STATUS_BLANK){
            return ['result' => 'error', 'code' => self::ERROR_ORDER_NOT_FOUND, 'error' => 'Order not found'];
        }
        if(!count($this->order->goods)){
            return ['result' => 'error', 'code' => self::ERROR_ORDER_EMPTY, 'error' => 'Order is empty'];
        }
        $model->setAttributes($data);
        $model->status = Order::STATUS_PENDING;
        $model->user_id = Yii::$app->user->getId();
        $model->community_id = Community::community()->id;
        $model->order_sn = $this->getOrderSn();

        if($model->save()){
            if ($new_address == 1){
                //插入地址表
                $address = new UserAddress();
                $address->setAttributes($data);
                $address->is_default = UserAddress::getTotal() == 0 ? 1 : 0;
                $address->country_id = 0;
                $address->user_id = $model->user_id;
                $address->mobile = Utils::is_mobile($model->phone) ? $model->phone : '';
                $address->phone = !Utils::is_mobile($model->phone) ? $model->phone : '';
                $address->save();
            }

            return [
                'result' => 'success',
                'order_id' => $this->order->id,
                'access_token' => $this->order->access_token
            ];
        } else {
            return ['result' => 'error', 'code' => self::ERROR_ORDER_UPDATE, 'error' => $model->formatErrors()];
        }
    }

    public function api_cost()
    {
        return $this->order->cost;
    }

    public function getOrder()
    {
        if(!$this->_order){
            $access_token = $this->token;

            if(!$access_token || !($order = Order::find()->where(['access_token' => $access_token])->status(Order::STATUS_BLANK)->one())){
                $order = new Order();
            }

            $this->_order = new OrderObject($order);
        }
        return $this->_order;
    }

    public function getToken(){
        return Yii::$app->session->get(Order::SESSION_KEY);
    }

    private function getOrderSn() 
    { 
        /* 选择一个随机的方案 */ 
        mt_srand((double) microtime() * 1000000); 

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT); 
    } 
}