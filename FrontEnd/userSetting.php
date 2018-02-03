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
        <li class="active"><a href="userSetting.php">Setting</a></li>
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
            //filter to keep non-users out of the site
            if(isset($_SESSION['type'])) {
              if(!($_SESSION['type'] == 'user')){
                echo "<h1>You are not a user!</h1>";
                die("<p><a href='index.php'>Please return to the home page.</a>");
              }
            }
            else {
              echo "<h1>You are not a user!</h1>";
              die("<p><a href='login.php'>Log in as a user to continue.</a>");
            }
            //helper function to validate password
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
          //helper function to sanitize user submitted data
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
          if(isset($_SESSION['type']) && $_SESSION['type'] == 'user'){
            //initialize success messages
            $oldpwerror = '';
            $pwmatcherror = '';
            $pwerror = '';
            $reseterror = '';
            $successmess = false;
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
                    $successmess = true;
    				        $_SESSION["password"] = $newpwHash;
                    $successmess = true;
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
              echo "There is not an User logged in!";
              die("<p><a href='login.php'>Log in</a> to continue.");
          }
           ?>

           <h1>User Settings</h1>
           <hr>
           <?php if($successmess) echo "<span class='good'>Password Changed.</span>"; ?>
          <p style="color: red">
           <?php echo $error; ?>
          </p>
          <form role="form" method="post" action="userSetting.php">
            <h3> Delete Account </h3>
            <p>Please Enter Your Username and Password.</p>
            <label><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="delusername" required>
            <input type="password" placeholder="Enter Paassword" name="delpassword" required>
            <span class="error"><?php echo $delerror; ?></span>
            <br>
            <input type = "submit" value="Delete">
          </form>


          <form role="form" method="post" action="userSetting.php">
            <h3> Change Password </h3>
            <p style="color: red">
            <span class="error"><?php if($reseterror) echo $reseterror['userpass'];?></span></p>
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
    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
