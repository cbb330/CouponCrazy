<?php
//Model class for all User objects
require_once(dirname(__DIR__)."/Models/CCException.php");
  class User
  {
    private $username;
    private $email;
    private $pwHash;
	private $errs;

    function __construct($username, $email, $pwHash){
        $this->username = $username;
        $this->email = $email;
		$this->errs = new CCException(); // Start saving validation errors
        $this->validateUsername();
        $this->validateEmail();
		$this->pwHash = $pwHash;
		if($this->errs->hasErr()){ // Throw validation errors if they exist.
			throw $this->errs;
		}
    }

    function validateUsername() {
        if(strlen($this->username) >= 15){
            $this->errs->addErr("username","Username length must be less than 15 characters.");
        }

        if(strlen($this->username) <= 3){
            $this->errs->addErr("username","Username length must be greater than 3 characters.");
        }

        if(!ctype_alnum($this->username)){
            $this->errs->addErr("username","Username must contain alphanumeric characters only.");
        }
    }

    function validateEmail(){
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errs->addErr("email","Email is not valid!");
        }
    }

		function getUsername() {return $this->username;}
		function getEmail() {return $this->email;}
		function getPwHash() {return $this->pwHash;}
  }
  ?>
