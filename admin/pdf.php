<?php
require_once('dompdf/vendor/autoload.php');
use Dompdf\Dompdf;
use Dompdf\Options;

define('HOST', 'localhost');
define('USERNAME', 'root');
define('PASSWORD', '310802');
define('DATABASE', 'asm_php1');

// Lấy doanh thu tháng hiện tại
$sql = "SELECT SUM(od.num * od.price) AS current_month_revenue FROM orders o JOIN order_details od ON o.id = od.order_id WHERE YEAR(o.order_date) = YEAR(CURRENT_DATE) AND MONTH(o.order_date) = MONTH(CURRENT_DATE);";
$conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
$result = mysqli_query($conn, $sql);
$month_revenue = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $month_revenue = $row['current_month_revenue'];
}

// Cấu hình Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo tài chính</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .address {
            text-align: left;
        }
        .address h5 {
            margin: 0;
            color: red;
        }
        .topbar-content {
            text-align: right;
        }
        .topbar-content h3, .topbar-content h5 {
            margin: 0;
        }
        .title h2, .title h4 {
            text-align: center;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 18px;
            text-align: center;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: black;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .main_month_revenue_product h3 {
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="address">
                <h5>Đơn vị: Cửa hàng 8</h5>
                <h5>Địa chỉ: 88 đường phen</h5>
            </div>
            <div class="topbar-content">
                <h3>Báo cáo tình hình tài chính tháng ' . date('m/Y') . '</h3>
                <h5>Tại ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y') . '</h5>
            </div>
        </div>
        <div class="main_month_revenue_product">
            <h3>Tổng doanh thu tháng ' . date('m/Y') . ': ' . number_format($month_revenue, 0, ',', '.') . ' VND</h3>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Doanh thu</th>
                </tr>
                <tbody>
';

$sql1 = "SELECT p.title AS product_title, SUM(od.num * od.price) AS total_revenue FROM order_details od JOIN product p ON od.product_id = p.id JOIN orders o ON od.order_id = o.id WHERE MONTH(o.order_date) = MONTH(CURRENT_DATE) AND YEAR(o.order_date) = YEAR(CURRENT_DATE) GROUP BY p.title;";
$conn1 = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
$result1 = mysqli_query($conn1, $sql1);

$index = 1;
foreach ($result1 as $row){
    $html .= '
        <tr>
            <td>' . ($index++) . '</td>
            <td>'.$row['product_title'].'</td>
            <td>'.number_format($row['total_revenue'], 0, ',', '.').'</td>
        </tr>
    ';
}

$html .= '
                    </tbody>
            </table>
        </div>
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('bao_cao_tai_chinh.pdf', array("Attachment" => false));
?>
