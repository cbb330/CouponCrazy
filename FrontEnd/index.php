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
        <li class="active"><a href="index.php">Home</a></li>
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

        <?php if(isset($_SESSION['username'])): ?>
          <li><a href="checkout.php">Cart</a></li>
        <?php else: ?>
          <li><a href="login.php">Cart</a></li>
        <?php endif?>
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
<?php
  //initialize database
  require_once(dirname(__DIR__)."/Models/CCException.php");
  require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
  $database = new CCdb;
?>

  <?php
  //script to add coupons to a cart
  if(isset($_POST["addToCart"])){
    $successmess = false;
    if(isset($_SESSION["username"]) && isset($_SESSION["type"])){
      if($_SESSION["type"] == "user"){
        foreach($_POST["addToCart"] as $coupon){
          try{
            $database->addToCart($_SESSION["username"], $coupon);
            $successmess = true;
          } catch(CCException $e) {
            echo $e->getString();
          } catch(Exception $e) {
            echo $e->getMessage();
          }
        }
      }
      else echo "<br><span class='error'>You are not logged in as a basic user.  Please log out and log back in as a basic user to add coupons to your cart.</span>";
    }
    else echo "<br><span class='error'>You are not logged in.  Please log in as a basic user to add coupons to your cart.</span>";
  }
  ?>
<div class="container-fluid text-center">
	<div class="row content">
		<div class="col-sm-8 text-left">
      <br>
      <h1><b>Hello!</h1> Welcome to Coupon Crazy, a website to browse and checkout coupons.<b>
			<h2>Stores</h2>
			<form method="GET" action="index.php">
					<select name="storechoice">
          <?php
          //display all stores
					$storeArr = $database->getStores();
					foreach($storeArr as $store)
					{
						echo "<option>$store</option>";
					}
				?>
				</select>
				<input type="submit" value="View coupons from this store.">
			</form>
	<?php
    //take user get input and store in a session so the table remains viewable.
    if(isset($_GET['storechoice'])){
      $_SESSION['storechoice'] = $_GET['storechoice'];
    }
    if(isset($_SESSION['storechoice'])){
			$couponArr = $database->getCoupons($_SESSION['storechoice']);
      if(empty($couponArr)){
        echo "<span class='error'>" . $_SESSION['storechoice'] . " does not offer any coupons.</span>";
      }
      else {
				echo "<h2>Coupons from <b><span class='blue'>" . $_SESSION['storechoice'] . '</span></b></h2>';
        if(isset($successmess) && $successmess) echo "<span class='good'>Coupons successfully added to cart!</span>";
				echo '<form method="POST" action="index.php"><table>
					    <tr><th> Coupon Name </th><th> Description </th><th> Discount </th><th> Quantity </th><th> Select </th></tr>';
				foreach($couponArr as $coupon) {
					echo "<tr><td>" . $coupon->getCouponName();
					echo "</td><td>" . $coupon->getDescription();
					echo "</td><td>$" . number_format($coupon->getDiscount(),2);
					echo "</td><td>" . $coupon->getQuantity();
					echo '</td><td>';
					if(isset($_SESSION["username"])) {
						if($database->hasCoupon($_SESSION["username"],$coupon->getCouponName())) {
							echo "in cart";
						}
            else echo '<input type="checkbox" name="addToCart[]" value="' . $coupon->getCouponName() . '">';
          }
          else echo '<input type="checkbox" name="addToCart[]" value="' . $coupon->getCouponName() . '">';
					echo "</td></tr>";
				}
			  echo '</table><br><input type="submit" value="Add selected coupons to Cart"></form>';
      }
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
