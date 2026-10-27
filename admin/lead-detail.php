<?php
/**
 * SOBHA REALTY LUXURY - LEAD DETAIL INTELLIGENCE VIEW
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../api/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: leads.php');
    exit;
}

$db = getDbConnection();
$lead = null;
if ($db) {
    $stmt = $db->prepare("SELECT * FROM leads WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $lead = $stmt->fetch();
}

if (!$lead) {
    echo "Enquiry not found or database not initialized.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lead #<?php echo $lead['id']; ?> | Sobha Luxury Intelligence</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">
  <header class="admin-navbar">
    <div class="nav-brand">
      <div class="brand-crest">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
      </div>
      <div>
        <span class="brand-title">SOBHA REALTY</span>
        <span class="brand-sub">LUXURY CRM</span>
      </div>
    </div>
    <nav class="nav-links">
      <a href="leads.php" class="nav-item">← Back to Leads Inbox</a>
    </nav>
    <div class="nav-user">
      <span class="user-name"><?php echo htmlspecialchars($_SESSION['sobha_admin_name'] ?? 'Admin'); ?></span>
      <a href="logout.php" class="logout-link">Sign Out</a>
    </div>
  </header>

  <main class="admin-container">
    <div class="detail-header">
      <div>
        <span class="detail-eyebrow">ENQUIRY INTELLIGENCE DOSSIER</span>
        <h1 class="detail-title"><?php echo htmlspecialchars($lead['full_name']); ?></h1>
        <p class="detail-sub">Submitted at <?php echo date('d F Y, H:i:s', strtotime($lead['created_at'])); ?> • Reference #SBH-<?php echo str_pad($lead['id'], 5, '0', STR_PAD_LEFT); ?></p>
      </div>
      <div class="detail-actions">
        <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>" class="btn-export">Email Client</a>
        <a href="https://wa.me/<?php echo preg_replace('/[^\d]/', '', $lead['phone_country_code'] . $lead['phone_number']); ?>" target="_blank" class="btn-whatsapp">WhatsApp</a>
      </div>
    </div>

    <div class="detail-grid">
      <!-- Contact Information -->
      <div class="detail-card">
        <h3>Contact & Residence Interest</h3>
        <table class="info-table">
          <tr>
            <td>Client Name:</td>
            <td><strong><?php echo htmlspecialchars($lead['full_name']); ?></strong></td>
          </tr>
          <tr>
            <td>Phone Number:</td>
            <td><strong><?php echo htmlspecialchars($lead['phone_country_code'] . ' ' . $lead['phone_number']); ?></strong></td>
          </tr>
          <tr>
            <td>Email Address:</td>
            <td><a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>"><?php echo htmlspecialchars($lead['email']); ?></a></td>
          </tr>
          <tr>
            <td>Development of Interest:</td>
            <td><span class="dev-badge"><?php echo htmlspecialchars($lead['development']); ?></span></td>
          </tr>
          <tr>
            <td>Status:</td>
            <td>
              <select class="status-select status-<?php echo strtolower($lead['status']); ?>" onchange="updateLeadStatus(<?php echo $lead['id']; ?>, this.value)">
                <option value="New" <?php echo $lead['status'] === 'New' ? 'selected' : ''; ?>>New</option>
                <option value="Contacted" <?php echo $lead['status'] === 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                <option value="Qualified" <?php echo $lead['status'] === 'Qualified' ? 'selected' : ''; ?>>Qualified</option>
                <option value="Converted" <?php echo $lead['status'] === 'Converted' ? 'selected' : ''; ?>>Converted</option>
                <option value="Spam" <?php echo $lead['status'] === 'Spam' ? 'selected' : ''; ?>>Spam</option>
              </select>
            </td>
          </tr>
        </table>
      </div>

      <!-- Geo & Technical Intelligence -->
      <div class="detail-card">
        <h3>Geolocation & Device Telemetry</h3>
        <table class="info-table">
          <tr>
            <td>Country & Flag:</td>
            <td><?php echo htmlspecialchars(($lead['country_flag'] ?? '🌐') . ' ' . ($lead['country'] ?: 'Global')); ?> (<?php echo htmlspecialchars($lead['country_code'] ?? 'AE'); ?>)</td>
          </tr>
          <tr>
            <td>City:</td>
            <td><?php echo htmlspecialchars($lead['city'] ?: 'Unknown / Direct'); ?></td>
          </tr>
          <tr>
            <td>Client IP Address:</td>
            <td><code><?php echo htmlspecialchars($lead['ip_address']); ?></code></td>
          </tr>
          <tr>
            <td>Device & Browser:</td>
            <td><?php echo htmlspecialchars(($lead['device_type'] ?? 'Desktop') . ' • ' . ($lead['browser'] ?? 'Browser')); ?></td>
          </tr>
          <tr>
            <td>VPN / Proxy Detected:</td>
            <td><?php echo !empty($lead['is_vpn']) ? '<span class="vpn-alert">YES (VPN/Proxy Detected)</span>' : '<span class="clean-pill">Clean Residential/Cellular</span>'; ?></td>
          </tr>
        </table>
      </div>

      <!-- Acquisition & Attribution -->
      <div class="detail-card full-width">
        <h3>Marketing Attribution & Traffic Origins</h3>
        <table class="info-table">
          <tr>
            <td>Landing Page URL:</td>
            <td><a href="<?php echo htmlspecialchars($lead['landing_page'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($lead['landing_page'] ?? ''); ?></a></td>
          </tr>
          <tr>
            <td>Referring Website:</td>
            <td><?php echo htmlspecialchars($lead['referrer_url'] ?: 'Direct Visit / No Referrer'); ?></td>
          </tr>
          <tr>
            <td>Source CTA Trigger:</td>
            <td><?php echo htmlspecialchars($lead['source_button']); ?></td>
          </tr>
          <tr>
            <td>Campaign (UTM Source):</td>
            <td><?php echo htmlspecialchars($lead['utm_source'] ?: '—'); ?></td>
          </tr>
          <tr>
            <td>Medium & Campaign Name:</td>
            <td><?php echo htmlspecialchars(($lead['utm_medium'] ?: '—') . ' / ' . ($lead['utm_campaign'] ?: '—')); ?></td>
          </tr>
          <tr>
            <td>User-Agent Header:</td>
            <td style="font-size:12px; color:#999; word-break:break-all;"><?php echo htmlspecialchars($lead['user_agent']); ?></td>
          </tr>
        </table>
      </div>
    </div>
  </main>

  <script src="assets/admin.js"></script>
</body>
</html>
