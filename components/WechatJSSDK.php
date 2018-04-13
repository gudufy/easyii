<?php
namespace yii\easyii\components;

use Yii;
use yii\helpers\FileHelper;

class WechatJSSDK {
    private $appId;
    private $appSecret;
  
    public function __construct($appId, $appSecret) {
      $this->appId = $appId;
      $this->appSecret = $appSecret;
    }
  
    public function getSignPackage() {
      $jsapiTicket = $this->getJsApiTicket();
  
      // 注意 URL 一定要动态获取，不能 hardcode.
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  
      $timestamp = time();
      $nonceStr = $this->createNonceStr();
  
      // 这里参数的顺序要按照 key 值 ASCII 码升序排序
      $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
  
      $signature = sha1($string);
  
      $signPackage = array(
        "appId"     => $this->appId,
        "nonceStr"  => $nonceStr,
        "timestamp" => $timestamp,
        "url"       => $url,
        "signature" => $signature,
        "rawString" => $string
      );
      return $signPackage; 
    }
  
    private function createNonceStr($length = 16) {
      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      $str = "";
      for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
      }
      return $str;
    }
  
    private function getJsApiTicket() {
        //使用Redis缓存 jsapi_ticket
        $cache = Yii::$app->cache; 
        $cache_ticket = $cache->get('wechat_jsapi_ticket');
        if ($cache_ticket) {
            $ticket = $cache_ticket;
        } else {
            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$accessToken;
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $cache->set('wechat_jsapi_ticket', $ticket,7000);
            }
        }
        return $ticket;
    }
    private function getAccessToken() {
        //使用Redis缓存 access_token
        $cache = Yii::$app->cache; 
        $cache_token = $cache->get('wechat_access_token');
        if ($cache_token) {
            $access_token = $cache_token;
        } else {
            $appid = $this->appId;
            $appsecret = $this->appSecret;
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $cache->set('wechat_access_token', $access_token,7000);
            }
        }
        return $access_token;
    }
  
    private function httpGet($url) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 500);
      // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
      // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($curl, CURLOPT_URL, $url);
  
      $res = curl_exec($curl);
      curl_close($curl);

      return $res;
    }
  }
  
  