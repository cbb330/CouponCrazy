<?php
//Model class for all Administrator objects
require_once(dirname(__DIR__)."/DataAccess/CCException.php");
class Admin{
		private $username;
    private $email;
    private $err;

    function __construct($username, $email, $pw, $hashed = false){
        $this->username = $username;
        $this->email = $email;
				$this->err = new CCException();
        $this->validateUsername();
        $this->validateEmail();

				if($this->err->hasErr())
					$throw $this->err;
		}

    function validateUsername() {
        if(strlen($this->username) > 15){
            $this->err->addErr("username","Username length must be less than 15 characters.");
        }

        if(strlen($this->username) < 3){
            $this->err->addErr("username","Username length must be greater than 3 characters.");
        }

        if(!ctype_alnum($this->username)){
            $this->err->addErr("username","Username must contain alphanumeric characters only.");
        }
    }

    function validateEmail(){
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->err->addErr("email","Email is not valid!");
        }
    }

		function getUsername() {return $this->username;}
		function getEmail() {return $this->email;}
}
?>
