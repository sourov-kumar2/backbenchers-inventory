<?php
require 'auth.php';
require 'config.php';

$api_key = $sys['fraud_api_key'] ?? '';
$search_phone = $_GET['phone'] ?? '';
$fraud_data = null;
$fetch_error = '';

if ($search_phone && $api_key) {
    $url = "https://fraudbd.com/api/check-courier-info";
    $ch = curl_init($url);
    
    $payload = json_encode(['phone_number' => $search_phone]);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api_key: ' . $api_key
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $fraud_data = json_decode($response, true);
    } else {
        $fetch_error = "API Error: " . ($response ?: "Connection timeout (HTTP $http_code)");
    }
} elseif ($search_phone && !$api_key) {
    $fetch_error = "System Configuration Error: Fraud API Key is not set. Please update in Settings.";
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Fraud Intelligence';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            

            <!-- Search Console -->
            <div class="checker-console animate-fade-in">
                <div class="search-card glass">
                    <form method="GET" action="" class="checker-form">
                        <div class="input-with-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <input type="text" name="phone" class="phone-input" placeholder="Enter Customer Phone Number (017...)" value="<?= htmlspecialchars($search_phone) ?>" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary">Run Intelligence Check</button>
                    </form>
                </div>
            </div>

            <?php if ($fetch_error): ?>
                <div class="alert-error-box glass animate-fade-in">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= htmlspecialchars($fetch_error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($fraud_data && isset($fraud_data['status']) && $fraud_data['status']): ?>
                <div class="results-layout animate-fade-in">
                    <!-- Main Scoreboard -->
                    <div class="score-card-primary glass">
                        <div class="score-header">
                            <span class="score-label">Global Success Rate</span>
                            <div class="score-percent <?= $fraud_data['data']['totalSummary']['successRate'] > 75 ? 'text-success' : 'text-warning' ?>">
                                <?= $fraud_data['data']['totalSummary']['successRate'] ?>%
                            </div>
                        </div>
                        <div class="score-stats">
                            <div class="stat-item">
                                <span class="stat-val"><?= $fraud_data['data']['totalSummary']['total'] ?></span>
                                <span class="stat-label">Total Orders</span>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <span class="stat-val text-success"><?= $fraud_data['data']['totalSummary']['success'] ?></span>
                                <span class="stat-label">Delivered</span>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <span class="stat-val text-danger"><?= $fraud_data['data']['totalSummary']['cancel'] ?></span>
                                <span class="stat-label">Canceled</span>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Breakdown -->
                    <div class="breakdown-grid">
                        <?php foreach ($fraud_data['data']['Summaries'] as $name => $info): ?>
                            <div class="courier-card glass">
                                <div class="courier-header">
                                    <div class="courier-logo-box">
                                        <img src="<?= $info['logo'] ?>" alt="<?= $name ?>" onerror="this.style.display='none'">
                                        <span class="courier-name"><?= $name ?></span>
                                    </div>
                                    <?php if (isset($info['risk_level'])): ?>
                                        <span class="risk-badge risk-<?= $info['risk_level'] ?>"><?= strtoupper($info['risk_level']) ?> RISK</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($info['data_type'] === 'rating'): ?>
                                    <div class="rating-box">
                                        <p class="rating-text"><?= htmlspecialchars($info['message']) ?></p>
                                        <span class="rating-label"><?= ucwords(str_replace('_', ' ', $info['customer_rating'])) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="delivery-stats">
                                        <div class="d-stat">
                                            <span class="d-val"><?= $info['total'] ?></span>
                                            <span class="d-label">Total</span>
                                        </div>
                                        <div class="d-stat">
                                            <span class="d-val text-success"><?= $info['success'] ?></span>
                                            <span class="d-label">Success</span>
                                        </div>
                                        <div class="d-stat">
                                            <span class="d-val text-danger"><?= $info['cancel'] ?></span>
                                            <span class="d-label">Cancel</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($search_phone && !$fetch_error): ?>
                <div class="alert-info-box glass animate-fade-in">
                    <p>No historical courier data found for this phone number.</p>
                </div>
            <?php endif; ?>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .checker-console { margin-bottom: 3rem; }
    .search-card { padding: 1.5rem 2rem; border-radius: 20px; }
    .checker-form { display: flex; gap: 1.5rem; align-items: center; }
    .input-with-icon { flex: 1; position: relative; display: flex; align-items: center; }
    .input-with-icon svg { position: absolute; left: 1.25rem; color: var(--text-dim); }
    .phone-input { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1rem 1rem 3.5rem; color: white; font-size: 1.1rem; outline: none; transition: 0.2s; }
    .phone-input:focus { border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); }

    .results-layout { display: grid; gap: 2rem; }
    .score-card-primary { padding: 2.5rem; border-radius: 24px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(0,0,0,0)); text-align: center; }
    .score-header { margin-bottom: 2rem; }
    .score-label { color: var(--text-dim); font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; }
    .score-percent { font-size: 4rem; font-weight: 800; }
    
    .score-stats { display: flex; justify-content: center; gap: 3rem; align-items: center; }
    .stat-item { display: flex; flex-direction: column; }
    .stat-val { font-size: 1.75rem; font-weight: 700; color: var(--text-primary); }
    .stat-label { font-size: 0.8rem; color: var(--text-dim); }
    .stat-divider { width: 1px; height: 40px; background: var(--border-color); }

    .breakdown-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
    .courier-card { padding: 1.5rem; border-radius: 18px; display: flex; flex-direction: column; gap: 1.25rem; }
    .courier-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .courier-logo-box { display: flex; align-items: center; gap: 0.75rem; }
    .courier-logo-box img { height: 24px; border-radius: 4px; }
    .courier-name { font-weight: 700; color: var(--text-primary); }
    
    .risk-badge { font-size: 0.65rem; font-weight: 800; padding: 0.35rem 0.6rem; border-radius: 6px; letter-spacing: 0.05em; }
    .risk-low { background: rgba(16, 185, 129, 0.1); color: #6ee7b7; }
    .risk-medium { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
    .risk-high { background: rgba(239, 68, 68, 0.1); color: #fca5a5; }

    .rating-box { text-align: center; padding: 0.5rem 0; }
    .rating-text { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; }
    .rating-label { font-size: 0.8rem; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; }

    .delivery-stats { display: flex; justify-content: space-between; background: rgba(0,0,0,0.15); padding: 1rem; border-radius: 12px; }
    .d-stat { display: flex; flex-direction: column; align-items: center; }
    .d-val { font-size: 1.1rem; font-weight: 700; }
    .d-label { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; }

    .alert-error-box { padding: 1.25rem; border-radius: 14px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; }
    .alert-info-box { padding: 3rem; text-align: center; color: var(--text-dim); border-radius: 20px; }

    .text-success { color: #10b981 !important; }
    .text-danger { color: #ef4444 !important; }
    .text-warning { color: #f59e0b !important; }

    @media (max-width: 768px) {
        .checker-form { flex-direction: column; }
        .checker-form button { width: 100%; }
        .score-stats { gap: 1rem; }
    }
    </style>
</body>
</html>
