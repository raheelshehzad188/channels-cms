<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ec_mail {

    protected $CI;
    public $lastError = '';

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function send($to, $subject, $html, $options = array())
    {
        $this->lastError = '';
        $to = trim((string) $to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'Invalid recipient email.';
            return false;
        }
        if (!is_array($options)) {
            $options = array();
        }

        if ((string) platform_setting('smtp_enabled', '0') === '1') {
            return $this->send_smtp($to, $subject, $html, $options);
        }

        // Fallback: log when SMTP is off so local/dev still works without bouncing.
        $dir = APPPATH . 'logs/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/mail_' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $to) . '.html';
        @file_put_contents($file, "To: {$to}\nSubject: {$subject}\n\n" . $html);
        $this->lastError = 'SMTP is disabled. Email was saved to logs/mail instead of being sent.';
        return true;
    }

    protected function send_smtp($to, $subject, $html, $options = array())
    {
        $host = preg_replace('#^(ssl|tls|tcp)://#i', '', trim((string) platform_setting('smtp_host', '')));
        $port = (int) platform_setting('smtp_port', 587);
        $user = trim((string) platform_setting('smtp_user', ''));
        $pass = (string) platform_setting('smtp_pass', '');
        $crypto = strtolower(trim((string) platform_setting('smtp_crypto', 'tls')));
        $from = trim((string) platform_setting('smtp_from_email', ''));
        $fromName = trim((string) platform_setting('smtp_from_name', 'Ecommerce Platform'));

        if ($host === '') {
            $this->lastError = 'SMTP host is empty.';
            $this->log_last_error();
            return false;
        }
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $from = $user;
        }
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'From email is invalid. Use the mailbox address (e.g. info@yourdomain).';
            $this->log_last_error();
            return false;
        }

        // Port 465 = implicit SSL. 993 is IMAP (inbox) and is a common Hostinger mix-up.
        if ($port === 993 || $port === 143) {
            $port = 465;
            $crypto = 'ssl';
        }
        if ($port === 465 || $crypto === 'ssl') {
            $crypto = 'ssl';
            if ($port <= 0) {
                $port = 465;
            }
        } elseif ($port === 587 || $crypto === 'tls') {
            $crypto = 'tls';
            if ($port <= 0) {
                $port = 587;
            }
        } else {
            $crypto = '';
            if ($port <= 0) {
                $port = 25;
            }
        }

        $ok = $this->smtp_send($host, $port, $crypto, $user, $pass, $from, $fromName, $to, $subject, $html, $options);
        if (!$ok) {
            $this->log_last_error();
        }
        return $ok;
    }

    protected function smtp_send($host, $port, $crypto, $user, $pass, $from, $fromName, $to, $subject, $html, $options = array())
    {
        $timeout = 30;
        $scheme = ($crypto === 'ssl') ? 'ssl://' : 'tcp://';
        $remote = $scheme . $host . ':' . $port;
        $ctx = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'SNI_enabled' => true,
                'peer_name' => $host,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            ),
        ));

        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!is_resource($fp)) {
            $this->lastError = 'Could not connect to ' . $host . ':' . $port . ' (' . $crypto . '). ' . trim($errstr . ' ' . $errno);
            return false;
        }
        stream_set_timeout($fp, $timeout);

        try {
            $this->smtp_expect($fp, array(220));
            $this->smtp_ehlo($fp, $host);

            if ($crypto === 'tls') {
                $this->smtp_cmd($fp, 'STARTTLS', array(220));
                $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                if ($cryptoOk !== true) {
                    throw new Exception('STARTTLS failed.');
                }
                $this->smtp_ehlo($fp, $host);
            }

            if ($user !== '') {
                $this->smtp_cmd($fp, 'AUTH LOGIN', array(334));
                $this->smtp_cmd($fp, base64_encode($user), array(334));
                $this->smtp_cmd($fp, base64_encode($pass), array(235));
            }

            $this->smtp_cmd($fp, 'MAIL FROM:<' . $from . '>', array(250));
            $this->smtp_cmd($fp, 'RCPT TO:<' . $to . '>', array(250, 251));
            $this->smtp_cmd($fp, 'DATA', array(354));
            $payload = $this->build_message($from, $fromName, $to, $subject, $html, $options);
            fwrite($fp, $payload . "\r\n.\r\n");
            $this->smtp_expect($fp, array(250));
            fwrite($fp, "QUIT\r\n");
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            fclose($fp);
            return false;
        }

        fclose($fp);
        return true;
    }

    protected function smtp_ehlo($fp, $host)
    {
        $helo = preg_replace('/[^a-z0-9.\-]/i', '', (string) $host);
        if ($helo === '') {
            $helo = 'localhost';
        }
        fwrite($fp, 'EHLO ' . $helo . "\r\n");
        $this->smtp_expect($fp, array(250));
    }

    protected function smtp_cmd($fp, $cmd, $expect)
    {
        fwrite($fp, $cmd . "\r\n");
        $this->smtp_expect($fp, $expect);
    }

    protected function smtp_expect($fp, $expect)
    {
        $expect = array_map('intval', (array) $expect);
        $reply = '';
        while (!feof($fp)) {
            $line = fgets($fp, 2048);
            if ($line === false) {
                break;
            }
            $reply .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($reply, 0, 3);
        if (!in_array($code, $expect, true)) {
            $safe = trim(preg_replace('/[\r\n]+/', ' ', $reply));
            throw new Exception('SMTP ' . implode('/', $expect) . ' expected, got: ' . $safe);
        }
        return $reply;
    }

    protected function build_message($from, $fromName, $to, $subject, $html, $options = array())
    {
        $encodedName = $this->encode_header($fromName);
        $encodedSubject = $this->encode_header($subject);
        $headers = array(
            'Date: ' . date('r'),
            'From: ' . $encodedName . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: ZENvello',
        );
        $replyTo = isset($options['reply_to']) ? trim((string) $options['reply_to']) : '';
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }
        $body = str_replace("\n.", "\n..", str_replace("\r\n", "\n", (string) $html));
        $body = str_replace("\n", "\r\n", $body);
        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    protected function encode_header($value)
    {
        $value = trim(str_replace(array("\r", "\n"), '', (string) $value));
        if ($value === '') {
            return 'Store';
        }
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }
        return $value;
    }

    protected function log_last_error()
    {
        $dir = APPPATH . 'logs/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $msg = date('c') . ' ' . $this->lastError . "\n";
        @file_put_contents($dir . '/smtp_last.txt', $msg);
        log_message('error', 'Ec_mail: ' . $this->lastError);
    }

    public function send_many($emails, $subject, $html, $options = array())
    {
        $sent = 0;
        $unique = array();
        foreach ((array) $emails as $email) {
            $email = strtolower(trim((string) $email));
            if ($email === '' || isset($unique[$email])) {
                continue;
            }
            $unique[$email] = true;
            if ($this->send($email, $subject, $html, $options)) {
                $sent++;
            }
        }
        return $sent;
    }
}
