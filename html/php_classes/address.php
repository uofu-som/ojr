<?php

class Address{

	protected const DB = 'gz_ehr';
	protected const COLLECTION = 'address';

	private $name = NULL;
	private $description = NULL;

	private $latitude = NULL;
	private $longitude = NULL;

	public $country = NULL;
	public $region = array(); // Subdivisions of Country bigger than city. Could be array('Region'=>"[Region Name]", 'State'=>"[State Name]", 'County'=>"[County Name]") Ordered from greatest subdivision to smallest.
	public $city = NULL;
	public $street_1 = NULL;
	public $street_2 = NULL;
	public $zipcode = NULL;

	private $possible_matches = NULL;

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
					switch (strtolower($key)) {
						case 'name':
							$this->name=$value;
							break;
						case 'description':
							$this->description=$value;
							break;
						case 'lat':
						case 'latitude':
							$this->latitude=$value;
							break;
						case 'long':
						case 'longitude':
							$this->longitude=$value;
							break;
						case 'country_name':
						case 'country':
							$this->country=$value;
							break;
						case 'state':
						case 'subdivision_1_name':
						case 'region':
							switch (gettype($value)) {
								case 'string':
									$this->region=explode("|", $value);
									foreach ($this->region as $key => $value) {
										$this->region[$key]=trim($value);
									}
									break;
								case 'array':
									$this->region=$value;
									break;
								default:
									break;
							}
							break;
						case 'city':
						case 'city_name':
							$this->city=$value;
							break;
						case 'street':
							if(is_array($value)){
								foreach ($value as $street_key => $street){
									$var="street_".$street_key;
									$this->$var = $street;
									// echo($this->$var.PHP_EOL);
								}
							}
							// $this->street_1=$value;
							break;
						case 'street 1':
						case 'street_1':
							$this->street_1=$value;
							break;
						case 'street 2':
						case 'street_2':
							$this->street_2=$value;
							break;
						case 'zipcode':
							$this->zipcode=$value;
							break;
						default:
							// $error_message='Unknown parameter:'.$value.', ignoring parameter';
							// trigger_error($error_message, E_USER_WARNING);
							break;
					}
				}
				break;
			case 'string':
				$matches = $this->parse($init);
				if(is_array($matches)){
					switch (count($matches)) {
						case 1:
							$this->__construct1($matches[0]);
							break;
						default:
							$this->possible_matches = $matches;
							break;
					}
				}
				break;
			default:
				# code...
				break;
		}
	}

	function __construct2($one, $two){ // Assuming that Address is being initialized by Latittude & Longitude
		// echo("Two Parameters");
		$this->setLatLong($one,$two);
	}

	function parse($init_string){
		$possible_matches=array();

		$init_array=array();
		$init_string=preg_replace('/\n/', ',', $init_string);

		$tospace=array();
		$tospace[]='/\t/';
		$tospace[]='/\r/';
		$tospace[]='/\0/';
		$tospace[]='/\x0B/';
		$tospace[]='/\xC2/';
		$tospace[]='/\xA0/';
		$init_string=preg_replace($tospace, ' ', $init_string);

		$bleng=strlen($init_string);
		$aleng=0;
		while($bleng!=$aleng){
			$bleng=strlen($init_string);
			$init_string=preg_replace('/  /', ' ', $init_string);
			$aleng=strlen($init_string);
		}
		$init_string=explode(',', trim($init_string));
		foreach ($init_string as $key => $value) {
			$value = trim($value);
			if(preg_match('/([a-z]*)\s{1,}(\b[0-9]{5,5}-[0-9]{1,5}\b|\b[0-9]{5,5}\b)/i', $value, $matches)){
				$init_string[$key]=$matches[1];
				$init_string[]=$matches[2];
			}else{
				$init_string[$key]=$value;
			}
		}

		$city_database = new MDB(array('db'=>self::DB, 'collection'=>'city'));
		$f_in = ['$in'=>array()];
		foreach ($init_string as $key => $value) {
			$f_in['$in'][]=ucfirst($value);
			$f_in['$in'][]=strtoupper($value);
		}

		// echo('<pre>'.PHP_EOL.print_r($init_string,true).PHP_EOL.'</pre>');

		$filter = ['$and'=>
			[
				// ['$or'=>[
				// 		['country_name' => $f_in],
				// 		['country_iso_code' => $f_in]
				// 	]
				// ],
				['$or'=>[
						['subdivision_1_iso_code' => $f_in],
						['subdivision_1_name' => $f_in]
					]
				],
				['city_name' => $f_in]
			]
		];

		$i_search=0;

		rerun_city_finder:

		$results = $city_database->get(array('filter'=>$filter));
		$i_search++;
		// echo('<pre>'.PHP_EOL.print_r($results['rows'],true).PHP_EOL.'</pre>');

		switch(count($results['rows'])){
			case 0:
				switch ($i_search) {
					case 1:	
						$filter = ['$and'=>
							[
								['$or'=>[
										['country_name' => $f_in],
										['country_iso_code' => $f_in]
									]
								],
								['city_name' => $f_in]
							]
						];
						goto rerun_city_finder;
						break;
					case 2:	
						$filter = ['city_name' => $f_in];
						goto rerun_city_finder;
						break;
					
					default:
						$row=array();
						// echo('<pre>'.PHP_EOL.print_r($this->parse_format($init_string,(array)$row),true).PHP_EOL.'</pre>');
						break;
				}

				break;
			case 1:
				$tmp = $results['rows'][0];
				$possible_matches[]=$this->parse_format($init_string,(array)$tmp);
				return $possible_matches;
				break;
			default:
				// foreach ($results['rows'] as $row) {
				// 	$possible_matches[]=$this->parse_format($init_string,(array)$row);
				// }
				break;
		}

		$zipcode_database = new MDB(array('db'=>self::DB, 'collection'=>'zipcodes'));
		foreach ($results['rows'] as $row) {
			$possiblity = $this->parse_format($init_string,(array)$row);
			if(isset($possiblity['zipcode'])){
				$filter=array();
				$filter=['$and'=>[
					['Zipcode'=>['$regex'=>$possiblity['zipcode']]],
					['LocationType'=>['$in'=>["PRIMARY","ACCEPTABLE"]]]
				]];
				$z_results = $zipcode_database->get(array('filter'=>$filter));
				foreach ($z_results['rows'] as $key => $value) {
					if(strtoupper($value->Country)==strtoupper($possiblity['country_iso_code'])){
						if(strtoupper($value->State)==strtoupper($possiblity['subdivision_1_iso_code'])){
							$possible_matches[]=$possiblity;
							break;
						}
					}
				}
			}else{
				$possible_matches[]=$possiblity;
			}
		}
		return $possible_matches;
	}

	function parse_format($address_pieces, $match){
		// echo("<pre>".PHP_EOL."Formating stuff.".PHP_EOL.print_r($address_pieces,true).PHP_EOL.print_r($match,true));
		$return_format = array();
		$used=array();
		$i=1;
		foreach ($address_pieces as $index => $value) {
			$key = array_search($value, $match);
			if(!is_null($key) && !($key===false)){
				// $return_format[$key]=$match[$key];
				$used[]=$key;
			}else{
				if(preg_match('/(^\b[0-9]{5,5}-[0-9]{1,5}\b$|^\b[0-9]{5,5}\b$)/i', $value, $matches)){
					$return_format['zipcode']=$matches[1];
				}else{
					if(!isset($return_format["street"])){
						$return_format["street"]=array();
					}
					$return_format["street"][$i]=$value;
					$i++;
				}
			}
		}
		foreach ($match as $key => $value) {
			switch ($key) {
				case 'city_name':
					$return_format['city']=$match[$key];
					break;
				case 'country_name':
					$return_format['country']=$match[$key];
					break;
				case 'country_iso_code':
					$return_format[$key]=$match[$key];
					break;
				case 'subdivision_1_name':
					$return_format['region']['state']=$match[$key];
					break;
				case 'subdivision_1_iso_code':
					$return_format[$key]=$match[$key];
					break;
				case 'time_zone':
					$return_format[$key]=$match[$key];
					break;
				default:
					# code...
					break;
			}
		}
		// $return_string="";
		// if(isset($return_format["street"])){
		// 	foreach ($return_format["street"] as $key => $value) {
		// 		$return_string.=$value.PHP_EOL;
		// 	}
		// }
		// if(isset($return_format["city_name"])){$return_string.=$return_format["city_name"];}
		// if(isset($return_format["city_name"])&&isset($return_format["subdivision_1_name"])){$return_string.=", ";}
		// if(isset($return_format["subdivision_1_name"])){$return_string.=$return_format["subdivision_1_name"]." ";}
		// if(isset($return_format["zipcode"])){$return_string.=$return_format["zipcode"].PHP_EOL;}else{$return_string.=PHP_EOL;}
		// if(isset($return_format["country_name"])){$return_string.=$return_format["country_name"].PHP_EOL;}
		return $return_format;
	}

	function getDescription(){return $this->description;}
	function setDescription($d){}
	
	function getLat(){return $this->latitude;}
	function setLat($lat){
		$this->latitude = $lat;
		return 1;
	}
	
	function getLatLong(){return array('latitude'=>$this->latitude,'longitude'=>$this->longitude);}
	function setLatLong($lat,$long){
		$this->setLat($lat);
		$this->setLong($long);
	}
	
	function getLong(){return $this->longitude;}
	function setLong($long){
		$this->longitude = $long;
		return 1;
	}
	
	function getName(){return $this->name;}
	function setName($n){
		$this->name = $n;
		return 1; 
	}

	function toArray(){
		$return_array=array();
		foreach ($this as $key => $value) {
			if(!is_null($value) && !empty($value)){
				$return_array[$key]=$value;
			}
		}
		return $return_array;
	}

	function toFlatArray(){
		$return_array=array();
		foreach ($this as $key => $value) {
			switch ($key) {
				case 'region':
					foreach ($value as $akey => $avalue) {
						if(!is_null($value) && !empty($value)){
							$return_array[$key."_".$akey]=$avalue;
						}
					}
					break;
				default:
					if(!is_null($value) && !empty($value)){
						$return_array[$key]=$value;
					}
					break;
			}
		}
		return $return_array;
	}

	function __toString(){
		$return_string = "";

		if(!is_null($this->possible_matches)){
			foreach ($this->possible_matches as $match) {
				$return_string = print_r($match,true).PHP_EOL;
			}
		}else{
			if(!empty($this->street_1))
				$return_string .= "".$this->street_1.", ";
			if(!empty($this->street_2))
				$return_string .= "".$this->street_2.", ";
			if(!empty($this->city))
				$return_string .= "".$this->city."";
			foreach ($this->region as $key => $value) {
				$return_string .= ", ".$value;
			}
			if(!empty($this->country))
				$return_string .= ", ".$this->country."";
			if(!empty($this->zipcode))
				$return_string .= " ".$this->zipcode."";
		}
		return $return_string;
	}

}

?>