<?php
namespace yii\easyii\behaviors;

class KindEditorAction extends \pjkui\kindeditor\KindEditorAction {
    public function init() {
        parent::init();

        $this->php_path =  $_SERVER['DOCUMENT_ROOT'] . '/';
        $this->php_url =  '/';
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $this->root_path = $this->php_path . 'uploads/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $this->root_url = $this->php_url . 'uploads/';
        //文件保存目录路径
        $this->save_path = $this->php_path . 'uploads/';
        //文件保存目录URL
        $this->save_url = $this->php_url . 'uploads/';
        $this->max_size = 2000000;
    }
}