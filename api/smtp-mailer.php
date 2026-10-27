<?php
/**
 * Sobha Luxury CRM — Lightweight Native SMTP Client
 * Zero external dependencies (No Composer required).
 * Supports SSL (Port 465) and TLS / STARTTLS (Port 587) with standard verification.
 */

class SobhaSmtpMailer {
    private $host;
    private $port;
    private $secure;
    private $user;
    private $pass;
    private $fromEmail;
    private $fromName;
    private $timeout;

    public function __construct(array $config = []) {
        $this->host      = $config['host']      ?? 'localhost';
        $this->port      = (int)($config['port'] ?? 465);
        $this->secure    = strtolower($config['secure'] ?? 'ssl');
        $this->user      = $config['user']      ?? '';
        $this->pass      = $config['pass']      ?? '';
        $this->fromEmail = $config['from_email'] ?? ($config['user'] ?? 'noreply@sobha-luxury.com');
        $this->fromName  = $config['from_name']  ?? 'Sobha Realty Luxury Desk';
        $this->timeout   = 12;
    }

    /**
     * Send email via SMTP
     * @param string|array $to Single recipient or array of recipients
     * @param string $subject
     * @param string $body
     * @param string $replyTo
     * @return bool
     */
    public function send($to, $subject, $body, $replyTo = '') {
        $recipients = is_array($to) ? $to : explode(',', (string)$to);
        $cleanRecipients = [];
        foreach ($recipients as $r) {
            $r = trim($r);
            if (filter_var($r, FILTER_VALIDATE_EMAIL)) {
                $cleanRecipients[] = $r;
            }
        }

        if (empty($cleanRecipients)) {
            return false;
        }

        // Determine supported TLS client protocols: TLS 1.2 and TLS 1.3 only (exclude TLS 1.0 / 1.1)
        $cryptoMethod = 0;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }
        if ($cryptoMethod === 0) {
            error_log("[Sobha SMTP Error] Runtime environment does not support TLS 1.2+ client encryption");
            return false;
        }

        // Standard SSL context with peer and certificate verification enabled, restricted to TLS 1.2+
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
                'crypto_method'    => $cryptoMethod
            ]
        ]);

        $remoteTarget = ($this->secure === 'ssl') ? 'ssl://' . $this->host : $this->host;

        $socket = @stream_socket_client(
            $remoteTarget . ':' . $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            error_log("[Sobha SMTP Error] Cannot connect to {$this->host}:{$this->port} - $errstr ($errno)");
            return false;
        }

        stream_set_timeout($socket, $this->timeout);

        try {
            $this->readResponse($socket, 220);

            // Send EHLO
            $localDomain = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'sobha-luxury.com';
            $this->sendCommand($socket, "EHLO $localDomain", 250);

            $encryptedTransport = ($this->secure === 'ssl');

            // STARTTLS for TLS ports (e.g. 587)
            if ($this->secure === 'tls') {
                $this->sendCommand($socket, "STARTTLS", 220);
                if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                    throw new Exception("STARTTLS encryption handshake failed");
                }
                $encryptedTransport = true;
                $this->sendCommand($socket, "EHLO $localDomain", 250);
            }

            // Authenticate only over a verified encrypted transport (SSL or after successful STARTTLS)
            if (!empty($this->user) && !empty($this->pass)) {
                if (!$encryptedTransport) {
                    throw new Exception("Insecure transport: credentials cannot be transmitted over plain unencrypted socket");
                }
                $this->sendCommand($socket, "AUTH LOGIN", 334);
                $this->sendCommand($socket, base64_encode($this->user), 334);
                $this->sendCommand($socket, base64_encode($this->pass), 235);
            }

            // Mail transaction
            $fromAddr = !empty($this->fromEmail) ? $this->fromEmail : $this->user;
            $this->sendCommand($socket, "MAIL FROM:<$fromAddr>", 250);

            foreach ($cleanRecipients as $rcpt) {
                $this->sendCommand($socket, "RCPT TO:<$rcpt>", 250);
            }

            $this->sendCommand($socket, "DATA", 354);

            // Normalize line endings to standard CRLF before dot-stuffing
            $normalizedBody = str_replace(["\r\n", "\r"], "\n", $body);
            $normalizedBody = str_replace("\n", "\r\n", $normalizedBody);

            // Encode body in quoted-printable (safe 7-bit ASCII transport for all MTAs)
            $encodedBody = quoted_printable_encode($normalizedBody);

            // SMTP dot-stuffing: leading dots must be doubled per RFC 5321
            $safeBody = preg_replace('/^\./m', '..', $encodedBody);

            // Build MIME message headers
            $msgDate = date('r');
            $headers  = "Date: $msgDate\r\n";
            $headers .= "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <$fromAddr>\r\n";
            $headers .= "To: " . implode(', ', $cleanRecipients) . "\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            if (!empty($replyTo) && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $headers .= "Reply-To: $replyTo\r\n";
            }
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $headers .= "X-Mailer: SobhaLuxury-SMTP-Engine\r\n";

            $fullMessage = $headers . "\r\n" . $safeBody . "\r\n.\r\n";

            fwrite($socket, $fullMessage);
            $this->readResponse($socket, 250);

            // Best-effort cleanup: DATA has already been accepted (250 OK) by the server.
            // Transmit QUIT and immediately close without blocking on server teardown delays.
            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);
            return true;
        } catch (Exception $e) {
            error_log("[Sobha SMTP Exception] " . $e->getMessage());
            @fclose($socket);
            return false;
        }
    }

    private function sendCommand($socket, $cmd, $expectedCode) {
        fwrite($socket, $cmd . "\r\n");
        return $this->readResponse($socket, $expectedCode);
    }

    private function readResponse($socket, $expectedCode) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            // Multiline responses have '-' after the 3-digit code; last line has a space
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception("SMTP Error: expected $expectedCode but got $code: " . trim($response));
        }
        return $response;
    }
}
