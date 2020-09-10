<?php

require_once('funct.php');
Funct::LoadClass('address');

class Person{

	protected const DB = 'ojr';
	protected const COLLECTION = 'person';

	protected $_id = null;

	protected $prefix = null;

	protected $legal_first_name = null;
	protected $legal_middle_name = null;
	protected $legal_last_name = null;

	protected $preffered_first_name = null;
	protected $preffered_middle_name = null;
	protected $preffered_last_name = null;

	protected $title = null;

	protected $birth_location = null;

	protected $birth_date = null;
	protected $gender = null;

	function __construct() {
		$this->birth_location = new Address();
		$a = func_get_args(); 
		$i = func_num_args(); 
		if (method_exists($this,$f='__construct'.$i)) { 
			call_user_func_array(array($this,$f),$a); 
		} else {
			//
		}
		// echo($this);
	}

	function __construct1($init){
		switch (gettype($init)) {
			case 'array': // Assuming a new person or update person
				foreach ($init as $key => $value) {
					switch (strtolower($key)) {
						case '_id':
						case 'person_id':
							$this->_id=$value;
							break;
						case 'honorific':
						case 'prefix':
							$this->prefix=$value;
							break;
						case 'first name':
						case 'first_name':
						case 'firstname':
						case 'f_name':
						case 'fname':
						case 'given_name':
						case 'givenname':
							$this->legal_first_name=$value;
							$this->preffered_first_name=$value;
							break;
						case 'middle name':
						case 'middle_name':
						case 'middlename':
						case 'm_name':
						case 'mname':
							$this->legal_middle_name=$value;
							$this->preffered_middle_name=$value;
							break;
						case 'last name':
						case 'last_name':
						case 'lastname':
						case 'l_name':
						case 'lname':
						case 'family name':
						case 'family_name':
						case 'familyname':
						case 'surname':
							$this->legal_last_name=$value;
							$this->preffered_last_name=$value;
							break;
						case 'legal_first_name':
							$this->legal_first_name=$value;
							break;
						case 'legal_middle_name':
							$this->legal_middle_name=$value;
							break;
						case 'legal_last_name':
							$this->legal_last_name=$value;
							break;
						case 'preffered_first_name':
							$this->preffered_first_name=$value;
							break;
						case 'preffered_middle_name':
							$this->preffered_middle_name=$value;
							break;
						case 'preffered_last_name':
							$this->preffered_last_name=$value;
							break;
						case 'title':
							$this->title=$value;
							break;
						case 'birth location':
						case 'birth_location':
							$this->birth_location = new Address($value);
							break;
						case 'birthday':
						case 'birthdate':
						case 'birth date':
						case 'birth_date':
							$this->birth_date = strtotime($value);
							break;
						case 'gender':
							switch (strtolower($value)) {
								case 'm':
								case 'male':
									$this->gender = 'm';
									break;
								case 'f':
								case 'female':
									$this->gender = 'f';
									break;
								default:
									$this->gender = 'c';
									break;
							}
							break;
						default:
							# code...
							break;
					}
				}
				if(is_null($this->_id)){
					// TODO: create new person
					$this->personAdd();
				}else{
					// TODO: update person
				}
				break;
			
			case 'string': // Assuming _id is passed in so get it from the database
				# code...
				$this->__construct1(new MongoDB\BSON\ObjectId("$init"));
				break;
			
			case 'object':
				switch (get_class($init)) {
					case 'MongoDB\BSON\ObjectId':
						$results = Person::search($init);
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
				
				break;
			default:
				break;
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
		$return_string = "";
		if(!empty($this->prefix))
			$return_string .= "".$this->prefix." ";
		if(!empty($this->preffered_first_name))
			$return_string .= "".$this->preffered_first_name." ";
		if(!empty($this->preffered_middle_name))
			$return_string .= "".$this->preffered_middle_name." ";
		if(!empty($this->preffered_last_name))
			$return_string .= "".$this->preffered_last_name."";
		if(!empty($this->title))
			$return_string .= ", ".$this->title." ";
		return $return_string;
	}

	function get($id){
		switch (gettype($id)) {
			case 'string':
				switch (strtolower($id)) {
					case '_id':
					case 'id':
					case 'person_id':
						return $this->_id;
						break;
					case 'honorific':
					case 'prefix':
						return $this->prefix;
						break;
					case 'first name':
					case 'first_name':
					case 'firstname':
					case 'f_name':
					case 'fname':
					case 'given_name':
					case 'givenname':
						if(!empty($this->preffered_first_name))
							return $this->preffered_first_name;
						elseif(!empty($this->legal_first_name))
							return $this->legal_first_name;
						else
							return null;
						break;
					case 'middle name':
					case 'middle_name':
					case 'middlename':
					case 'm_name':
					case 'mname':
						if(!empty($this->preffered_middle_name))
							return $this->preffered_middle_name;
						elseif(!empty($this->legal_middle_name))
							return $this->legal_middle_name;
						else
							return null;
						break;
					case 'last name':
					case 'last_name':
					case 'lastname':
					case 'l_name':
					case 'lname':
					case 'family name':
					case 'family_name':
					case 'familyname':
					case 'surname':
						if(!empty($this->preffered_last_name))
							return $this->preffered_last_name;
						elseif(!empty($this->legal_last_name))
							return $this->legal_last_name;
						else
							return null;
						break;
					case 'legal_first_name':
						return $this->legal_first_name;
						break;
					case 'legal_middle_name':
						return $this->legal_middle_name;
						break;
					case 'legal_last_name':
						return $this->legal_last_name;
						break;
					case 'preffered_first_name':
						return $this->preffered_first_name;
						break;
					case 'preffered_middle_name':
						return $this->preffered_middle_name;
						break;
					case 'preffered_last_name':
						return $this->preffered_last_name;
						break;
					case 'title':
						return $this->title;
						break;
					case 'birth location':
					case 'birth_location':
						return $this->birth_location;
						break;
					case 'birthday':
					case 'birthdate':
					case 'birth date':
					case 'birth_date':
						return $this->birth_date;
						break;
					case 'gender':
						return $this->gender;
						break;
					default:
						return null;
						break;
				}
				break;
			
			case 'object':
				switch (get_class($id)) {
					
					default:
						return get_class($id);
						break;
				}
				
			default:
				# code...
				break;
		}
	}

	function getID(){
		return $this->_id;
	}

	function getFullName(){
		$a = func_get_args(); 
		$i = func_num_args();
		switch ($i) {
		 	case 1:
		 		if(is_bool($a[0])){
		 			if($a[0]){
		 				return $this->getFullPrefferedName();
		 			}else{
		 				return $this->getFullLegalName();
		 			}
		 		}else{
	 				return $this->getFullLegalName();
	 			}
		 		break;
		 	
		 	default:
		 		return $this->getFullLegalName();
		 		break;
		 } 
	}
	
	function getFullLegalName(){
		$return_string="";
		if(!is_null($this->legal_first_name)){
			$return_string.="".$this->legal_first_name." ";
		}
		if(!is_null($this->legal_middle_name)){
			$return_string.="".$this->legal_middle_name." ";
		}
		if(!is_null($this->legal_last_name)){
			$return_string.="".$this->legal_last_name."";
		}
		return trim($return_string);
	}
	
	function getFullPrefferedName(){
		$return_string="";
		if(!is_null($this->preffered_first_name)){
			$return_string.="".$this->preffered_first_name." ";
		}
		if(!is_null($this->preffered_middle_name)){
			$return_string.="".$this->preffered_middle_name." ";
		}
		if(!is_null($this->preffered_last_name)){
			$return_string.="".$this->preffered_last_name."";
		}
		return trim($return_string);
	}

	protected function personAdd(){
		$insert_row=array();
		foreach ($this as $key => $value) {
			switch($key){
				case 'prefix':
				case 'legal_first_name':
				case 'legal_middle_name':
				case 'legal_last_name':
				case 'preffered_first_name':
				case 'preffered_middle_name':
				case 'preffered_last_name':
				case 'title':
				case 'birth_location':
				case 'gender':
					$insert_row[$key]=$value;
					break;
				case 'birth_date':
					$insert_row[$key]=new MongoDB\BSON\UTCDateTime($value * 1000);
					break;
				default:
					break;

			}
		}
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$result = $database->insert(array('rows'=>$insert_row,'is_bulk'=>false));
		$this->_id = $result['_id'];
		// echo(print_r($result,true).PHP_EOL);
		return (array('rc'=>true,'action'=>'Add Person','status'=>'success','message'=>$result));
				
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
				$return_array['message']='No Parameters exist. Assuming returning first page of patients.';
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
								
								case 'honorific':
								case 'prefix':
									$_query_parameters[]=['prefix'=>$value];
									break;
								case 'first name':
								case 'first_name':
								case 'firstname':
								case 'f_name':
								case 'fname':
								case 'given_name':
								case 'givenname':
								case 'legal_first_name':
								case 'preffered_first_name':
									$_query_parameters[]=['$or'=>[
										'legal_first_name'=>$value,
										'preffered_first_name'=>$value
									]];
									break;
								case 'middle name':
								case 'middle_name':
								case 'middlename':
								case 'm_name':
								case 'mname':
								case 'legal_middle_name':
								case 'preffered_middle_name':
									$_query_parameters[]=['$or'=>[
										'legal_middle_name'=>$value,
										'preffered_middle_name'=>$value
									]];
									break;
								case 'last name':
								case 'last_name':
								case 'lastname':
								case 'l_name':
								case 'lname':
								case 'family name':
								case 'family_name':
								case 'familyname':
								case 'surname':
								case 'legal_last_name':
									$_query_parameters[]=['$or'=>[
										'legal_last_name'=>$value,
										'preffered_last_name'=>$value
									]];
									break;
								case 'title':
									$_query_parameters[]=['title'=>$value];
									break;
								case 'birth date':
								case 'birth_date':
									$_query_parameters[]=['birth_date'=> new MongoDB\BSON\UTCDateTime(strtotime($value) * 1000)];
									break;
								case 'gender':
									switch (strtolower($value)) {
										case 'm':
										case 'male':
											$_query_parameters[]=['gender'=>'m'];
											break;
										case 'f':
										case 'female':
											$_query_parameters[]=['gender'=>'f'];
											break;
										default:
											$_query_parameters[]=['gender'=>'c'];
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
							$return_array['message']='No Parameters exist. Assuming returning first page of patients.';
							$filter = [];
							$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
							$db_results = $database->get(array('filter'=>$filter));
							$return_array['rows']=$db_results['rows'];
							$return_array['rc']=true;
							$return_array['status']='success';
						}
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
		$return_array=array();
		foreach ($this as $key => $value) {
			if(!is_null($value) && !empty($value)){
				switch ($key) {
					case 'birth_location':
						$return_array[$key]=$value->toArray();
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
		foreach ($this as $key => $value) {
			if(!is_null($value) && !empty($value)){
				switch ($key) {
					case 'birth_location':
						foreach ($value->toFlatArray() as $akey => $avalue) {
							$return_array[$key."_".$akey]=$avalue;
						}
						break;
					default:
						$return_array[$key]=$value;
						break;
				}
			}
		}
		return $return_array;
	}

}

?>