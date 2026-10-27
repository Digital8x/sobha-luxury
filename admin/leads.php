<?php
/**
 * SOBHA REALTY LUXURY - ADMIN LEADS DASHBOARD
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../api/config.php';

$db = getDbConnection();

// Query Filters
$search = trim($_GET['search'] ?? '');
$projectFilter = trim($_GET['project'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$countryFilter = trim($_GET['country'] ?? '');

$leads = [];
$totalLeads = 0;
$todayLeads = 0;
$contactedCount = 0;
$convertedCount = 0;

if ($db) {
    // Metrics
    $totalLeads = (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    $todayLeads = (int)$db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $contactedCount = (int)$db->query("SELECT COUNT(*) FROM leads WHERE status = 'Contacted'")->fetchColumn();
    $convertedCount = (int)$db->query("SELECT COUNT(*) FROM leads WHERE status = 'Converted'")->fetchColumn();

    // Base Filter Query
    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(full_name LIKE :s OR email LIKE :s OR phone_number LIKE :s OR reference_code LIKE :s OR id = :id_s)";
        $params[':s'] = "%$search%";
        $params[':id_s'] = (int)$search;
    }

    if (!empty($projectFilter)) {
        $where[] = "development = :project";
        $params[':project'] = $projectFilter;
    }

    if (!empty($statusFilter)) {
        $where[] = "status = :status";
        $params[':status'] = $statusFilter;
    }

    if (!empty($countryFilter)) {
        $where[] = "country_code = :country";
        $params[':country'] = $countryFilter;
    }

    $sql = "SELECT * FROM leads WHERE " . implode(" AND ", $where) . " ORDER BY id DESC LIMIT 200";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $leads = $stmt->fetchAll();
} else {
    // If DB is offline, read from localStorage backup if present in session or show empty state
    $leads = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leads Management | Sobha Realty Luxury CRM</title>
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
      <a href="leads.php" class="nav-item active">Leads Inbox</a>
      <a href="blocked.php" class="nav-item">Blocked Threats</a>
      <a href="export.php" class="nav-item">Export CSV</a>
    </nav>

    <div class="nav-user">
      <span class="user-name"><?php echo htmlspecialchars($_SESSION['sobha_admin_name'] ?? 'Admin'); ?></span>
      <a href="logout.php" class="logout-link" title="Sign Out">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      </a>
    </div>
  </header>

  <main class="admin-container">
    <!-- Metrics Row -->
    <section class="metrics-grid">
      <div class="metric-card">
        <div class="metric-label">TOTAL LEADS</div>
        <div class="metric-val"><?php echo number_format($totalLeads); ?></div>
        <div class="metric-sub">Global enquiries received</div>
      </div>
      <div class="metric-card highlight">
        <div class="metric-label">TODAY'S ENQUIRIES</div>
        <div class="metric-val"><?php echo number_format($todayLeads); ?></div>
        <div class="metric-sub">Past 24 hours</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">IN CONTACT</div>
        <div class="metric-val"><?php echo number_format($contactedCount); ?></div>
        <div class="metric-sub">Under active advisory</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">CONVERTED / CLOSED</div>
        <div class="metric-val"><?php echo number_format($convertedCount); ?></div>
        <div class="metric-sub">Reserved properties</div>
      </div>
    </section>

    <!-- Filters & Action Bar -->
    <section class="filter-bar">
      <form method="GET" action="leads.php" class="filter-form">
        <div class="search-box">
          <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, email, phone, or ID...">
          <button type="submit" class="search-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </button>
        </div>

        <select name="project" onchange="this.form.submit()">
          <option value="">All Developments</option>
          <option value="SOBHA Solis" <?php echo $projectFilter === 'SOBHA Solis' ? 'selected' : ''; ?>>SOBHA Solis</option>
          <option value="SOBHA Orbis" <?php echo $projectFilter === 'SOBHA Orbis' ? 'selected' : ''; ?>>SOBHA Orbis</option>
          <option value="SOBHA Sanctuary Villas" <?php echo $projectFilter === 'SOBHA Sanctuary Villas' ? 'selected' : ''; ?>>SOBHA Sanctuary Villas</option>
          <option value="SOBHA Central" <?php echo $projectFilter === 'SOBHA Central' ? 'selected' : ''; ?>>SOBHA Central</option>
          <option value="SOBHA Siniya Island" <?php echo $projectFilter === 'SOBHA Siniya Island' ? 'selected' : ''; ?>>SOBHA Siniya Island</option>
        </select>

        <select name="status" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="New" <?php echo $statusFilter === 'New' ? 'selected' : ''; ?>>New</option>
          <option value="Contacted" <?php echo $statusFilter === 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
          <option value="Qualified" <?php echo $statusFilter === 'Qualified' ? 'selected' : ''; ?>>Qualified</option>
          <option value="Converted" <?php echo $statusFilter === 'Converted' ? 'selected' : ''; ?>>Converted</option>
          <option value="Spam" <?php echo $statusFilter === 'Spam' ? 'selected' : ''; ?>>Spam</option>
        </select>

        <?php if (!empty($search) || !empty($projectFilter) || !empty($statusFilter)): ?>
          <a href="leads.php" class="btn-reset">Reset Filters</a>
        <?php endif; ?>
      </form>

      <div class="actions-right">
        <a href="export.php?<?php echo http_build_query($_GET); ?>" class="btn-export">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span>Export CSV</span>
        </a>
      </div>
    </section>

    <!-- Leads Table -->
    <section class="table-container">
      <?php if (empty($leads)): ?>
        <div class="empty-state">
          <div class="empty-icon">📂</div>
          <h3>No Enquiries Found</h3>
          <p>Try modifying your search criteria or submit a test enquiry on any residence page.</p>
        </div>
      <?php else: ?>
        <table class="leads-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>CLIENT / INVESTOR</th>
              <th>CONTACT DETAILS</th>
              <th>DEVELOPMENT</th>
              <th>LOCATION</th>
              <th>ACQUISITION SOURCE</th>
              <th>STATUS</th>
              <th>RECEIVED AT</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leads as $lead): ?>
              <tr>
                <td class="lead-id">#<?php echo htmlspecialchars($lead['id']); ?></td>
                <td>
                  <div class="client-name"><?php echo htmlspecialchars($lead['full_name']); ?></div>
                  <div class="client-sub"><?php echo htmlspecialchars($lead['device_type'] ?? 'Desktop'); ?> • <?php echo htmlspecialchars($lead['browser'] ?? 'Browser'); ?></div>
                </td>
                <td>
                  <div class="contact-phone">
                    <span class="flag-pill"><?php echo htmlspecialchars($lead['country_flag'] ?? '🌐'); ?></span>
                    <strong><?php echo htmlspecialchars($lead['phone_country_code'] . ' ' . $lead['phone_number']); ?></strong>
                  </div>
                  <div class="contact-email">
                    <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>"><?php echo htmlspecialchars($lead['email']); ?></a>
                  </div>
                </td>
                <td>
                  <span class="dev-badge"><?php echo htmlspecialchars($lead['development']); ?></span>
                </td>
                <td>
                  <div class="geo-loc">
                    <?php echo htmlspecialchars(($lead['city'] ? $lead['city'] . ', ' : '') . ($lead['country'] ?: 'Global')); ?>
                  </div>
                  <div class="ip-addr"><?php echo htmlspecialchars($lead['ip_address']); ?></div>
                </td>
                <td>
                  <div class="source-tag"><?php echo htmlspecialchars($lead['source_button'] ?: 'Modal CTA'); ?></div>
                  <?php if (!empty($lead['utm_source'])): ?>
                    <div class="utm-mini">UTM: <?php echo htmlspecialchars($lead['utm_source'] . ' / ' . ($lead['utm_campaign'] ?: 'direct')); ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <select class="status-select status-<?php echo strtolower($lead['status']); ?>" onchange="updateLeadStatus(<?php echo $lead['id']; ?>, this.value)">
                    <option value="New" <?php echo $lead['status'] === 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="Contacted" <?php echo $lead['status'] === 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="Qualified" <?php echo $lead['status'] === 'Qualified' ? 'selected' : ''; ?>>Qualified</option>
                    <option value="Converted" <?php echo $lead['status'] === 'Converted' ? 'selected' : ''; ?>>Converted</option>
                    <option value="Spam" <?php echo $lead['status'] === 'Spam' ? 'selected' : ''; ?>>Spam</option>
                  </select>
                </td>
                <td class="date-col">
                  <?php echo date('d M Y, H:i', strtotime($lead['created_at'])); ?>
                </td>
                <td>
                  <a href="lead-detail.php?id=<?php echo $lead['id']; ?>" class="view-btn" title="View Full Intelligence">
                    Inspect
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>

  <script src="assets/admin.js"></script>
</body>
</html>
