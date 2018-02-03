<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Coupon Crazy</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <style>
    /* Remove the navbar's default margin-bottom and rounded borders */
    .navbar {
      margin-bottom: 0;
      border-radius: 0;
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
  </style>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="index.php">Coupon Crazy</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="index.php">Home</a></li>
        <li><a href="userSetting.php">Setting</a></li>
        <li><a href="checkout.php">Cart</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="active"><a href="signup.php"><span class="glyphicon glyphicon-log-in"></span> Login/Signup</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-8 text-left">

    <h2>Reset Password </h2>
	<?php
  //initialize database and begin script for resetting password
	if(isset($_POST["username"]) && isset($_POST["email"])) {
    require_once(dirname(__DIR__)."/Models/User.php");
    require_once(dirname(__DIR__)."/Models/CCException.php");
    require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
		$db = new CCdb;
		try{
			$db->requestReset($_POST["username"], $_POST["email"]);
			echo '<h2>Success!</h2><p>An administrator will see your request soon. Please Contact Admin at admin@couponCrazy.com </p>';
		} catch (CCException $e) {
			if(isset($e->getErrArray()["genErr"])) {
				echo $e->getErrArray()["genErr"];
			} else {
				echo $e->getString();
			}
		}
		unset($_POST["username"]);
		unset($_POST["email"]);
		echo '<p><a href="index.php">Click here</a> to return to home page.</p>';
  //display form for user to reset password
	} else {
		echo '<form role="form" method="post" action="resetPassword.php">
        <label><b>Username</b></label>
        <input type="text" placeholder="Username" name="username" required>
        <br>

        <label><b>Email</b></label>
      <input type="text" placeholder="Enter Email" name="email" required>
      <br>

        <input type="submit" class="signupbtn">
        <br>
    </form>';
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
