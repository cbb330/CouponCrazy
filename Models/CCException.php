<?php
// Custom exception class to store an associative array of exception messages.  Can store multiple errors.  Useful for thorough error checking.
class CCException extends Exception
{
	private $errArray;
	private $errOccured;

	public function __construct($exceptionItem = NULL, $code = 0, Exception $previous = null)
	{
		$this->errOccured = false;
		$this->errArray = array();
		if(isset($exceptionItem))
			switch(gettype($exceptionItem))
			{
				case "array":
					if(count($exceptionItem) == 1)
						$this->addErr($exceptionItem[0]);
					else
						$this->addErr($exceptionItem[0],$exceptionItem[1]);
					break;
				case "string":
					$this->addErr($exceptionItem);
					break;
				default:
					throw new CCException(["type","Type error in CCException: Expected string, array, or NULL, got " . gettype($exceptionItem)]);
					break;
			}
		parent::__construct("", $code, $previous);
	}

	public function getErrArray(){
		// Returns stored errors.
		
		return $this->errArray;
	}

	public function addErr($key, $value = "")
	{
		// Adds an error.  Overwrites previous errors of same key.
		
		$this->errArray[$key] = $value;
		$this->errOccured = true;
	}

	public function hasErr(){
		// Returns whether an error has been stored.
		
		return $this->errOccured;
	}

	public function getString(){
		// Returns a formatted string showing all stored errors.
		
		$string = "";
		foreach($this->errArray as $err => $msg)
			$string .= $err . " error: " . $msg . "<br>";
		return $string;
	}
}
