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

    .alpha table {
      color: #333;
      font-family: Helvetica, Arial, sans-serif;
      width: 840px;
      border-collapse:
      collapse; width: 100%;
    }

    .alpha td, .alpa th {
      border: 1px solid transparent; /* No more visible border */
      height: 30px;
    }

    .alpha th {
      background: #DFDFDF;  /* Darken header a bit */
      font-weight: bold;
      text-align: center;
    }

    .alpha td {
      background: #FAFAFA;
      text-align: left;
    }

    /* Cells in even rows (2,4,6...) are one color */
    .alpha tr:nth-child(even) td { background: #F1F1F1; }

    /* Cells in odd rows (1,3,5...) are another (excludes header cells)  */
    .alpha tr:nth-child(odd) td { background: #FEFEFE; }
  </style>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="index.php">Coupon Crazy - Store Owner</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="index.php">Home</a></li>
        <li class="active"><a href="storeOwnerSetting.php">Setting</a></li>
        <li><a href="checkout.php">Cart</a></li>
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

        <?php
        //filter for store_owner not being logged in
        if(isset($_SESSION['type'])) {
          if(!($_SESSION['type'] == 'store_owner')){
            echo "<h1>You are not a Store Owner!</h1>";
            die("<p><a href='index.php'>Please return to the home page.</a>");
          }
        }
        else {
          echo "<h1>You are not a Store Owner!</h1>";
          die("<p><a href='login.php'>Log in as a Store Owner to continue.</a>");
        }
        //initialize database
        require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
        require_once(dirname(__DIR__)."/Models/CCException.php");
        //success message initialization
        $successmess['addcoup'] = false;
        $successmess['chgquant'] = false;
        $successmess['delcoup'] = false;
        $successmess['delaccount'] = false;
        $successmess['chgpass'] = false;
        //helper function for user submitted password
			function validatePw($pw){
              if(strlen($pw) >= 15){
                  throw new Exception("Password length must be less than 15 characters.");
              }

              if(strlen($pw) <= 3){
                  throw new Exception("Password length must be greater than 3 characters.");
              }

              if(!preg_match('/^[A-Za-z0-9_~\-!@#\$%\^&*\(\)]+$/',$pw)){
                  throw new Exception("Password contains an invalid character");
              }
          }
          //helper function for user data sanatizaiton
          function mysql_entities_fix_string($connection, $string)
          {
            return htmlentities(mysql_fix_string($connection, $string));
          }

          function mysql_fix_string($connection, $string)
          {
            if (get_magic_quotes_gpc()) $string = stripslashes($string);
            return $connection->real_escape_string($string);
          }
			    $delerror = "";
          if(isset($_SESSION['type']) && $_SESSION['type'] == 'store_owner'){
            $oldpwerror = '';
            $pwmatcherror = '';
            $pwerror = '';
            $reseterror = '';
            //initialize database
            require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
            require_once(dirname(__DIR__)."/DataAccess/dblogin.php");
            $database = new CCdb();
            $connection = new mysqli($hn, $un, $pw, $db);
            if($connection->connect_error) die($connection->connect_error);
            $error = NULL;

            //script for deleting account
            if (isset($_POST['delusername']) && isset($_POST['delpassword']) && isset($_SESSION['username'])) {
                $un_temp = mysql_entities_fix_string($connection, $_POST['delusername']);
                $pw_temp = mysql_entities_fix_string($connection, $_POST['delpassword']);
                if($un_temp == $_SESSION['username']) {
                  $database = new CCdb;
                  $result = $database->getUsers($un_temp);
                  if ($result) {
                      $salt1 = "qm&h*";
                      $salt2 = "pg!@";
                      $token = hash('ripemd128', "$salt1$pw_temp$salt2");
                      if ($token == $result[0]['password']) {
                          $database->deleteAccount($un_temp);
                          header("Location: logout.php");
                          die();
                      }
                      else $error = 'Incorrect password.';
                  }
                  else $error = 'The username does not match your account.';
                }
                else $error = 'The username does not match your account.';
            }

            //script for changing password
            if (isset($_POST['oldpassword']) && isset($_POST['newpassword']) && isset($_POST['repassword']) && isset($_SESSION['username'])) {
              if($_POST['newpassword'] != $_POST['repassword']){
                $pwmatcherror = "Passwords must match.";
              }
              else{
              $oldpw_temp = mysql_entities_fix_string($connection, $_POST['oldpassword']);
              $newpw_temp = mysql_entities_fix_string($connection, $_POST['newpassword']);
                try{
                  validatePw($newpw_temp);
                  $salt1 = "qm&h*";
                  $salt2 = "pg!@";
                  $newpwHash = hash('ripemd128', "$salt1$newpw_temp$salt2");
                  $oldpwHash = hash('ripemd128', "$salt1$oldpw_temp$salt2");
                  if($oldpwHash != $_SESSION['password']){
                    $oldpwerror = "That is not your old password.";
                  }
                  try{
                    $database->changePassword($_SESSION['username'], $oldpwHash, $newpwHash);
                    $reseterror['userpass'] = 'Password Changed!';
    				        $_SESSION["password"] = $newpwHash;
                    $successmess['chgpass'] = true;
                  }
                  catch(CCException $e){
                    $reseterror = $e->getErrArray();
                  }
                }
                catch(Exception $e){
                  $pwerror = $e->getMessage();
                }
              }
        			unset($_POST['oldpassword']);
        			unset($_POST['newpassword']);
        			unset($_POST['repassword']);
        			unset($_POST['username']);
            }
		      }
          else {
              echo "You are not logged in as a store owner.  Please log out and log back in as a store owner to view this page.";
              die("<p><a href='login.php'>Log in</a> to continue.");
          }
          ?>

			<?php
			//Create a coupon
			if(isset($_POST["couponname"])){
				try{
					$coupon = new Coupon($_POST["couponname"], $_POST["discount"], $_POST["description"], $_POST["couponname"], $_POST["quantity"], $database->getOwnedStore($_SESSION["username"]));
					$database->addCoupon($coupon);
          $successmess['addcoup'] = true;
					unset($_POST["couponname"]);
					unset($_POST["discount"]);
					unset($_POST["description"]);
					unset($_POST["type"]);
					unset($_POST["quantity"]);
				}
				catch(CCException $e){echo $e->getString();}
				catch(Exception $e){
					$e = new CCException();
					$e->addErr("unknown","Unknown error in storeOwnerSetting.addCoupon()");
					throw $e;
				}
			}
			?>
      <h1>Store Owner Setting</h1>
      <?php
        if($successmess['addcoup']) echo "<span class='good'>New coupon has been added.</span>";
        if($successmess['delaccount']) echo "<span class='good'></span>";
        if($successmess['chgpass']) echo "<span class='good'>Password has been changed.</span>";
      ?>
      <hr>
        <form method="POST" action="storeOwnerSetting.php">

			<h3> Add Coupon </h3>
			<table class='alpha'>
            <tr><td><label><b>Coupon Name</b></label></td>
            <td><input type="text" placeholder="Coupon Name" name="couponname" required <?php if(isset($_POST["couponname"])) echo 'value="' . $_POST["couponname"] . '"';?>></td></tr>
            <tr><td><label><b>Discount</b></label></td>
            <td><input type="text" placeholder="Discount" name="discount" required <?php if(isset($_POST["discount"])) echo 'value="' . $_POST["discount"] . '"';?>></td></tr>
            <tr><td><label><b>Coupon Description</b></label></td>
            <td><input type="text" placeholder="Coupon Description" name="description" required <?php if(isset($_POST["description"])) echo 'value="' . $_POST["description"] . '"';?>></td></r>
            <tr><td><label><b>Quantity</b></label></td>
            <td><input type="text" placeholder="Quantity" name="quantity" required <?php if(isset($_POST["quantity"])) echo 'value="' . $_POST["quantity"] . '"';?>></td></tr>
            </table>
            <input type = "submit" value="Add">
        </form>



		<h3> Change Quantity or Delete Coupons </h3>

		<form method="POST" action="storeOwnerSetting.php">
		<?php
			// Adjust coupon quantities

			$database = new CCdb;
			if(isset($_POST["adjCoupons"])){
				foreach($_POST["adjCoupons"] as $couponname => $adjustment){
          if($adjustment){
  					try{
  						$database->adjustQuantity($couponname, $adjustment);
  						$successmess['chgquant'] = true;
  					} catch(CCException $e) {
  						echo $e->getString();
  					} catch(Exception $e) {
  						echo $e->getMessage();
  					}
  				}
        }
				unset($_POST["adjCoupons"]);
			}

			// Remove coupons
			if(isset($_POST["delCoupons"])){
				foreach($_POST["delCoupons"] as $couponname){
					try {
            $database->removeCoupon($couponname);
            $successmess['delcoup'] = true;
          }
					catch(CCException $e) {echo $e->getString();}
					catch(Exception $e) {echo $e->getMessage();}
				}
				unset($_POST["delCoupons"]);
			}


      if($successmess['chgquant']){
        echo "<span class='good'>Coupon quantity changed.</span>";
        $successmess['chgquant'] = false;
      }
      if($successmess['delcoup']){
        echo "<span class='good'>Coupon has been deleted.</span>";
        $successmess['delcoup'] = false;
      }

      try {$coupons = $database->getOwnedCoupons($_SESSION['username']);}
			catch(CCException $e) {echo $e->getString(); $coupons = array();}
      if(!empty($coupons)){
            echo "<table class='alpha'>
              <tr><th>Couponname</th><th>Description</th><th>Item Type</th><th>Discount</th><th>Quantity</th><th>Adjust Quantity</th><th>Delete</th></tr>";
            foreach($coupons as $coupon) {
				echo "<tr><td>" . $coupon->getCouponName();
				echo "</td><td>" . $coupon->getDescription();
				echo "</td><td>" . $coupon->getItemType();
				echo "</td><td>$" . number_format($coupon->getDiscount(),2);
				echo "</td><td>" . $coupon->getQuantity();
				echo '</td><td><input type="number" name="adjCoupons[' . $coupon->getCouponName() . ']" min="' . (0 - $coupon->getQuantity()) . '">';
				echo '</td><td><input type="checkbox" name="delCoupons[]" value="' . $coupon->getCouponName() . '">';
              echo "</td></tr>";
            }
            echo '</table><br><input type="submit" value="Commit changes"></form>';
          }
          else {
            echo "<span class='error'><b>Your store contains no coupons!</b></span>";
          }
          ?>
		<br>
		<p style="color: red">
           <?php echo $error; ?>
          </p>
          <form role="form" method="post" action="storeOwnerSetting.php">
            <h3> Delete Account </h3>
            <p>Please Enter Your Username and Password.</p>
            <label><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="delusername" required>
            <input type="password" placeholder="Enter Paassword" name="delpassword" required>
            <span class="error"><?php echo $delerror; ?></span>
            <br>
            <input type = "submit" value="Delete">
          </form>


          <form role="form" method="post" action="storeOwnerSetting.php">
            <h3> Change Password </h3>
            <label><b>Current Password</b></label>
            <input type="text" placeholder="Current Password" name="oldpassword" required>
            <span class="error"><?php echo $oldpwerror; ?></span>
            <br>
            <label><b>New Password</b></label>
            <input type="text" placeholder="New Password" name="newpassword" required>
            <span class="error"><?php echo $pwerror;?></span>
            <br>
            <label><b>Repeat Password</b></label>
            <input type="text" placeholder="New Password" name="repassword" required>
            <span class="error"><?php echo $pwmatcherror; ?></span>
            <br>
            <input type = "submit" value="Change">
          </form>
	</form>
    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
