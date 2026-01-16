<?php
include '../includes/session-check.php';

// Only admins can access settings
// Note: requirePermission() always exists (defined as fallback in session-check.php)
requirePermission('manage_settings');

$pageTitle = 'System Settings - Bluedots Technologies';

// Fetch current settings from database
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings_rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    // Settings table might not exist yet
    $settings_rows = [];
}

// Helper function to get setting value
function getSetting($key, $default = '') {
    global $settings_rows;
    return $settings_rows[$key] ?? $default;
}

include '../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-900">System Settings</h2>
    <p class="text-gray-600 mt-1">Configure your ERP system preferences and business information</p>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-800 font-semibold">✓ Settings saved successfully!</p>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <p class="text-red-800 text-sm"><?php echo htmlspecialchars($_GET['error']); ?></p>
</div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md">
    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchTab('company')" id="tab-company" class="tab-button active px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">
                Company Info
            </button>
            <button onclick="switchTab('email')" id="tab-email" class="tab-button px-6 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300">
                Email Settings
            </button>
            <button onclick="switchTab('system')" id="tab-system" class="tab-button px-6 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300">
                System
            </button>
            <button onclick="switchTab('bank')" id="tab-bank" class="tab-button px-6 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300">
                Bank Accounts
            </button>
            <button onclick="switchTab('display')" id="tab-display" class="tab-button px-6 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300">
                Display
            </button>
        </nav>
    </div>

    <form method="POST" action="../api/save-settings.php" class="p-8" enctype="multipart/form-data">
        
        <!-- Company Info Tab -->
        <div id="content-company" class="tab-content">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Company Information</h3>
            
            <div class="space-y-6 max-w-2xl">
                <!-- Logo Upload Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Company Logo</h4>
                    
                    <?php
                    $current_logo = getSetting('company_logo', '');
                    $logo_path = $current_logo ? '../' . $current_logo : '';
                    ?>
                    
                    <?php if ($current_logo && file_exists(__DIR__ . '/' . $logo_path)): ?>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Current Logo:</p>
                        <img src="<?php echo $logo_path; ?>" alt="Company Logo" class="h-20 object-contain bg-white p-2 rounded border border-gray-300">
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Upload New Logo
                        </label>
                        <input 
                            type="file" 
                            name="company_logo" 
                            accept="image/png,image/jpeg,image/jpg,image/gif"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            onchange="previewLogo(this)"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Recommended: PNG or JPG, max 3MB. Will be auto-resized and used as favicon.
                        </p>
                        
                        <!-- Preview -->
                        <div id="logoPreview" class="mt-3 hidden">
                            <p class="text-sm text-gray-600 mb-2">Preview:</p>
                            <img id="logoPreviewImg" src="" alt="Logo Preview" class="h-20 object-contain bg-white p-2 rounded border border-gray-300">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Company Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars(getSetting('company_name', 'Bluedots Technologies')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Business Address</label>
                    <textarea name="company_address" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"><?php echo htmlspecialchars(getSetting('company_address', 'No. 9 Ugbor Village Road, Ugbor GRA, Benin City, Edo State')); ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="company_phone" value="<?php echo htmlspecialchars(getSetting('company_phone', '07031635955')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="company_email" value="<?php echo htmlspecialchars(getSetting('company_email', 'bluedotsng@gmail.com')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Website</label>
                    <input type="url" name="company_website" value="<?php echo htmlspecialchars(getSetting('company_website', 'www.bluedots.com.ng')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tax/VAT Registration Number (Optional)</label>
                    <input type="text" name="company_tax_id" value="<?php echo htmlspecialchars(getSetting('company_tax_id', '')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
            </div>
        </div>

        <!-- Email Settings Tab -->
        <div id="content-email" class="tab-content hidden">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Email Configuration</h3>
            
            <div class="space-y-6 max-w-2xl">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Method</label>
                    <select name="email_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="php_mail" <?php echo getSetting('email_method', 'php_mail') === 'php_mail' ? 'selected' : ''; ?>>PHP mail() - Simple (Default)</option>
                        <option value="smtp" <?php echo getSetting('email_method') === 'smtp' ? 'selected' : ''; ?>>SMTP - Advanced (Recommended for Production)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">PHP mail() works for testing. Use SMTP for production.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">From Email</label>
                        <input type="email" name="email_from_address" value="<?php echo htmlspecialchars(getSetting('email_from_address', 'noreply@bluedots.com.ng')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">From Name</label>
                        <input type="text" name="email_from_name" value="<?php echo htmlspecialchars(getSetting('email_from_name', 'Bluedots Technologies')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-semibold text-gray-900 mb-4">SMTP Configuration (If using SMTP)</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Host</label>
                            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars(getSetting('smtp_host', 'smtp.gmail.com')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="smtp.gmail.com">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Port</label>
                            <input type="number" name="smtp_port" value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="587">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Username</label>
                            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars(getSetting('smtp_username', '')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="your-email@gmail.com">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Password</label>
                            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars(getSetting('smtp_password', '')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="App password or account password">
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Encryption</label>
                        <select name="smtp_encryption" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="tls" <?php echo getSetting('smtp_encryption', 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                            <option value="ssl" <?php echo getSetting('smtp_encryption') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2">📧 Gmail SMTP Setup:</h4>
                    <ol class="text-sm text-gray-700 space-y-1 ml-4 list-decimal">
                        <li>Enable 2-Step Verification on your Gmail</li>
                        <li>Go to Google Account → Security → App Passwords</li>
                        <li>Generate an App Password for "Mail"</li>
                        <li>Use that as your SMTP password here</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- System Settings Tab -->
        <div id="content-system" class="tab-content hidden">
            <h3 class="text-xl font-bold text-gray-900 mb-6">System Configuration</h3>
            
            <div class="space-y-6 max-w-2xl">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">VAT Rate (%)</label>
                    <input type="number" step="0.01" name="vat_rate" value="<?php echo htmlspecialchars(getSetting('vat_rate', '7.5')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Current Nigerian VAT is 7.5%</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Quote Prefix</label>
                        <input type="text" name="quote_prefix" value="<?php echo htmlspecialchars(getSetting('quote_prefix', 'QUOT-')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Prefix</label>
                        <input type="text" name="invoice_prefix" value="<?php echo htmlspecialchars(getSetting('invoice_prefix', 'INV-')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Receipt Prefix</label>
                        <input type="text" name="receipt_prefix" value="<?php echo htmlspecialchars(getSetting('receipt_prefix', 'REC-')); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="<?php echo htmlspecialchars(getSetting('currency_symbol', '₦')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date Format</label>
                    <select name="date_format" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="d/m/Y" <?php echo getSetting('date_format', 'd/m/Y') === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY (31/12/2024)</option>
                        <option value="m/d/Y" <?php echo getSetting('date_format') === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY (12/31/2024)</option>
                        <option value="Y-m-d" <?php echo getSetting('date_format') === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD (2024-12-31)</option>
                    </select>
                </div>
                
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="auto_archive_days" value="90"
                               <?php echo getSetting('auto_archive_days') ? 'checked' : ''; ?>
                               class="w-5 h-5 text-primary rounded focus:ring-2 focus:ring-primary">
                        <span class="text-sm font-semibold text-gray-700">Auto-archive documents after 90 days</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Bank Accounts Tab -->
        <div id="content-bank" class="tab-content hidden">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Bank Account Details</h3>
            <p class="text-gray-600 mb-6">These details appear on invoices and receipts</p>
            
            <div class="space-y-6 max-w-2xl">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Primary Bank Account</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Bank Name</label>
                            <input type="text" name="bank1_name" value="<?php echo htmlspecialchars(getSetting('bank1_name', 'Access Bank')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Account Number</label>
                            <input type="text" name="bank1_account" value="<?php echo htmlspecialchars(getSetting('bank1_account', '0107309773')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Secondary Bank Account</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Bank Name</label>
                            <input type="text" name="bank2_name" value="<?php echo htmlspecialchars(getSetting('bank2_name', 'United Bank For Africa (UBA)')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Account Number</label>
                            <input type="text" name="bank2_account" value="<?php echo htmlspecialchars(getSetting('bank2_account', '1023821430')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Account Name</label>
                    <input type="text" name="bank_account_name" value="<?php echo htmlspecialchars(getSetting('bank_account_name', 'Bluedots Technologies')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Same account name for both banks (usually company name)</p>
                </div>
            </div>
        </div>

        <!-- Display Settings Tab -->
        <div id="content-display" class="tab-content hidden">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Display Preferences</h3>
            
            <div class="space-y-6 max-w-2xl">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Items Per Page</label>
                    <select name="items_per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="10" <?php echo getSetting('items_per_page', '25') === '10' ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo getSetting('items_per_page', '25') === '25' ? 'selected' : ''; ?>>25 (Default)</option>
                        <option value="50" <?php echo getSetting('items_per_page', '25') === '50' ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo getSetting('items_per_page', '25') === '100' ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
                
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_dashboard_charts" value="1"
                               <?php echo getSetting('show_dashboard_charts', '1') ? 'checked' : ''; ?>
                               class="w-5 h-5 text-primary rounded focus:ring-2 focus:ring-primary">
                        <span class="text-sm font-semibold text-gray-700">Show charts on dashboard</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_recent_activity" value="1"
                               <?php echo getSetting('show_recent_activity', '1') ? 'checked' : ''; ?>
                               class="w-5 h-5 text-primary rounded focus:ring-2 focus:ring-primary">
                        <span class="text-sm font-semibold text-gray-700">Show recent activity on dashboard</span>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">PDF Export Quality</label>
                    <select name="pdf_quality" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="standard" <?php echo getSetting('pdf_quality', 'high') === 'standard' ? 'selected' : ''; ?>>Standard (Faster)</option>
                        <option value="high" <?php echo getSetting('pdf_quality', 'high') === 'high' ? 'selected' : ''; ?>>High (Default)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="submit" class="px-8 py-3 bg-primary text-white rounded-lg hover:bg-blue-700 font-semibold">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab  contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-primary', 'text-primary');
        button.classList.add('border-transparent', 'text-gray-600');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('active', 'border-primary', 'text-primary');
    activeTab.classList.remove('border-transparent', 'text-gray-600');
}

function previewLogo(input) {
    const preview = document.getElementById('logoPreview');
    const previewImg = document.getElementById('logoPreviewImg');
    
    if (input.files && input.files[0]) {
        // Check file size (3MB = 3145728 bytes)
        if (input.files[0].size > 3145728) {
            alert('File size must be less than 3MB');
            input.value = '';
            preview.classList.add('hidden');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}
</script>

<style>
.tab-button.active {
    border-bottom-color: #0076BE;
    color: #0076BE;
}
</style>

<?php include '../includes/footer.php'; ?>
