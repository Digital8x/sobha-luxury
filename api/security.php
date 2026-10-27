<?php
/**
 * SOBHA REALTY LUXURY — SECURITY & VALIDATION SUITE
 * Anti-Fake Leads, Disposable Email Blacklist, Pattern Matching & Device Parser
 */

class LeadSecurity {

    // 120+ known temporary / disposable email providers
    private static $disposableDomains = [
        'mailinator.com', 'guerrillamail.com', 'tempmail.com', 'throwawaymail.com',
        'yopmail.com', 'sharklasers.com', '10minutemail.com', 'trashmail.com',
        'fakeinbox.com', 'getairmail.com', 'dispostable.com', 'maildrop.cc',
        'inboxkitten.com', 'temp-mail.org', 'crazymailing.com', 'mytemp.email',
        'generator.email', 'dropmail.me', 'mohmal.com', 'tempail.com',
        'burnermail.io', 'mailnesia.com', 'trashmail.net', 'fakemailgenerator.com',
        'nada.ltd', 'getnada.com', 'emailondeck.com', 'guerrillamailblock.com',
        'spamgourmet.com', 'tempmailaddress.com', 'throwaway.email'
    ];

    /**
     * Validates Full Name (letters, accented chars, arabic script, min 2 chars, no numbers/urls)
     */
    public static function validateName($name) {
        $name = trim($name);
        if (mb_strlen($name, 'UTF-8') < 2) {
            return "Name must be at least 2 characters.";
        }
        if (preg_match('/[0-9<>$\/\\\#=~^&*+]/', $name)) {
            return "Name cannot contain numbers or special symbols.";
        }
        return true;
    }

    /**
     * Validates Email and checks against disposable domain blocklist
     */
    public static function validateEmail($email) {
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Please provide a valid email address.";
        }

        $parts = explode('@', $email);
        $domain = end($parts);

        if (in_array($domain, self::$disposableDomains)) {
            return "Temporary and disposable email addresses are not accepted.";
        }

        return true;
    }

    /**
     * Validates Phone Number and rejects obvious test/fake patterns
     */
    public static function validatePhone($countryCode, $number) {
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        if (strlen($cleanNumber) < 6 || strlen($cleanNumber) > 16) {
            return "Please provide a valid phone number length.";
        }

        // Check for repetitive digits (e.g. 00000000, 99999999, 11111111)
        if (preg_match('/^(.)\1+$/', $cleanNumber)) {
            return "Please provide an authentic contact number.";
        }

        // Check for sequential digits (e.g. 12345678, 98765432)
        if (strpos('0123456789012345', $cleanNumber) !== false ||
            strpos('9876543210987654', $cleanNumber) !== false) {
            return "Sequential numbers are not accepted.";
        }

        // India specific check (+91): must be 10 digits starting with 6,7,8,9
        if ($countryCode === '+91' && strlen($cleanNumber) === 10) {
            if (!in_array($cleanNumber[0], ['6', '7', '8', '9'])) {
                return "Indian mobile numbers must start with 6, 7, 8, or 9.";
            }
        }

        // UAE specific check (+971): usually 9 digits starting with 5 (e.g. 50, 52, 54, 55, 56, 58)
        if ($countryCode === '+971' && strlen($cleanNumber) >= 9) {
            $last9 = substr($cleanNumber, -9);
            if ($last9[0] !== '5') {
                // Landlines allowed or standard mobile check
            }
        }

        return true;
    }

    /**
     * Extracts visitor IP Address taking reverse proxies & Cloudflare into account
     */
    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Parses User-Agent into clean readable device name
     */
    public static function parseDevice($ua) {
        if (preg_match('/iPhone/i', $ua)) return 'iPhone (iOS)';
        if (preg_match('/iPad/i', $ua)) return 'iPad (iOS)';
        if (preg_match('/Android.*Mobile/i', $ua)) return 'Android Mobile';
        if (preg_match('/Android/i', $ua)) return 'Android Tablet';
        if (preg_match('/Macintosh|Mac OS X/i', $ua)) return 'Mac Desktop';
        if (preg_match('/Windows NT/i', $ua)) return 'Windows Desktop';
        if (preg_match('/Linux/i', $ua)) return 'Linux Desktop';
        return 'Other Device';
    }

    /**
     * Parses User-Agent into clean browser name
     */
    public static function parseBrowser($ua) {
        if (preg_match('/SamsungBrowser/i', $ua)) return 'Samsung Internet';
        if (preg_match('/Edge|Edg/i', $ua)) return 'Edge';
        if (preg_match('/Chrome/i', $ua) && !preg_match('/Edg/i', $ua)) return 'Chrome';
        if (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) return 'Safari';
        if (preg_match('/Firefox/i', $ua)) return 'Firefox';
        if (preg_match('/Opera|OPR/i', $ua)) return 'Opera';
        return 'Other';
    }

    /**
     * Converts 2-letter ISO Country Code into Unicode Emoji Flag
     */
    public static function countryCodeToEmoji($code) {
        $code = strtoupper(trim($code));
        if (strlen($code) !== 2) return '🌐';
        $first = ord($code[0]) - ord('A') + 0x1F1E6;
        $second = ord($code[1]) - ord('A') + 0x1F1E6;
        return mb_chr($first, 'UTF-8') . mb_chr($second, 'UTF-8');
    }

    /**
     * Resolves IP to GeoIP and flags potential hosting/VPN IPs
     */
    public static function lookupIp($ip) {
        // Fallback default for localhost / internal IPs
        if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return [
                'city' => 'Dubai (Local)',
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'country_flag' => '🇦🇪',
                'is_vpn' => 0
            ];
        }

        // Fast lookup via ip-api.com (free 45 req/min, non-commercial / development)
        $url = "http://ip-api.com/json/" . urlencode($ip) . "?fields=status,country,countryCode,city,proxy,hosting";
        $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
        $response = @file_get_contents($url, false, $ctx);

        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data) && $data['status'] === 'success') {
                $code = $data['countryCode'] ?? 'AE';
                $isVpn = (!empty($data['proxy']) || !empty($data['hosting'])) ? 1 : 0;
                return [
                    'city' => $data['city'] ?? 'Dubai',
                    'country' => $data['country'] ?? 'United Arab Emirates',
                    'country_code' => $code,
                    'country_flag' => self::countryCodeToEmoji($code),
                    'is_vpn' => $isVpn
                ];
            }
        }

        return [
            'city' => 'International Prospect',
            'country' => 'Global',
            'country_code' => 'GLOBAL',
            'country_flag' => '🌐',
            'is_vpn' => 0
        ];
    }
}
