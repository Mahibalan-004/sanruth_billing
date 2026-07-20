<?php
require_once("../config/db.php");
require_once("../includes/auth.php");
requireAdmin();

// Excel Export
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $month = isset($_GET['month']) ? $_GET['month'] : date('m');
    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Monthly_Sales_Full_Report_$month-$year.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
}

if (!isset($_GET['export'])) {
    include("../includes/header.php");
}

// Filters
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Full detailed sales query
$query = "
    SELECT 
        s.id,
        s.orderNo,
        s.orderDate,
        s.orderStatus,
        s.actualAmountSum,
        s.paidAmountSum,
        c.name AS customer_name,
        c.phoneNo1 AS contact_no
    FROM sales s
    LEFT JOIN customer c ON s.customer = c.id
    WHERE MONTH(s.orderDate) = '$filter_month'
      AND YEAR(s.orderDate) = '$filter_year'
    ORDER BY s.orderDate DESC, s.id DESC
";
$result = mysqli_query($conn, $query);

// Monthly totals
$total_query = "
    SELECT 
        COUNT(id) AS total_orders,
        SUM(actualAmountSum) AS total_sales,
        SUM(paidAmountSum) AS total_paid
    FROM sales
    WHERE MONTH(orderDate) = '$filter_month'
      AND YEAR(orderDate) = '$filter_year'
";
$total_res = mysqli_query($conn, $total_query);
$totals = mysqli_fetch_assoc($total_res);

$total_orders = (int) ($totals['total_orders'] ?? 0);
$total_sales = (float) ($totals['total_sales'] ?? 0);
$total_paid = (float) ($totals['total_paid'] ?? 0);
$total_balance = $total_sales - $total_paid;
?>

<div style="padding:20px; background:#f8fafc; min-height:calc(100vh - 110px);">

    <?php if (!isset($_GET['export'])): ?>
        <div class="no-print"
            style="background:#0d9488; min-height:60px; display:flex; align-items:center; justify-content:space-between; border-radius:10px 10px 0 0; padding:12px 20px; gap:15px; flex-wrap:wrap;">
            <div style="color:#fff; font-weight:700; font-size:20px;">Monthly Sales Full Report</div>

            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <form method="GET" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <select name="month"
                        style="padding:8px 12px; border-radius:6px; border:1px solid #dbeafe; outline:none;">
                        <?php for ($m = 1; $m <= 12; $m++):
                            $mv = sprintf('%02d', $m); ?>
                            <option value="<?= $mv ?>" <?= $mv == $filter_month ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <select name="year"
                        style="padding:8px 12px; border-radius:6px; border:1px solid #dbeafe; outline:none;">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $filter_year ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit"
                        style="background:#fff; color:#0d9488; padding:8px 16px; border-radius:6px; font-weight:700; border:none; cursor:pointer;">
                        Filter
                    </button>
                </form>

                <button onclick="window.print()"
                    style="background:#fff; color:#111827; padding:8px 14px; border-radius:6px; border:none; cursor:pointer; font-weight:700;">
                    PDF
                </button>

                <a href="?month=<?= urlencode($filter_month) ?>&year=<?= urlencode($filter_year) ?>&export=excel"
                    style="background:#16a34a; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:700;">
                    Excel
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div style="background:#fff; border:1px solid #e2e8f0; border-top:none; border-radius:0 0 12px 12px; padding:24px;">

        <div style="margin-bottom:20px;">
            <h2 style="margin:0; color:#1e293b; font-size:28px;">Sales Summary</h2>
            <div style="margin-top:8px; color:#64748b; font-size:14px;">
                Month: <strong><?= date('F', mktime(0, 0, 0, (int) $filter_month, 1)) ?>
                    <?= htmlspecialchars($filter_year) ?></strong>
            </div>
        </div>

        <div
            style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin-bottom:24px;">
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">Total Orders
                </div>
                <div style="font-size:26px; font-weight:700; color:#111827; margin-top:6px;"><?= $total_orders ?></div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">Total Sales</div>
                <div style="font-size:26px; font-weight:700; color:#111827; margin-top:6px;">
                    ₹<?= number_format($total_sales, 2) ?></div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">Collected Amount
                </div>
                <div style="font-size:26px; font-weight:700; color:#16a34a; margin-top:6px;">
                    ₹<?= number_format($total_paid, 2) ?></div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">Pending Balance
                </div>
                <div style="font-size:26px; font-weight:700; color:#ef4444; margin-top:6px;">
                    ₹<?= number_format($total_balance, 2) ?></div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; border:1px solid #cbd5e1; table-layout:fixed;">
                <thead>
                    <tr style="background:#d1d5db; font-weight:700;">
                        <th style="border:1px solid #000; padding:8px;">S.No</th>
                        <th style="border:1px solid #000; padding:8px;">Status</th>
                        <th style="border:1px solid #000; padding:8px;">Date</th>
                        <th style="border:1px solid #000; padding:8px;">Order #</th>
                        <th style="border:1px solid #000; padding:8px;">Customer Name</th>
                        <th style="border:1px solid #000; padding:8px;">Contact #</th>
                        <th style="border:1px solid #000; padding:8px;">Billed Amt</th>
                        <th style="border:1px solid #000; padding:8px;">Paid Amt</th>
                        <th style="border:1px solid #000; padding:8px;">Balance</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $customer_name = !empty($row['customer_name']) ? $row['customer_name'] : 'CASH BILL';
                            $contact_no = !empty($row['contact_no']) ? $row['contact_no'] : '-';
                            $billed_amt = (float) $row['actualAmountSum'];
                            $paid_amt = (float) $row['paidAmountSum'];
                            $balance_amt = $billed_amt - $paid_amt;
                            $status = !empty($row['orderStatus']) ? $row['orderStatus'] : 'New';
                            $row_bg = ($i % 2 == 0) ? '#f9fafb' : '#ffffff';
                            ?>
                            <tr style="background: <?= $row_bg ?>;">
                                <td style="border:1px solid #cbd5e1; padding:8px; text-align:center;"><?= $i++ ?></td>
                                <td
                                    style="border:1px solid #cbd5e1; padding:8px; text-align:center; font-weight:700; color:<?= ($status === 'Invoiced' || $status === 'Completed') ? '#16a34a' : '#f59e0b' ?>;">
                                    <?= htmlspecialchars($status) ?>
                                </td>
                                <td style="border:1px solid #cbd5e1; padding:8px; text-align:center;">
                                    <?= !empty($row['orderDate']) ? date('d/m/Y', strtotime($row['orderDate'])) : '-' ?>
                                </td>
                                <td style="border:1px solid #cbd5e1; padding:8px; text-align:center; font-weight:700;">
                                    <?= htmlspecialchars($row['orderNo'] ?: '-') ?>
                                </td>
                                <td
                                    style="border:1px solid #cbd5e1; padding:8px; text-align:center; font-weight:600; word-break:break-word;">
                                    <?= htmlspecialchars($customer_name) ?>
                                </td>
                                <td style="border:1px solid #cbd5e1; padding:8px; text-align:center; word-break:break-word;">
                                    <?= htmlspecialchars($contact_no) ?>
                                </td>
                                <td style="border:1px solid #cbd5e1; padding:8px; text-align:right; font-weight:700;">
                                    <?= number_format($billed_amt, 2) ?>
                                </td>
                                <td
                                    style="border:1px solid #cbd5e1; padding:8px; text-align:right; font-weight:700; color:#16a34a;">
                                    <?= number_format($paid_amt, 2) ?>
                                </td>
                                <td
                                    style="border:1px solid #cbd5e1; padding:8px; text-align:right; font-weight:700; color:#ef4444; min-width:100px;">
                                    <?= number_format($balance_amt, 2) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9"
                                style="border:1px solid #cbd5e1; padding:24px; text-align:center; color:#64748b;">
                                No sales recorded for this month.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="6"
                            style="border:1px solid #94a3b8; padding:10px; text-align:right; font-weight:700;">
                            Grand Total
                        </td>
                        <td style="border:1px solid #94a3b8; padding:10px; text-align:right; font-weight:700;">
                            <?= number_format($total_sales, 2) ?>
                        </td>
                        <td
                            style="border:1px solid #94a3b8; padding:10px; text-align:right; font-weight:700; color:#16a34a;">
                            <?= number_format($total_paid, 2) ?>
                        </td>
                        <td
                            style="border:1px solid #94a3b8; padding:10px; text-align:right; font-weight:700; color:#ef4444;">
                            <?= number_format($total_balance, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if (!isset($_GET['export'])): ?>
    <?php include("../includes/footer.php"); ?>

    <style>
        @media print {

            .topbar,
            .menu-container,
            .no-print,
            .btn,
            .link {
                display: none !important;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                font-size: 10px !important;
            }

            .container,
            .content,
            .wrapper,
            .card {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            table {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
                page-break-inside: auto;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid !important;
                page-break-after: auto;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 4px !important;
                font-size: 9px !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }

            th:nth-child(1),
            td:nth-child(1) {
                width: 5% !important;
            }

            th:nth-child(2),
            td:nth-child(2) {
                width: 10% !important;
            }

            th:nth-child(3),
            td:nth-child(3) {
                width: 10% !important;
            }

            th:nth-child(4),
            td:nth-child(4) {
                width: 14% !important;
            }

            th:nth-child(5),
            td:nth-child(5) {
                width: 18% !important;
            }

            th:nth-child(6),
            td:nth-child(6) {
                width: 14% !important;
            }

            th:nth-child(7),
            td:nth-child(7) {
                width: 10% !important;
            }

            th:nth-child(8),
            td:nth-child(8) {
                width: 10% !important;
            }

            th:nth-child(9),
            td:nth-child(9) {
                width: 9% !important;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
<?php endif; ?>