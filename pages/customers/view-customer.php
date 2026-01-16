<?php
include '../../includes/session-check.php';
requirePermission('manage_customers');

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage-customers.php?error=Invalid customer');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: manage-customers.php?error=Customer not found');
    exit;
}

// Get documents for this customer
$stmt = $pdo->prepare("
    SELECT id, quote_number as doc_number, document_type, quote_date as doc_date, total_amount, status 
    FROM documents 
    WHERE customer_id = ? AND deleted_at IS NULL
    ORDER BY created_at DESC LIMIT 10
");
$stmt->execute([$id]);
$documents = $stmt->fetchAll();

$pageTitle = 'View Customer - Bluedots Technologies';
include '../../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Customer Profile</h2>
        <div class="flex gap-3">
            <a href="edit-customer.php?id=<?php echo $customer['id']; ?>"
                class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-blue-700 font-semibold">Edit</a>
            <a href="manage-customers.php"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Contact Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="font-semibold">
                        <?php echo htmlspecialchars($customer['name']); ?>
                    </p>
                </div>
                <?php if ($customer['company']): ?>
                    <div>
                        <p class="text-sm text-gray-600">Company</p>
                        <p class="font-semibold">
                            <?php echo htmlspecialchars($customer['company']); ?>
                        </p>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-semibold">
                        <?php echo htmlspecialchars($customer['email']); ?>
                    </p>
                </div>
                <?php if ($customer['phone']): ?>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-semibold">
                            <?php echo htmlspecialchars($customer['phone']); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Address & Notes</h3>
            <div class="space-y-3">
                <?php if ($customer['address']): ?>
                    <div>
                        <p class="text-sm text-gray-600">Address</p>
                        <p class="font-semibold">
                            <?php echo nl2br(htmlspecialchars($customer['address'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if ($customer['city']): ?>
                    <div>
                        <p class="text-sm text-gray-600">City</p>
                        <p class="font-semibold">
                            <?php echo htmlspecialchars($customer['city']); ?>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if ($customer['notes']): ?>
                    <div>
                        <p class="text-sm text-gray-600">Notes</p>
                        <p class="text-sm">
                            <?php echo nl2br(htmlspecialchars($customer['notes'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Documents</h3>

        <?php if (!empty($documents)): ?>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Document #</th>
                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Date</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">Amount</th>
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm">
                                <?php echo htmlspecialchars($doc['doc_number']); ?>
                            </td>
                            <td class="px-4 py-3 text-sm capitalize">
                                <?php echo htmlspecialchars($doc['document_type']); ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php echo date('d/m/Y', strtotime($doc['doc_date'])); ?>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                <?php echo formatNaira($doc['total_amount']); ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full capitalize">
                                    <?php echo $doc['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center py-8 text-gray-500">No documents found</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>