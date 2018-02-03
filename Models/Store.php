<?php
//Model class for all Store objects
	require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
	require_once(dirname(__DIR__)."/Models/CCException.php");
  class Store
  {
    private $name;
    private $owner;
    private $description;
		private $err;

    function __construct($name, $owner, $description, $new = true)
    {
        $this->name = $name;
        $this->owner = $owner;
        $this->description = $description;
		$this->err = new CCException(); // Start saving validation errors.
		$this->db = new CCdb; // Make connection for input sterilization
		if($new) { // If information is new, validate it.
			$this->validateName();
			$this->validateDescription();
			$this->validateOwner();
		}
		if($this->err->hasErr()){ // throw validation errors if they exist.
			throw $this->err;
		}
	}

	function validateName()
	{
		if(strlen($this->name) >= 15)
		{
			$this->err->addErr("store","Store name length must be less than 15 characters");
		}
		if(strlen($this->name) <= 3)
		{
			$this->err->addErr("store","Store name length must be greater than 3 characters");
		}
		$this->name = $this->db->sanitize($this->name);
	}

	function validateOwner() {
		if(strlen($this->owner) >= 15){
			$this->err->addErr("username","Username length must be less than 15 characters.");
		}

		if(strlen($this->owner) <= 3){
			$this->err->addErr("username","Username length must be greater than 3 characters.");
		}

		if(!ctype_alnum($this->owner)){
			$this->err->addErr("username","Username must contain alphanumeric characters only.");
		}
	}

	function validateDescription()
	{
		if(strlen($this->description) <= 3){
			$this->err->addErr("description","Description length must be greater than 3 characters.");
		}
		$this->description = $this->db->sanitize($this->description);
	}

	function getStore() {return $this->name;}
	function getDescription() {return $this->description;}
	function getOwner() {return $this->owner;}
}
?>
