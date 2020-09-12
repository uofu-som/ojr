<?php

require_once("funct.php");
Funct::LoadClass('person');
Funct::LoadClass('role');

class Auth{
	protected const DB = 'ojr';
	protected const COLLECTION = 'auth';

	private $session_timeout_min = null;
	private $session_timeout_sec = null;

	protected $_id;
	protected $email;
	protected $password;
	protected $login_source;
	protected $person;
	protected $roles;

	function __construct() {

		$this->session_timeout_min = 60;
		$this->session_timeout_sec = $this->session_timeout_min*60;

		$a = func_get_args(); 
		$i = func_num_args(); 
		$number=NULL;
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case '_id':
									$number = $value;
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
		if(!is_null($number)){
			$this->init_DB($number);
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

	function login($user,$pass){
		$return_array=array();
		$return_array['message']=array();
		$filter = [ 'email' => ['$eq' => $user ]];
		$filter = ['$and'=>
			[
				[ 'email' => 
					['$eq' => $user ]
				],
				[ 'source' => 
					['$eq' => 'local' ]
				],
			]
		];

		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$db_results = $database->get(array('filter'=>$filter));
		$rows = $db_results['rows'];
		$row_count = count($rows);
		switch (true) {
			case $row_count < 1:
				return array(
					'rc'=>false
					,'status'=>'error'
					,'message'=>'User email not found '.$user.''
					,'records'=>0
					);
				break;
			case $row_count == 1:
				$user = $rows[0];
				// echo(print_r($user,true));
				if(Auth::secret_Check($user,$pass,$user->phash)){
					$this->_id=$user->_id;
					$this->email=$user->email;
					if($user->person){
						$this->person = new Person($user->person);
					}
					$auth_array=array();
					$auth_array['_id'] = $this->_id;
					$auth_array['email'] = $this->email;

					Funct::LoadClass('auth_token');
					$atoken = Auth_Token::create(array('_id'=>$this->_id,'email'=>$this->email));
					if($atoken['rc']){
						$return_array['rc']=true;
						$return_array['status']='success';
						$return_array['records']=''.$row_count.'';
						$return_array['_id']=''.$user->_id.'';
						$return_array['email']=$user->email;
						if($this->person){
							$return_array['given_name']=$this->person->get('given_name');
							$return_array['family_name']=$this->person->get('family_name');
						}
						$return_array['token']=$atoken['token'];
						foreach ($user->roles as $role_id) {
							$role_name = Role::nameByID($role_id);
							if(!empty($role_name)){
								// echo "tick - ".$role_name.PHP_EOL;
								$role_id=$role_id->jsonSerialize()['$oid'];
								// echo "tock - ".$role_id.PHP_EOL;
								$return_array['roles'][] = $role_name;
							}
						}
						$return_array['user']=$user;
						return $return_array;
					}else{
						return $atoken;
					}
				}else{
					return array('rc'=>false,'status'=>'failed'
						,'records'=>''.$row_count.''
						,'message'=>array("reason"=>"Authentication Failed")
					);
				}
				break;
			
			default:
				return array('rc'=>false,'status'=>'error'
					,'message'=>'Too many users with that email address'
					,'records'=>''.$row_count.''
					);
				break;
		}
	}

	function login_google($gtoken){
		$g_response=null;
		if(!empty($gtoken)){
			$g_response = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=".$gtoken);
			$g_response = json_decode($g_response,true);
			$g_response['login_source']='google';
				// [iss] => accounts.google.com
				// [azp] => 509284590532-elvq72nt1ph18m1cjc3rkhifhnul46b7.apps.googleusercontent.com
				// [aud] => 509284590532-elvq72nt1ph18m1cjc3rkhifhnul46b7.apps.googleusercontent.com
				// [sub] => 108909779659057511317
				// [email] => everett.pilling@gmail.com
				// [email_verified] => true
				// [at_hash] => y8MOmiKtSiAfbQFck1qlMQ
				// [name] => Everett Pilling
				// [picture] => https://lh3.googleusercontent.com/-OAb0hwbpmDE/AAAAAAAAAAI/AAAAAAAAEHw/RwvgVeOWK4E/s96-c/photo.jpg
				// [given_name] => Everett
				// [family_name] => Pilling
				// [locale] => en
				// [iat] => 1562049158
				// [exp] => 1562052758
				// [jti] => d8671732909f0e9418c0d4a7b0ed543df67bd43a
				// [alg] => RS256
				// [kid] => 118df254b837189d1bc2be9650a82112c00df5a4
				// [typ] => JWT
			// echo("<pre>".PHP_EOL);
			// echo(print_r($g_response,true).PHP_EOL);
			// echo("</pre>".PHP_EOL);
			$filter = ['$and'=>
				[
					[ 'email' => 
						['$eq' => $g_response['email'] ]
					],
					[ 'source' => 
						['$eq' => 'google' ]
					],
				]
			];

			$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
			$db_results = $database->get(array('filter'=>$filter));
			$rows = $db_results['rows'];
			$row_count = count($rows);
			switch (true) {
				case $row_count < 1:

					$new_user = Auth::add($g_response);
					// return array(
					// 	'rc'=>false
					// 	,'status'=>'error'
					// 	,'message'=>'User email not found '.$g_response['email'].' Created new user.'
					// 	,'records'=>0
					// 	);
					break;
				case $row_count == 1:
					$user = $rows[0];
					$this->_id=$user->_id;
					$this->email=$user->email;

					$auth_array=array();
					$auth_array['_id'] = $this->_id;
					$auth_array['email'] = $this->email;

					Funct::LoadClass('auth_token');
					$atoken = Auth_Token::create(array('_id'=>$this->_id,'email'=>$this->email));
					if($atoken['rc']){
						return array('rc'=>true,'status'=>'success'
							,'records'=>''.$row_count.''
							,'_id'=>''.$user->_id.''
							,'email'=>$user->email
							,'token'=>$atoken['token']
						);
					}else{
						return $atoken;
					}
					break;
				
				default:
					return array('rc'=>false,'status'=>'error'
						,'message'=>'Too many users with that email address'
						,'records'=>''.$row_count.''
						);
					break;
			}
		}
	}

	static function logout($ttoken){
		Funct::LoadClass('auth_token');
		return Auth_Token::destroy(array('token'=>$ttoken));
	}

	static function search(){

		$a = func_get_args(); 
		$i = func_num_args(); 

		// $filter = [ 'email' => ['$eq' => $user ]];
		$filter=[];
		if($i>0){
			$filter = ['$and' => $a];
		}
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$db_results = $database->get(array('filter'=>$filter));
		$rows = $db_results['rows'];
		$row_count = count($rows);
		return $rows;
	}

	static function secret_Make($user,$pass){

		// Create a 256 bit (64 characters) long random salt
		// Let's add 'something random' and the username
		// to the salt as well for added security
		$salt = hash('sha256', uniqid(mt_rand(), true) . 'lickenmyshtine' . strtolower($user));

		// Prefix the password with the salt
		$hash = $salt . $pass;

		// Hash the salted password a bunch of times
		for ( $i = 0; $i < 100000; $i ++ ) {
		  $hash = hash('sha256', $hash);
		}

		// Prefix the hash with the salt so we can find it back later
		$hash = $salt . $hash;

		return $hash;
	}

	static function secret_Check($user,$pass,$chash){

		// The first 64 characters of the hash is the salt
		$salt = substr($chash, 0, 64);

		$hash = $salt . $pass;

		// Hash the password as we did before
		for ( $i = 0; $i < 100000; $i ++ ) {
		  $hash = hash('sha256', $hash);
		}

		$hash = $salt . $hash;
		if ( $chash == $hash ) {
			return true;
		}else{
			return false;
		}
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
		$return_array = array();
		$username = null;
		$pass = null;

		$a = func_get_args(); 
		$i = func_num_args(); 
		$number=NULL;
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						$unused_properties = array(); // Keep passing properties to objects until all are consumed or no more objects to pass it to.
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'email':
								case 'mail':
								case 'un':
								case 'username':
									$return_array['email'] = $value;
									break;
								case 'pwd':
								case 'pass':
								case 'password':
									$return_array['password'] = $value;
									break;
								case 'login_source':
								case 'source':
									$return_array['login_source'] = $value;
									break;
								case '_id':
									$return_array['_id'] = $value;
									break;
								default:
									# Ignore the key
									$unused_properties[$key] = $value;
									break;
							}
						}
						$return_array['person'] = new Person($unused_properties);
						break;
					default:
						# I only accept arrays...
						break;
				}
				break;
			case 2:

				break;
			default:
				break;
		}

		switch ($return_array['login_source']) {
			case 'google':
				# All ready checked if email exists and doesn't so just create the auth
				$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
				$insert_row=['email' => $return_array['email'], 'person'=>$return_array['person']->getID(), 'source'=>'google'];
				$result = $database->insert(array('rows'=>$insert_row,'is_bulk'=>false));
				return (array('rc'=>true,'action'=>'Add User','status'=>'success','message'=>$result,'data'=>$return_array));
				break;
			case 'local':
				$authtmp = new Auth();

				$check = $authtmp->login($return_array['email'],$return_array['password']);
				switch (true) {
					case $check['records']<1:
						$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
						$insert_row=['email' => $return_array['email'], 'phash' => Auth::secret_Make($return_array['email'],$return_array['password']), 'person'=>$return_array['person']->getID(), 'source'=>'local'];
						$result = $database->insert(array('rows'=>$insert_row,'is_bulk'=>false));
						return (array('rc'=>true,'action'=>'Add User','status'=>'success','message'=>$result,'data'=>$return_array));
						break;
					default:
						return (array('rc'=>false,'action'=>'Add User','status'=>'failed','message'=>'User email already in use.'));
						break;
				}
				break;
			
			default:
				return (array('rc'=>false,'action'=>'Add User','status'=>'failed','message'=>'Login source not specified.'));
				break;
		}

		return $return_array;
	}

	static function addRole($auth_id,$role_id){
		$return_array = array();
		$return_array['rc'] = false;
		$return_array['status'] = "error";
		$return_array['message'] = array();
		$user_info=null;
		if(Funct::isMongoObjectID($role_id)){}else{
			switch (gettype($role_id)) {
				case 'string':
					$role_id = new MongoDB\BSON\ObjectId("$role_id");
					break;
				default:
					$return_array['message'][]="role_id: incompatible type must be string or MongoDB\BSON\ObjectId";
					return $return_array;
					break;
			}
		}
		switch (gettype($auth_id)) {
			case 'string':
				$auth_id = new MongoDB\BSON\ObjectId("$auth_id");
				break;
			case 'object':
				switch (get_class($auth_id)) {
					case 'MongoDB\BSON\ObjectId':
						// Do some work here to add the Role_id to the auth user
						$filter = [ '_id' => $auth_id, 'roles'=> $role_id ];
						$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
						$db_results = $database->get(array('filter'=>$filter));
						switch(count($db_results['rows'])){
							case 0:
								// auth user doesn't have role... but need to be sure auth user exists...
								$filter = [ '_id' => $auth_id ];
								$db_results = $database->get(array('filter'=>$filter));
								switch(count($db_results['rows'])){
									case 1:
										$user_info = json_decode(json_encode($db_results['rows'][0]),true);
										if(isset($user_info['roles'])){
											if(!is_array($user_info['roles'])){
												$user_info['roles']=array();
											}
										}else{
											$user_info['roles']=array();
										}
										array_push($user_info['roles'], $role_id);
										$update = array();
										$update['roles'] = $user_info['roles'];
										$result = $database->update(array('filter'=>$filter, 'update'=>$update, 'is_bulk'=>false));
										if($result['rc']){
											$return_array['rc'] = true;
											$return_array['status'] = "success";
											$return_array['message'][] = $result;
										}else{
											$return_array['message'][] = 'Update query failed';
											$return_array['message'][] = $result;
										}
										break;
									default:
										// something went wrong either user_id doesn't exist or too many exist...
										$return_array['message'][]="user_id doesn't exist";
										break;
								}
								break;
							default:
								// User all ready has role;
								$return_array['message'][]="User all ready has role";
								break;
						}
						break;
					default:
						# ignore the value because of incompatible type
						$return_array['message'][]="auth_id: incompatible object class must be string or MongoDB\BSON\ObjectId";
						break;
				}
				break;
			default:
				# ignore the value because of incompatible type
				$return_array['message'][]="auth_id: incompatible type must be string or MongoDB\BSON\ObjectId";
				break;
		}
		return $return_array;
	}

	static function hasRole($auth_id,$role_id){
		$return_array = array();
		$return_array['rc'] = false;
		$return_array['status'] = "error";
		$return_array['message'] = array();
		$user_info=null;
		if(Funct::isMongoObjectID($role_id)){}else{
			switch (gettype($role_id)) {
				case 'string':
					$role_id = new MongoDB\BSON\ObjectId("$role_id");
					break;
				default:
					$return_array['message'][]="role_id: incompatible type must be string or MongoDB\BSON\ObjectId";
					return $return_array;
					break;
			}
		}
		switch (gettype($auth_id)) {
			case 'string':
				$auth_id = new MongoDB\BSON\ObjectId("$auth_id");
			case 'object':
				switch (get_class($auth_id)) {
					case 'MongoDB\BSON\ObjectId':
						// Do some work here to add the Role_id to the auth user
						$filter = [ '_id' => $auth_id, 'roles'=> $role_id ];
						$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
						$db_results = $database->get(array('filter'=>$filter));
						switch(count($db_results['rows'])){
							case 0:
								// auth user doesn't have role... but need to be sure auth user exists...
								$return_array['status'] = "false";
								$return_array['message'][]="User does not have that role";
								break;
							default:
								// User all ready has role;
								$return_array['rc'] = true;
								$return_array['status'] = "true";
								$return_array['message'][]="User has that role";
								break;
						}
						break;
					default:
						# ignore the value because of incompatible type
						$return_array['message'][]="auth_id: incompatible object class must be string or MongoDB\BSON\ObjectId";
						break;
				}
				break;
			default:
				# ignore the value because of incompatible type
				$return_array['message'][]="auth_id: incompatible type must be string or MongoDB\BSON\ObjectId";
				break;
		}
		return $return_array;
	}

	static function listUsersAll(){
		$return_array = [];
		$users = Auth::search();
		foreach ($users as $user) {
			$user_array = [];
			foreach ($user as $key => $value) {
				switch ($key) {
					case '_id':
					case 'email':
					case 'roles':
						$user_array[$key] = $value;
						break;
					case 'person':
						$tper = new Person($value);
						$user_array['first_name'] = $tper->get('first_name');
						$user_array['last_name'] = $tper->get('last_name');
						break;
					default:
						# code...
						break;
				}
			}
			$return_array[]=$user_array;
		}
		return $return_array;
	}

	function remove($user){
		$delete_rows=array();
		$delete_rows[] = ['filter'=>['email' => $user]];
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$result = $database->delete(array('is_bulk'=>true,'rows'=>$delete_rows));
		return (array('rc'=>true,'action'=>'Remove User','status'=>'success','message'=>array($user,$result)));
	}

	function update($user,$pass){
		$filter = ['email' => $user];
		$update = array();
		$update['phash'] = Auth::secret_Make($user,$pass);
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$result = $database->update(array('filter'=>$filter, 'update'=>$update, 'is_bulk'=>false));
		return (array('rc'=>true,'action'=>'Update User','status'=>'success','message'=>$result));
	}

}

?>