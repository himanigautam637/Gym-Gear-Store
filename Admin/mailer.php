<?php

class SmtpMailer {

    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $lastError = '';

    public function __construct($host, $port, $username, $password, $fromEmail, $fromName) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function send($toEmail, $toName, $subject, $bodyHtml) {
        $socket = @fsockopen('ssl://' . $this->host, $this->port, $errno, $errstr, 15);

        if (!$socket) {
            $this->lastError = "Connection failed: $errstr ($errno)";
            return false;
        }

        stream_set_timeout($socket, 15);

        if (!$this->expect($socket, '220')) return false;

        $this->send_cmd($socket, "EHLO " . $this->host);
        if (!$this->expect($socket, '250')) return false;

        $this->send_cmd($socket, "AUTH LOGIN");
        if (!$this->expect($socket, '334')) return false;

        $this->send_cmd($socket, base64_encode($this->username));
        if (!$this->expect($socket, '334')) return false;

        $this->send_cmd($socket, base64_encode($this->password));
        if (!$this->expect($socket, '235')) return false;

        $this->send_cmd($socket, "MAIL FROM:<{$this->fromEmail}>");
        if (!$this->expect($socket, '250')) return false;

        $this->send_cmd($socket, "RCPT TO:<{$toEmail}>");
        if (!$this->expect($socket, '250')) return false;

        $this->send_cmd($socket, "DATA");
        if (!$this->expect($socket, '354')) return false;

        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "To: {$toName} <{$toEmail}>\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "\r\n";

        $body = str_replace("\n.", "\n..", $bodyHtml);

        $this->send_cmd($socket, $headers . $body . "\r\n.");
        if (!$this->expect($socket, '250')) return false;

        $this->send_cmd($socket, "QUIT");

        fclose($socket);
        return true;
    }

    private function send_cmd($socket, $cmd) {
        fwrite($socket, $cmd . "\r\n");
    }

    private function expect($socket, $code) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }

        if (substr($response, 0, 3) !== $code) {
            $this->lastError = "Expected $code, got: " . trim($response);
            return false;
        }

        return true;
    }
}