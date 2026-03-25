<?php
/**
 * Mailer — raw SMTP client using PHP sockets.
 * No third-party libraries. Supports SSL (port 465) and STARTTLS (port 587).
 */
class Mailer {

    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $fromEmail;
    private string $fromName;
    private $socket = null;

    public function __construct() {
        $this->host      = defined('SMTP_HOST')       ? SMTP_HOST       : '';
        $this->port      = defined('SMTP_PORT')       ? (int) SMTP_PORT : 587;
        $this->user      = defined('SMTP_USER')       ? SMTP_USER       : '';
        $this->pass      = defined('SMTP_PASS')       ? SMTP_PASS       : '';
        $this->fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : $this->user;
        $this->fromName  = defined('SMTP_FROM_NAME')  ? SMTP_FROM_NAME  : 'Swypply';
    }

    /**
     * Send an HTML email.
     *
     * @param string $toEmail  Recipient email address
     * @param string $toName   Recipient display name
     * @param string $subject  Email subject line
     * @param string $html     Full HTML body
     * @throws RuntimeException on SMTP failure
     */
    public function send(string $toEmail, string $toName, string $subject, string $html): void {
        $this->connect();
        $this->ehlo();
        $this->auth();
        $this->sendCommand("MAIL FROM:<{$this->fromEmail}>", 250);
        $this->sendCommand("RCPT TO:<{$toEmail}>", 250);
        $this->sendCommand('DATA', 354);
        $this->write($this->buildMessage($toEmail, $toName, $subject, $html));
        $this->sendCommand('.', 250);
        $this->sendCommand('QUIT', 221);
        fclose($this->socket);
        $this->socket = null;
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function connect(): void {
        $prefix = ($this->port === 465) ? 'ssl://' : '';
        $ctx    = stream_context_create([
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ],
        ]);

        $this->socket = @stream_socket_client(
            "{$prefix}{$this->host}:{$this->port}",
            $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx
        );

        if (!$this->socket) {
            throw new RuntimeException("SMTP connect failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, 10);
        $this->read(220); // Server greeting
    }

    private function ehlo(): void {
        $domain = parse_url("https://{$this->host}", PHP_URL_HOST) ?? 'localhost';
        $this->sendCommand("EHLO {$domain}", 250);

        // Upgrade to TLS on port 587
        if ($this->port === 587) {
            $this->sendCommand('STARTTLS', 220);
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS failed');
            }
            $this->sendCommand("EHLO {$domain}", 250); // Re-EHLO after TLS
        }
    }

    private function auth(): void {
        $this->sendCommand('AUTH LOGIN', 334);
        $this->sendCommand(base64_encode($this->user), 334);
        $this->sendCommand(base64_encode($this->pass), 235);
    }

    private function buildMessage(string $toEmail, string $toName, string $subject, string $html): string {
        $boundary  = bin2hex(random_bytes(16));
        $plain     = strip_tags(preg_replace('#<br\s*/?>#i', "\n", $html));
        $plain     = preg_replace('/\n{3,}/', "\n\n", $plain);
        $date      = date('r');
        $msgId     = '<' . bin2hex(random_bytes(8)) . '@' . $this->host . '>';
        $encodedTo = $toName ? "=?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>" : $toEmail;
        $encodedFrom = $this->fromName
            ? "=?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$this->fromEmail}>"
            : $this->fromEmail;
        $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        $msg  = "Date: {$date}\r\n";
        $msg .= "From: {$encodedFrom}\r\n";
        $msg .= "To: {$encodedTo}\r\n";
        $msg .= "Subject: {$encodedSubject}\r\n";
        $msg .= "Message-ID: {$msgId}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $msg .= "\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($plain)) . "\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($html)) . "\r\n";
        $msg .= "--{$boundary}--\r\n";

        return $msg;
    }

    private function sendCommand(string $cmd, int $expectedCode): string {
        $this->write($cmd . "\r\n");
        return $this->read($expectedCode);
    }

    private function write(string $data): void {
        if (fwrite($this->socket, $data) === false) {
            throw new RuntimeException('SMTP write failed');
        }
    }

    private function read(int $expectedCode): string {
        $response = '';
        while ($line = fgets($this->socket, 512)) {
            $response .= $line;
            // Multi-line responses have a dash after the code; last line has a space
            if (substr($line, 3, 1) === ' ') break;
        }
        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new RuntimeException("SMTP error (expected {$expectedCode}, got {$code}): {$response}");
        }
        return $response;
    }
}
