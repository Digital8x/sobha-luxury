<?php
/**
 * SOBHA REALTY LUXURY — LEAD SUBMISSION ENDPOINT
 * POST /api/submit-lead.php
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

// Accept JSON or standard URL-encoded Form POST
$rawInput = file_get_contents('php://input');
$inputData = [];
if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $inputData = $decoded;
    }
}
if (empty($inputData)) {
    $inputData = $_POST;
}

$clientIp = LeadSecurity::getClientIp();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// ----------------------------------------------------------------------------
// 1. Honeypot Bot Trap Check
// ----------------------------------------------------------------------------
// If the hidden 'website' or 'fax' field is populated, a bot filled it out
$honeypotField = trim($inputData['company_website'] ?? $inputData['fax'] ?? $inputData['website'] ?? '');
if (!empty($honeypotField)) {
    recordBlockedLead($clientIp, "Bot trap triggered: Honeypot field was populated.", $inputData, $userAgent);
    // Silent pseudo-success so bots do not re-try with different field variations
    echo json_encode([
        'success' => true,
        'reference' => 'SBH-' . strtoupper(substr(md5(uniqid()), 0, 7)),
        'message' => 'Enquiry transmitted successfully.'
    ]);
    exit;
}

// ----------------------------------------------------------------------------
// 2. Timing Threshold Bot Check
// ----------------------------------------------------------------------------
$renderTime = floatval($inputData['form_render_ts'] ?? 0);
if ($renderTime > 0) {
    $timeTakenSeconds = (microtime(true) * 1000 - $renderTime) / 1000;
    if ($timeTakenSeconds < 1.8) {
        recordBlockedLead($clientIp, "Submission too fast ({$timeTakenSeconds}s). Identified as automated script.", $inputData, $userAgent);
        echo json_encode([
            'success' => false,
            'error' => 'Your submission was flagged as automated. Please take a moment and submit again.'
        ]);
        exit;
    }
}

// ----------------------------------------------------------------------------
// 3. Rate Limiting Check
// ----------------------------------------------------------------------------
$pdo = getDbConnection();
if ($pdo) {
    try {
        $hourlyStmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE ip_address = ? AND created_at >= (NOW() - INTERVAL 1 HOUR)");
        $hourlyStmt->execute([$clientIp]);
        $submissionsCount = (int) $hourlyStmt->fetchColumn();

        if ($submissionsCount >= 5) {
            echo json_encode([
                'success' => false,
                'error' => 'You have recently submitted multiple enquiries. Our private client team will contact you shortly.'
            ]);
            exit;
        }
    } catch (Exception $e) {
        // Continue if rate limit query fails
    }
}

// ----------------------------------------------------------------------------
// 4. Input Sanitization & Validation
// ----------------------------------------------------------------------------
$fullName = trim($inputData['full_name'] ?? $inputData['name'] ?? '');
$countryCode = trim($inputData['country_code'] ?? $inputData['phone_country_code'] ?? '+971');
$phone = trim($inputData['phone'] ?? $inputData['phone_number'] ?? '');
$email = trim($inputData['email'] ?? '');
$development = trim($inputData['development'] ?? 'SOBHA Luxury Portfolio');
$residenceType = trim($inputData['residence_type'] ?? $inputData['residence'] ?? 'Selected Layout');
$sourceButton = trim($inputData['source_button'] ?? 'Request Floor Plan');
$pageUrl = trim($inputData['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '');
$referrer = trim($inputData['referrer'] ?? '');

// UTM parameters
$utmSource = trim($inputData['utm_source'] ?? 'Direct');
$utmMedium = trim($inputData['utm_medium'] ?? '');
$utmCampaign = trim($inputData['utm_campaign'] ?? '');
$utmTerm = trim($inputData['utm_term'] ?? '');
$utmContent = trim($inputData['utm_content'] ?? '');

// Validate Name
$nameVal = LeadSecurity::validateName($fullName);
if ($nameVal !== true) {
    echo json_encode(['success' => false, 'error' => $nameVal, 'field' => 'full_name']);
    exit;
}

// Validate Phone
$phoneVal = LeadSecurity::validatePhone($countryCode, $phone);
if ($phoneVal !== true) {
    recordBlockedLead($clientIp, "Invalid phone number attempt: {$countryCode} {$phone}. " . $phoneVal, $inputData, $userAgent);
    echo json_encode(['success' => false, 'error' => $phoneVal, 'field' => 'phone']);
    exit;
}

// Validate Email
$emailVal = LeadSecurity::validateEmail($email);
if ($emailVal !== true) {
    recordBlockedLead($clientIp, "Disposable or invalid email blocked: {$email}", $inputData, $userAgent);
    echo json_encode(['success' => false, 'error' => $emailVal, 'field' => 'email']);
    exit;
}

// Format full phone
$fullPhone = $countryCode . ' ' . preg_replace('/[^0-9]/', '', $phone);

// ----------------------------------------------------------------------------
// 5. Geolocation & Telemetry Lookup
// ----------------------------------------------------------------------------
$geo = LeadSecurity::lookupIp($clientIp);
$deviceType = LeadSecurity::parseDevice($userAgent);
$browser = LeadSecurity::parseBrowser($userAgent);

// Optional VPN Enforcement
$blockVpn = getCrmSetting('block_vpn', '0');
if ($blockVpn === '1' && !empty($geo['is_vpn'])) {
    recordBlockedLead($clientIp, "Blocked VPN/Hosting IP connection", $inputData, $userAgent);
    echo json_encode([
        'success' => false,
        'error' => 'We were unable to verify your connection security. Please disable your VPN/proxy and try again.'
    ]);
    exit;
}

// Generate Reference ID
$referenceCode = 'SBH-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 7));

// ----------------------------------------------------------------------------
// 6. Save Lead into Database
// ----------------------------------------------------------------------------
$savedToDb = false;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO leads (
            reference_code, full_name, phone_country_code, phone_number, full_phone, email,
            development, residence_type, ip_address, city, country, country_code, country_flag, is_vpn,
            device_type, browser, user_agent, utm_source, utm_medium, utm_campaign, utm_term, utm_content,
            referrer_url, landing_page, source_button
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?
        )");

        $stmt->execute([
            $referenceCode, $fullName, $countryCode, $phone, $fullPhone, $email,
            $development, $residenceType, $clientIp, $geo['city'], $geo['country'], $geo['country_code'], $geo['country_flag'], $geo['is_vpn'],
            $deviceType, $browser, $userAgent, $utmSource, $utmMedium, $utmCampaign, $utmTerm, $utmContent,
            $referrer, $pageUrl, $sourceButton
        ]);

        $savedToDb = true;
    } catch (Exception $e) {
        error_log("[Sobha CRM DB Insert Exception] " . $e->getMessage());
    }
}

// ----------------------------------------------------------------------------
// 7. Dispatch Email Notification to Admin
// ----------------------------------------------------------------------------
dispatchLeadNotification([
    'reference_code' => $referenceCode,
    'full_name'      => $fullName,
    'full_phone'     => $fullPhone,
    'email'          => $email,
    'development'    => $development,
    'residence_type' => $residenceType,
    'source_button'  => $sourceButton,
    'ip_address'     => $clientIp,
    'city'           => $geo['city'],
    'country'        => $geo['country'],
    'country_flag'   => $geo['country_flag'],
    'device_type'    => $deviceType,
    'browser'        => $browser,
    'utm_source'     => $utmSource,
    'utm_medium'     => $utmMedium,
    'landing_page'   => $pageUrl
]);

// ----------------------------------------------------------------------------
// 8. Return Success JSON
// ----------------------------------------------------------------------------
echo json_encode([
    'success'        => true,
    'reference_code' => $referenceCode,
    'redirect_url'   => 'thank-you.html',
    'message'        => 'Your private enquiry has been verified and registered with our property advisory team.'
]);
exit;


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function recordBlockedLead($ip, $reason, $data, $ua) {
    $pdo = getDbConnection();
    if (!$pdo) return;
    try {
        $stmt = $pdo->prepare("INSERT INTO blocked_leads (full_name, phone_number, email, ip_address, block_reason, user_agent, payload_dump) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['full_name'] ?? $data['name'] ?? null,
            ($data['country_code'] ?? '') . ' ' . ($data['phone'] ?? ''),
            $data['email'] ?? null,
            $ip,
            $reason,
            $ua,
            json_encode($data)
        ]);
    } catch (Exception $e) {}
}

function dispatchLeadNotification($lead) {
    $adminEmails = getCrmSetting('admin_emails', DEFAULT_ADMIN_EMAILS);
    $subject = "🏠 New Lead — Sobha Luxury | {$lead['full_name']} | {$lead['development']} | {$lead['country_flag']} {$lead['city']}, {$lead['country']}";

    $body = "New Luxury Property Enquiry Transmitted:\n\n"
          . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
          . "CLIENT PROFILE\n"
          . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
          . "Reference Code:   #{$lead['reference_code']}\n"
          . "Full Name:        {$lead['full_name']}\n"
          . "Direct Mobile:    {$lead['full_phone']}\n"
          . "Email Address:    {$lead['email']}\n"
          . "Development:      {$lead['development']}\n"
          . "Unit Preference:  {$lead['residence_type']}\n"
          . "Source CTA:       {$lead['source_button']}\n\n"
          . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
          . "GEOLOCATION & TELEMETRY\n"
          . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
          . "Location:         {$lead['country_flag']} {$lead['city']}, {$lead['country']}\n"
          . "IP Address:       {$lead['ip_address']}\n"
          . "Device / OS:      {$lead['device_type']}\n"
          . "Browser:          {$lead['browser']}\n\n"
          . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
          . "MARKETING ATTRIBUTION\n"
          . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
          . "UTM Source:       {$lead['utm_source']}\n"
          . "UTM Medium:       {$lead['utm_medium']}\n"
          . "Landing Page:     {$lead['landing_page']}\n";

    // Resolve SMTP settings (.env takes priority, then crm_settings table)
    $smtpHost   = !empty(ENV_SMTP_HOST) ? ENV_SMTP_HOST : getCrmSetting('smtp_host', '');
    $smtpPort   = !empty(ENV_SMTP_PORT) ? ENV_SMTP_PORT : getCrmSetting('smtp_port', '465');
    $smtpSecure = !empty(ENV_SMTP_SECURE) ? ENV_SMTP_SECURE : getCrmSetting('smtp_secure', 'ssl');
    $smtpUser   = !empty(ENV_SMTP_USER) ? ENV_SMTP_USER : getCrmSetting('smtp_user', '');
    $smtpPass   = !empty(ENV_SMTP_PASS) ? ENV_SMTP_PASS : getCrmSetting('smtp_pass', '');
    $smtpFromName = !empty(ENV_SMTP_FROM_NAME) ? ENV_SMTP_FROM_NAME : getCrmSetting('smtp_from_name', 'Sobha Realty Luxury Desk');
    $smtpFromEmail = !empty(ENV_SMTP_FROM_EMAIL) ? ENV_SMTP_FROM_EMAIL : (!empty($smtpUser) ? $smtpUser : 'noreply@sobha-luxury.com');

    // If authenticated SMTP is fully configured (host + user + pass), transmit via SMTP
    if (!empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
        require_once __DIR__ . '/smtp-mailer.php';
        $mailer = new SobhaSmtpMailer([
            'host'       => $smtpHost,
            'port'       => $smtpPort,
            'secure'     => $smtpSecure,
            'user'       => $smtpUser,
            'pass'       => $smtpPass,
            'from_email' => $smtpFromEmail,
            'from_name'  => $smtpFromName
        ]);

        $sent = $mailer->send($adminEmails, $subject, $body, $lead['email']);
        if ($sent) {
            error_log("[Sobha Lead Mail] Sent successfully via SMTP to: " . $adminEmails);
            return;
        }
        error_log("[Sobha Lead Mail] SMTP failed. Falling back to native mail()...");
    }

    // Native PHP mail() fallback
    $fromHeader = !empty($smtpFromEmail) ? $smtpFromEmail : 'noreply@sobha-luxury.com';
    $headers = [
        'From: Sobha Realty Luxury <' . $fromHeader . '>',
        'Reply-To: ' . $lead['email'],
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];

    $recipients = explode(',', $adminEmails);
    foreach ($recipients as $recipient) {
        $recipient = trim($recipient);
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $mailResult = @mail($recipient, $subject, $body, implode("\r\n", $headers), "-f" . $fromHeader);
            if (!$mailResult) {
                // Secondary attempt without -f parameter (some shared cPanel hosts disallow extra flag)
                @mail($recipient, $subject, $body, implode("\r\n", $headers));
            }
        }
    }
}
