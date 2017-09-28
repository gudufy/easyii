<?php
/**
 * @link https://github.com/SDKiller/zyx-phpmailer
 * @copyright Copyright (c) 2014 Serge Postrash
 * @license BSD 3-Clause, see LICENSE.md
 */

 namespace yii\easyii\phpmailer;


/**
 * A wrapper class to resolve some inconsistencies across original PHPMailer versions
 * (i.e. some missing setters and getters, etc.)
 *
 * @package zyx\phpmailer
 */

class Adapter extends \PHPMailer\PHPMailer\PHPMailer
{
    /**
     * Returns the PHPMailer Version number.
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Sets the callback function to return results from PHPMailer (see PHPMailer property '$action_function')
     * @param   callable $callback character set name.
     * @return  void
     */
    public function setCallback($callback)
    {
        $this->action_function = $callback;
    }

    /**
     * Sets the character set of this message.
     * @param string $charset character set name.
     * @return void
     */
    public function setCharset($charset)
    {
        $this->CharSet = $charset;
    }

    /**
     * Returns the character set of this message.
     * @return string the character set of this message.
     */
    public function getCharset()
    {
        return $this->CharSet;
    }

    /**
     * Returns the message sender.
     * @return string the sender email
     */
    public function getFrom()
    {
        return $this->From;
    }

    /**
     * Returns the message sender.
     * @return array the sender email and name
     */
    public function getFromFull()
    {
        return array($this->From => $this->FromName);
    }

    /**
     * Sets the message subject.
     * @param string $subject message subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->Subject = $subject;
    }

    /**
     * Returns the message subject.
     * @return string the message subject
     */
    public function getSubject()
    {
        return $this->Subject;
    }

    /**
     * @inheritdoc
     */
    public function html2text($html, $advanced = false)
    {
        return parent::html2text($html, $advanced);
    }

    /**
     * Sets message plain text content.
     * @param string $text message plain text conten
     * @return void
     */
    public function msgText($text)
    {
        $this->isHTML(false);
        $text = self::html2text($text, true);
        $text = self::normalizeBreaks($text);
        $this->Body = $text;
    }

    /**
     * Allows for public read access to 'MIMEHeader' property.
     * @access public
     * @return string
     */
    public function getMIMEHeader()
    {
        return $this->MIMEHeader;
    }

    /**
     * Allows for public read access to 'mailHeader' property.
     * @access public
     * @return string
     */
    public function getMailHeader()
    {
        return $this->mailHeader;
    }

    /**
     * Allows for public read access to 'MIMEBody' property.
     * @access public
     * @return string
     */
    public function getMIMEBody()
    {
        return $this->MIMEBody;
    }

    /**
     * @access  public
     * @param   int     $timestamp
     * @return  void
     */
    public function setMessageDate($timestamp = null)
    {
        if (empty($timestamp)) {
            $this->MessageDate = self::rfcDate();
        } else {
            date_default_timezone_set(@date_default_timezone_get());
            $this->MessageDate = date('D, j M Y H:i:s O', $timestamp);
        }
    }

    /**
     * @access public
     * @return string RFC 822 formatted message date
     */
    public function getMessageDate()
    {
        return $this->MessageDate;
    }

    /**
     * @access public
     * @return void
     */
    public function resetMailer()
    {
        $this->Subject        = '';
        $this->Body           = '';
        $this->AltBody        = '';
        $this->MIMEBody       = '';
        $this->MIMEHeader     = '';
        $this->mailHeader     = '';
        $this->MessageID      = '';
        $this->MessageDate    = '';
        $this->to             = array();
        $this->cc             = array();
        $this->bcc            = array();
        $this->ReplyTo        = array();
        $this->all_recipients = array();
        $this->attachment     = array();
        $this->CustomHeader   = array();
        $this->lastMessageID  = '';
        $this->message_type   = '';
        $this->boundary       = array();
    }


    /**
     * The following getter methods for protected properties are missing in PHPMailer releases <= 5.2.7.
     * They were introduced only in dev-master
     * @see https://github.com/PHPMailer/PHPMailer/commit/338dd086182eaad63dcfc5e017f157b28274e30a
     * Note: as of now this getters are used for Yii-debug mail panel purpose only.
     */

    /**
     * Allows for public read access to 'to' property.
     * @access public
     * @return array
     */
    public function getToAddresses()
    {
        return $this->to;
    }

    /**
     * Allows for public read access to 'cc' property.
     * @access public
     * @return array
     */
    public function getCcAddresses()
    {
        return $this->cc;
    }

    /**
     * Allows for public read access to 'bcc' property.
     * @access public
     * @return array
     */
    public function getBccAddresses()
    {
        return $this->bcc;
    }

    /**
     * Allows for public read access to 'ReplyTo' property.
     * @access public
     * @return array
     */
    public function getReplyToAddresses()
    {
        return $this->ReplyTo;
    }

    /**
     * Allows for public read access to 'all_recipients' property.
     * @access public
     * @return array
     */
    public function getAllRecipientAddresses()
    {
        return $this->all_recipients;
    }

}
