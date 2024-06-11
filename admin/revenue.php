<?php
require_once('database/dbhelper.php');
// Mảng để lưu tổng tiền của mỗi loại mặt hàng
$totalAmounts = array(
    'tra_sua' => 0,
    'coffe' => 0,
    'sinh_to' => 0,
    'do_an_vat' => 0
);

// Truy vấn tổng tiền cho từng mặt hàng
$queries = [
    'tra_sua' => "SELECT SUM(order_details.num * order_details.price) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 1;
    ",
    'coffe' => "SELECT SUM(order_details.num * order_details.price) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 2;
    ",
    'sinh_to' => "SELECT SUM(order_details.num * order_details.price) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 3;
    ",
    'do_an_vat' => "SELECT SUM(order_details.num * order_details.price) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 4;
    "
];
$tongsl=[
    'tra_sua' => "SELECT SUM(order_detail.num) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 1;
    ",
    'coffe' => "SELECT SUM(order_detail.num) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 2;
    ",
    'sinh_to' => "SELECT SUM(order_detail.num) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 3;
    ",
    'do_an_vat' => "SELECT SUM(order_details.num * order_details.price) AS total
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    WHERE product.id_category = 4;
    "  
];
$topProductsQuery = "
    SELECT product.title, category.name AS category_name, SUM(order_details.num) AS total_quantity, SUM(order_details.num * order_details.price) AS total_revenue
    FROM order_details
    JOIN product ON order_details.product_id = product.id
    JOIN category ON product.id_category = category.id
    GROUP BY product.id
    ORDER BY total_quantity DESC
    LIMIT 3;
";
$topProductsResult = executeResult($topProductsQuery);



// Khởi tạo mảng để lưu trữ top 3 sản phẩm bán chạy nhất
$topProducts = array();

// Kiểm tra xem kết quả truy vấn có dữ liệu hay không
if ($topProductsResult && !empty($topProductsResult)) {
    // Duyệt qua từng dòng kết quả và thêm vào mảng topProducts
    foreach ($topProductsResult as $product) {
        $productInfo = array(
            'title' => $product['title'],
            'category_name' => $product['category_name'],
            'total_quantity' => $product['total_quantity'],
            'total_revenue' => $product['total_revenue']
        );
        $topProducts[] = $productInfo;
    }
}
$totalProductQuery="SELECT SUM(order_details.num) AS total_quantity FROM order_details";
$result = executeSingleResult($totalProductQuery);

// Kiểm tra nếu có kết quả
if ($result) {
    // Lấy tổng số lượng sản phẩm đã bán từ kết quả
    $totalQuantity = $result['total_quantity'];
} else {
    // Nếu không có kết quả, gán tổng số lượng sản phẩm đã bán là 0
    $totalQuantity = 0;
}

// Hiển thị thông tin top 3 sản phẩm bán chạy nhất

foreach ($queries as $key => $query) {
    $result = executeResult($query); // Thay đổi dòng này
    if ($result && !empty($result)) {
        $totalAmounts[$key] = $result[0]['total']; // Chỉ lấy giá trị đầu tiên
    } else {
        $totalAmounts[$key] = 0; // Gán giá trị 0 nếu không có kết quả hoặc kết quả rỗng
    }
}

// Kiểm tra và gán giá trị 0 cho các mặt hàng không có giá trị
foreach ($totalAmounts as $key => $total) {
    if (!isset($total)) {
        $totalAmounts[$key] = 0;
    }
}
$totalRevenue = array_sum($totalAmounts);
// In ra tổng tiền của mỗi mặt hàng
$percentages = [];
foreach ($totalAmounts as $key => $total) {
    $percentage = ($total / $totalRevenue) * 100;
    $percentages[$key] = $percentage;
}

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="newstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    
    <!-- <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var traSua = <?php echo $percentages['tra_sua']; ?>;
            var coffe = <?php echo $percentages['coffe']; ?>;
            var sinhTo = <?php echo $percentages['sinh_to']; ?>;
            var doAnVat = <?php echo $percentages['do_an_vat']; ?>;

            var dataPie = google.visualization.arrayToDataTable([
                ['Mặt hàng', 'Phần trăm'],
                ['Trà Sữa', traSua],
                ['Coffe', coffe],
                ['Sinh Tố', sinhTo],
                ['Đồ Ăn Vặt', doAnVat]
            ]);

            var dataColumn = google.visualization.arrayToDataTable([
                ['Mặt hàng', 'Tổng tiền'],
                <?php foreach ($totalAmounts as $key => $total): ?>
                    ['<?php echo ucfirst(str_replace('_', ' ', $key)); ?>', <?php echo $total; ?>],
                <?php endforeach; ?>
            ]);

            var optionsPie = {
                title: 'Phần trăm doanh thu của các danh mục',
                animationEnabled: true,
                startAngle: 240,
                yValueFormatString: "##0.00\"%\"",
                indexLabel: "{label} {y}"
            };

            var optionsColumn = {
                title: 'Tổng tiền các mặt hàng',
                legend: { position: 'none' },
                width: 300,
                height: 300
            };

            var chartPie = new google.visualization.PieChart(document.getElementById('pie_chart'));
            var chartColumn = new google.visualization.ColumnChart(document.getElementById('column_chart'));

            chartPie.draw(dataPie, optionsPie);
            chartColumn.draw(dataColumn, optionsColumn);
        }

    </script>
 -->

    <style>
        *{
            padding: 0;
            margin: 0;
            box-sizing: border-box
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="logo">
                <h2>Drink.</h2>
            </div>
            <div class="search">
                <input type="text" id="search" placeholder="Search here">
                <label for="search"><i class="fas fa-search"></i></label>
            </div>
            <div class="user">
                <img src="https://static.vecteezy.com/system/resources/previews/021/548/095/non_2x/default-profile-picture-avatar-user-avatar-icon-person-icon-head-icon-profile-picture-icons-default-anonymous-user-male-and-female-businessman-photo-placeholder-social-network-avatar-portrait-free-vector.jpg" alt="">
            </div>
            <button>Xuất PDF</button>
        </div>
        <div class="sidebar">
            <ul class="nav nav-tabs">
            <li class="nav-item">
                    <a class="nav-link" href="index.php">
                    <i class="fa-solid fa-chart-line"></i>    
                    Thống kê</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="category/index.php">
                    <i class="fa-solid fa-bars-progress"></i>    
                    Quản lý danh mục</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="product/">
                    <i class="fa-solid fa-mug-saucer"></i>    
                    Quản lý sản phẩm</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link " href="dashboard.php">
                    <i class="fa-solid fa-cart-shopping"></i>    
                    Quản lý đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customer.php">
                    <i class="fa-solid fa-person"></i>    
                    Quản lý khách hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="revenue.php">
                    <i class="fa-solid fa-chart-simple"></i>
                    Biểu đồ</a>
                </li>
            </ul>
        </div>
        <div class="main">
            <div class="cards">
                <div class="card">
                    <div class="card-content">
                        <div class="number"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> VND</div>
                        <div class="card-name">Tổng tiền</div>
                    </div>
                    <div class="icon-box">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="number">
                            <?php
                                $sql = "SELECT COUNT(*) AS total_products FROM product;";
                                $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                echo '<span>' . $row['total_products'] . '</span>';
                                mysqli_close($conn);
                            ?>
                        </div>
                        <div class="card-name">Tổng sản phẩm</div>
                    </div>
                    <div class="icon-box">
                        <i class="fas fa-mug-hot"></i>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="number">
                            <?php
                                $sql = "SELECT COUNT(*) AS total_orders FROM order_details;";
                                $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                echo '<span>' . $row['total_orders'] . '</span>';
                                mysqli_close($conn);
                            ?>
                        </div>
                        <div class="card-name">Tổng số đơn hàng</div>
                    </div>
                    <div class="icon-box">
                        <i class="fas fa-cart-shopping"></i>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="number">
                            <?php
                                $sql = "SELECT COUNT(*) AS total_users FROM user;";
                                $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                echo '<span>' . $row['total_users'] . '</span>';
                                mysqli_close($conn);
                            ?>
                        </div>
                        <div class="card-name">Tổng số khách hàng</div>
                    </div>
                    <div class="icon-box">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="charts">
                <div class="chart">
                    <h2><?php echo "Doanh thu theo từng sản phẩm - " . date("F"); ?></h2>
                    <canvas id="revenue-product"></canvas>
                    <h4 class="test"></h4>
                    <h2>Doanh thu theo 12 tháng</h2>
                    <canvas id="lineChart"></canvas>
                    <h4 class="test"></h4>
                    <h2>Top 3 người mua hàng nhiều nhất</h2>
                    <canvas id="lineUser"></canvas>
                </div>
                <div class="chart" id="doughnut-chart">
                    <h2><?php echo "KPI - " . date("d") . " " . date("F"); ?></h2>
                    <canvas id="doughnut-kpi"></canvas>
                    <h4 class="test"></h4>
                    <h2><?php echo "Doanh thu theo danh mục - " . date("F"); ?></h2>
                    <canvas id="doughnut"></canvas>
                    <h4 class="test"></h4>
                    <h2><?php echo "Doanh thu theo năm"; ?></h2>
                    <canvas id="doughnut-year"></canvas>
                    <h4 class="test"></h4>
                </div>
            </div>
            <div class="panel-body">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <td>STT</td>
                <td>Sản phẩm</td>
                <td>Số lượng</td>
                <td>Giá dự kiến</td>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql="SELECT order_details.num, product.title FROM order_details JOIN product ON order_details.product_id = product.id WHERE order_details.num > 1;";
            $productList = executeResult($sql);
            $index = 1;

            if (!empty($productList)) {
                foreach ($productList as $product) {
                    echo '<tr>
                        <td>' . ($index++) . '</td>
                        <td>' . $product['title'] . '</td>
                        <td>' . $product['num'] . '</td>
                        <td>Giá dự kiến</td> <!-- Bạn cần thay thế phần này bằng giá dự kiến thực tế -->
                    </tr>';
                }
            } else {
                echo '<tr><td colspan="4" class="text-center">Không có sản phẩm nào</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

                </table>
            </div>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
    var navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(function(link) {
      link.addEventListener('mouseover', function() {
        navLinks.forEach(function(otherLink) {
          if (otherLink !== link) {
            otherLink.classList.add('hovered');
          }
        });
      });

      link.addEventListener('mouseout', function() {
        navLinks.forEach(function(otherLink) {
          otherLink.classList.remove('hovered');
        });
      });
    });
  });
    // Hàm để lấy dữ liệu doanh thu hàng tháng qua AJAX
    function fetchMonthlyRevenue() {
        // Thực hiện một yêu cầu AJAX đến script PHP
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_monthly_revenue.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                console.log(xhr);
                // Parse dữ liệu JSON nhận được thành một mảng
                var revenueData = JSON.parse(xhr.responseText);
                updateChart(revenueData);
            }
        };
        xhr.send();
    }

    // Hàm để cập nhật biểu đồ với dữ liệu đã lấy được
    function updateChart(revenueData) {
        var ctx = document.getElementById('lineChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                datasets: [{
                    label: "Doanh thu",
                    data: revenueData,
                    backgroundColor: [
                        'rgba(85,85,85,1)',
                    ],
                    borderColor: [
                        'rgb(41,155,99)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    function fecthCategory() {
        var cate = new XMLHttpRequest();
        cate.open('GET', 'get_category_revenue.php', true);
        cate.onreadystatechange = function() {
            if (cate.readyState === 4 && cate.status === 200) {
                // Parse dữ liệu JSON nhận được thành một đối tượng JavaScript
                var responseData = JSON.parse(cate.responseText);
                // Truy cập vào mảng tên danh mục
                var categoryData = responseData.category_names;
                var categoryRevenueData = responseData.total_amounts;
                // Gọi hàm updateCategory và truyền vào mảng tên danh mục
                updateCategory(categoryData, categoryRevenueData);
            }
        };
        cate.send();
    }

    function updateCategory(categoryData, categoryRevenueData) {
        var ctx2 = document.getElementById('doughnut').getContext('2d');
        var myChart2 = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: categoryData,
                datasets: [{
                    label: 'Doanh thu theo tháng',
                    data: categoryRevenueData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 88, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    function fetchDayRevenue() {
        // Thực hiện một yêu cầu AJAX đến script PHP
        var dayReven = new XMLHttpRequest();
        dayReven.open('GET', 'get_day_revenue.php', true);
        dayReven.onreadystatechange = function() {
            if (dayReven.readyState === 4 && dayReven.status === 200) {
                var revenueDay = JSON.parse(dayReven.responseText);
                console.log(revenueDay);
                updateKpi(revenueDay);
            }
        };
        dayReven.send();
    }

    function updateKpi(revenueDay) {
        var ctx3 = document.getElementById('doughnut-kpi').getContext('2d');
        var tong = 1000000 - revenueDay
        var myChart3 = new Chart(ctx3, {
            type: 'pie',
            data: {
                labels: ['Còn lại', 'Đạt'],
                datasets: [{
                    label: ['Còn lại', 'Đạt'],
                    data: [tong, revenueDay],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    function fetchProductRevenue() {
        var product = new XMLHttpRequest();
        product.open('GET', 'get_product_revenue.php', true);
        product.onreadystatechange = function() {
            if (product.readyState === 4 && product.status === 200) {
                // Parse dữ liệu JSON nhận được thành một đối tượng JavaScript
                var responseData = JSON.parse(product.responseText);
                console.log(responseData);
                // Truy cập vào mảng tên danh mục
                var productTitle = responseData.product_names;
                var totalRevenue = responseData.total_revenue;
                // Gọi hàm updateCategory và truyền vào mảng tên danh mục
                updateProductRevenue(productTitle, totalRevenue);
            }
        };
        product.send();
    }

    function updateProductRevenue(productTitle, totalRevenue) {
        var ctx4 = document.getElementById('revenue-product').getContext('2d');
        var myChart4 = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: productTitle,
                datasets: [{
                    label: "Doanh thu",
                    data: totalRevenue,
                    backgroundColor: [
                        'rgb(25,183,212)',
                    ],
                    borderColor: [
                        'rgb(41,155,99)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    function fetchYearRevenue() {
        var yearRevenue = new XMLHttpRequest();
        yearRevenue.open('GET', 'get_year_revenue.php', true);
        yearRevenue.onreadystatechange = function() {
            if (yearRevenue.readyState === 4 && yearRevenue.status === 200) {
                // Parse dữ liệu JSON nhận được thành một đối tượng JavaScript
                var responseData = JSON.parse(yearRevenue.responseText);
                console.log(responseData);
                // Truy cập vào mảng tên danh mục
                var year = responseData.years;
                var total_revenue = responseData.total_revenues;
                // Gọi hàm updateCategory và truyền vào mảng tên danh mục
                updateYearRevenue(year, total_revenue);
            }
        };
        yearRevenue.send();
    }

    function updateYearRevenue(year, total_revenue) {
        var ctx5 = document.getElementById('doughnut-year').getContext('2d');
        var myChart5 = new Chart(ctx5, {
            type: 'polarArea',
            data: {
                labels: year,
                datasets: [{
                    label: year,
                    data: total_revenue,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    function fetchProductRevenue() {
        var product = new XMLHttpRequest();
        product.open('GET', 'get_product_revenue.php', true);
        product.onreadystatechange = function() {
            if (product.readyState === 4 && product.status === 200) {
                // Parse dữ liệu JSON nhận được thành một đối tượng JavaScript
                var responseData = JSON.parse(product.responseText);
                console.log(responseData);
                // Truy cập vào mảng tên danh mục
                var productTitle = responseData.product_names;
                var totalRevenue = responseData.total_revenue;
                // Gọi hàm updateCategory và truyền vào mảng tên danh mục
                updateProductRevenue(productTitle, totalRevenue);
            }
        };
        product.send();
    }

    function updateProductRevenue(productTitle, totalRevenue) {
        var ctx4 = document.getElementById('revenue-product').getContext('2d');
        var myChart4 = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: productTitle,
                datasets: [{
                    label: "Doanh thu",
                    data: totalRevenue,
                    backgroundColor: [
                        'rgb(25,183,212)',
                    ],
                    borderColor: [
                        'rgb(41,155,99)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    function fetchUserRevenue() {
        var user = new XMLHttpRequest();
        user.open('GET', 'get_top_buys.php', true);
        user.onreadystatechange = function() {
            if (user.readyState === 4 && user.status === 200) {
                // Parse dữ liệu JSON nhận được thành một đối tượng JavaScript
                var responseData = JSON.parse(user.responseText);
                console.log(responseData);
                // Truy cập vào mảng tên danh mục
                var user_name = responseData.user_names;
                var total_spent = responseData.total_spent;
                // Gọi hàm updateCategory và truyền vào mảng tên danh mục
                updateUserRevenue(user_name, total_spent);
            }
        };
        user.send();
    }

    function updateUserRevenue(user_name, total_spent) {
        var ctx6 = document.getElementById('lineUser').getContext('2d');
        var myChart6 = new Chart(ctx6, {
            type: 'bar',
            data: {
                labels: user_name,
                datasets: [{
                    label: "Số tiền",
                    data: total_spent,
                    backgroundColor: [
                        'rgb(215,236,251)',
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    // Gọi hàm để lấy dữ liệu doanh thu hàng tháng khi trang tải
    window.onload = function() {
        fetchMonthlyRevenue();
        fecthCategory();
        fetchDayRevenue();
        fetchProductRevenue();
        fetchYearRevenue();
        fetchUserRevenue();
    };
</script>

</body>
</html>

