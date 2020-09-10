<?php
Funct::LoadClass('mdb');
class Settings{

	protected const DB = 'ojr';
	protected const COLLECTION = 'system_settings';

	function __construct() {
		//print "In BaseClass constructor\n";
		$a = func_get_args(); 
		$i = func_num_args(); 
		if (method_exists($this,$f='__construct'.$i)) { 
			call_user_func_array(array($this,$f),$a); 
		} else{
			// echo($this);
		}
	}

	function __construct1($init){
		// echo("One Parameter");
		switch (gettype($init)) {
			case 'array':
				foreach ($init as $key => $value) {
					switch ($key) {
						// case 'name':
						// 	$this->name=$value;
						// 	break;
						default:
							// $error_message='Unknown parameter:'.$value.', ignoring parameter';
							// trigger_error($error_message, E_USER_WARNING);
							break;
					}
				}
				break;
			case 'string':
				// $this->parse($init);
				break;
			default:
				# code...
				break;
		}
	}

	static function add(){
		$properties_search=array();
		$properties=array();
		// $properties['name'] = null;
		// $properties['description'] = null;

		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'key':
									$properties_search['key'] = $value;
									$properties['key'] = $value;
									break;
								case 'value':
									$properties['value'] = $value;
									break;
								case 'private':
									switch (gettype($value)) {
										case 'boolean':
											$properties[$key]=$value;
											break;
										case 'string':
											switch (strtolower($value)) {
												case 't':
												case 'true':
													$properties[$key]=true;
													break;
												case 'f':
												case 'false':
													$properties[$key]=false;
													break;
												default:
													# code...
													break;
											}
											break;
										
										default:
											# code...
											break;
									}
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
			case 2:
				// Assuming key, value pair
				return Settings::add(array('key'=>$a[0],'value'=>$a[1]));
				break;
			case 3:
				// Assuming key, value pair w/ private indicator
				return Settings::add(array('key'=>$a[0],'value'=>$a[1],'private'=>$a[2]));
				break;
			default:
				return (array('rc'=>false,'action'=>'Add Setting','status'=>'failed','message'=>'Not sure what to do with '.$i.' parameters.'));
				break;
		}
		if(!empty($properties['key']) && !empty($properties['value'])){
			$check_if_exists = Settings::search($properties_search);
			switch (count($check_if_exists['rows'])) {
				case 0:
					# All ready checked if role name exists and doesn't so just create the role
					$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
					$result = $database->insert(array('rows'=>$properties,'is_bulk'=>false));
					return (array('rc'=>true,'action'=>'Add Setting','status'=>'success','message'=>$result));
					break;
				case 1:
					# Checked if setting exists and it does so update the setting
					$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
					$filter = ["key"=>$properties['key']];
					$update = ["filter"=>$filter,"update"=>$properties,'is_bulk'=>false];
					$result = $database->update($update);
					return (array('rc'=>true,'action'=>'Update Setting','status'=>'success','message'=>$result));
					break;
				default:
					return (array('rc'=>false,'action'=>'Add Setting','status'=>'failed','message'=>'The setting "'.$properties['key'].'" already exists.'));
					break;
			}
		}else{
			return (array('rc'=>false,'action'=>'Add Setting','status'=>'failed','message'=>'Missing either "key", "value", or both "key" and "value".'));
		}
	}

	static function get(){
		$return_array = array();
		foreach (Settings::search()['rows'] as $row) {
			switch ($row->key) {
				default:
					$return_array[$row->key]=$row->value;
					break;
			}
		}
		return $return_array;
	}

	static function get_private(){
		$return_array = array();
		foreach (Settings::search()['rows'] as $row) {
			$return_array[$row->key]=$row->value;
		}
		foreach (Settings::search(array('private'=>true))['rows'] as $row) {
			$return_array[$row->key]=$row->value;
		}
		return $return_array;
	}

	static function search(){
		$return_array = array();
		$return_array['rc']=false;
		$return_array['status']='null';
		$return_array['message']='Nothing seems to have happened.';

		$a = func_get_args(); 
		$i = func_num_args(); 

		$_query_parameters = [];
		$_query_parameters[]=['private'=>false];
		switch ($i) {
			case 0:
				$return_array['message']='No Parameters exist. Assuming returning first page of roles.';
				$filter = ['$or'=>[['private'=>false],['private'=>['$exists'=>false]]]];
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
								case 'key':
									$_query_parameters[]=['key'=>$value];
									break;
								case 'value':
									$_query_parameters[]=['value'=>$value];
									break;
								case 'private':
									switch (gettype($value)) {
										case 'boolean':
											$_query_parameters[]=['private'=>$value];
											break;
										case 'string':
											switch (strtolower($value)) {
												case 't':
												case 'true':
													$_query_parameters[]=['private'=>true];
													break;
												case 'f':
												case 'false':
													$_query_parameters[]=['private'=>false];
													break;
												default:
													# code...
													break;
											}
											break;
										
										default:
											# code...
											break;
									}
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
						return Settings::search(new MongoDB\BSON\ObjectId($a[0]));
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

	function toArray(){
		return Settings::get();
	}

	function toFlatArray(){
		return NULL;
	}

	function __toString(){
		return json_encode(Settings::get());
	}

}

?>