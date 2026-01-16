<?php
include '../../includes/session-check.php';
requirePermission('manage_customers');
$pageTitle = 'Manage Customers - Bluedots Technologies';
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter !== '') {
    $where[] = "is_active = ?";
    $params[] = $status_filter;
}

$where_clause = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT * FROM customers WHERE $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$customers = $stmt->fetchAll();

$stats = $pdo->query("SELECT COUNT(*) as total, SUM(is_active=1) as active FROM customers")->fetch();

include '../../includes/header.php';
?>

<?php if (isset($_GET['created'])): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800 font-semibold">✓ Customer created successfully!</p>
    </div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-3xl font-bold text-gray-900">Manage Customers</h2>
    <a href="create-customer.php" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-blue-700 font-semibold">
        + Add Customer
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow">
        <p class="text-sm text-gray-600">Total Customers</p>
        <p class="text-2xl font-bold text-gray-900">
            <?php echo $stats['total']; ?>
        </p>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
        <p class="text-sm text-gray-600">Active</p>
        <p class="text-2xl font-bold text-green-600">
            <?php echo $stats['active']; ?>
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="text" name="search" placeholder="Search by name, email, or phone..."
            value="<?php echo htmlspecialchars($search); ?>"
            class="px-4 py-2 border border-gray-300 rounded-lg col-span-2">
        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700">Search</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Name</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Company</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Contact</th>
                <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-semibold">
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </p>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <?php echo htmlspecialchars($customer['company'] ?? '—'); ?>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <p>
                            <?php echo htmlspecialchars($customer['email'] ?? '—'); ?>
                        </p>
                        <p class="text-gray-500">
                            <?php echo htmlspecialchars($customer['phone'] ?? '—'); ?>
                        </p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="view-customer.php?id=<?php echo $customer['id']; ?>"
                                class="text-primary hover:text-blue-700 font-semibold text-sm">View</a>
                            <span class="text-gray-300">|</span>
                            <a href="edit-customer.php?id=<?php echo $customer['id']; ?>"
                                class="text-primary hover:text-blue-700 font-semibold text-sm">Edit</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($customers)): ?>
        <p class="text-center py-8 text-gray-500">No customers found</p>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>