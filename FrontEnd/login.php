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
  <?php
      //initialize database
      require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
      require_once(dirname(__DIR__)."/DataAccess/dblogin.php");
      $error_string = NULL;
      $connection = new mysqli($hn, $un, $pw, $db);
      if($connection->connect_error) die($connection->connect_error);
      if(isset($_SESSION['username'])) {
          //below code not needed but could be used in the future
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
      //script for user login, taken from lab5
      if (isset($_POST['username']) && isset($_POST['password'])) {
          $un_temp = mysql_entities_fix_string($connection, $_POST['username']);
          $pw_temp = mysql_entities_fix_string($connection, $_POST['password']);
          //ccdb.getUser
          $database = new CCdb;
          //echo $un_temp;
          $result = $database->getUsers($un_temp);
          //print_r($result);
          if ($result) {
              $salt1 = "qm&h*";
              $salt2 = "pg!@";
              $token = hash('ripemd128', "$salt1$pw_temp$salt2");
              if ($token == $result[0]['password']) {
                  $error_string = NULL;
                  setSession($un_temp, $token, $result[0]['user_type']);
                  if($result[0]['user_type'] == 'admin') {
                      header("Location: index.php");
                      die();
                  }
                  elseif ($result[0]['user_type'] == 'user') {
                      header("Location: index.php");
                      die();
                  }
                  else {
                      header("Location: index.php");
                      die();
                  }
              }
              else $error_string = 'The username / password combination is not correct.';
          }
          else $error_string = 'The username / password combination is not correct.';
      }
      //set session data function
      function setSession($un, $pw, $t) {
          $_SESSION['username'] = $un;
          $_SESSION['password'] = $pw;
          $_SESSION['type'] = $t;
      }
?>

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
        <li class="active"><a href="signup.php"><span class="glyphicon glyphicon-log-in"></span> Signup</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-8 text-left">
    <h2>Sign In</h2>
    <right><b>Please sign in with your username and password below.</b>
    <p style="color: red">
    <?php echo $error_string; ?>
    </p>

    <form method="post" action="login.php">
        <label>Username: </label>
        <input type="text" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : '' ?>">
        <br>
        <label>Password: </label>
        <input type="password" name="password" value="<?php echo isset($_POST['password']) ? $_POST['password'] : '' ?>"> <br>
        <input type="submit" value="Log in">


    </form>
      <br><br>
      <p>Forgot your <a href="resetPassword.php">password?</a></p>
    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
<?php
    //helper functions to sanitize user entries
    function mysql_entities_fix_string($connection, $string)
    {
      return htmlentities(mysql_fix_string($connection, $string));
    }

    function mysql_fix_string($connection, $string)
    {
      if (get_magic_quotes_gpc()) $string = stripslashes($string);
      return $connection->real_escape_string($string);
    }
?>
