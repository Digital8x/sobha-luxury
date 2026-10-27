<?php
/**
 * SOBHA REALTY LUXURY - ADMIN BLOCKED THREATS LOG
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../api/config.php';

$db = getDbConnection();
$blocked = [];
$totalBlocked = 0;

if ($db) {
    $totalBlocked = (int)$db->query("SELECT COUNT(*) FROM blocked_leads")->fetchColumn();
    $stmt = $db->query("SELECT * FROM blocked_leads ORDER BY id DESC LIMIT 150");
    $blocked = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blocked Threats | Sobha Realty Luxury CRM</title>
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
      <a href="leads.php" class="nav-item">Leads Inbox</a>
      <a href="blocked.php" class="nav-item active">Blocked Threats (<?php echo $totalBlocked; ?>)</a>
      <a href="export.php" class="nav-item">Export CSV</a>
    </nav>

    <div class="nav-user">
      <span class="user-name"><?php echo htmlspecialchars($_SESSION['sobha_admin_name'] ?? 'Admin'); ?></span>
      <a href="logout.php" class="logout-link">Sign Out</a>
    </div>
  </header>

  <main class="admin-container">
    <div class="detail-header">
      <div>
        <span class="detail-eyebrow">AUTOMATED ANTI-FAKE DEFENSE LOG</span>
        <h1 class="detail-title">Blocked Spam & Bot Submissions</h1>
        <p class="detail-sub">Real-time audit log of trapped honeypot bots, speed scripts, disposable email domains, and rate-limited attackers.</p>
      </div>
    </div>

    <section class="table-container">
      <?php if (empty($blocked)): ?>
        <div class="empty-state">
          <div class="empty-icon">🛡️</div>
          <h3>No Blocked Attacks Recorded</h3>
          <p>Your anti-fake filters are active and waiting to deflect automated bot traffic.</p>
        </div>
      <?php else: ?>
        <table class="leads-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>TRIGGERED REASON</th>
              <th>IP ADDRESS</th>
              <th>SUBMITTED DATA DUMP</th>
              <th>BLOCKED AT</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($blocked as $row): ?>
              <tr>
                <td class="lead-id">#<?php echo htmlspecialchars($row['id']); ?></td>
                <td>
                  <span class="threat-badge"><?php echo htmlspecialchars($row['block_reason'] ?? 'Flagged Activity'); ?></span>
                </td>
                <td>
                  <code><?php echo htmlspecialchars($row['ip_address']); ?></code>
                </td>
                <td>
                  <pre class="dump-code"><?php echo htmlspecialchars(json_encode(json_decode($row['payload_dump'] ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                </td>
                <td class="date-col">
                  <?php echo date('d M Y, H:i:s', strtotime($row['attempted_at'] ?? 'now')); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
