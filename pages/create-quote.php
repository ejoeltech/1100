<?php include '../includes/session-check.php'; ?>
<?php
require_once '../config.php';

$pageTitle = 'Create Quote - Bluedots Technologies';
$nextQuoteNumber = generateQuoteNumber($pdo);
$todayDate = date('Y-m-d');

// Check if coming from a readymade quote template
$templateData = null;
if (isset($_GET['from_template']) && isset($_SESSION['template_data'])) {
    $templateData = $_SESSION['template_data'];
    unset($_SESSION['template_data']); // Clear after use
}

include '../includes/header.php';
?>

<?php if ($templateData): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-blue-800 font-semibold">
            ✓ Using template: <strong><?php echo htmlspecialchars($templateData['template_name']); ?></strong>
        </p>
        <p class="text-sm text-blue-600 mt-1">Fill in customer details and adjust as needed</p>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md p-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Create New Quote</h2>

    <form id="quoteForm" method="POST" action="../api/save-quote.php">

        <!-- Quote Header Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Quote Number
                </label>
                <input type="text" name="quote_number" value="<?php echo $nextQuoteNumber; ?>" readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-lg">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="quote_date" value="<?php echo $todayDate; ?>" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Quote Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="quote_title" placeholder="e.g., Website Development Project" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Customer Name <span class="text-red-500">*</span>
                </label>

                <!-- Customer Dropdown -->
                <select id="customerSelect"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-2"
                    onchange="handleCustomerSelect()">
                    <option value="">-- Select Existing Customer --</option>
                    <?php
                    $stmt = $pdo->query("SELECT DISTINCT name FROM customers ORDER BY name");
                    $customers = $stmt->fetchAll();
                    foreach ($customers as $customer):
                        ?>
                        <option value="<?php echo htmlspecialchars($customer['name']); ?>">
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__new__">+ Enter New Customer</option>
                </select>

                <!-- Manual Input -->
                <input type="text" id="customerNameInput" name="customer_name" placeholder="e.g., ABC Limited" required
                    style="display: none;"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <script>
                function handleCustomerSelect() {
                    const select = document.getElementById('customerSelect');
                    const input = document.getElementById('customerNameInput');

                    if (select.value === '__new__') {
                        input.style.display = 'block';
                        input.value = '';
                        input.focus();
                    } else if (select.value === '') {
                        input.style.display = 'none';
                        input.value = '';
                    } else {
                        input.style.display = 'none';
                        input.value = select.value;
                    }
                }
            </script>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Salesperson <span class="text-red-500">*</span>
                </label>
                <input type="text" name="salesperson"
                    value="<?php echo htmlspecialchars($current_user['full_name']); ?>"
                    placeholder="e.g., Joel Okenabirhie" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Payment Terms
                </label>
                <input type="text" name="payment_terms" value="<?php echo DEFAULT_PAYMENT_TERMS; ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
            </div>
        </div>

        <!-- Line Items Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Line Items</h3>
                <button type="button" id="addLineBtn"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Line Item
                </button>
            </div>

            <!-- Line Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-300">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-3 py-2 text-left text-sm font-semibold w-16">#</th>
                            <th class="px-3 py-2 text-left text-sm font-semibold w-24">Qty</th>
                            <th class="px-3 py-2 text-left text-sm font-semibold">Description</th>
                            <th class="px-3 py-2 text-left text-sm font-semibold w-40">Unit Price</th>
                            <th class="px-3 py-2 text-center text-sm font-semibold w-20">VAT?</th>
                            <th class="px-3 py-2 text-right text-sm font-semibold w-40">Line Total</th>
                            <th class="px-3 py-2 w-16"></th>
                        </tr>
                    </thead>
                    <tbody id="lineItemsContainer">
                        <!-- Line items will be added here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals Section -->
        <div class="flex justify-end mb-8">
            <div class="w-full md:w-96 bg-gray-50 border border-gray-300 rounded-lg p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">Subtotal:</span>
                        <span id="subtotalDisplay" class="text-lg font-bold text-gray-900">₦0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">VAT (7.5%):</span>
                        <span id="vatDisplay" class="text-lg font-bold text-gray-900">₦0.00</span>
                    </div>
                    <div class="border-t border-gray-300 pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-900">Grand Total:</span>
                            <span id="grandTotalDisplay" class="text-2xl font-bold text-primary">₦0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for submission -->
                <input type="hidden" name="subtotal" id="subtotalInput">
                <input type="hidden" name="total_vat" id="vatInput">
                <input type="hidden" name="grand_total" id="grandTotalInput">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-end">
            <button type="button" onclick="window.location.href='view-quotes.php'"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
                Cancel
            </button>
            <button type="submit" name="status" value="draft"
                class="px-6 py-3 border border-primary text-primary rounded-lg hover:bg-blue-50 font-semibold">
                Save as Draft
            </button>
            <button type="submit" name="status" value="finalized"
                class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-blue-700 font-semibold">
                Save & Finalize
            </button>
        </div>

    </form>
</div>

<!-- JavaScript for Line Items and Calculations -->
<?php if ($templateData): ?>
    <script>
        // Template data from readymade quote
        const templateData = <?php echo json_encode($templateData); ?>;

        // Wait for page to fully load
        window.addEventListener('load', function () {
            console.log('Loading template data:', templateData);

            // Populate payment terms
            if (templateData.payment_terms) {
                const paymentField = document.querySelector('[name="payment_terms"]');
                if (paymentField) {
                    paymentField.value = templateData.payment_terms;
                }
            }

            // Populate line items
            if (templateData.items && templateData.items.length > 0) {
                // Clear any existing empty lines
                const container = document.getElementById('lineItemsContainer');
                if (container) {
                    container.innerHTML = '';
                    lineItemCount = 0;
                }

                // Add each template item
                templateData.items.forEach((item, index) => {
                    console.log('Adding item:', item);
                    addLineItem();

                    // Small delay to ensure row is added
                    setTimeout(() => {
                        const rowId = 'line-' + lineItemCount;
                        const row = document.getElementById(rowId);

                        if (row) {
                            const qtyInput = row.querySelector('[name*="[quantity]"]');
                            const descInput = row.querySelector('[name*="[description]"]');
                            const priceInput = row.querySelector('[name*="[unit_price]"]');
                            const vatCheckbox = row.querySelector('[name*="[vat_applicable]"]');

                            if (qtyInput) qtyInput.value = item.quantity;
                            if (descInput) descInput.value = item.description;
                            if (priceInput) priceInput.value = item.unit_price;
                            if (vatCheckbox) vatCheckbox.checked = item.vat_applicable == 1;

                            calculateLineTotal(lineItemCount);
                        }
                    }, 100 * index);
                });

                // Final totals calculation
                setTimeout(() => calculateTotals(), 500);
            }
        });
    </script>
<?php endif; ?>
<script src="../assets/js/quote-form.js"></script>

<?php include '../includes/footer.php'; ?>