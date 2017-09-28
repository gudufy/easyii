<?php
/**
 * @link https://github.com/SDKiller/zyx-phpmailer
 * @copyright Copyright (c) 2014 Serge Postrash
 * @license BSD 3-Clause, see LICENSE.md
 */

 namespace yii\easyii\phpmailer;

use yii\mail\BaseMessage;

class Message extends BaseMessage
{
    /**
     * @var \zyx\phpmailer\Mailer $mailer the mailer instance that created this message
     */
    public $mailer = null;


    /**
     * Sets the character set of this message.
     * @param string $charset character set name.
     * @return static self reference.
     */
    public function setCharset($charset)
    {
        $this->mailer->adapter->setCharSet($charset);

        return $this;
    }

    /**
     * Returns the character set of this message.
     * @return string the character set of this message.
     */
    public function getCharset()
    {
        return $this->mailer->adapter->getCharSet();
    }

    /**
     * Sets the message sender.
     * @param string|array $from sender email address.
     * You may pass an array of addresses if this message is from multiple people.
     * You may also specify sender name in addition to email address using format:
     * `[email => name]`.
     * @return static self reference.
     */
    public function setFrom($from)
    {
        $recipients = $this->normalizeRecipients($from);

        //see RFC2822 3.6.2 'Originator fields' - multiple 'From' is allowed...
        foreach ($recipients as $email => $name) {
            $this->mailer->adapter->setFrom($email, $name);
            //...but anyway PHPMailer consideres it should be single 'From', so we stop processing array
            break;
        }

        return $this;
    }

    /**
     * Returns the message sender.
     * @return string the sender
     */
    public function getFrom()
    {
        return $this->mailer->adapter->getFrom();
    }

    /**
     * Sets the message recipient(s).
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return static self reference.
     */
    public function setTo($to)
    {
        $recipients = $this->normalizeRecipients($to);

        foreach ($recipients as $email => $name) {
            $this->mailer->adapter->addAddress($email, $name);
        }

        return $this;
    }

    /**
     * Returns the message recipient(s).
     * @return array the message recipients
     */
    public function getTo()
    {
        $arr = $this->mailer->adapter->getToAddresses();

        return $this->reformatArray($arr);
    }

    /**
     * Sets the reply-to address of this message.
     * @param string|array $replyTo the reply-to address.
     * You may pass an array of addresses if this message should be replied to multiple people.
     * You may also specify reply-to name in addition to email address using format:
     * `[email => name]`.
     * @return static self reference.
     */
    public function setReplyTo($replyTo)
    {
        $recipients = $this->normalizeRecipients($replyTo);

        foreach ($recipients as $email => $name) {
            $this->mailer->adapter->addReplyTo($email, $name);
        }

        return $this;
    }

    /**
     * Returns the reply-to address of this message.
     * @return string the reply-to address of this message.
     *
     * Note: inherited phpdoc is inconsistent, other getters return array
     */
    public function getReplyTo()
    {
        $arr = $this->mailer->adapter->getReplyToAddresses();

        return $this->reformatArray($arr);
    }

    /**
     * Sets the Cc (additional copy receiver) addresses of this message.
     * @param string|array $cc copy receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return static self reference.
     */
    public function setCc($cc)
    {
        $recipients = $this->normalizeRecipients($cc);

        foreach ($recipients as $email => $name) {
            $this->mailer->adapter->addCC($email, $name);
        }

        return $this;
    }

    /**
     * Returns the Cc (additional copy receiver) addresses of this message.
     * @return array the Cc (additional copy receiver) addresses of this message.
     */
    public function getCc()
    {
        $arr = $this->mailer->adapter->getCcAddresses();

        return $this->reformatArray($arr);
    }

    /**
     * Sets the Bcc (hidden copy receiver) addresses of this message.
     * @param string|array $bcc hidden copy receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return static self reference.
     */
    public function setBcc($bcc)
    {
        $recipients = $this->normalizeRecipients($bcc);

        foreach ($recipients as $email => $name) {
            $this->mailer->adapter->addBCC($email, $name);
        }

        return $this;
    }

    /**
     * Returns the Bcc (hidden copy receiver) addresses of this message.
     * @return array the Bcc (hidden copy receiver) addresses of this message.
     */
    public function getBcc()
    {
        $arr = $this->mailer->adapter->getBccAddresses();

        return $this->reformatArray($arr);
    }

    /**
     * Sets the message subject.
     * @param string $subject message subject
     * @return static self reference.
     */
    public function setSubject($subject)
    {
        $this->mailer->adapter->setSubject(strip_tags($subject));

        return $this;
    }

    /**
     * Returns the message subject.
     * @return string the message subject
     */
    public function getSubject()
    {
        return $this->mailer->adapter->getSubject();
    }

    /**
     * Sets message plain text content.
     * @param string $text message plain text content.
     * @return static self reference.
     */
    public function setTextBody($text)
    {
        $this->mailer->adapter->msgText($text);

        return $this;
    }

    /**
     * Sets message HTML content.
     * @param string $input message HTML content.
     * @return static self reference.
     *
     * Note: PHPMailer autocreates both html-body and alt-body (and normalizes text for alt-body), so we need not to set
     * additionally text body to create 'multipart/alternative' content-type.
     *
     * We also try to make possible to call [[setHtmlBody()]] from elsewhere, not only from [[compose()]],
     * for simple cases, when we have no params to pass to the view to build html, just text.
     * Another usecase is when you don't want to render views and layouts and build html message on-the-fly.
     * Othervise you do everything via Mailer [[compose()]] function, passing params their.
     */
    public function setHtmlBody($input)
    {
        if (array_key_exists('ishtml', $this->mailer->config) && $this->mailer->config['ishtml'] === false
            || (empty($this->mailer->htmlView) && empty($this->mailer->htmlLayout))
        ) {
            //prevent sending html messages if it is explicitly denied in application config or no view and layout is set
            $this->setTextBody($input);
        } else {
            if (preg_match('|<body[^>]*>(.*?)</body>|is', $input) != 1) {
                //html was not already rendered by view - lets do it
                if (empty($this->mailer->htmlView)) {
                    $html = $this->mailer->render($this->mailer->htmlLayout, ['content' => $input, 'message' => $this], false);
                } else {
                    //The most simple case is supposed here - your html view file should use '$text' variable
                    $html = $this->mailer->render($this->mailer->htmlView, ['text' => $input, 'message' => $this], $this->mailer->htmlLayout);
                }
            } else {
                $html = $input;
            }

            //TODO: check usage and default behavior of '$basedir' argument (used for images)
            $this->mailer->adapter->msgHTML($html, $basedir = '', true);
        }

        return $this;
    }

    /**
     * Attaches existing file to the email message.
     * @param string $path full file name or path alias
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return static self reference.
     */
    public function attach($path, array $options = [])
    {

        $name        = isset($options['fileName']) ? $options['fileName'] : '';
        $type        = isset($options['contentType']) ? $options['contentType'] : '';
        $encoding    = isset($options['encoding']) ? $options['encoding'] : 'base64';
        $disposition = isset($options['disposition']) ? $options['disposition'] : 'attachment';

        $this->mailer->adapter->addAttachment(\Yii::getAlias($path, false), $name, $encoding, $type, $disposition);

        return $this;
    }

    /**
     * Attach specified content as file for the email message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return static self reference.
     */
    public function attachContent($content, array $options = [])
    {
        $filename    = isset($options['fileName']) ? $options['fileName'] : (md5(uniqid('', true)) . '.txt'); //fallback
        $type        = isset($options['contentType']) ? $options['contentType'] : '';
        $encoding    = isset($options['encoding']) ? $options['encoding'] : 'base64';
        $disposition = isset($options['disposition']) ? $options['disposition'] : 'attachment';

        $this->mailer->adapter->addStringAttachment($content, $filename, $encoding, $type, $disposition);

        return $this;
    }

    /**
     * Attach a file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $path full file name or path alias
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embed($path, array $options = [])
    {
        $name        = isset($options['fileName']) ? $options['fileName'] : '';
        $type        = isset($options['contentType']) ? $options['contentType'] : '';
        $encoding    = isset($options['encoding']) ? $options['encoding'] : 'base64';
        $disposition = isset($options['disposition']) ? $options['disposition'] : 'inline';

        $cid = md5($path). '@phpmailer.0'; //RFC2392 S 2

        $this->mailer->adapter->addEmbeddedImage(\Yii::getAlias($path, false), $cid, $name, $encoding, $type, $disposition);

        return $cid;
    }

    /**
     * Attach a content as file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embedContent($content, array $options = [])
    {
        $name        = isset($options['fileName']) ? $options['fileName'] : (md5(uniqid('', true)) . '.jpg'); //fallback
        $type        = isset($options['contentType']) ? $options['contentType'] : '';
        $encoding    = isset($options['encoding']) ? $options['encoding'] : 'base64';
        $disposition = isset($options['disposition']) ? $options['disposition'] : 'inline';

        $cid = md5($name). '@phpmailer.0'; //RFC2392 S 2

        $this->mailer->adapter->addStringEmbeddedImage($content, $cid, $name, $encoding, $type, $disposition);

        return $cid;
    }

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString()
    {
        return $this->mailer->adapter->getSentMIMEMessage();
    }


    /**
     * Reformat PHPMailer's recipients arrays for Yii debug purposes
     *
     * @param   array   $source
     * @return  array
     */
    private function reformatArray($source)
    {
        $result = array();

        foreach ($source as $data) {
            $result[$data[0]] = (isset($data[1])) ? $data[1] : '';
        }

        return $result;
    }

    /**
     * Creates uniform recipients array for phpMailer's [[addAnAddress]] method (as 'email' => 'name' pairs),
     * because in \yii\mail\MessageInterface by design recipients may be defined in different ways
     *
     * @param string|array $addr copy receiver email address.
     * @return array
     */
    private function normalizeRecipients($addr)
    {
        $recipients = array();

        if (is_string($addr)) {
            //consider it as 'email'
            $recipients[$addr] = '';
        } elseif (is_array($addr)) {
            foreach ($addr as $key => $value) {
                if (is_int($key)) {
                    //consider it as numeric array of 'emails'
                    $recipients[$value] = '';
                } else {
                    //consider it as ['email' => 'name'] pairs
                    $recipients[$key] = $value;
                }
            }
        }

        return $recipients;
    }


    //The next funtions are not present in \yii\mail\MessageInterface but who knows - may be introduced in future -
    //some of them are nesessary for debug panel and are used to output debug info for bundled yii2-swiftmailer

    public function getMessageID()
    {
        return $this->mailer->adapter->getLastMessageID();
    }

    public function getDate()
    {
        return $this->mailer->adapter->getMessageDate();
    }

    public function getHeaders()
    {
        return (($this->mailer->adapter->getMIMEHeader()) . ($this->mailer->adapter->getMailHeader()));
    }

    public function getBody()
    {
        return $this->mailer->adapter->getMIMEBody();
    }

}
