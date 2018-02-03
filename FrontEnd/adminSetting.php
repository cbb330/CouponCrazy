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
      <a class="navbar-brand" href="index.php">Coupon Crazy - Administrator</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="index.php">Home</a></li>
        <li class="active"><a href="adminSetting.php">Setting</a></li>
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
      //initialize page according to session
      if(isset($_SESSION['type'])) {
        if(!($_SESSION['type'] == 'admin')){
          echo "<h1>You are not an Administrator!</h1>";
          die("<p><a href='index.php'>Please return to the home page.</a>");
        }
      }
      else {
        echo "<h1>You are not an Administrator!</h1>";
        die("<p><a href='login.php'>Log in as Administrator to continue.</a>");
      }
      require_once(dirname(__DIR__)."/Models/Coupon.php");
      require_once(dirname(__DIR__)."/Models/Store.php");
      require_once(dirname(__DIR__)."/Models/CCException.php");
      require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
      $database = new CCdb();
      //initialize success messages for form entries
      $successmess['delstores'] = false;
      $successmess['delusers'] = false;
      $successmess['respass'] = false;
      $successmess['addcoup'] = false;
      $successmess['addstore'] = false;

			//Delete a store
			if(isset($_POST["liststores"])){
				if($_POST["liststores"] != ""){
					try{
            $database->removeStore($_POST["liststores"]);
            $successmess['delstores'] = true;
          }
					catch(CCException $e){echo $e->getString();}
				}
				unset($_POST["liststores"]);
			}

			//Delete a user
			if(isset($_POST["listusers"])){
				if($_POST["listusers"] != ""){
					try{
            $database->deleteAccount($_POST["listusers"]);
            $successmess['delusers'] = true;
          }
					catch(CCException $e){echo $e->getString();}
				}
				unset($_POST["listusers"]);
			}

			//Reset a password
			if(isset($_POST["resetPassword"])){
				if($_POST["resetPassword"] != ""){
					try{
            $database->resetPassword($_POST["resetPassword"]);
            $successmess['respass'] = true;
          }
					catch(CCException $e){echo $e->getString();}
				}
				unset($_POST["resetPassword"]);
			}

			//Create a coupon
			if(isset($_POST["couponname"])){
				try{
					$coupon = new Coupon($_POST["couponname"], $_POST["discount"], $_POST["description"], $_POST["couponname"], $_POST["quantity"], $_POST["store"]);
					$database->addCoupon($coupon);
          $successmess['addcoup'] = true;
					unset($_POST["couponname"]);
					unset($_POST["discount"]);
					unset($_POST["description"]);
					unset($_POST["type"]);
					unset($_POST["quantity"]);
					unset($_POST["store"]);
				}
				catch(CCException $e){echo $e->getString();}
				catch(Exception $e){
					$e = new CCException();
					$e->addErr("unknown","Unknown error in adminSetting.addCoupon()");
					throw $e;
				}
			}

			//Create a store
			if(isset($_POST["addStore"])){
				try{
					$store = new Store($_POST["storename"], $_POST["addStore"], $_POST["s_description"]);
					$database->addStore($store);
					unset($_POST["storename"]);
          unset($_POST["addStore"]);
					unset($_POST["s_description"]);
          $successmess['addstore'] = true;
				}
        catch(CCException $e){echo $e->getString();}
				catch(Exception $e){
					echo $e->getMessage();
				}
			}
?>

          <h1>Admin Settings</h1>
          <b><hr></b>
          <?php
            //display success messages
            if($successmess['delstores']) echo "<span class='good'>Store has been deleted.</span>";
            if($successmess['delusers']) echo "<span class='good'>User has been deleted.</span>";
            if($successmess['respass']) echo "<span class='good'>User password has been reset.</span>";
            if($successmess['addcoup']) echo "<span class='good'>New coupon added.</span>";
            if($successmess['addstore']) echo "<span class='good'>New Store Owner added.</span>";
          ?>
          <h3> Delete Stores </h3>
            <form method="POST" action="adminSetting.php">
              <select name="liststores" value="">
              <?php
                //display all stores
          			$storeArr = $database->getStores();
          			echo '<option value=""></option>';
                foreach($storeArr as $store) {
          				echo "<option value=\"$store\">$store</option>";
                }
              ?>
              </select>
              <input type="submit" value="Delete">
            </form>


          <h3> Delete Users </h3>
            <form method="POST" action="adminSetting.php">
              <select name="listusers">
              <?php
                 //display all basic users
      		       $users = $database->getBasicUsers();
      		       echo '<option value=""></option>';
                 foreach($users as $user) {
      			       echo "<option value=\"$user\">$user</option>";
                 }
              ?>
              </select>
              <input type="submit" value="Delete">
            </form>


          <h3> Reset Password </h3>
          <form method="POST" action="adminSetting.php">
            <select name="resetPassword">
            <?php
              //display all users with a resetrequest value of 1 in the database
              $users = $database->passwordsReset();
  			      echo '<option value=""></option>';
              foreach($users as $user) {
  				      if($user != $_SESSION["username"]){
  					      echo "<option value=\"$user\">$user</option>";
                }
              }
            ?>
            </select>
            <input type="submit" value="Reset">
          </form>


          <form method="POST" action="adminSetting.php">
            <h3> Add Coupon </h3>
        			<table>
                    <tr><td><label><b>Coupon Name</b></label></td>
                    <td><input type="text" placeholder="Coupon Name" name="couponname" required <?php if(isset($_POST["couponname"])) echo 'value="' . $_POST["couponname"] . '"';?>></td></tr>
                    <tr><td><label><b>Discount</b></label></td>
                    <td><input type="text" placeholder="Discount" name="discount" required <?php if(isset($_POST["discount"])) echo 'value="' . $_POST["discount"] . '"';?>></td></tr>
                    <tr><td><label><b>Coupon Description</b></label></td>
                    <td><input type="text" placeholder="Coupon Description" name="description" required <?php if(isset($_POST["description"])) echo 'value="' . $_POST["description"] . '"';?>></td></r>
                    <tr><td><label><b>Quantity</b></label></td>
                    <td><input type="text" placeholder="Quantity" name="quantity" required <?php if(isset($_POST["quantity"])) echo 'value="' . $_POST["quantity"] . '"';?>></td></tr>
                    <tr><td><label><b>Store</b></label></td>
                    <td><select name="store" value="">
            			  <?php
                      //get all stores
              				$storeArr = $database->getStores();
              				echo '<option value=""></option>';
              				foreach($storeArr as $store) {
              					echo "<option value=\"$store\">$store</option>";
              				}
            			  ?>
            			</select></td></tr>
        			</table>
            <br>
            <input type = "submit" value="Add">
          </form>


          <h3> Add Store </h3>
          <p> Please choose a user to upgrade to Store Owner </p>
          <form method="POST" action="adminSetting.php">
          <table>
			         <tr><td><label><b>New Owner</b></label></td>
			         <td><select name="addStore">
      			  <?php
        				$users = $database->getBasicUsers();
        				echo '<option value=""></option>';
        				foreach($users as $user) {
        					echo "<option value=\"$user\">$user</option>";
        				}
      			  ?>
			         </select></td>
            <tr><td><label><b>Store Name</b></label></td>
            <td><input type="text" placeholder="Store Name" name="storename" required <?php if(isset($_POST["storename"])) echo 'value="' . $_POST["storename"] . '"';?>></td></tr>
            <tr><td><label><b>Description</b></label></td>
            <td><input type="text" placeholder="Description" name="s_description" required <?php if(isset($_POST["s_description"])) echo 'value="' . $_POST["s_description"] . '"';?>></td></tr>
		     </table>
		     <br>
          <input type="submit" value="Add">
          </form>

    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p>Coupon Crazy @ 2017</p>
</footer>

</body>
</html>
