<?php
  require_once(dirname(__DIR__)."/Models/User.php");
  require_once(dirname(__DIR__)."/Models/Coupon.php");
  require_once(dirname(__DIR__)."/Models/Store.php");
  require_once(dirname(__DIR__)."/Models/CCException.php");
	class CCdb{
		private $username;
		private $password;
		private $hostname;
		private $dbname;

		public function __construct(){

			// Hardcode login information
			$this->username = 'root';
			$this->password = '';
			$this->hostname = 'localhost';
			$this->dbname = 'nlt77';
		}

		public function sanitize($input){
			// Return sanitized input string
			$conn = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
            if(!$conn->connect_error) {
				if (get_magic_quotes_gpc()) {
					$input = stripslashes($input);
				}
				$output = $conn->real_escape_string($input);
				$conn->close();
				return $output;
			}
			throw new CCException(["connection","Could not make connection to the database"]);  // Only gets here if connection failed.  Throw error.
		}

		private function query($query_str){
			//Inspired by lab03/fetchrow.php

			// the following turns on error reporting (important on pluto)
			error_reporting(E_ALL);
			ini_set('display_errors', 1);

			$conn = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
			if ($conn->connect_error)
			  die($conn->connect_error);

			$result = $conn->query($query_str);
			if(gettype($result) == "boolean") // If the result of the query is a boolean value, return it
			{
				$conn->close();
				return $result;
			} else if(get_class($result) == "mysqli_result") // else if he result is a mysqli_result object, turn it into an array of associative arrays and return it.
			{
				$rows = $result->num_rows;
				$results = array();

				for ($j = 0 ; $j < $rows ; ++$j)
				{
					$result->data_seek($j);
					$results[] = $result->fetch_array(MYSQLI_ASSOC);
				}
				$result->close();
				$conn->close();
				return $results;
			} else { // else throw an error
				$conn->close();
				throw new CCException(["query","Unknown error in CCdb.query()"]);
			}
		}

		public function addCoupon($coupon){
			// Add a coupon to the inventory table

			// Simplify variable names
			$couponname = $coupon->getCouponName();
			$discount = $coupon->getDiscount();
			$description = $coupon->getDescription();
			$itemtype = $coupon->getItemType();
			$quantity = $coupon->getQuantity();
			$storename = $coupon->getStore();

			// Insert coupon info into table.  If it fails, find why and throw appropriate error
			if(!$this->query("INSERT INTO `inventory` (`coupon_name`, `discount`, `c_description`, `item_type`, `count`, `store_name`) VALUES (\"$couponname\", \"$discount\", \"$description\", \"$itemtype\", \"$quantity\", \"$storename\");")){
				$e = new CCException();
				if(count($this->query("SELECT `coupon_name` FROM `inventory` WHERE `coupon_name` = \"$couponname\";")) != 0)
					$e->addErr("coupon","Attempted to add a coupon that already exists");
				if(count($this->query("SELECT `store_name` FROM `stores` WHERE `store_name` = \"$storename\";")) == 0)
					$e->addErr("store","Attempted to add a coupon to a nonexistant store");
				if(!$e->hasErr())
					$e->addErr("unknown","Unknown error in CCdb.addCoupon()");
				throw $e;
			}
		}

		public function addStore($store){
			// Add a store to the stores table

			// Simplify the variable names
			$username = $store->getOwner();
			$storename = $store->getStore();
			$description = $store->getDescription();

			// Precheck for errors
			// Check if store name already exists or user already owns a store.  Throw error in either case.
			$failures = $this->query("SELECT `username`, `store_name` FROM `stores` WHERE `username` = \"$username\" OR `store_name` = \"$storename\";");
			if(count($failures) > 0){
				if($failures[0]["username"] == $username)
					throw new CCException("User of name \"$username\" already owns a store!");
				if($failures[0]["store_name"] == $storename)
					throw new CCException("Store of name \"$storename\" already exists!");
			}

			// Make sure user exists and is not an admin (would not be store owner if above check succeeded).  Throw an error if false.
			$usercheck = $this->query("SELECT `user_type` FROM `users` WHERE `username` = \"$username\";");
			if(count($usercheck) == 0)
				throw new Exception("User of name \"$username\" does not exist!");
			if($usercheck[0]["user_type"] == "admin")
				throw new Exception("User of name \"$username\" is an administrator!");

			// Try to run queries, add store info and update user type.  Throw error if failed.
			try{
				$this->query("INSERT INTO `stores` (`store_name`, `username`, `s_description`) VALUES (\"$storename\", \"$username\", \"$description\");");
				$this->query("UPDATE `users` SET `user_type` = \"store_owner\" WHERE `username` = \"$username\";");
			} catch(Exception $e) {
				throw new Exception("Unknown error in CCdb.addStore()");
			}
		}

		public function addToCart($username, $couponname){
			// Removes one coupon from inventory when adds to cart.  Will not add if inventory count = 0.

			// Make sure user exists, target coupon exists, and there are more than 0 of the target coupon.
			$usertype = $this->query("SELECT `user_type` FROM `users` WHERE `username` = \"$username\";");
			$count = $this->query("SELECT `count` FROM `inventory` WHERE `coupon_name` = \"$couponname\";");

			// Start error saving
			$err = new CCException();

			// If user exists, make sure user is basic user.  If not, or if user does not exist, save error
			if(count($usertype) > 0) {
				if($usertype[0]["user_type"] != "user") {
					$err->addErr("user","User is not a basic user");
				}
			} else {
				$err->addErr("user","User does not exist");
			}

			// Make sure coupon exists and there are more than 0.  If either is not true, save error
			if(count($count) > 0) {
				if($count[0]["count"] <= 0) {
					$err->addErr("inventory","Not enough coupons called \"$couponname\" in the store's inventory");
				}
			} else {
				$err->addErr("inventory","Coupon does not exist");
			}

			// If no errors exist, attempt to update inventory and add coupon to user's cart.  Save error if either attempt fails.  Roll back changes if necessary.
			if(!$err->hasErr()) {
				if($this->query("UPDATE `inventory` SET `count` = `count` - 1 WHERE `coupon_name` = \"$couponname\";")) {
					if(!$this->query("INSERT INTO `users_coupons` (`username`, `coupon_name`) VALUES (\"$username\", \"$couponname\");")) {
						$err->addErr("cart","You already own the coupon \"$couponname\"");
						$this->query("UPDATE `inventory` SET `count` = `count` + 1 WHERE `coupon_name` = \"$couponname\";");
					}
				} else {
					$err->addErr("inventory update", "Could not update inventory");
				}
			}

			// Throw saved errors if any exist.
			if($err->hasErr()) throw $err;
		}

		public function hasCoupon($username, $couponname){
			// Check if user owns a coupon.  Return bool.
			$result = $this->query("SELECT `username` FROM `users_coupons` WHERE `username` = \"$username\" AND `coupon_name` = \"$couponname\";");
			return count($result) > 0;
		}

		public function adjustQuantity($couponname, $quantitychange){
			// Change the quantity of a coupon in inventory.  Quantity of a coupon in inventory represents the number of unclaimed coupons available.  Number of coupons in cart do not count.

			// Make sure coupon exists.  Throw an error if it does not.
			$count_row = $this->query("SELECT `count` FROM `inventory` WHERE `coupon_name` = \"$couponname\";");
			if(count($count_row) > 0){
				if($count_row[0]["count"] + $quantitychange >= 0){  // Make sure the result of the change is not less than 0.  Throw error if it is.
					try{ // Attempt to change quantity.  Throw error if failed.
						$this->query("UPDATE `inventory` SET `count` = `count` + $quantitychange WHERE `coupon_name` = \"$couponname\";");
					} catch(Exception $e) {
						throw new Exception("Uknown error in CCdb.adjustQuantity()");
					}
				} else {
					$quantitychange = abs($quantitychange);
					throw new Exception("$quantitychange coupons of name \"$couponname\" are not availabe to remove!");
				}
			} else {
				throw new Exception("Coupon of name \"$couponname\" does not exist!");
			}
		}

		public function changePassword($username, $oldpassword, $newpassword){
			// Change the username's password.
			
			// Double check to make sure the user exists and gave the right old password.  Save an error if not.
			$success = $this->query("SELECT `username` FROM `users` WHERE `username` = \"$username\" AND `password` = \"$oldpassword\";");
			$e = new CCException();
			if(count($success) == 0){
				$e->addErr("userpass","Invalid username/password combination!");
				throw $e;
			}
			
			// Attempt to update password.  Save error if failed.
			if(!$this->query("UPDATE `users` SET `password` = \"$newpassword\" WHERE `username` = \"$username\";"))
				$e->addErr("unknown","Unknown error in CCdb.changePassword()");
			
			// Throw saved errors if there are any.
			if($e->hasErr()) throw $e;
		}

		public function resetPassword($username){
			// Reset password of target basic user to '1234'
			
			$newpass = hash('ripemd128', 'qm&h*1234pg!@');
			
			$e = new CCException();
			
			// Make sure the user exists and is a basic user.  Save appropriate errors if either fail.
			$user = $this->query("SELECT `user_type` FROM `users` WHERE `username` = \"$username\";");
			if(count($user) != 0){
				switch($user[0]["user_type"]){
					case "user":  // If user exists and is a basic user, attempt to change password.  Save error if failed.
						if(!$this->query("UPDATE `users` SET `password` = \"$newpass\", `password_reset` = 0 WHERE `username` = \"$username\" AND `user_type` = \"user\";"))
							$e->addErr("unknown","Unknown error in CCdb.resetPassword()");
						break;
					default:
						$e->addErr("user type","Attempted to delete user of type " . $user[0]["user_type"]);
						break;
				}
			} else {
				$e->addErr("user","Attempted to delete a nonexistant user");
			}
			
			// Throw saved errors if any exist.
			if($e->hasErr()) throw $e;
		}

		public function checkoutCart($username, $items, $storename){
			// Find coupons belonging to a specific user which match the selected items from the selected store and remove them.  Return the sum of the discounts of removed coupons.
			
			$err = new CCException();
			$savings = 0;
			
			// Make sure the user exists.  Save an appropriate error if not.
			$u_exists = $this->query("SELECT `username` FROM `users` WHERE `username` = \"$username\";");
			if(count($u_exists) < 1) {
				$err->addErr("user","User of name \"$username\" does not exist");
			} else {
				foreach($items as $item){
					// Sanitize the selected items.
					$item_type = $this->sanitize($item);
					
					// Increment $savings by the discount of the coupons.  Select from joined tables `inventory` and `users_coupons` where coupon names match, where username is the given user,
					// where the store name is the selected store, and the item is the current selected item.
					$coupons = $this->query("SELECT i.`discount`, i.`coupon_name` FROM `inventory` i, `users_coupons` u WHERE i.`coupon_name` = u.`coupon_name` AND u.`username` = \"$username\" AND i.`item_type` = \"$item_type\" AND i.`store_name` = \"$storename\";");
					foreach($coupons as $coupon){
						try { // Attempt to delete matching coupons.  Save an error if failed.
							$this->query("DELETE FROM `users_coupons` WHERE `username` = \"$username\" AND `coupon_name` = \"" . $coupon["coupon_name"] . "\";");
							$savings += $coupon["discount"];
						} catch(Exception $e) {
							$err->addErr("unknown", "Unknown error in CCdb.checkoutCart()");
						}
					}
				}
			}

			// Throw errors if they exist.
			if($err->hasErr()) throw $err;

			// Return sum of discounts.
			return $savings;
		}

        public function createUser($user) {
            // Add a user to the database.
			
			// Simplify variable names.
            $username = $user->getUsername();
            $email = $user->getEmail();
            $password = $user->getPwHash();
			
			// Attempt to add the user to the database.  If failed, find out why and save all appropriate errors.
			if(!$this->query("INSERT INTO `users` (`username`, `email`, `password`, `user_type`) VALUES (\"$username\", \"$email\", \"$password\", \"user\");"))
			{
				$userExists = $this->query("SELECT `username` FROM `users` WHERE `username` = \"$username\";");
				$emailExists = $this->query("SELECT `email` FROM `users` WHERE `email` = \"$email\";");
				$e = new CCException();
				if(count($userExists) != 0)
				{
					$e->addErr("username", "That username already exists!");
				}
				if(count($emailExists) != 0)
				{
					$e->addErr("email", "That email already exists!");
				}
				if($e->hasErr())
					throw $e;
				else
				{
					$e->addErr("unknown","Unknown error in CCdb.createUser");
					throw $e;
				}
			}
        }

		public function deleteAccount($username){
			// Delete a user from the database.  Throws no errors if user already does not exist.
			
			// ***WARNING: If user is a store owner, all that store's coupons will also be deleted from the inventory and all carts.***
			
			$this->query("UPDATE `inventory` SET `count` = `count` + 1 WHERE `coupon_name` = ANY (SELECT `coupon_name` FROM `users_coupons` WHERE `username` = \"$username\");");
			$this->query("DELETE FROM  `users` WHERE `username` = \"$username\";");
		}

		public function passwordsReset(){
			// Returns all usernames where the user has requested a password reset and is a basic.
			
			$results = $this->query("SELECT `username` FROM `users` WHERE `password_reset` AND `user_type` = \"user\";");
			$usernames = array();
			
			foreach($results as $result)
				$usernames[] = $result["username"];
				
			return $usernames;
		}

		public function passwordIsReset($username){
			// Returns whether a given user has requested a password reset.  Throws an error if the user does not exist.
			
			$passwordreset = $this->query("SELECT `password_reset` FROM `users` WHERE `username` = \"$username\";");
			
			if(count($passwordreset > 0))
				return $passwordreset[0]["password_reset"];
			else
				throw new Exception("User of name \"$username\" does not exist!");
		}

		public function removeCoupon($couponname){
			// Removes a coupon from the inventory.  Automatically removes them from all users' carts as well.
			
			// Make sure the coupon exists.  If so, delete it.  If not, throw an error.
			$couponExists = $this->query("SELECT `coupon_name` FROM `inventory` WHERE `coupon_name` = \"$couponname\";");
			if(count($couponExists) < 1)
				throw new CCException(["coupon","Coupon of name \"$couponname\" does not exist!"]);
			try{
				$this->query("DELETE FROM `inventory` WHERE `coupon_name` = \"$couponname\";");
			} catch(Exception $e) {  // If deletion failed for an unknown reason, throw an error.
				throw new CCException(["unknown","Unknown error in CCdb.removeCoupon()"]);
			}
		}

		public function removeFromCart($username, $couponname){
			// Remove a given coupon from a given user's cart.  Also increments the inventory count of that coupon by 1.
			
			// Make sure the user exists.  If not, throw an error.
			$u_exists = $this->query("SELECT `username` FROM `users` WHERE `username` = \"$username\";");
			if(count($u_exists) == 0)
				throw new Exception("User of name \"$username\" does not exist!");
			
			// Make sure the user owns the coupon.  If not, throw an error.
			$u_has_c = $this->query("SELECT `coupon_name` FROM `users_coupons` WHERE `username` = \"$username\" AND `coupon_name` = \"$couponname\";");
			if(count($u_has_c) == 0)
				throw new Exception("This coupon is already not in the cart!");
			
			// Attempt to delete the coupon from the cart and increment the inventory count.  If failed, throw an error.
			try {
				$this->query("DELETE FROM `users_coupons` WHERE `username` = \"$username\" AND `coupon_name` = \"$couponname\";");
				$this->query("UPDATE `inventory` SET `count` = `count` + 1 WHERE `coupon_name` = \"$couponname\";");
			} catch(Exception $e) {
				throw new Exception("Unknown error in CCdb.removeCoupon()");
			}
		}

		public function removeStore($storename){
			// Remove a store from the database.  Also removes that store's coupons from the inventory and from all users' carts and sets the store owner to a basic user.
			
			$e = new CCException();
			
			// Make sure the store exists.  If not, save an error.
			$store = $this->query("SELECT * FROM `stores` WHERE `store_name` = \"$storename\";");
			if(!$store) {
				$e->addErr("store","Store of name \"$storename\" does not exist!");
				throw $e;
			}
			
			// Attempt to delete the store.  If it failed, save an error.
			if(!$this->query("DELETE FROM `stores` WHERE `store_name` = \"$storename\";"))
				$e->addErr("delete store","Could not delete store");
			
			// Attempt to set the user to a basic user.  If it failed, save an error.
			$username = $store[0]["username"];
			if(!$this->query("UPDATE `users` SET `user_type` = \"user\" WHERE `username` = \"$username\";"))
				$e->addErr("set user","Could not set store owner to user");
			
			// Throw saved errors, if they exist.
			if($e->hasErr()) throw $e;
		}

		public function requestReset($username, $email){
			// Request a password reset from a given user.
			
			$userEmail = $this->query("SELECT `password_reset`, `user_type` FROM `users` WHERE `username` = \"$username\" AND `email` = \"$email\";");
			$err = new CCException();

			// Make sure the given username and email match, that the user has not already requested a reset, and that the user is a basic user.  If not, save an appropriate error.
			if(count($userEmail) == 0){
				$err->addErr("genErr", "Invalid username/email combination");
			} else if($userEmail[0]['password_reset'] == 1) {
				$err->addErr("genErr", "This account is already pending a password reset");
			} else if($userEmail[0]['user_type'] != "user") {
				$err->addErr("genErr", "This account is not a basic user.  Please contact an adminstrator to get your password reset");
			} else {
				// Attempt to set the request flag to true.  Save an error if failed.
				try {
					$this->query("UPDATE `users` SET `password_reset` = 1 WHERE `username` = \"$username\" AND `email` = \"$email\";");
				} catch (Exception $e) {
					$err->addErr("unknown", "Unknown error in CCdb.requestReset()");
				}
			}

			// Throw saved errors if any exist.
			if($err->hasErr()) throw $err;
		}

		//Getters

		public function getCoupons($store_name){
			// Return all data for all coupons from a specific store.
			
			$rows = $this->query('SELECT * FROM `inventory` WHERE `store_name` = "' . $store_name . '";');
			
			$results = array();
			foreach($rows as $row)
			{
				try{
					if($row["count"] != 0)
						$results[] = new Coupon($row["coupon_name"], $row["discount"], $row["c_description"], $row["item_type"], $row["count"], $row["store_name"], false);
				} catch(CCException $e) {
					echo $e->getString();
				}
			}

			return $results;
		}

		public function getStores(){
			$rows = $this->query('SELECT `store_name` FROM `stores`;');

			$results = array();
			foreach($rows as $row)
			{
				$results[] = $row["store_name"];
			}

			return $results;
		}

		public function getCart($username){
			// Get all information for all coupons in a specific user's cart.
			
			$results = $this->query("SELECT i.`coupon_name`, i.`discount`, i.`c_description`, i.`item_type`, i.`count`, i.`store_name` FROM `inventory` i, `users_coupons` u WHERE u.`username` = \"$username\" AND i.`coupon_name` = u.`coupon_name`;");
			$coupons = array();
			foreach($results as $result){
				$coupons[] = new Coupon($result["coupon_name"], $result["discount"], $result["c_description"], $result["item_type"], $result["count"], $result["store_name"], false);
			}
			return $coupons;
		}

		public function getUsers($username){
			// Get username, password, and user type for a given user.
			
			return $this->query("SELECT `username`, `password`, `user_type` FROM `users` WHERE `username` = '$username';");
		}

		public function getAllUsers(){
			// Get all usernames.
			
			$results = $this->query("SELECT `username` FROM `users`;");
			$usernames = array();
			foreach($results as $result)
				$usernames[] = $result["username"];
			return $usernames;
		}

		public function getBasicUsers(){
			// Get all basic users' usernames.
			
			$results = $this->query('SELECT `username` FROM `users` WHERE `user_type` = "user" ORDER BY `username`;');
			$usernames = array();
			foreach($results as $result)
				$usernames[] = $result["username"];
			return $usernames;
		}

		public function getAdmins(){
			// Get all admins' usernames.
			
			$results = $this->query('SELECT `username` FROM `users` WHERE `user_type` = "admin" ORDER BY `username`;');
			$usernames = array();
			foreach($results as $result)
				$usernames[] = $result["username"];
			return $usernames;
		}

		public function getStoreOwners(){
			// Get all store owners' usernames.
			
			$results = $this->query('SELECT `username` FROM `users` WHERE `user_type` = "store_owner" ORDER BY `username`;');
			$usernames = array();
			foreach($results as $result)
				$usernames[] = $result["username"];
			return $usernames;
		}

		public function getOwnedCoupons($username){
			// Get all information for all coupons belonging to the store of a given store owner.
			
			$results = $this->query("SELECT c.`store_name`, c.`coupon_name`, c.`c_description`, c.`count`, c.`discount`, c.`item_type` FROM `stores` s, `users` u, `inventory` c WHERE u.`username` = \"$username\" AND u.`username` = s.`username` AND s.`store_name` = c.`store_name`;");
			$coupons = array();
			$errs = new CCException();
			
			foreach($results as $result){
				try{
					$coupons[] = new Coupon($result["coupon_name"], $result["discount"], $result["c_description"], $result["item_type"], $result["count"], $result["store_name"], false);
				} catch(CCException $e) {
					foreach($e->getErrArray() as $err => $msg){
						$errs->addErr($err, $msg);
					}
				}
			}
			
			// Throw errors if any exist.  Else return coupons.
			if($errs->hasErr()) throw $errs;
			else return $coupons;
		}

		public function getOwnedStore($username){
			// Get the store name from the store that a specific user owns.
			
			$store = $this->query("SELECT `store_name` FROM `stores` WHERE `username` = \"$username\";");
			if(count($store) > 0)
				return $store[0]["store_name"];
			throw new CCException(["unknown","Unknown error in CCdb.getOwnedStore()"]);
		}
	}
?>
