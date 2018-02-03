<?php session_start(); error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Coupon Crazy</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    /* Remove the navbar's default margin-bottom and rounded borders */
    .navbar {
      margin-bottom: 0;
      border-radius: 0;
    }

    .error {
      color: red;
    }

    .good{
      color: green;
    }

    .blue {
      color: blue;
    }

    /* Set height of the grid so .sidenav can be 100% (adjust as needed) */
    .row.content {height: 450px}

    /* Set gray background color and 100% height */
    .sidenav {
      padding-top: 20px;
      background-color: #f1f1f1;
      height: 100%;
    }

    /* Set black background color, white text and some padding */
    footer {
      background-color: #555;
      color: white;
      padding: 15px;
    }

    /* On small screens, set height to 'auto' for sidenav and grid */
    @media screen and (max-width: 767px) {
      .sidenav {
        height: auto;
        padding: 15px;
      }
      .row.content {height:auto;}
    }

    table {
      color: #333;
      font-family: Helvetica, Arial, sans-serif;
      width: 840px;
      border-collapse:
      collapse; width: 100%;
    }

    td, th {
      border: 1px solid transparent; /* No more visible border */
      height: 30px;
      transition: all 0.3s;  /* Simple transition for hover effect */
    }

    th {
      background: #DFDFDF;  /* Darken header a bit */
      font-weight: bold;
      text-align: center;
    }

    td {
      background: #FAFAFA;
      text-align: left;
    }

    /* Cells in even rows (2,4,6...) are one color */
    tr:nth-child(even) td { background: #F1F1F1; }

    /* Cells in odd rows (1,3,5...) are another (excludes header cells)  */
    tr:nth-child(odd) td { background: #FEFEFE; }

    tr td:hover { background: #666; color: #FFF; }
  </style>
</head>
<body>
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="index.php">Coupon Crazy
      <?php
        //display different banner identification based on session type
        if(isset($_SESSION['type'])){
        switch ($_SESSION['type']) {
          case 'user':
            echo " - Basic User";
            break;
          case 'admin':
            echo " - Administrator";
            break;
          case 'store_owner':
            echo " - Store Owner";
            break;
        }
      }
      else{
        echo "";
      }
      ?></a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="index.php">Home</a></li>
        <?php
        //lead settings to different sites depending on user type
        if(isset($_SESSION['type'])){
        switch ($_SESSION['type']) {
          case 'user':
            echo "<li><a href='userSetting.php'>Setting</a></li>";
            break;
          case 'admin':
            echo "<li><a href='adminSetting.php'>Setting</a></li>";
            break;
          case 'store_owner':
            echo "<li><a href='storeOwnerSetting.php'>Setting</a></li>";
            break;
        }
      }
      else{
        echo "<li><a href='login.php'>Setting</a></li>";
      }
        ?>

        <li class= "active"><a href="checkout.php">Cart</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if(isset($_SESSION['username'])): ?>
        <li><a href="logout.php"><span class="glyphicon glyphicon-log-in"></span> Logout</a></li>
        <?php else: ?>
        <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login/Signup</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-8 text-left">


     <h1>Cart</h1>
	 <?php
   //initialize database
   require_once(dirname(__DIR__)."/Models/Coupon.php");
   require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
		if(isset($_SESSION["username"]) && isset($_SESSION["type"])){
			if($_SESSION["type"] == "user"){
				$db = new CCdb;

        //script to remove coupon from displayed cart and display html accordingly
				if(isset($_POST["coupons"])){
					foreach($_POST["coupons"] as $coupon){
						try {
							$db->removeFromCart($_SESSION["username"], $coupon);
              echo "<b><span class='good'>Coupon successfully removed from your cart.</span></b><br>";
						} catch(Exception $e) {
							echo $e->getMessage() . "<br>";
						}
					}
				}
				$coupons = $db->getCart($_SESSION["username"]);
				if(count($coupons) > 0){
					echo "<form method=\"POST\" action=\"checkout.php\">
					<table>
						<tr>
							<th>Name</th><th>Description</th><th>Store</th><th>Discount</th><th>Remove</th>
						</tr>";
					foreach($coupons as $coupon){
						echo '<tr><td>' . $coupon->getCouponName() . '</td>';
						echo '<td>' . $coupon->getDescription() . '</td>';
						echo '<td>' . $coupon->getStore() . '</td>';
						echo '<td>$' . number_format($coupon->getDiscount(),2) . '</td>';
						echo '<td><input type="checkbox" name="coupons[]" value="' . $coupon->getCouponName() . '"></td></tr>';
					}
          //below is html to lead to redemption page
					echo '</table>
					<br>
					<button type="submit" value="Submit">Remove selected coupons from cart</button>
					</form>
					<form method="POST" action="RedemptionSim.php">
					<br>
          <h3>Redemption</h3><b>Go here to simulate an actual purchase with your cart\'s coupons.</b><br>
					<button type="submit" value="Submit">Redeem</button>
					</form>';
				} else {
					echo 'Your cart is currently empty.';
				}
			} else {
				echo "You are not a basic user.  Please log out and log back in as a basic user.<br>";
			}
		} else {
			echo "Please log in to view your cart<br>";
		}
	?>
</div>
</div>
</div>

<footer class="container-fluid text-center">
<p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
