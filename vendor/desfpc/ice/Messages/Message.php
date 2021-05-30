<?php
/**
 * iceCMS v0.1
 * Created by Sergey Peshalov https://github.com/desfpc
 * PHP framework and CMS based on it.
 * https://github.com/desfpc/iceCMS
 *
 * Sending messages (email, sms, push, etc...) Class
 *
 */

namespace ice\Messages;

use ice\Settings\Settings;
use pechkin\pechkin;

class Message
{

    const TYPES = [
        'email' //TODO SMS, PUSH, Telegram, etc
    ];

    public $types;
    public $settings;

    /**
     * Message constructor.
     *
     * @param Settings $settings
     * @param string[] $types
     */
    public function __construct(Settings $settings, $types = ['email'])
    {

        if (empty($types)) return false;

        if (!is_array($types)) {
            $types = explode(',', $types);
        }

        foreach ($types as $type) {
            if (!in_array($type, self::TYPES)) {
                return false;
            }
        }

        $this->types = $types;
        $this->settings = $settings;

    }

    /**
     * Формирование email заголовка TO
     *
     * @param $email
     * @param $name
     */
    public static function makeTo($email, $name)
    {

    }

    /**
     * Отсылка сообщения
     *
     * @param $to
     * @param $subject
     * @param $message
     * @param array $attachments
     * @return false
     */
    public function send($to, $subject, $message, $attachments = [])
    {

        if (count($this->types) == 0) return false;

        foreach ($this->types as $type) {

            switch ($type) {
                case 'email':
                    $this->sendEmail($to, $subject, $message, $attachments);
                    break;
            }
        }
    }

    /**
     * Отсылка email транспорт
     *
     * @param $to
     * @param $subject
     * @param $message
     * @param array $attachments
     * @return bool
     */
    public function sendEmail($to, $subject, $message, $attachments = [])
    {
        $mail = new pechkin(
            $this->settings->email->smtp,
            $this->settings->email->port,
            $this->settings->email->mail,
            $this->settings->email->pass,
            'ssl',
            60,
            false
        );

        if (is_array($attachments) && count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }

        return $mail->send($this->settings->email->mail, $to, $subject, $message);
    }

}