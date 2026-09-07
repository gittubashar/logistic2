<?php

function smtp_is_configured(?array $settings = null): bool
{
    global $site;

    $settings ??= $site['smtp'] ?? [];

    return trim((string) ($settings['host'] ?? '')) !== ''
        && filter_var(trim((string) ($settings['from_email'] ?? '')), FILTER_VALIDATE_EMAIL) !== false;
}

function smtp_read_response($socket): array
{
    $response = '';

    while (($line = fgets($socket, 8192)) !== false) {
        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }

    return [(int) substr($response, 0, 3), trim($response)];
}

function smtp_command($socket, string $command, array $expectedCodes, string &$error): bool
{
    if (fwrite($socket, $command . "\r\n") === false) {
        $error = 'Unable to write to the SMTP server.';
        return false;
    }

    [$code, $response] = smtp_read_response($socket);
    if (!in_array($code, $expectedCodes, true)) {
        $error = $response !== '' ? $response : 'The SMTP server returned an empty response.';
        return false;
    }

    return true;
}

function smtp_header_text(string $value): string
{
    $value = trim(str_replace(["\r", "\n"], '', $value));

    return preg_match('/[^\x20-\x7E]/', $value)
        ? mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n")
        : $value;
}

function smtp_send_html_email(string $to, string $subject, string $htmlBody, string $replyTo = '', ?string &$error = null): bool
{
    global $site;

    $error = '';
    $smtp = $site['smtp'] ?? [];
    $to = trim($to);
    $fromEmail = trim((string) ($smtp['from_email'] ?? ''));
    $fromName = trim((string) ($smtp['from_name'] ?? ($site['title'] ?? 'Website')));

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'The recipient email address is invalid.';
        return false;
    }
    if (!smtp_is_configured($smtp)) {
        $error = 'SMTP is not configured. Add the SMTP host and from email in Site Settings.';
        return false;
    }

    $host = trim((string) $smtp['host']);
    $port = (int) ($smtp['port'] ?? 587);
    $port = $port > 0 ? $port : 587;
    $encryption = strtolower(trim((string) ($smtp['encryption'] ?? 'tls')));
    $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
    $socketErrorNumber = 0;
    $socketError = '';
    $socket = @stream_socket_client(
        $transport . $host . ':' . $port,
        $socketErrorNumber,
        $socketError,
        15,
        STREAM_CLIENT_CONNECT
    );

    if (!is_resource($socket)) {
        $error = 'Unable to connect to SMTP: ' . ($socketError ?: 'connection failed');
        return false;
    }

    stream_set_timeout($socket, 15);

    try {
        [$greetingCode, $greeting] = smtp_read_response($socket);
        if ($greetingCode !== 220) {
            $error = $greeting ?: 'SMTP server did not accept the connection.';
            return false;
        }

        $hostname = preg_replace('/[^a-z0-9.-]/i', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
        if (!smtp_command($socket, 'EHLO ' . $hostname, [250], $error)) {
            return false;
        }

        if ($encryption === 'tls') {
            if (!smtp_command($socket, 'STARTTLS', [220], $error)) {
                return false;
            }
            if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $error = 'Unable to establish SMTP TLS encryption.';
                return false;
            }
            if (!smtp_command($socket, 'EHLO ' . $hostname, [250], $error)) {
                return false;
            }
        }

        $username = trim((string) ($smtp['username'] ?? ''));
        if ($username !== '') {
            if (!smtp_command($socket, 'AUTH LOGIN', [334], $error)
                || !smtp_command($socket, base64_encode($username), [334], $error)
                || !smtp_command($socket, base64_encode((string) ($smtp['password'] ?? '')), [235], $error)) {
                return false;
            }
        }

        if (!smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250], $error)
            || !smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251], $error)
            || !smtp_command($socket, 'DATA', [354], $error)) {
            return false;
        }

        $replyTo = filter_var($replyTo, FILTER_VALIDATE_EMAIL) ? $replyTo : $fromEmail;
        $domain = substr(strrchr($fromEmail, '@') ?: '@localhost', 1);
        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
            'From: ' . smtp_header_text($fromName) . ' <' . $fromEmail . '>',
            'To: <' . $to . '>',
            'Reply-To: <' . $replyTo . '>',
            'Subject: ' . smtp_header_text($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $encodedBody = rtrim(chunk_split(base64_encode($htmlBody), 76, "\r\n"), "\r\n");
        $payload = implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody;
        $payload = str_replace("\n", "\r\n", $payload);
        $payload = preg_replace('/(?m)^\./', '..', $payload) . "\r\n.";

        if (!smtp_command($socket, $payload, [250], $error)) {
            return false;
        }

        $quitError = '';
        smtp_command($socket, 'QUIT', [221], $quitError);
        return true;
    } finally {
        fclose($socket);
    }
}
