<?php

require_once('funct.php');

class OJR{

	protected const DB = 'ojr';
	protected const COLLECTION = 'raw_log';

	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		if (method_exists($this,$f='__construct'.$i)) { 
			call_user_func_array(array($this,$f),$a); 
		} else {
			//
		}
		// echo($this);
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
		return $return_string;
	}

	static function add($data_decoded,$token_attr){
		$return_message=[];
		$return_message['rc'] = 0;
		$return_message['status'] = 'error';
		$return_message['messages'] = [];

		$client = new MongoDB\Client(''.$_ENV['DB_URL'].'');
		$db_name = "ojr";
		$collection_name = 'raw_log';
		$collection = $client->$db_name->$collection_name;
		// echo(PHP_EOL."token_attr:".PHP_EOL.print_r($token_attr,true).PHP_EOL);
		foreach ($token_attr as $key => $value) {
			switch ($key) {
				case 'tags':
					if(isset($data_decoded['tags'])){
					}else{
						$data_decoded['tags']=[];
					}
					foreach ($value as $ttag) {
						$data_decoded['tags'][]=$ttag;
					}
					break;
				default:
					// $data_decoded[$key]=$value;
					break;
			}
		}
		unset($data_decoded['_object']);
		unset($data_decoded['_action']);
		// echo(PHP_EOL."data_decoded:".PHP_EOL.print_r($data_decoded,true).PHP_EOL);
		// exit();
		// $data_decoded = array_merge($data_decoded,$token_attr);
		$result = $collection->insertOne($data_decoded);
		switch ($result->getInsertedCount()) {
			case 1:
				$return_message['rc'] = 1;
				$return_message['status'] = 'success';
				$return_message['insertedId'] = $result->getInsertedId();
				break;
			default:
				$return_message['messages'][]="Expecting 1 insert got ".$result->getInsertedCount()."".' <'.__FILE__.':'.__LINE__.'>';
				break;
		}

		return $return_message;
				
	}

	static function getScript($type,$url="",$apptoken=""){
		$file="";
		$filename = "";
		$dir = "".__DIR__."";
		$dir = explode('/', $dir);
		array_pop($dir);
		array_push($dir, 'assets');
		switch (strtolower($type)) {
			case 'powershell':
			case 'windows':
			case 'win':
				array_push($dir, 'Oracle_Java_Retirement_Report.ps1');
				$filename=implode('/', $dir);
				$file=file_get_contents($filename);
				if(!empty($url) && !empty($apptoken)){
					$file = str_replace('#%apiurloverride%', ' = "'.$url.'"', $file);
				}else{
					$file = str_replace('#%apiurloverride%', '', $file);
				}
				if(!empty($apptoken)){
					$file = str_replace('#%AppAuthToken%', ' = "'.$apptoken.'"', $file);
				}else{
					$file = str_replace('#%AppAuthToken%', '', $file);
				}
				break;
			default:
				array_push($dir, 'Oracle_Java_Retirement_Report.sh');
				$filename=implode('/', $dir);
				$file=file_get_contents($filename);
				$file = str_replace("%body%", "black", $file);
				if(!empty($url) && !empty($apptoken)){
					$file = str_replace('#%apiurloverride%', 'api_endpoint='.$url.'', $file);
				}else{
					$file = str_replace('#%apiurloverride%', 'api_endpoint=', $file);
				}
				if(!empty($apptoken)){
					$file = str_replace('#%AppAuthToken%', 'app_auth_token='.$apptoken.'', $file);
				}else{
					$file = str_replace('#%AppAuthToken%', 'app_auth_token=', $file);
				}
				break;
		}
		return $file;
	}

	static function search(){
		$return_array = array();
		// $return_array['rc']=false;
		// $return_array['status']='null';
		// $return_array['message']='Nothing seems to have happened.';

		$a = func_get_args(); 
		$i = func_num_args(); 

		$_query_parameters = [];

		switch ($i) {
			case 0:
				// $return_array['message']='No Parameters exist. Assuming returning first page of patients.';
				$filter = [];
				$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
				$db_results = $database->get(array('filter'=>$filter));
				$return_array=$db_results['rows'];
				// $return_array['rc']=true;
				// $return_array['status']='success';
				break;
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								default:
									$_query_parameters[]=[$key=>$value];
									break;
							}
						}
						if(count($_query_parameters)>0){
							$filter = [ '$and' => $_query_parameters];
							$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
							// echo("".__FILE__."<".__LINE__.">".PHP_EOL.print_r($filter,true));
							$db_results = $database->get(array('filter'=>$filter));
							if(count($db_results['rows'])>0){
								// $return_array['message']='And';
								$return_array=$db_results['rows'];
								// $return_array['rc']=true;
								// $return_array['status']='success';
							}else{
								$filter = [ '$or' => $_query_parameters];
								$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
								$db_results = $database->get(array('filter'=>$filter));
								// $return_array['message']='Or';
								$return_array=$db_results['rows'];
								// $return_array['rc']=true;
								// $return_array['status']='success';
							}
						}else{
							// $return_array['message']='No Parameters exist.';
							$filter = [];
							$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
							$db_results = $database->get(array('filter'=>$filter));
							$return_array=$db_results['rows'];
							// $return_array['rc']=true;
							// $return_array['status']='success';
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
										// $return_array['rc']=true;
										// $return_array['message']='_id found';
										$return_array=$db_results['rows'];
										break;
									
									default:
										// $return_array['rc']=false;
										// $return_array['message']='_id not found.';
										break;
								}
								break;
							default:
								break;
						}
						
						break;

							default:
								// $return_array['message']='Search parameter is not an array. Will not perform search.';
								// $return_array['status']='error';
								break;
						}
				break;
			default:
				// $return_array['message']='Too many search parameters. All parameters must be in an array. Will not perform search.';
				// $return_array['status']='error';
				break;
		}

		return $return_array;
	}

	static function listUniqueTags(){
		$a = func_get_args(); 
		$i = func_num_args(); 

		$return_array = array();

		$client = new MongoDB\Client(''.$_ENV['DB_URL'].'');
		$db_name = "ojr";
		$collection_name = 'raw_log';
		$collection = $client->$db_name->$collection_name;

		$distinct = null;

		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						$distinct = $collection->distinct('tags',['$and' => $a[0]]);
						foreach($distinct as $tag) {
							$ta = array();
							$ta = $a[0];
							$ta[] = ['tags'=> $tag];
							$return_array[$tag]=$collection->count(['$and' => $ta]);
						}
						break;
					default:
						echo("".__FILE__."<".__LINE__.">".PHP_EOL.json_encode($a[0]).PHP_EOL);
						break;
				}
				break;
			
			default:
				$distinct = $collection->distinct('tags');
				foreach($distinct as $tag) {
					$return_array[$tag]=$collection->count(['tags'=> $tag]);
				}
				break;
		}
		
		$tmp=[];
		foreach ($return_array as $tag => $count) {
			$tmp[]=array('tag'=>$tag, 'count'=>$count);
		}
		return $tmp;
	}

	static function listUniqueHosts(){
		$a = func_get_args(); 
		$i = func_num_args(); 

		$return_array = array();

		$client = new MongoDB\Client(''.$_ENV['DB_URL'].'');
		$db_name = "ojr";
		$collection_name = 'raw_log';
		$collection = $client->$db_name->$collection_name;

		$distinct = null;

		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						$distinct = $collection->distinct('hostname',['$and' => $a[0]]);
						foreach($distinct as $host) {
							$ta = array();
							$ta = $a[0];
							$ta[] = ['hostname'=> $host];
							$return_array[$host]=$collection->count(['$and' => $ta]);
						}
						break;
					default:
						echo("".__FILE__."<".__LINE__.">".PHP_EOL.json_encode($a[0]).PHP_EOL);
						break;
				}
				break;
			
			default:
				$distinct = $collection->distinct('hostname');
				foreach($distinct as $host) {
					$return_array[$host]=$collection->count(['hostname'=> $host]);
				}
				break;
		}
		
		$tmp=[];
		foreach ($return_array as $tag => $count) {
			$tmp[]=array('hostname'=>$tag, 'count'=>$count);
		}
		return $tmp;
	}

	static function listUniqueFiles(){
		$a = func_get_args(); 
		$i = func_num_args(); 

		$return_array = array();

		$client = new MongoDB\Client(''.$_ENV['DB_URL'].'');
		$db_name = "ojr";
		$collection_name = 'raw_log';
		$collection = $client->$db_name->$collection_name;

		$distinct = null;

		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						$distinct = $collection->distinct('search-file.file',['$and' => $a[0]]);
						foreach($distinct as $file) {
							$ta = array();
							$ta = $a[0];
							$ta[] = ['search-file.file'=> $file];
							$return_array[$file]=$collection->count(['$and' => $ta]);
						}
						break;
					default:
						echo("".__FILE__."<".__LINE__.">".PHP_EOL.json_encode($a[0]).PHP_EOL);
						break;
				}
				break;
			
			default:
				$distinct = $collection->distinct('search-file.file');
				foreach($distinct as $file) {
					$return_array[$file]=$collection->count(['search-file.file'=> $file]);
				}
				break;
		}
		$tmp=[];
		foreach ($return_array as $filename => $count) {
			$tmp[]=array('path'=>$filename, 'count'=>$count);
		}
		return $tmp;
	}

	function toArray(){
		$return_array=array();
		return $return_array;
	}

	function toFlatArray(){
		$return_array=array();
		return $return_array;
	}

}

?>