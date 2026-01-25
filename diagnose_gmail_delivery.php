<?php
/**
 * Gmail Delivery Diagnostic Tool
 * Checks SPF, DKIM, DMARC records and provides recommendations
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','super_admin'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

// Get sender email from config
$senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : '';
$senderDomain = $senderEmail ? substr(strrchr($senderEmail, "@"), 1) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Delivery Diagnostic - D'MARSIANS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-info { color: #17a2b8; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }
        .config-value { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .check-item { margin-bottom: 20px; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; }
        .check-item h5 { margin-bottom: 10px; }
        .recommendation { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 15px; }
        .critical { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <h1><i class="fas fa-envelope-open-text"></i> Gmail Delivery Diagnostic Tool</h1>
        <p class="text-muted">Diagnosing email delivery issues to Gmail accounts</p>
        
        <?php if (empty($senderEmail) || $senderEmail === 'your_email@example.com'): ?>
            <div class="alert alert-danger">
                <strong>⚠️ Configuration Missing:</strong> SMTP2GO_SENDER_EMAIL is not configured. Please set it in your environment variables.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <strong>Sender Email:</strong> <code><?php echo htmlspecialchars($senderEmail); ?></code><br>
                <strong>Sender Domain:</strong> <code><?php echo htmlspecialchars($senderDomain); ?></code>
            </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3><i class="fas fa-search"></i> DNS Record Checks</h3>
            </div>
            <div class="card-body">
                <?php if (empty($senderDomain)): ?>
                    <div class="alert alert-warning">
                        Cannot check DNS records: Sender domain not found in configuration.
                    </div>
                <?php else: ?>
                    <div class="check-item">
                        <h5><i class="fas fa-shield-alt"></i> SPF Record Check</h5>
                        <p><strong>Domain:</strong> <code><?php echo htmlspecialchars($senderDomain); ?></code></p>
                        <p><strong>Status:</strong> <span class="status-info">Manual Check Required</span></p>
                        <p>SPF (Sender Policy Framework) records tell email servers which servers are authorized to send emails for your domain.</p>
                        <div class="recommendation">
                            <strong>📋 How to Check:</strong>
                            <ol>
                                <li>Go to: <a href="https://mxtoolbox.com/spf.aspx" target="_blank">https://mxtoolbox.com/spf.aspx</a></li>
                                <li>Enter domain: <code><?php echo htmlspecialchars($senderDomain); ?></code></li>
                                <li>Look for SPF record that includes: <code>include:spf.smtp2go.com</code></li>
                            </ol>
                            <strong>✅ Expected SPF Record:</strong>
                            <pre>v=spf1 include:spf.smtp2go.com ~all</pre>
                            <strong>⚠️ If SPF record is missing or doesn't include SMTP2Go:</strong>
                            <ul>
                                <li>Contact domain administrator for <code><?php echo htmlspecialchars($senderDomain); ?></code></li>
                                <li>Request them to add SMTP2Go to SPF record</li>
                                <li>This requires DNS access to the domain</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="check-item">
                        <h5><i class="fas fa-key"></i> DKIM Record Check</h5>
                        <p><strong>Domain:</strong> <code><?php echo htmlspecialchars($senderDomain); ?></code></p>
                        <p><strong>Status:</strong> <span class="status-info">Check in SMTP2Go Dashboard</span></p>
                        <p>DKIM (DomainKeys Identified Mail) provides email authentication using cryptographic signatures.</p>
                        <div class="recommendation">
                            <strong>📋 How to Check:</strong>
                            <ol>
                                <li>Log into SMTP2Go: <a href="https://app.smtp2go.com/" target="_blank">https://app.smtp2go.com/</a></li>
                                <li>Go to: <strong>Settings</strong> → <strong>Domain Authentication</strong></li>
                                <li>Check if DKIM is configured for <code><?php echo htmlspecialchars($senderDomain); ?></code></li>
                            </ol>
                            <strong>⚠️ If DKIM is not configured:</strong>
                            <ul>
                                <li>SMTP2Go may be signing with their own domain (less trusted by Gmail)</li>
                                <li>Contact domain administrator to set up DKIM</li>
                                <li>Or consider using a custom domain you control</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="check-item">
                        <h5><i class="fas fa-lock"></i> DMARC Record Check</h5>
                        <p><strong>Domain:</strong> <code><?php echo htmlspecialchars($senderDomain); ?></code></p>
                        <p><strong>Status:</strong> <span class="status-info">Manual Check Required</span></p>
                        <p>DMARC (Domain-based Message Authentication, Reporting & Conformance) provides policy for handling emails that fail SPF/DKIM.</p>
                        <div class="recommendation">
                            <strong>📋 How to Check:</strong>
                            <ol>
                                <li>Go to: <a href="https://mxtoolbox.com/dmarc.aspx" target="_blank">https://mxtoolbox.com/dmarc.aspx</a></li>
                                <li>Enter domain: <code><?php echo htmlspecialchars($senderDomain); ?></code></li>
                                <li>Check if DMARC record exists</li>
                            </ol>
                            <strong>✅ Recommended DMARC Record (for monitoring):</strong>
                            <pre>v=DMARC1; p=none; rua=mailto:admin@<?php echo htmlspecialchars($senderDomain); ?></pre>
                            <p class="text-muted small">Note: DMARC is optional but recommended for better deliverability.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h3><i class="fas fa-exclamation-triangle"></i> Common Issues & Solutions</h3>
            </div>
            <div class="card-body">
                <div class="critical">
                    <h5><i class="fas fa-times-circle"></i> Issue 1: SPF Record Missing or Incorrect</h5>
                    <p><strong>Symptom:</strong> Gmail silently drops emails, SMTP2Go shows "sent" but emails never arrive.</p>
                    <p><strong>Solution:</strong></p>
                    <ul>
                        <li>Verify SPF record includes SMTP2Go: <code>include:spf.smtp2go.com</code></li>
                        <li>Contact domain administrator to update DNS records</li>
                        <li><strong>Alternative:</strong> Use a custom domain you control, or use SMTP2Go's shared domain</li>
                    </ul>
                </div>
                
                <div class="recommendation mt-3">
                    <h5><i class="fas fa-lightbulb"></i> Issue 2: Domain Reputation</h5>
                    <p><strong>Symptom:</strong> Educational domains (like <code>.edu.ph</code>) may have reputation issues.</p>
                    <p><strong>Solution:</strong></p>
                    <ul>
                        <li>Check Gmail Postmaster Tools: <a href="https://postmaster.google.com/" target="_blank">https://postmaster.google.com/</a></li>
                        <li>Verify domain reputation and spam rate</li>
                        <li>Consider using a custom domain (e.g., <code>dmarsians.com</code>)</li>
                    </ul>
                </div>
                
                <div class="recommendation mt-3">
                    <h5><i class="fas fa-tachometer-alt"></i> Issue 3: Bulk Sending Pattern</h5>
                    <p><strong>Symptom:</strong> Sending many emails at once triggers Gmail's spam filters.</p>
                    <p><strong>Solution:</strong></p>
                    <ul>
                        <li>✅ <strong>FIXED:</strong> Rate limiting has been implemented (5 emails per batch, 2-second delays)</li>
                        <li>Emails are now sent with delays to avoid triggering spam filters</li>
                        <li>Monitor delivery rates and adjust batch size if needed</li>
                    </ul>
                </div>
                
                <div class="recommendation mt-3">
                    <h5><i class="fas fa-ban"></i> Issue 4: SMTP2Go Suppression List</h5>
                    <p><strong>Symptom:</strong> Some recipients never receive emails, others do.</p>
                    <p><strong>Solution:</strong></p>
                    <ul>
                        <li>Log into SMTP2Go dashboard</li>
                        <li>Go to: <strong>Reports</strong> → <strong>Suppressions</strong></li>
                        <li>Check if recipient emails are on suppression list (bounced, complained, unsubscribed)</li>
                        <li>Remove from suppression list if needed</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h3><i class="fas fa-tools"></i> Diagnostic Tools</h3>
            </div>
            <div class="card-body">
                <h5>Online Tools:</h5>
                <ul>
                    <li><strong>SPF Checker:</strong> <a href="https://mxtoolbox.com/spf.aspx" target="_blank">https://mxtoolbox.com/spf.aspx</a></li>
                    <li><strong>DKIM Checker:</strong> <a href="https://mxtoolbox.com/dkim.aspx" target="_blank">https://mxtoolbox.com/dkim.aspx</a></li>
                    <li><strong>DMARC Checker:</strong> <a href="https://mxtoolbox.com/dmarc.aspx" target="_blank">https://mxtoolbox.com/dmarc.aspx</a></li>
                    <li><strong>Email Header Analyzer:</strong> <a href="https://mxtoolbox.com/emailhealth/" target="_blank">https://mxtoolbox.com/emailhealth/</a></li>
                    <li><strong>Gmail Postmaster Tools:</strong> <a href="https://postmaster.google.com/" target="_blank">https://postmaster.google.com/</a></li>
                </ul>
                
                <h5 class="mt-4">SMTP2Go Dashboard:</h5>
                <ul>
                    <li><strong>Activity Logs:</strong> <a href="https://app.smtp2go.com/reports/activity" target="_blank">https://app.smtp2go.com/reports/activity</a></li>
                    <li><strong>Suppressions:</strong> <a href="https://app.smtp2go.com/reports/suppressions" target="_blank">https://app.smtp2go.com/reports/suppressions</a></li>
                    <li><strong>Verified Senders:</strong> <a href="https://app.smtp2go.com/settings/verified_senders" target="_blank">https://app.smtp2go.com/settings/verified_senders</a></li>
                    <li><strong>Domain Authentication:</strong> <a href="https://app.smtp2go.com/settings/domain_authentication" target="_blank">https://app.smtp2go.com/settings/domain_authentication</a></li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h3><i class="fas fa-check-circle"></i> Quick Test</h3>
            </div>
            <div class="card-body">
                <p><strong>Test with Different Sender Email:</strong></p>
                <p>To isolate whether the issue is domain-specific:</p>
                <ol>
                    <li>In SMTP2Go, verify a Gmail address (e.g., <code>helmandacuma5@gmail.com</code>)</li>
                    <li>Temporarily change <code>SMTP2GO_SENDER_EMAIL</code> to the Gmail address</li>
                    <li>Send a test reminder</li>
                    <li><strong>If Gmail address works:</strong> Domain/DNS issue confirmed → Fix SPF/DKIM</li>
                    <li><strong>If Gmail address also fails:</strong> SMTP2Go configuration issue → Check API key, account status</li>
                </ol>
                
                <div class="alert alert-warning mt-3">
                    <strong>⚠️ Important:</strong> After testing, remember to change the sender email back to the original value.
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="check_email_config.php" class="btn btn-primary"><i class="fas fa-envelope"></i> Email Configuration Check</a>
            <a href="GMAIL_DELIVERY_TROUBLESHOOTING.md" class="btn btn-info" target="_blank"><i class="fas fa-book"></i> Full Troubleshooting Guide</a>
        </div>
    </div>
</body>
</html>
