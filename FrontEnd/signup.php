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
    .error {color: #FF0000;}

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
        <?php if(isset($_SESSION['username'])): ?>
        <li><a href="userSetting.php">Setting</a></li>
        <?php else: ?>
        <li><a href="login.php">Setting</a></li>
        <?php endif; ?>

        <?php if(isset($_SESSION['username'])): ?>
        <li><a href="checkout.php">Cart</a></li>
        <?php else: ?>
        <li><a href="login.php">Cart</a></li>
        <?php endif; ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="active"><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-8 text-left">
      <h2> Signup Form</h2>

      <?php
        //initalize database
        require_once(dirname(__DIR__)."/Models/User.php");
        require_once(dirname(__DIR__)."/Models/CCException.php");
        require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
        require_once(dirname(__DIR__)."/DataAccess/dblogin.php");
        $database = new CCdb;
        $connection = new mysqli($hn, $un, $pw, $db);
        if($connection->connect_error) die($connection->connect_error);
        //initialize error messages
        $formerror = '';
        $pwerror = '';
        $pwmatcherror = '';
        //filter for if user is already logged in
        if(isset($_SESSION['username'])) {
            $error_string = NULL;
            if($_SESSION['type'] == 'admin') {
                header("Location: index.php");
                die();
            }
            elseif($_SESSION['type'] == 'user') {
                header("Location: index.php");
                die();
            }
            elseif($_SESSION['type'] == 'storeowner') {
                header("Location: index.php");
                die();
            }
        }
        //script for creating a new account
        if(isset($_POST['newusername']) && isset($_POST['newpassword']) && isset($_POST['repassword']) && isset($_POST['newemail'])){
            if($_POST['newpassword'] != $_POST['repassword']){
              $pwmatcherror = "Passwords must match.";
            }
            else{
            $un_temp = mysql_entities_fix_string($connection, $_POST['newusername']);
            $pw_temp = mysql_entities_fix_string($connection, $_POST['newpassword']);
            try{
              validatePw($pw_temp);
            }
            catch(Exception $e){
              $pwerror = $e->getMessage();
            }
            $email_temp = mysql_entities_fix_string($connection, $_POST['newemail']);
            $salt1 = "qm&h*";
            $salt2 = "pg!@";
            $pwHash = hash('ripemd128', "$salt1$pw_temp$salt2");
            try{
              $newUser = new User($un_temp, $email_temp, $pwHash);
              $database->createUser($newUser);
              setSession($un_temp, $pwHash, 'user');
              header("Location: index.php");
              die();
            }
            catch(CCException $e){
              $error = $e->getErrArray();
            }
        }
      }
        else {
          $formerror = "All forms must be filled to create a new account.";
        }
       ?>


      <p style="color: red">
      <p><span class="error"><?php if($formerror) echo $formerror;?></span></p>
      <form method="post" action="signup.php">
          Username: <input type="text" size="25" name="newusername" value="<?php echo isset($_POST['newusername']) ? $_POST['newusername'] : '' ?>">
          <span class="error"><?php if(isset($error['username'])) echo $error['username'];?></span>
          <br><br>

          E-mail: <input type="text" size="35" name="newemail" value="<?php echo isset($_POST['newemail']) ? $_POST['newemail'] : '' ?>">
          <span class="error"><?php if(isset($error['email'])) echo $error['email'];?></span>
          <br><br>

          Password: <input type="password" size="35" name="newpassword" value="">
          <span class="error"><?php echo $pwerror;?></span>
          <br><br>

          Re-enter Password: <input type="password" size="35" name="repassword" value="">
          <span class="error"><?php echo $pwmatcherror; ?></span>
          <br><br>

          <input type="submit" name="submit" value="Create Account">
      </form>


    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
<?php
    //helper function for sanitizing user entry
    function mysql_entities_fix_string($connection, $string)
    {
      return htmlentities(mysql_fix_string($connection, $string));
    }

    function mysql_fix_string($connection, $string)
    {
      if (get_magic_quotes_gpc()) $string = stripslashes($string);
      return $connection->real_escape_string($string);
    }
    //helper function for validating user requested password
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
    //helper function to set user session data
    function setSession($un, $pw, $t) {
        $_SESSION['username'] = $un;
        $_SESSION['password'] = $pw;
        $_SESSION['type'] = $t;
    }
?>
