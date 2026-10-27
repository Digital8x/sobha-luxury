<?php
/**
 * SOBHA REALTY LUXURY - LEADS CSV EXPORTER
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../api/config.php';

$db = getDbConnection();
if (!$db) {
    die("Database connection required for CSV export.");
}

$search = trim($_GET['search'] ?? '');
$projectFilter = trim($_GET['project'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(full_name LIKE :s OR email LIKE :s OR phone_number LIKE :s)";
    $params[':s'] = "%$search%";
}

if (!empty($projectFilter)) {
    $where[] = "development = :project";
    $params[':project'] = $projectFilter;
}

if (!empty($statusFilter)) {
    $where[] = "status = :status";
    $params[':status'] = $statusFilter;
}

$sql = "SELECT id, reference_code, full_name, email, phone_country_code, phone_number, development, status, country, city, ip_address, device_type, browser, utm_source, utm_medium, utm_campaign, source_button, created_at FROM leads WHERE " . implode(" AND ", $where) . " ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "Sobha_Luxury_Leads_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
// Output UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// CSV Header
fputcsv($output, [
    'Lead ID',
    'Reference Code',
    'Full Name',
    'Email Address',
    'Country Code',
    'Phone Number',
    'Development',
    'Status',
    'Country',
    'City',
    'Client IP',
    'Device Type',
    'Browser',
    'UTM Source',
    'UTM Medium',
    'UTM Campaign',
    'Source CTA',
    'Created At'
]);

foreach ($leads as $row) {
    fputcsv($output, [
        $row['id'],
        $row['reference_code'],
        $row['full_name'],
        $row['email'],
        $row['phone_country_code'],
        $row['phone_number'],
        $row['development'],
        $row['status'],
        $row['country'],
        $row['city'],
        $row['ip_address'],
        $row['device_type'],
        $row['browser'],
        $row['utm_source'],
        $row['utm_medium'],
        $row['utm_campaign'],
        $row['source_button'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
