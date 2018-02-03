<?php
require_once(dirname(__DIR__)."/DataAccess/CCException.php");
  class StoreOwner
  {
    private $username;
    private $store;
	private $email;
	private $err;

    function __construct($username, $email, $store)
    {
        $this->username = $username;
        $this->email = $email;
        $this->store = $store;
		$this->err = new CCException(); // Start saving validation errors.
        $this->validateUsername();
        $this->validateEmail();
        $this->validateStore();
		if($this->err->hasErr()){ // Throw validation errors if they exist.
			throw $this->err;
        }
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

    function validateStore(){
        if(strlen($this->store) >= 15){
            $this->err->addErr("store","Store name length must be less than 15 characters.")
        }

        if(strlen($this->store) <= 3){
            $this->err->addErr("store","Store name length must be greater than 3 characters.");
        }

        if(!ctype_alnum($this->store)){
            $this->err->addErr("store","Store name must contain alphanumeric characters only.");
        }
    }

  	function getUsername() {return $this->username;}
  	function getEmail() {return $this->email;}
  	function getStore() {return $this->store;}
  }
?>
