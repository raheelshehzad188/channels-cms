<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ec_mail {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function send($to, $subject, $html)
    {
        $to = trim((string) $to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ((string) platform_setting('smtp_enabled', '0') === '1') {
            return $this->send_smtp($to, $subject, $html);
        }

        // Fallback: log when SMTP is off so local/dev still works without bouncing.
        $dir = APPPATH . 'logs/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/mail_' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $to) . '.html';
        @file_put_contents($file, "To: {$to}\nSubject: {$subject}\n\n" . $html);
        return true;
    }

    protected function send_smtp($to, $subject, $html)
    {
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => platform_setting('smtp_host', ''),
            'smtp_port' => (int) platform_setting('smtp_port', 587),
            'smtp_user' => platform_setting('smtp_user', ''),
            'smtp_pass' => platform_setting('smtp_pass', ''),
            'smtp_crypto' => platform_setting('smtp_crypto', 'tls'),
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'crlf' => "\r\n",
        );
        $this->CI->email->initialize($config);
        $this->CI->email->clear(true);
        $this->CI->email->from(
            platform_setting('smtp_from_email', 'noreply@ecommerce.local'),
            platform_setting('smtp_from_name', 'Ecommerce Platform')
        );
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($html);
        return (bool) $this->CI->email->send();
    }

    public function send_many($emails, $subject, $html)
    {
        $sent = 0;
        $unique = array();
        foreach ((array) $emails as $email) {
            $email = strtolower(trim((string) $email));
            if ($email === '' || isset($unique[$email])) {
                continue;
            }
            $unique[$email] = true;
            if ($this->send($email, $subject, $html)) {
                $sent++;
            }
        }
        return $sent;
    }
}
