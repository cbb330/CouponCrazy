<?php
//Model class for all Coupon objects
	require_once(dirname(__DIR__)."/DataAccess/CCdb.php");
	require_once(dirname(__DIR__)."/Models/CCException.php");
  class Coupon
  {
    private $couponName;
    private $discount;
    private $description;
    private $itemType;
    private $quantity;
    private $store;
	private $err;
	private $db;

    function __construct($couponName, $discount, $description,
                            $itemType, $quantity, $store, $new = true)
    {
        $this->couponName = $couponName;
        $this->discount = $discount;
        $this->description = $description;
        $this->itemType = $itemType;
        $this->quantity = $quantity;
        $this->store = $store;
		$this->err = new CCException(); // Start saving errors.
		$this->db = new CCdb; // Make a connection for input sterilization
        if($new) { // If the data is new, validate it
			$this->validateCouponName();
			$this->validateDiscount();
			$this->validateDescription();
			$this->validateQuantity();
		}
		if($this->err->hasErr()) // Throw validation errors if they exist
			throw $this->err;
	}

		function getCouponName() {return $this->couponName;}
		function getDiscount() {return $this->discount;}
		function getDescription() {return $this->description;}
		function getItemType() {return $this->itemType;}
		function getQuantity() {return $this->quantity;}
		function getStore() {return $this->store;}

		function validateCouponName()
		{
			if(strlen($this->couponName) <= 3)
			{
	          $this->err->addErr("coupon","CouponName length must be greater than 3 characters.");
			}
			$this->couponName = $this->db->sanitize($this->couponName);
		}

		function validateDiscount()
		{
			if(!is_numeric($this->discount)){
	          $this->err->addErr("discount","Discount must contain numeric characters only.");
			}
			$this->discount = round($this->discount, 2);
			if($this->discount <= 0){
				$this->err->addErr("discount","Discount must be greater than \$0.00.");
			}
		}

		function validateDescription()
		{
			if(strlen($this->description) <= 3){
	          $this->err->addErr("description","Description length must be greater than 3 characters.");
			}
			$this->description = $this->db->sanitize($this->description);
		}

		function validateQuantity()
		{
			if(!is_numeric($this->quantity))
			{
				$this->err->addErr("quantity","Quantity must be numeric.");
			}
			if($this->quantity < 0){
	      $this->err->addErr("quantity","Quantity must be greater than or equal to 0.");
	    }
		}
  }
?>
