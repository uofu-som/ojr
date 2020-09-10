<?php
// require_once '../vendor/autoload.php';
// use MongoDB\Client as Mongo;

class MDB{

	private $db_manager = NULL;
	private $db_client = NULL;
	private $db_user = NULL;
	private $db_pwd = NULL;
	private $db_host = 'localhost';
	private $db_port = '27017';
	private $db = 'ojr';
	private $db_collection = NULL;
	private $collection = NULL;

	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'user':
									$this->db_user = $value;
									break;
								case 'pwd':
									$this->db_pwd = $value;
									break;
								case 'host':
									$this->db_host = $value;
									break;
								case 'port':
									$this->db_port = $value;
									break;
								default:
									# Ignore the key
									break;
							}
						}
						break;
					default:
						# I only accept arrays...
						break;
				}
				break;
			default:
				# take default settings
				break;
		}
		if(!empty($this->db_user) && !empty($this->db_pwd)){
			$this->db_manager = new MongoDB\Driver\Manager("mongodb://".$this->db_user.":".$this->db_pwd."@".$this->db_host.":".$this->db_port."");
			$this->db_client = new MongoDB\Client("mongodb://".$this->db_user.":".$this->db_pwd."@".$this->db_host.":".$this->db_port."");
		}elseif(!empty($_ENV['DB_URL'])){
			$this->db_manager = new MongoDB\Driver\Manager("".$_ENV['DB_URL']."");
			$this->db_client = new MongoDB\Client("".$_ENV['DB_URL']."");
		}else{
			$this->db_manager = new MongoDB\Driver\Manager("mongodb://".$this->db_host.":".$this->db_port."");
			$this->db_client = new MongoDB\Client("mongodb://".$this->db_host.":".$this->db_port."");
		}
		$this->setDBaC($a[0]);
	}

	function __toString(){
		$return_array = array();
		$return_array['rc'] = true;
		$return_array['status'] = 'success';
		$return_array['message'] = 'DB Config returned in conneciton_info array.';
		$return_array['conneciton_info'] = $this->toString_ConnectionInfo();
		$return_string = json_encode($return_array);
		return $return_string;
	}

	function delete(){
		$a = func_get_args(); 
		$i = func_num_args();

		$rows = NULL;
		$is_bulk = false;

		switch ($i) {
			case 1:
				foreach ($a[0] as $key => $value) {
					switch ($key) {
						case 'is_bulk':
							$is_bulk = $value;
							break;
						case 'rows':
							$rows = $value;
							break;
						default:
							# code...
							break;
					}
				}
				$this->setDBaC($a[0]);
				break;
			
			default:
				# code...
				break;
		}

		$bulk = new MongoDB\Driver\BulkWrite();

		$return_array = array();
		$return_array['rc']=true;
		$return_array['status']='success';
		$return_array['action']='delete';

		if($is_bulk){
			foreach ($rows as $key => $row) {
				switch (gettype($row)) {
					case 'array':
						if(array_key_exists('filter', $row)){
							if(is_array($row['filter'])){
								$bulk->delete($row['filter'], ['limit' => false]);
							}else{
								$return_array['rc']=false;
								$return_array['status']='error';
								$return_array['message'][] = 'Expecting filter to be an array';
								$return_array['message'][] = 'filter: '.json_encode($row['filter']);
							}
						}else{
							$return_array['rc']=false;
							$return_array['status']='error';
							$return_array['message'][] = 'Expecting the key filter, but not found';
							$return_array['message'][] = 'row: '.json_encode($row);
						}
						break;
					default:
						$return_array['rc']=false;
						$return_array['status']='error';
						$return_array['message'][] = 'On row '.$key.': Expecting [Array], found ['.gettype($row).']';
						break;
				}
			}
		}else{
			switch (gettype($rows)) {
				case 'array':
					if(array_key_exists('filter', $rows) && array_key_exists('update', $rows)){
						if(is_array($rows['filter']) && is_array($rows['update'])){
							$bulk->delete($rows['filter'], ['limit' => false]);
						}else{
							$return_array['rc']=false;
							$return_array['status']='error';
							$return_array['message'][] = 'Expecting filter to be an array';
							$return_array['message'][] = 'filter: '.json_encode($row['filter']);
						}
					}else{
						$return_array['rc']=false;
						$return_array['status']='error';
						$return_array['message'][] = 'Expecting the key filter, but not found';
						$return_array['message'][] = 'row: '.json_encode($row);
					}
					break;
				default:
					$return_array['rc']=false;
					$return_array['status']='error';
					$return_array['message'][] = 'On row '.$key.': Expecting [Array], found ['.gettype($row).']';
					break;
			}
		}
		if($return_array['rc']){
			$result = $this->db_manager->executeBulkWrite(''.$this->db.'.'.$this->db_collection.'',$bulk);
			$return_array['records']=array();
				$return_array['records']['inserted']=$result->getInsertedCount();
				$return_array['records']['matched']=$result->getMatchedCount();
				$return_array['records']['modified']=$result->getModifiedCount();
				$return_array['records']['upserted']=$result->getUpsertedCount();
				$return_array['records']['deleted']=$result->getDeletedCount();
		}
		// $result = $this->db_manager->executeBulkWrite(''.$this->db.'.'.$this->db_collection.'',$bulk);
		return $return_array;
	}

	function distinct(){
		$a = func_get_args(); 
		$i = func_num_args();

		$return_array = array();
		$return_array['rc'] = NULL;
		$return_array['status'] = NULL;
		$return_array['action'] = 'distinct';

		$filter = NULL;
		$column = NULL;
		$options = NULL; // Not implemented... not sure if I can pass $options

		switch ($i) {
			case 1:
				switch (strtolower(gettype($a[0]))) {
					case 'string':
						$column = $a[0];
						break;
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'column':
									$column = $value;
									break;
								case 'filter':
									$filter = $value;
									break;
								case 'options':
									$options = $value;
									break;
								default:
									# code...
									break;
							}
						}
						$this->setDBaC($a[0]);
						break;
					
					default:
						$return_array['rc'] = false;
						$return_array['status'] = 'error';
						$return_array['status'] = 'Only expecting a String or an Array but found '.gettype($a[0]).' instead.';
						break;
				}
				break;
			case 2:
				if((strtolower(gettype($a[0]))=='string') && (strtolower(gettype($a[1]))=='array')){
					$column = $a[0];
					$filter = $a[1];
				} elseif ((strtolower(gettype($a[1]))=='string') && (strtolower(gettype($a[0]))=='array')){
					$column = $a[1];
					$filter = $a[0];
				}
				break;
			default:
				$return_array['rc'] = false;
				$return_array['status'] = 'error';
				$return_array['status'] = 'The parameters don\'t make sense...';
				break;
		}

		if(!is_null($filter) && !is_null($column)){
			$result = $this->collection->distinct($column,$filter);
			$return_array['rc']=true;
			$return_array['status']='success';
			$return_array['records']=''.count($result).'';
			$return_array['rows']=$result;
		} elseif (is_null($filter) && !is_null($column)){
			$result = $this->collection->distinct($column);
			$return_array['rc']=true;
			$return_array['status']='success';
			$return_array['records']=''.count($result).'';
			$return_array['rows']=$result;
		}
		// if(is_null($return_array['rc'])){
		// 	$return_array['debug']=$a;
		// 	$return_array['filter']=$filter;
		// 	$return_array['column']=$column;
		// }
		return $return_array;
	}

	function get(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$return_array = array();
		$return_array['message'] = array();
		$filter = array();
		$options=array();
		switch ($i) {
			case 1:
				foreach ($a[0] as $key => $value) {
					switch ($key) {
						case 'filter':
							$filter = $value;
							break;
						case 'options':
							$options = $value;
							break;
						default:
							# code...
							break;
					}
				}
				$this->setDBaC($a[0]);
				break;
			
			default:
				# code...
				break;
		}
		$query = new MongoDB\Driver\Query($filter, $options);
		$result = $this->db_manager->executeQuery(''.$this->db.'.'.$this->db_collection.'', $query);
		$rows = $result->toArray();
		$return_array['rc']=true;
		$return_array['status']='success';
		$return_array['action']='get';
		$return_array['records']=''.count($rows).'';
		$return_array['rows']=$rows;
		return $return_array;
	}

	function insert(){
		$a = func_get_args(); 
		$i = func_num_args();

		$rows = NULL;
		$is_bulk = false;

		switch ($i) {
			case 1:
				foreach ($a[0] as $key => $value) {
					switch ($key) {
						case 'is_bulk':
							$is_bulk = $value;
							break;
						case 'rows':
							$rows = $value;
							break;
						default:
							# code...
							break;
					}
				}
				$this->setDBaC($a[0]);
				break;
			
			default:
				# code...
				break;
		}


		$return_array = array();
		$return_array['message'] = array();
		$return_array['rc']=true;
		$return_array['status']='success';
		$return_array['action']='insert';

		$bulk = new MongoDB\Driver\BulkWrite();

		$ids=array();
		if($is_bulk){
			// $bulk = new MongoDB\Driver\BulkWrite();
			foreach ($rows as $key => $row) {
				switch (gettype($row)) {
					case 'array':
						$ids[]=$bulk->insert($row);
						break;
					default:
						$return_array['rc']=false;
						$return_array['status']='error';
						$return_array['message'][] = 'On row '.$key.': Expecting [Array], found ['.gettype($row).']';
						break;
				}
			}
		}else{
			switch (gettype($rows)) {
				case 'array':
					$ids = $bulk->insert($rows);
					break;
				default:
					$return_array['rc']=false;
					$return_array['status']='error';
					$return_array['message'][] = 'Expecting [Array], found ['.gettype($rows).']';
					break;
			}
		}
		if($return_array['rc']){
			$result = $this->db_manager->executeBulkWrite(''.$this->db.'.'.$this->db_collection.'',$bulk);
			$return_array['records']=array();
				$return_array['records']['inserted']=$result->getInsertedCount();
				$return_array['records']['matched']=$result->getMatchedCount();
				$return_array['records']['modified']=$result->getModifiedCount();
				$return_array['records']['upserted']=$result->getUpsertedCount();
				$return_array['records']['deleted']=$result->getDeletedCount();
				$return_array['_id']=$ids;
		}
		return $return_array;
	}

	function update(){
		$a = func_get_args(); 
		$i = func_num_args();

		$rows = NULL;
		$is_bulk = false;

		switch ($i) {
			case 1:
				foreach ($a[0] as $key => $value) {
					switch ($key) {
						case 'is_bulk':
							$is_bulk = $value;
							break;
						case 'rows':
							$rows = $value;
							break;
						default:
							# code...
							break;
					}
				}
				$this->setDBaC($a[0]);
				break;
			
			default:
				# code...
				break;
		}

		$return_array = array();
		$return_array['message'] = array();
		$return_array['rc']=true;
		$return_array['status']='success';
		$return_array['action']='update';
		
		$bulk = new MongoDB\Driver\BulkWrite();
		if($is_bulk){
			foreach ($rows as $key => $row) {
				switch (gettype($row)) {
					case 'array':
						if(array_key_exists('filter', $row) && array_key_exists('update', $row)){
							if(is_array($row['filter']) && is_array($row['update'])){
								$bulk->update($row['filter'],['$set' => $row['update']], ['multi' => true, 'upsert' => true]);
							}else{
								$return_array['rc']=false;
								$return_array['status']='error';
								$return_array['message'][]='File: '.__FILE__;
								$return_array['message'][]='Line: '.__LINE__;
								$return_array['message'][] = 'Expecting filter and update to be arrays';
								$return_array['message'][] = 'filter: '.json_encode($row['filter']);
								$return_array['message'][] = 'update: '.json_encode($row['update']);
							}
						}else{
							$return_array['rc']=false;
							$return_array['status']='error';
							$return_array['message'][]='File: '.__FILE__;
							$return_array['message'][]='Line: '.__LINE__;
							$return_array['message'][] = 'Expecting the keys filter and update, at least one not found';
							$return_array['message'][] = 'row: '.json_encode($row);
						}
						break;
					default:
						$return_array['rc']=false;
						$return_array['status']='error';
						$return_array['message'][]='File: '.__FILE__;
						$return_array['message'][]='Line: '.__LINE__;
						$return_array['message'][] = 'On row '.$key.': Expecting [Array], found ['.gettype($row).']';
						break;
				}
			}
		}else{
			$rows = $a[0];
			switch (gettype($rows)) {
				case 'array':
					if(array_key_exists('filter', $rows) && array_key_exists('update', $rows)){
						if(is_array($rows['filter']) && is_array($rows['update'])){
							$bulk->update($rows['filter'],['$set' => $rows['update']], ['multi' => true, 'upsert' => true]);
						}else{
							$return_array['rc']=false;
							$return_array['status']='error';
							$return_array['message'][]='File: '.__FILE__;
							$return_array['message'][]='Line: '.__LINE__;
							$return_array['message'][] = 'Expecting filter and update to be arrays';
							$return_array['message'][] = 'filter: '.json_encode($rows['filter']);
							$return_array['message'][] = 'update: '.json_encode($rows['update']);
						}
					}else{
						$return_array['rc']=false;
						$return_array['status']='error';
						$return_array['message'][]='File: '.__FILE__;
						$return_array['message'][]='Line: '.__LINE__;
						$return_array['message'][] = 'Expecting the keys filter and update, at least one not found';
						$return_array['message'][] = 'rows: '.json_encode($rows);
					}
					break;
				default:
					$return_array['rc']=false;
					$return_array['status']='error';
					$return_array['message'][]='File: '.__FILE__;
					$return_array['message'][]='Line: '.__LINE__;
					$return_array['message'][] = 'On rows '.$key.': Expecting [Array], found ['.gettype($rows).']';
					break;
			}
		}
		if($return_array['rc']){
			$result = $this->db_manager->executeBulkWrite(''.$this->db.'.'.$this->db_collection.'',$bulk);
			$return_array['records']=array();
				$return_array['records']['inserted']=$result->getInsertedCount();
				$return_array['records']['matched']=$result->getMatchedCount();
				$return_array['records']['modified']=$result->getModifiedCount();
				$return_array['records']['upserted']=$result->getUpsertedCount();
				$return_array['records']['deleted']=$result->getDeletedCount();
		}
		return $return_array;
	}

	private function setDBaC(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'db':
									$this->db = $value;
									break;
								case 'collection':
									$this->db_collection = $value;
									break;
								default:
									# Ignore the key
									break;
							}
						}
						break;
					default:
						# I only accept arrays...
						break;
				}
				break;
			default:
				# take default settings
				break;
		}
		if(!empty($this->db) && !empty($this->db_collection)){
			$this->collection = $this->db_client->selectCollection($this->db,$this->db_collection);
		}
	}

	private function toString_ConnectionInfo(){
		$return_array=array();
		$return_array['db'] = $this->db;
		$return_array['collection'] = $this->db_collection;
		$return_array['user'] = $this->db_user;
		$return_array['host'] = $this->db_host;
		$return_array['port'] = $this->db_port;
		return $return_array;
	}

}

?>