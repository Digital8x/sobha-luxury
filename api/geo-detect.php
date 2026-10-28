<?php
/**
 * SOBHA REALTY LUXURY - GEOLOCATION DETECTION ENDPOINT
 * GET /api/geo-detect.php
 * Returns visitor country code, dial code, flag, and name
 */

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

require_once __DIR__ . '/security.php';

$clientIp = LeadSecurity::getClientIp();
$geo = LeadSecurity::lookupIp($clientIp);

$countryCode = strtoupper($geo['country_code'] ?? 'AE');
if ($countryCode === 'GLOBAL' || empty($countryCode)) {
    // Check Cloudflare Country header if present
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $countryCode = strtoupper(trim($_SERVER['HTTP_CF_IPCOUNTRY']));
    } else {
        $countryCode = 'AE';
    }
}

// Map common ISO 2-letter codes to international dial codes
$dialCodes = [
    'AE' => '+971', 'IN' => '+91',  'US' => '+1',   'GB' => '+44',  'SA' => '+966',
    'QA' => '+974', 'KW' => '+965', 'OM' => '+968', 'BH' => '+973', 'CA' => '+1',
    'RU' => '+7',   'CH' => '+41',  'DE' => '+49',  'FR' => '+33',  'SG' => '+65',
    'CN' => '+86',  'AU' => '+61',  'IT' => '+39',  'ES' => '+34',  'TR' => '+90',
    'EG' => '+20',  'PK' => '+92',  'BD' => '+880', 'NG' => '+234', 'ZA' => '+27',
    'KE' => '+254', 'NL' => '+31',  'SE' => '+46',  'NO' => '+47',  'DK' => '+45',
    'IE' => '+353', 'BE' => '+32',  'AT' => '+43',  'PL' => '+48',  'JO' => '+962',
    'LB' => '+961', 'KZ' => '+7',   'UZ' => '+998', 'AZ' => '+994', 'ID' => '+62',
    'MY' => '+60',  'TH' => '+66',  'VN' => '+84',  'PH' => '+63',  'JP' => '+81',
    'KR' => '+82',  'BR' => '+55',  'MX' => '+52',  'AR' => '+54',  'IL' => '+972'
];

$dial = $dialCodes[$countryCode] ?? '+971';
$flag = LeadSecurity::countryCodeToEmoji($countryCode);

echo json_encode([
    'success'      => true,
    'country_code' => $countryCode,
    'dial_code'    => $dial,
    'flag'         => $flag,
    'country'      => $geo['country'] ?? 'United Arab Emirates',
    'city'         => $geo['city'] ?? 'Dubai'
]);
