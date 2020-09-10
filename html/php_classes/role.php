<?php

require_once("funct.php");

class Role{
	protected const DB = 'ojr';
	protected const COLLECTION = 'role';

	protected $_id = null;
	protected $name = null;
	protected $description = null;

	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		if (method_exists($this,$f='__construct_'.$i)) { 
			call_user_func_array(array($this,$f),$a); 
		} else {
			//
		}
		// echo($this);
	}

	function __construct_1($init) {
		switch (gettype($init)) {

			case 'array':
				foreach ($init as $key => $value) {
					switch (strtolower($key)) {
						case '_id':
						case 'id':
						case 'role_id':
							switch (gettype($value)) {
								case 'string':
									$this->_id = new MongoDB\BSON\ObjectId("$value");
									break;
								case 'object':
									switch (get_class($value)) {
										case 'MongoDB\BSON\ObjectId':
											$this->_id = $value;
											break;
										default:
											# ignore the value because of incompatible type
											break;
									}
								default:
									# ignore the value because of incompatible type
									break;
							}
							break;
						case 'n':
						case 'name':
							$this->name = $value;
							break;
						case 'd':
						case 'desc':
						case 'description':
							$this->description = $value;
							break;
						default:
							# Ignore the key
							break;
					}
				}
				break;

			case 'string':
				$this->__construct1(new MongoDB\BSON\ObjectId("$init"));
				break;

			case 'object':
				switch (get_class($init)) {
					case 'MongoDB\BSON\ObjectId':
						$results = Role::search($init);
						if ($results['rc']){
							switch (count($results['rows'])) {
								case 1:
									$this->__construct1((array)$results['rows'][0]);
									break;
								default:
									break;
							}
						}
						break;
					default:
						break;
				}
				
			default:
				# I only accept arrays...
				break;
		}
		if(is_null($this->_id)&&!is_null($this->name)&&!is_null($this->description)){
			$result = Role::add(array('name'=>$this->name,'description'=>$this->description));
			if($result['rc']){
				$this->_id = $result['_id'];
			}
		}
	}

	function __isset($name){
		// trigger_error("Returning false: ".$name."", E_USER_WARNING);
		return false;
		$is_empty=true;
		foreach ($this as $key => $value) {
			if(!empty($value)){
				$is_empty = false;
				trigger_error("Not Empty: [".$key."]".$value."", E_USER_WARNING);
			}else{
				trigger_error("Empty: [".$key."]".$value."", E_USER_WARNING);
			}
		}
		if($is_empty){
			return false;
		}else{
			return true;
		}
	}

	function __toString(){
		$return_string = $this->toArray();
		$return_string = json_encode($return_string);
		return $return_string;
	}

	static function search(){
		$return_array = array();
		$return_array['rc']=false;
		$return_array['status']='null';
		$return_array['message']='Nothing seems to have happened.';

		$a = func_get_args(); 
		$i = func_num_args(); 

		$_query_parameters = [];

		switch ($i) {
			case 0:
				$return_array['message']='No Parameters exist. Assuming returning first page of roles.';
				$filter = [];
				$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
				$db_results = $database->get(array('filter'=>$filter));
				$return_array['rows']=$db_results['rows'];
				$return_array['rc']=true;
				$return_array['status']='success';
				break;
			case 1:
				switch (gettype($a[0])) {
					case 'array':

						foreach ($a[0] as $key => $value) {
							switch ($key) {
								
								case '_id':
								case 'id':
								case 'role_id':
									switch (gettype($value)) {
										case 'string':
											$_query_parameters[]=['_id'=> new MongoDB\BSON\ObjectId("$value")];
											break;
										case 'object':
											switch (get_class($value)) {
												case 'MongoDB\BSON\ObjectId':
													$_query_parameters[]=['_id'=> $value];
													break;
												default:
													# ignore the value because of incompatible type
													break;
											}
										default:
											# ignore the value because of incompatible type
											break;
									}
									break;
								case 'n':
								case 'name':
									$_query_parameters[]=['name'=>$value];
									break;
								case 'd':
								case 'desc':
								case 'description':
									$_query_parameters[]=['description'=>$value];
									break;
								default:
									# code...
									break;
							}
						}
						if(count($_query_parameters)>0){
							$filter = [ '$and' => $_query_parameters];
							$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
							$db_results = $database->get(array('filter'=>$filter));
							if(count($db_results['rows'])>0){
								$return_array['message']='And';
								$return_array['rows']=$db_results['rows'];
								$return_array['rc']=true;
								$return_array['status']='success';
							}else{
								$filter = [ '$or' => $_query_parameters];
								$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
								$db_results = $database->get(array('filter'=>$filter));
								$return_array['message']='Or';
								$return_array['rows']=$db_results['rows'];
								$return_array['rc']=true;
								$return_array['status']='success';
							}
						}else{
							$return_array['message']='No Parameters exist. Assuming returning first page of roles.';
							$filter = [];
							$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
							$db_results = $database->get(array('filter'=>$filter));
							$return_array['rows']=$db_results['rows'];
							$return_array['rc']=true;
							$return_array['status']='success';
						}
						break;
					case 'string':
						return Role::search(new MongoDB\BSON\ObjectId($a[0]));
						break;
					case 'object':
						switch (get_class($a[0])) {
							case 'MongoDB\BSON\ObjectId':
								$filter=['_id'=>['$eq'=>$a[0]]];
								$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
								$db_results = $database->get(array('filter'=>$filter));
								switch (count($db_results['rows'])) {
									case 1:
										$return_array['rc']=true;
										$return_array['message']='_id found';
										$return_array['rows']=$db_results['rows'];
										break;
									
									default:
										$return_array['rc']=false;
										$return_array['message']='_id not found.';
										break;
								}
								break;
							default:
								break;
						}
						
						break;

							default:
								$return_array['message']='Search parameter is not an array. Will not perform search.';
								$return_array['status']='error';
								break;
						}
				break;
			default:
				$return_array['message']='Too many search parameters. All parameters must be in an array. Will not perform search.';
				$return_array['status']='error';
				break;
		}

		return $return_array;
	}

	static function getMap(){
		$allRoles = Role::search();
		$return_array = array();
		foreach ($allRoles['rows'] as $key => $role) {
			$return_array[]=array('_id'=>$role->_id->jsonSerialize()['$oid'],'name'=>$role->name,'description'=>$role->description);
		}
		return $return_array;
	}

	static function nameByID($_id){
		$role = Role::search(array('_id'=>$_id));
		switch (count($role['rows'])) {
			case 1:
				return $role['rows'][0]->name;
				break;
			default:
				// echo(print_r($role,true).PHP_EOL);
				break;
		}
		return null;
	}

	function toArray(){
		$return_array=array();
		foreach ($this as $key => $value) {
			if(!is_null($value) && !empty($value)){
				switch ($key) {
					case 'password':
					case 'db_pwd':
						break;
					default:
						$return_array[$key]=$value;
						break;
				}
			}
		}
		return $return_array;
	}

	function toFlatArray(){
		$return_array=array();
		$return_array=$this->toArray();
		return $return_array;
	}

	static function add(){
		$properties_search=array();
		$properties=array();
		// $properties['name'] = null;
		// $properties['description'] = null;

		$a = func_get_args(); 
		$i = func_num_args(); 
		$number=NULL;
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						// $unused_properties = array(); // Keep passing properties to objects until all are consumed or no more objects to pass it to.
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'n':
								case 'name':
									$properties_search['name'] = $value;
									$properties['name'] = $value;
									break;
								case 'd':
								case 'desc':
								case 'description':
									$properties['description'] = $value;
									break;
								default:
									# Ignore the key
									// $unused_properties[$key] = $value;
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
				break;
		}

		$check_if_exists = Role::search($properties_search);
		switch (count($check_if_exists['rows'])) {
			case 0:
				# All ready checked if role name exists and doesn't so just create the role
				$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
				$insert_row=['name' => $properties['name'], 'description'=>$properties['description']];
				$result = $database->insert(array('rows'=>$insert_row,'is_bulk'=>false));
				// echo(print_r($result,true).PHP_EOL);
				$return_array = array('rc'=>true,'action'=>'Add Role','status'=>'success','message'=>$result);
				if($result['records']['inserted']==1){
					$return_array['_id']=$result['_id'];
				}
				return $return_array;
				break;
			default:
				return (array('rc'=>false,'action'=>'Add Role','status'=>'failed','message'=>'Role already exists.'));
				break;
		}
	}

	function remove($user){
		$delete_rows=array();
		$delete_rows[] = ['filter'=>['email' => $user]];
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$result = $database->delete(array('is_bulk'=>true,'rows'=>$delete_rows));
		return (array('rc'=>true,'action'=>'Remove User','status'=>'success','message'=>$result));
	}

	function update($props){
		if(is_array($props)){
			$filter = ['_id' => $this->_id];
			$update = array();
			foreach ($props as $key => $value) {
				switch ($key) {
					case 'description':
					case 'name':
						$update[$key] = $value;
						break;
					default:
						# ignore key-value pair as it's not 
						break;
				}
				
			}
			$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
			$result = $database->update(array('filter'=>$filter, 'update'=>$update, 'is_bulk'=>false));
			return (array('rc'=>true,'action'=>'Update Role','status'=>'success','message'=>$result));
		}else{
			return (array('rc'=>false,'action'=>'Update Role','status'=>'failed','message'=>'Array not found'.PHP_EOL.'Usage: $Role->update($Property_array)'));
		}
	}

}

?>