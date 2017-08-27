<?php

class  SDKRuntimeExceptionNew extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}

}

?>