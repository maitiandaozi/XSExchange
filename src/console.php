<?php
include("block.php");

class console {

	protected $xs 		= null;
	protected $id 		= null;
	protected $key		= null;
	protected $postion	= null;

	public function __construct($key = null, $id = null)
    {
        $this->key = $key;
        $this->$id = $id;
    }

	public function work($key = null, $structs) 
	{

		if(!$this->key)
			return false;

		$data = $this->getData();

		if($data == -1) 
			return false;

		if(!is_array($structs) || empty($structs))
			return false;

		$res 	 = $this->dealData($data);

		$handles = $this->createProcess($res);

	}

	public function createProcess($data) 
	{
		$num = count($data);

		for($i = 0 ; $i < $num; $i++) {

		}
	}

	protected function dealData($data) 
	{
		$result = explode(";", $data);

		if(count($result) <= 1 || !$result[count($result) - 1])
			return array();

		unset($result[count($result) - 1]);

		$return = array(
			'key'  => array(),
			'sort' => array(),
			);
		$sort = array();
		foreach($result as $value) {

			$v = explode(":", $value);

			if(count($v) != 2) {
				return array();
			}
			$return[$v[0]] 	= $v[0];
			$sort[$v[1]]	= $v[0];
		}

		ksort($sort);
		$this->postion = $sort;
		return $return;
	}

	protected function getData() {
		$i = 0;
		while($i < 20) {

			if($this->id) {
				$id = $this->id;
			} else {
				$id = mt_rand(1, 65535);
			}

			$data = $this->checkSystemId($id, $this->key);


			if($data != -1) 
			{
				$this->setId($id);
				break;
				
			} else {

				if($this->id) 
					return -1;
			}

			$i++;
		}

		if($i >= 20) 
			return -1;

		return $data;
	}

	protected function checkSystemId($id, $key) {

		$xs = new XSExchange($id, $key);
		$data = $xs->readProcess();

		if($data != -1 && $data != "") {
			$this->xs = $xs;
			return $data;
		}

		return -1;
	}

	protected function setId($id) {

		if($this->id != $id)
			$this->id = $id;

	}
}