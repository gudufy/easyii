<?php
namespace yii\easyii\components;
use Yii;
use yii\easyii\helpers\Utils;

class WechatPay
{
    protected $mchid;
    protected $appid;
    protected $key;
    public function __construct($mchid, $appid, $key)
    {
        $this->mchid = $mchid;
        $this->appid = $appid;
        $this->key = $key;
    }
    public function createJsBizPackage($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp,$trade_type = 'JSAPI')
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );
        $unified = array(
            'appid' => $config['appid'],
            'attach' => '支付',
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notifyUrl,
            'openid' => $openid,
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' => Utils::get_client_ip(),
            'total_fee' => intval($totalFee * 100),
            'trade_type' => $trade_type,
        );
        if ($trade_type == 'NATIVE'){
            $unified['product_id'] = $outTradeNo.'666';
        }
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code_des);
        }

        if($trade_type == 'JSAPI'){
            $arr = array(
                "appId" => $config['appid'],
                "timeStamp" => (string)$timestamp,
                "nonceStr" => self::createNonceStr(),
                "package" => "prepay_id=" . $unifiedOrder->prepay_id,
                "signType" => 'MD5',
            );
            $arr['paySign'] = self::getSign($arr, $config['key']);
            return $arr;
        }

        return $unifiedOrder;
    }
    public function getSignPackage($url) {
        $jsapiTicket = self::getJsApiTicket();
        $timestamp = (string)time();
        $nonceStr = self::createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=".$jsapiTicket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appid,
            "nonceStr"  => $nonceStr,
            "timestamp" => (string)$timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
    //支付结果查询
    public function orderQuery($out_trade_no){
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );
        $unified = array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'out_trade_no' => $out_trade_no,
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/orderquery', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $unifiedOrder;
    }
    public function orderRefund($out_trade_no,$out_refund_no,$total_fee,$refund_fee){
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );
        $unified = array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => $out_refund_no,
            'total_fee' => intval($total_fee * 100),
            'refund_fee' => intval($refund_fee * 100),
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/secapi/pay/refund', self::arrayToXml($unified), array(), 2);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $unifiedOrder;
    }
    public function orderClose($out_trade_no){
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->key,
        );
        $unified = array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'out_trade_no' => $out_trade_no,
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/closeorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $unifiedOrder;
    }
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifypeer);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function curlPost($url = '', $postData = '', $options = array(), $verifypeer = false)
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifypeer);
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/cert/apiclient_cert.pem');
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/cert/apiclient_key.pem');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i<$length; $i++){
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function getJsApiTicket() {
        //使用Redis缓存 jsapi_ticket
        $redis = Yii::$app->redis;
        $redis_ticket = $redis->get('wechat:jsapi_ticket');
        if ($redis_ticket) {
            $ticket = $redis_ticket;
        } else {
            $accessToken = self::getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$accessToken;
            $res = json_decode(self::curlGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $redis->set('wechat:jsapi_ticket', $ticket);
                $redis->expire('wechat:jsapi_ticket', 7000);
            }
        }
        return $ticket;
    }
    public static function getAccessToken() {
        //使用Redis缓存 access_token
        $redis = Yii::$app->redis;
        $redis_token = $redis->get('wechat:access_token');
        if ($redis_token) {
            $access_token = $redis_token;
        } else {
            $appid = Yii::$app->params['wechat']['appid'];
            $appsecret = Yii::$app->params['wechat']['appsecret'];
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $res = json_decode(self::curlGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $redis->set('wechat:access_token', $access_token);
                $redis->expire('wechat:access_token', 7000);
            }
        }
        return $access_token;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val){
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff)>0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}