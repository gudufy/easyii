<?php
namespace yii\easyii\modules\feedback;

class FeedbackModule extends \yii\easyii\components\Module
{
    public $settings = [
        'mailAdminOnNewFeedback' => true,
        'subjectOnNewFeedback' => 'New feedback',
        'templateOnNewFeedback' => '@easyii/modules/feedback/mail/en/new_feedback',

        'answerTemplate' => '@easyii/modules/feedback/mail/en/answer',
        'answerSubject' => 'Answer on your feedback message',
        'answerHeader' => 'Hello,',
        'answerFooter' => 'Best regards.',

        'enableTitle' => false,
        'enablePhone' => true,
        'enableCaptcha' => false,
    ];

    public static $installConfig = [
        'title' => [
            'en' => 'Feedback',
            'ru' => 'Обратная связь',
            'zh-CN' => '留言反馈',
        ],
        'icon' => 'earphone',
        'order_num' => 60,
    ];
}