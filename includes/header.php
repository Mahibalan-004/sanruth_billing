<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial;
        }

        /* TOP BAR */
        .topbar {
            background: #fff;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            position: fixed;
            width: 100%;
            z-index: 1000;
        }

        .brand {
            font-weight: bold;
            color: green;
            font-size: 18px;
        }

        /* MENU */
        .menu-container {
            border-bottom: 2px solid #eee;
            background: #fff;
            position: fixed;
            width: 100%;
            z-index: 999;
            margin-top: 50px;
        }

        .menu-bar {
            display: flex;
            margin: 0;
            padding: 0;
            list-style: none;
            margin-left: 200px;
        }

        .menu-item {
            position: relative;
        }

        .menu-item a {
            display: block;
            padding: 12px 18px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .menu-item:hover {
            background: #f5f5f5;
        }

        /* DROPDOWN */
        .dropdown {
            display: none;
            position: absolute;
            background: #fff;
            min-width: 200px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            top: 100%;
            left: 0;
            z-index: 1000;
        }

        .dropdown a {
            padding: 10px;
            display: block;
            color: #333;
        }

        .dropdown a:hover {
            background: #f0f0f0;
        }

        /* SUB MENU (RIGHT SIDE) */
        .submenu {
            position: relative;
        }

        .submenu-dropdown {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background: #fff;
            min-width: 180px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* SHOW WHEN ACTIVE */
        .submenu.active .submenu-dropdown {
            display: block;
        }

        /* SHOW DROPDOWN */
        .menu-item.active .dropdown {
            display: block;
        }

        /* MOBILE */
#menuToggle {
    display: none;
    background:none;
    border:none;
    cursor:pointer;
}

/* MOBILE VIEW */
@media (max-width: 768px) {

    .menu-bar {
        flex-direction: column;
        display: none;
        margin-left: 0;
    }

    .menu-bar.active {
        display: flex;
    }

    .menu-item {
        width: 100%;
    }

    .dropdown {
        position: static;
        box-shadow: none;
    }

    .submenu-dropdown {
        position: static;
        margin-left: 15px;
    }

    #menuToggle {
        display: block;
    }
}
    </style>
</head>

<body>

    <!-- TOP BAR -->
    <div class="topbar">
        <div class="brand">Sewing Machines</div>

        <button onclick="window.location.href='../login/logout.php'"
            style="background:#f44336;color:white;padding:8px 15px;border:none;border-radius:5px;cursor:pointer;">
            Logout
        </button>
    </div>

    <br><br>
<button id="menuToggle" style="font-size:20px;">☰</button>
    <!-- MENU -->
    <div class="menu-container">
        <ul class="menu-bar">

            <li class="menu-item">
                <a href="../login/dashboard.php">Dashboard</a>
            </li>

            <!-- JOB CARD -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Job Card</a>
                <div class="dropdown">
                    <a href="../jobcard/create.php">Create Job</a>
                    <a href="../jobcard/list.php">Job Card Bills</a>
                </div>
            </li>

            <!-- SALES -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Sales</a>
                <div class="dropdown">
                    <a href="../sales/create.php">Create Sales</a>
                    <a href="../sales/list.php">Sales List</a>
                </div>
            </li>

            <!-- STOCK -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Stock</a>
                <div class="dropdown">
                    <a href="../stock/add_stock.php">Add Stock</a>
                    <a href="../stock/list.php">Stock List</a>
                </div>
            </li>

            <!-- PURCHASE -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Purchase</a>
                <div class="dropdown">
                    <a href="../purchase/create.php">Create Purchase</a>
                    <a href="../purchase/list.php">Purchase List</a>
                </div>
            </li>

            <!-- CUSTOMER -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Customer</a>
                <div class="dropdown">
                    <a href="../customers/c_list.php">Customers</a>
                    <a href="../customers/s_list.php">Suppliers</a>
                </div>
            </li>

            <!-- MASTER (NEW) -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Master</a>
                <div class="dropdown">

                    <!-- SPARES -->
                    <div class="submenu">
                        <a href="#" class="submenu-link">Spares ▶</a>
                        <div class="submenu-dropdown">
                            <a href="../spares/add_spare.php">+ New</a>
                            <a href="../spares/list_spare.php">List</a>
                            <a href="../spares/list_stock.php">Stock</a>
                        </div>
                    </div>

                    <!-- BRAND -->
                    <div class="submenu">
                        <a href="#" class="submenu-link">Brand ▶</a>
                        <div class="submenu-dropdown">
                            <a href="../brand/add.php">+ New</a>
                            <a href="../brand/list.php">List</a>
                        </div>
                    </div>

                    <!-- MODEL -->
                    <div class="submenu">
                        <a href="#" class="submenu-link">Model ▶</a>
                        <div class="submenu-dropdown">
                            <a href="../model/add.php">+ New</a>
                            <a href="../model/list.php">List</a>
                        </div>
                    </div>

                </div>
            </li>

            <!-- REPORT -->
            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Report</a>
                <div class="dropdown">
                    <a href="../report/daily_sales.php">Daily Sales</a>
                    <a href="../report/monthly_sales.php">Monthly Sales</a>
                </div>
            </li>

        </ul>
    </div>

    <script>
        // MAIN MENU
document.querySelectorAll(".menu-link").forEach(link => {
    link.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation(); // 🔥 IMPORTANT

        let parent = this.parentElement;

        document.querySelectorAll(".menu-item").forEach(item => {
            if (item !== parent) item.classList.remove("active");
        });

        parent.classList.toggle("active");
    });
});

// SUBMENU FIX
document.querySelectorAll(".submenu-link").forEach(link => {
    link.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation(); // 🔥 VERY IMPORTANT

        let parent = this.parentElement;

        document.querySelectorAll(".submenu").forEach(menu => {
            if (menu !== parent) menu.classList.remove("active");
        });

        parent.classList.toggle("active");
    });
});

// OUTSIDE CLICK
document.addEventListener("click", function(e) {
    if (!e.target.closest(".menu-item")) {
        document.querySelectorAll(".menu-item").forEach(item => {
            item.classList.remove("active");
        });

        document.querySelectorAll(".submenu").forEach(menu => {
            menu.classList.remove("active");
        });
    }
});

document.getElementById("menuToggle").addEventListener("click", function() {
    document.querySelector(".menu-bar").classList.toggle("active");
});
    </script>

</body>

</html>