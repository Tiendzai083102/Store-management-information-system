<?php require_once('database/dbhelper.php'); ?>
<!DOCTYPE html>
<html>

<head>
    <title>Quản Lý Khách Hàng</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
</head>

<body>
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
        </div>
        <div class="sidebar">      
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link " href="../index.php">
                    <i class="fa-solid fa-chart-line"></i>    
                    Thống kê</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                    <i class="fa-solid fa-bars-progress"></i>    
                    Quản lý danh mục</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="../product/">
                    <i class="fa-solid fa-mug-saucer"></i>    
                    Quản lý sản phẩm</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link " href="dashboard.php">
                    <i class="fa-solid fa-cart-shopping"></i>    
                    Quản lý đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="customer.php">
                    <i class="fa-solid fa-person"></i>    
                    Quản lý khách hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="revenue.php">
                    <i class="fa-solid fa-chart-simple"></i>
                    Biểu đồ</a>
                </li>
            </ul>
        </div>
    <div class="main">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h2 class="text-center">Quản lý khách hàng</h2>
            </div>
            <div class="panel-body">
                <a href="../login/reg.php">
                    <button id="add_customer" style="margin-bottom:20px">Thêm khách hàng</button>
                </a>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <td width="70px">STT</td>
                            <td>Tên khách hàng</td>
                            <td>Tên Tài khoản</td>
                            <td>Điện thoại</td>
                            <td>Email</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Lấy danh sách khách hàng
                        $sql = 'SELECT * FROM user';
                        $userList = executeResult($sql);
                        $index = 1;

                        if (!empty($userList)) {
                            foreach ($userList as $user) {
                                echo '<tr>
                                    <td>' . ($index++) . '</td>
                                    <td>' . $user['hoten'] . '</td>
                                    <td>' . $user['username'] . '</td>
                                    <td>' . $user['phone'] . '</td>
                                    <td>' . $user['email'] . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Không có khách hàng nào</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        document.getElementById('add_customer').addEventListener('click', function() {
            console.log('Đã ấn');
            window.location.href = '../login/reg.php';
        });
    </script>
</body>
<style>
        /* *{
            padding: 0;
            margin: 0;
            box-sizing: border-box */
        /* } */
    .container{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
  /* Đảm bảo không có căn chỉnh đặc biệt nào */
            text-align: left;
            position: relative;
        }
        .user {
            position: relative;
            width: 50px;
            height: 50px;
        }

        
        .user img {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            object-fit: cover
        }
        .topbar {
            position: fixed;
            background: #fff;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.08);
            width: 100%;
            height: 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 10fr 0.4fr 1fr;
            align-items: center;
            z-index: 1;
        }

        .search {
            position: relative;
            width: 60%;
            justify-self: center;
        }

        .search input{
            width: 100%;
            height: 40px;
            padding: 0 40px;
            font-size: 16px;
            outline: none;
            border: none;
            border-radius: 10px;
            background: #f5f5f5;
        }

        .search i {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            width: 260px;
            height: calc(100% - 60px);
            background: #299B63; 
            overflow-x: hidden;
        }

        .sidebar ul {
            margin-top: 20px;
        }

        .sidebar ul li {
            width: 100%;
            list-style: none;
        }

        .sidebar ul li a {
            width: 100%;
            text-decoration: none;
            color: #fff;
            height: 60px;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            min-width: 60px;
            font-size: 24px;
            text-align: center;
        }
        .main {
            top:80px;
            position: absolute;
            top: 60px;
            width: calc(100% - 10%);
            left: 260px;
            min-height: calc(100vh - 60px);
            background: #f5f5f5;
        }

        .cards {
            width: 100%;
            padding: 35px 20px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 20px;
        }


        .icon-box i {
            font-size: 45px;
            color: #299B63;
        }

        .topbar button {
            height: 30px;
        }
    </style>

</html>
