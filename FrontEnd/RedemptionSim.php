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

	.good {
      color: green;
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
      width: 640px;
      border-collapse:
      collapse; border-spacing: 0;
    }

    td, th {
      border: 1px solid transparent; /* No more visible border */
      height: 30px;
      transition: all 0.3s;  /* Simple transition for hover effect */
    }

    th {
      background: #DFDFDF;  /* Darken header a bit */
      font-weight: bold;
    }

    td {
      background: #FAFAFA;
      text-align: center;
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
        <a class="navbar-brand" href="index.php">Coupon Crazy - Basic User</a>
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav">
          <li><a href="index.php">Home</a></li>
          <li><a href="userSetting.php">Setting</a></li>
          <li class="active"><a href="checkout.php">Cart</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <?php if(isset($_SESSION['type'])): ?>
          <li><a href="logout.php"><span class="glyphicon glyphicon-log-in"></span> Logout</a></li>
        <?php else: ?>
          <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
        <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-8 text-left">


     <h1>Redemption</h1>

      <h3>Choose a Store</h3>
      <aside>*The inventory that will be listed for each store is not realistic and is only a simulation of offered items.</aside>
          <form method="GET" action="RedemptionSim.php">
          <select name="storechoice">
          <?php
            require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
            $database = new CCdb;
            $storeArr = $database->getStores();
            foreach($storeArr as $store)
            {
              echo "<option>$store</option>";
            }
          ?>
          </select>
          <input type="submit">
          </form>
          <?php
            //display hardcoded list of items for each store.
            //we are doing this because this is only a simulation for checking out not an actual inventory list for each store
            if(isset($_GET['storechoice'])){
				echo '<form method="POST" action="RedemptionSim.php"><input type="hidden" name="store" value="' . $_GET['storechoice'] . '">';
					echo "<table>";
                    echo '<tr><td><input type="checkbox" name="items[]" value="Betty Crocker Cake"></td><td><label>Betty Crocker Cake</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Bounty Paper Towel Roll"></td><td><label>Bounty Paper Towel Roll</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Charmin Toilet Paper"></td><td><label>Charmin Toilet Paper</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Dawn Dish Soap"></td><td><label>Dawn Dish Soap</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Nutella"></td><td><label>Nutella</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Progresso Soup"></td><td><label>Progresso Soup</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Tide Detergent"></td><td><label>Tide Detergent</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Glad Trash Bags"></td><td><label>Glad Trash Bags</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Swanson Broth"></td><td><label>Swanson Broth</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Planters"></td><td><label>Planters</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Reynolds Wrap Foil"></td><td><label>Reynolds Wrap Foil</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Windex"></td><td><label>Windex</label></td></tr>';
                    echo '<tr><td><input type="checkbox" name="items[]" value="Ziploc"></td><td><label>Ziploc</label></td></tr>';
					echo "</table>";
					echo '<br><button type="submit" name="submit">Check out selected items</button></form><br>';
            }
           ?>
	 <?php
   //initialize database
    require_once(dirname(__DIR__)."/Models/Coupon.php");
    require_once(dirname(__DIR__)."/Models/CCException.php");
    require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
		if(isset($_SESSION["username"]) && isset($_SESSION["type"])){
			if($_SESSION["type"] == "user"){
				$db = new CCdb;

        //script for displaying user discount
				if(isset($_POST['items'])){
					try {
						$discount = $db->checkoutCart($_SESSION["username"], $_POST['items'], $_POST['store']);
						if($discount > 0)
							echo '<h3 class="good">You saved $' . number_format($discount,2) . '!  Matching coupons have been removed.</h3>';
						else
							echo '<h3>None of your coupons matched any selected items.  No coupons were removed and you saved $0.00.</h3>';
					}
					catch(CCException $e) {echo '<p class="error">' . $e->getString() . '</p>';}
					catch(Exception $e) {echo '<p class="error">' . $e->getMessage() . '</p>';}
				}

        //display all coupons of the users cart
				$coupons = $db->getCart($_SESSION["username"]);
				if(count($coupons) > 0 && isset($_GET['storechoice'])){
					echo "<table>
						<tr>
							<th>Coupon Name</th><th>Description</th><th>Store</th><th>Discount</th>
						</tr>";
					foreach($coupons as $coupon){
						if($_GET['storechoice'] == $coupon->getStore()) {
							echo '<tr><td>' . $coupon->getCouponName() . '</td>';
							echo '<td>' . $coupon->getDescription() . '</td>';
							echo '<td>' . $coupon->getStore() . '</td>';
							echo '<td>$' . number_format($coupon->getDiscount(),2) . '</td>';
						}
					}
					echo '</table>
					<br>';
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
        </table>

    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
