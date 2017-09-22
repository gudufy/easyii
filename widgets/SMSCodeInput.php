<?php
namespace yii\easyii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\widgets\InputWidget;

use yii\easyii\models\Setting;

class SMSCodeInput extends InputWidget
{
    public $template = '{input} {button}';
    public $validator = false;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $this->registerClientScript();
        $this->customFieldPrepare();
    }

    protected function customFieldPrepare()
    {
        $id = $this->options['id'];
        $view = $this->view;
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, ['style'=>'width:100px;padding:5px;']);
        } else {
            $input = Html::textInput($this->name, $this->value, ['class'=> "form-control"]);
        }

        $button = Html::a('发送验证码', 'javascript:;', ['id'=>'btn-'.$id.'','class'=>'btn btn-primary']);

        echo strtr($this->template, [
            '{input}' => $input,
            '{button}' => $button,
        ]);
    }

    public function registerClientScript()
    {
        $id = str_replace('smscode','',$this->options['id']);
        $validator = $this->validator;
        $view = $this->getView();
        $view->registerJs("$(function () {
            function checkMobile(str) {
                var re = /^\d{6,11}$/;
                if (re.test(str)) {
                    return true;
                } else {
                    return false;
                }
            }

            var btn = $('#btn-{$id}smscode');
            btn.click(function () {
                var mobile = $('#{$id}mobile').val();
                if (!checkMobile(mobile)) {
                    alert('手机号格式错误!');
                    return;
                }
                
                btn.prop('disabled', true);
                $.post('/site/send-sms-code?mobile=' + mobile + '&validator=$validator', function (data) {
                    if (data.success) {
                        alert('验证码已经发送到您的手机!');
                        var i = 60;
                        var s = setInterval(function () {
                            btn.text(i + '秒后重新发送');
                            i--;
                            if (i == 0) {
                                clearInterval(s);
                                btn.text('点击获取').prop('disabled', false)
                            }
                        }, 1000);
                    }
                    else {
                        btn.prop('disabled', false);
                        alert(data.msg);
                    }
                });
            });
        });
        
        ");
    }
}