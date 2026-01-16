<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0076BE;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #0076BE;
        }

        .company-tagline {
            font-size: 8pt;
            letter-spacing: 3px;
            color: #666;
        }

        .company-details {
            font-size: 9pt;
            margin-top: 10px;
            color: #666;
        }

        .document-title {
            font-size: 32pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }

        .quote-info {
            margin-bottom: 20px;
        }

        .info-grid {
            width: 100%;
        }

        .info-grid td {
            padding: 5px;
            font-size: 10pt;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        table.line-items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.line-items th {
            background-color: #0076BE;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10pt;
        }

        table.line-items td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 10pt;
        }

        .totals {
            width: 40%;
            float: right;
            margin-top: 20px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .totals .grand-total {
            background-color: #0076BE;
            color: white;
            font-weight: bold;
            font-size: 12pt;
        }

        .footer {
            margin-top: 40px;
            border-top: 2px solid #0076BE;
            padding-top: 15px;
            text-align: center;
            font-size: 9pt;
        }

        .bank-details {
            background-color: #E3F2FD;
            padding: 15px;
            margin-top: 20px;
        }

        .bank-header {
            background-color: #0076BE;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <div class="company-name">Bluedots</div>
        <div class="company-tagline">TECHNOLOGIES</div>
        <div class="company-details">
            <strong>Contact Address:</strong>
            <?php echo COMPANY_ADDRESS; ?><br>
            <strong>Phone:</strong>
            <?php echo COMPANY_PHONE; ?> |
            <strong>Email:</strong>
            <?php echo COMPANY_EMAIL; ?> |
            <?php echo COMPANY_WEBSITE; ?>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">QUOTE</div>
    <div style="text-align: center; font-style: italic; color: #666; margin-bottom: 20px;">
        <?php echo htmlspecialchars($quote['quote_title']); ?>
    </div>

    <!-- Quote Info -->
    <div class="quote-info">
        <table class="info-grid">
            <tr>
                <td class="label" width="30%">Quote For:</td>
                <td>
                    <?php echo htmlspecialchars($quote['customer_name']); ?>
                </td>
                <td class="label" width="25%">Quote Number:</td>
                <td>
                    <?php echo htmlspecialchars($quote['document_number']); ?>
                </td>
            </tr>
            <tr>
                <td class="label">Salesperson:</td>
                <td>
                    <?php echo htmlspecialchars($quote['salesperson']); ?>
                </td>
                <td class="label">Date:</td>
                <td>
                    <?php echo date('d/m/Y', strtotime($quote['quote_date'])); ?>
                </td>
            </tr>
            <tr>
                <td class="label">Payment Terms:</td>
                <td colspan="3">
                    <?php echo htmlspecialchars($quote['payment_terms']); ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Line Items -->
    <table class="line-items">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="8%">Qty</th>
                <th width="45%">Description</th>
                <th width="15%">Unit Price</th>
                <th width="7%">VAT</th>
                <th width="20%">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($line_items as $item): ?>
                <tr>
                    <td style="text-align: center;">
                        <?php echo $item['item_number']; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo number_format($item['quantity'], 2); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($item['description']); ?>
                    </td>
                    <td style="text-align: right;">
                        <?php echo formatNaira($item['unit_price']); ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo $item['vat_applicable'] ? '✓' : '—'; ?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo formatNaira($item['line_total']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td style="text-align: right;">
                    <?php echo formatNaira($quote['subtotal']); ?>
                </td>
            </tr>
            <tr>
                <td><strong>VAT (7.5%):</strong></td>
                <td style="text-align: right;">
                    <?php echo formatNaira($quote['total_vat']); ?>
                </td>
            </tr>
            <tr class="grand-total">
                <td><strong>Grand Total:</strong></td>
                <td style="text-align: right; font-size: 14pt;">
                    <?php echo formatNaira($quote['grand_total']); ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Bank Details -->
    <?php $bank_accounts = getBankAccountsForDisplay(); ?>
    <?php if (!empty($bank_accounts)): ?>
        <div class="bank-details">
            <div class="bank-header">MAKE ALL PAYMENTS IN FAVOUR OF:
                <?php echo htmlspecialchars($bank_accounts[0]['account_name'] ?? COMPANY_NAME); ?></div>
            <table width="100%">
                <tr>
                    <?php
                    $column_width = floor(100 / count($bank_accounts));
                    foreach ($bank_accounts as $index => $account):
                        ?>
                        <td width="<?php echo $column_width; ?>%"
                            style="text-align: center; <?php echo ($index < count($bank_accounts) - 1) ? 'border-right: 1px solid #0076BE;' : ''; ?> padding: 10px;">
                            <strong><?php echo htmlspecialchars($account['bank_name']); ?></strong><br>
                            Account No: <?php echo htmlspecialchars($account['account_number']); ?><br>
                            <small><?php echo htmlspecialchars($account['account_name']); ?></small>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p style="font-style: italic; font-weight: bold;">We look forward to working with you! Thank you</p>
        <p style="margin-top: 10px; font-size: 8pt; color: #666;">
            Quote prepared by:
            <?php echo htmlspecialchars($quote['salesperson']); ?>
        </p>
    </div>

</body>

</html>