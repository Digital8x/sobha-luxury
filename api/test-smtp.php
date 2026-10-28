<?php
/**
 * SMTP Diagnostic & Test Tool
 * Requires administrator authentication.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../admin/auth_check.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOBHA SMTP Diagnostic Test</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f1117; color: #e2e8f0; padding: 30px; line-height: 1.6; }
        .card { background: #1a1f2c; border: 1px solid #2d3748; border-radius: 8px; padding: 24px; max-width: 800px; margin: 0 auto 20px; }
        h1 { margin-top: 0; color: #c5a059; font-size: 22px; }
        h2 { font-size: 16px; margin-top: 20px; color: #94a3b8; border-bottom: 1px solid #2d3748; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px 12px; border-bottom: 1px solid #2d3748; font-size: 14px; }
        td.label { color: #94a3b8; width: 35%; font-weight: 600; }
        .badge-success { background: #065f46; color: #34d399; padding: 4px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .badge-fail { background: #7f1d1d; color: #f87171; padding: 4px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .log-box { background: #090d16; border: 1px solid #334155; border-radius: 6px; padding: 14px; font-family: monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; margin-top: 12px; color: #cbd5e1; }
        .tip { background: #1e293b; border-left: 4px solid #c5a059; padding: 12px 16px; margin-top: 16px; font-size: 13px; color: #cbd5e1; }
    </style>
</head>
<body>
<div class="card">
    <h1>SOBHA Realty &mdash; SMTP Live Diagnostic</h1>
    
    <h2>1. Environment Values Loaded</h2>
    <table>
        <tr><td class="label">Config Source (.env)</td><td><?php echo file_exists(dirname(__DIR__) . '/.env') ? '<span class="badge-success">.env Found</span>' : '<span class="badge-fail">.env Not Found</span>'; ?></td></tr>
        <tr><td class="label">SMTP_HOST</td><td><code><?php echo htmlspecialchars(ENV_SMTP_HOST ?: '(EMPTY)'); ?></code></td></tr>
        <tr><td class="label">SMTP_PORT</td><td><code><?php echo htmlspecialchars(ENV_SMTP_PORT ?: '(EMPTY)'); ?></code></td></tr>
        <tr><td class="label">SMTP_SECURE</td><td><code><?php echo htmlspecialchars(ENV_SMTP_SECURE ?: '(EMPTY)'); ?></code></td></tr>
        <tr><td class="label">SMTP_USER</td><td><code><?php echo htmlspecialchars(ENV_SMTP_USER ?: '(EMPTY)'); ?></code></td></tr>
        <tr><td class="label">SMTP_PASS</td><td><code><?php echo !empty(ENV_SMTP_PASS) ? str_repeat('*', strlen(ENV_SMTP_PASS)) : '<span class="badge-fail">EMPTY</span>'; ?></code></td></tr>
        <tr><td class="label">SMTP_FROM_EMAIL</td><td><code><?php echo htmlspecialchars(ENV_SMTP_FROM_EMAIL ?: '(FALLBACK TO USER)'); ?></code></td></tr>
        <tr><td class="label">ADMIN_EMAILS (Recipient)</td><td><code><?php echo htmlspecialchars(DEFAULT_ADMIN_EMAILS ?: '(EMPTY)'); ?></code></td></tr>
    </table>

    <h2>2. Connection & Handshake Execution</h2>
    <?php
    $host = ENV_SMTP_HOST ?: getCrmSetting('smtp_host', '');
    $port = (int)(ENV_SMTP_PORT ?: getCrmSetting('smtp_port', 465));
    $secure = strtolower(ENV_SMTP_SECURE ?: getCrmSetting('smtp_secure', 'ssl'));
    $user = ENV_SMTP_USER ?: getCrmSetting('smtp_user', '');
    $pass = ENV_SMTP_PASS ?: getCrmSetting('smtp_pass', '');
    $fromEmail = ENV_SMTP_FROM_EMAIL ?: ($user ?: 'noreply@sobha-luxury.com');
    $to = DEFAULT_ADMIN_EMAILS;

    if (empty($host) || empty($user) || empty($pass)) {
        echo '<p class="badge-fail">FAILED PRE-CHECK: SMTP_HOST, SMTP_USER, or SMTP_PASS is empty in .env on the server.</p>';
        echo '<div class="tip">Please edit <code>.env</code> on your server and enter all required values.</div>';
    } elseif ($secure !== 'ssl' && $secure !== 'tls') {
        echo '<p class="badge-fail">FAILED PRE-CHECK: SMTP_SECURE must be "ssl" or "tls". Plain unencrypted sockets are rejected.</p>';
    } else {
        echo '<div class="log-box">';
        
        $addLog = function($msg) {
            echo htmlspecialchars($msg) . "\n";
            flush();
        };

        $addLog("Initiating socket connection to {$host}:{$port} (mode: {$secure})...");

        // Determine supported TLS client protocols: TLS 1.2 and TLS 1.3 only
        $cryptoMethod = 0;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }

        if ($cryptoMethod === 0) {
            $addLog("❌ Error: Runtime environment does not support TLS 1.2+ client encryption");
        } else {
            // Standard SSL context with peer and certificate verification enabled
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'      => true,
                    'verify_peer_name' => true,
                    'crypto_method'    => $cryptoMethod
                ]
            ]);

            $remoteTarget = ($secure === 'ssl') ? 'ssl://' . $host : $host;
            $timeout = 10;
            $errno = 0;
            $errstr = '';

            $socket = @stream_socket_client($remoteTarget . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

            if (!$socket) {
                $addLog("❌ Socket Error: Connection failed! $errstr (Code: $errno)");
                $addLog("\nPossible reasons on your server:");
                $addLog("1. Firewall/Security: Outbound connections on port $port are blocked.");
                $addLog("2. Certificate Validation: Server certificate or hostname does not match {$host}.");
                $addLog("3. cPanel 'SMTP Restrictions' enabled in WHM.");
            } else {
                $addLog(" Connected to socket successfully.");
                stream_set_timeout($socket, $timeout);

                function readSmtpResponse($socket, $expectedCodes, &$addLog) {
                    if (!is_array($expectedCodes)) {
                        $expectedCodes = [$expectedCodes];
                    }
                    $response = '';
                    $completed = false;
                    while ($line = fgets($socket, 515)) {
                        $response .= $line;
                        // Verify line has standard CRLF ending
                        if (substr($line, -2) === "\r\n") {
                            // Final line of SMTP reply: 3 digits followed by a space, or 3 digits followed directly by CRLF
                            if ((isset($line[3]) && $line[3] === ' ') || preg_match('/^[0-9]{3}\r\n$/', $line)) {
                                $completed = true;
                                break;
                            }
                        }
                    }
                    if (!$completed) {
                        throw new Exception("Connection terminated or timed out before complete SMTP response received: " . trim($response));
                    }
                    $trimmed = trim($response);
                    $addLog("< " . $trimmed);
                    $code = (int)substr($trimmed, 0, 3);
                    if (!in_array($code, $expectedCodes, true)) {
                        $expectedStr = implode('/', $expectedCodes);
                        throw new Exception("Expected SMTP code $expectedStr but received $code: $trimmed");
                    }
                    return $trimmed;
                }

                function sendSmtpCommand($socket, $cmd, &$addLog, $logCmd = true) {
                    if ($logCmd) {
                        $addLog("> " . $cmd);
                    } else {
                        $addLog("> [AUTH CREDENTIAL REDACTED]");
                    }
                    fwrite($socket, $cmd . "\r\n");
                }

                try {
                    readSmtpResponse($socket, 220, $addLog);

                    sendSmtpCommand($socket, "EHLO sobha-luxury.com", $addLog);
                    readSmtpResponse($socket, 250, $addLog);

                    if ($secure === 'tls') {
                        sendSmtpCommand($socket, "STARTTLS", $addLog);
                        readSmtpResponse($socket, 220, $addLog);
                        $addLog("Attempting TLS crypto handshake...");
                        if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                            throw new Exception("STARTTLS encryption negotiation failed.");
                        }
                        $addLog("TLS Handshake successful.");
                        sendSmtpCommand($socket, "EHLO sobha-luxury.com", $addLog);
                        readSmtpResponse($socket, 250, $addLog);
                    }

                    sendSmtpCommand($socket, "AUTH LOGIN", $addLog);
                    readSmtpResponse($socket, 334, $addLog);

                    sendSmtpCommand($socket, base64_encode($user), $addLog);
                    readSmtpResponse($socket, 334, $addLog);

                    // Send password without logging to browser output
                    sendSmtpCommand($socket, base64_encode($pass), $addLog, false);
                    readSmtpResponse($socket, 235, $addLog);

                    $addLog("\n Authentication successful!");

                    sendSmtpCommand($socket, "MAIL FROM:<$fromEmail>", $addLog);
                    readSmtpResponse($socket, 250, $addLog);

                    $recipients = explode(',', $to);
                    $firstRcpt = trim($recipients[0]);
                    sendSmtpCommand($socket, "RCPT TO:<$firstRcpt>", $addLog);
                    readSmtpResponse($socket, [250, 251], $addLog);

                    sendSmtpCommand($socket, "DATA", $addLog);
                    readSmtpResponse($socket, 354, $addLog);

                    $testMsg = "From: Sobha Realty Test <$fromEmail>\r\n"
                             . "To: <$firstRcpt>\r\n"
                             . "Subject: =?UTF-8?B?" . base64_encode("Sobha Luxury SMTP Test Delivery") . "?=\r\n"
                             . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
                             . "This is a live test message confirming your Sobha Luxury SMTP configuration is working correctly!\r\n"
                             . "Sent timestamp: " . date('Y-m-d H:i:s') . "\r\n.\r\n";

                    fwrite($socket, $testMsg);
                    readSmtpResponse($socket, 250, $addLog);

                    sendSmtpCommand($socket, "QUIT", $addLog);
                    @fclose($socket);

                    $addLog("\n🎉 SUCCESS: Test message successfully accepted by SMTP server for delivery to {$firstRcpt}!");
                } catch (Exception $ex) {
                    $addLog("\n❌ ERROR: " . $ex->getMessage());
                    @fclose($socket);
                }
            }
        }
        echo '</div>';
    }
    ?>

    <div class="tip">
        <strong>Security Notice:</strong> Delete or disable this file (<code>api/test-smtp.php</code>) once live server diagnostics are complete.
    </div>
</div>
</body>
</html>
