<?php

require_once("funct.php");
Funct::LoadClass('mdb');

class Auth_Token{
	const DB = 'ojr';
	const COLLECTION = 'auth_token';

	function __construct() {
		Auth_Token::cleanup();
	}

	function __toString(){
		Auth_Token::cleanup();
		return "";
	}

	static function cleanup(){
		$now = new DateTime();
		$delete_rows=array();
		$delete_rows[] = ['filter'=>['time_expire'=>['$lt'=> new MongoDB\BSON\UTCDateTime($now->getTimestamp()*1000)]]];
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		return $database->delete(array('is_bulk'=>true,'rows'=>$delete_rows));
	}

	static function create(){
		Auth_Token::cleanup();
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));

		$session_timeout_sec = 60*60; // One hour

		global $enc_key;
		$_id = null;
		$email = null;
		$is_app = false;
		$token = null;
		$time_created = null;
		$time_expire = null;
		$tags=[];

		$a = func_get_args(); 
		$i = func_num_args(); 

		$return_array = array();

		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case '_id':
								case 'auth_id':
									$_id = $value;
									break;
								case 'email':
									$email = $value;
									break;
								case 'is_app':
									switch ($value) {
										case true:
										case 'true':
											$is_app = true;
											break;
										default:
											$is_app = false;
											break;
									}
									break;
								case 'tags':
									$tags=$value;
									// echo("Value:".print_r($value,true).PHP_EOL);
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

		if($is_app){
			$session_timeout_sec = 60*60*24*365*2; // 2 years
		}

		if(is_null($_id) || is_null($email) ){
			$return_array['rc'] = false;
			$return_array['status'] = 'error';
			$return_array['message'] = 'Missing parameters. Expecting: _id, email';
			$return_array['backtrace'] = debug_backtrace();
			return $return_array;
		} else {
			$session_expire = new DateTime();
			$time_created = new MongoDB\BSON\UTCDateTime($session_expire->getTimestamp()*1000);
			$session_expire->add(new DateInterval('PT'.$session_timeout_sec.'S'));
			$time_expire = new MongoDB\BSON\UTCDateTime($session_expire->getTimestamp()*1000);

			$cipher = "aes-128-gcm";

			if($enc_key){
				if (in_array($cipher, openssl_get_cipher_methods())){
					$ivlen = openssl_cipher_iv_length($cipher);
					$iv = openssl_random_pseudo_bytes($ivlen);
					$token = openssl_encrypt($_id, $cipher, $enc_key, $options=0, $iv, $tag);
					//store $cipher, $iv, and $tag for decryption later
					$insert_row=array(
						'email'=>$email,
						'auth_id'=>$_id,
						'token'=>$token,
						'iv'=>base64_encode($iv),
						'tag'=>base64_encode($tag),
						'cipher'=>$cipher,
						'time_created' => $time_created,
						'time_expire' => $time_expire,
						'is_app' => $is_app
					);
					if($is_app){
						$insert_row['tags']=array();
						if($tags){
							// echo(print_r($tags,true).PHP_EOL);
							foreach ($tags as $value) {
								$insert_row['tags'][]=$value;
							}
						}
					}
					// echo(print_r($insert_rows,true).PHP_EOL);
					$insert = $database->insert(array('rows'=>$insert_row,'is_bulk'=>false));
					$return_array['rc'] = true;
					$return_array['status'] = 'success';
					$return_array['message'] = 'Token issued. It will expire at '.$session_expire->format('Y-m-d H:i:s').'';
					$return_array['token'] = $token;
					$return_array['debug'] = $insert;
					return $return_array;
				}else{
					$return_array['rc'] = false;
					$return_array['status'] = 'error';
					$return_array['message'] = 'The cipher "'.$cipher.'" is not supported on this system.';
					$return_array['backtrace'] = debug_backtrace();
					return $return_array;
				}
			} else {
				$return_array['rc'] = false;
				$return_array['status'] = 'error';
				$return_array['message'] = 'Missing environment variable. Make sure "enc_key" is set in the system settings.';
				$return_array['backtrace'] = debug_backtrace();
				return $return_array;
			}
		}
	}

	static function destroy(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$return_array = array();

		// $auth_id = null;
		$token = null;

		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'token':
									$token = $value;
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
		if(is_null($token)){
			$return_array['rc']=false;
			$return_array['status']="error";
			$return_array['message']="Missing neccessary information to destroy token.";
		}else{
			$delete_rows=array();
			// $delete_rows[] = ['filter'=>['$and'=>[['token'=>['$eq'=> $token]],['auth_id'=>['$eq'=>$auth_id]]]]];
			$delete_rows[] = ['filter'=>['token'=>['$eq'=> $token]]];
			$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
			return $database->delete(array('is_bulk'=>true,'rows'=>$delete_rows));
		}
		return $return_array;
	}

	static function getUserAppTokens($auth_id){
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		switch (gettype($auth_id)) {
			case 'string': // Assuming _id is passed in so get it from the database
				# code...
				$auth_id = new MongoDB\BSON\ObjectId("$auth_id");
				break;
			case 'object':
				switch (get_class($auth_id)) {
					case 'MongoDB\BSON\ObjectId':
						# already in correct format
						break;
					default:
					return false;
						break;
				}
				
				break;
			default:
			return false;
				break;
		}

		$db_results = $database->get(
			array(
				'filter'=> ['$and'=>[
					['auth_id'=>['$eq'=>$auth_id]],
					['is_app'=>['$eq'=>true]]
					// ['time_expire'=>['$gt'=> new MongoDB\BSON\UTCDateTime($now->getTimestamp()*1000)]]
				]]
			)
		);
		$return_array=[];
		foreach ($db_results['rows'] as $row) {
			$return_row=[];
			foreach ($row as $key => $value) {
				switch ($key) {
					case 'token':
					case 'email':
					case 'auth_id':
					case 'time_created':
					case 'time_expire':
					case 'is_app':
					case 'tags':
						$return_row[$key]=$value;
						break;
					default:
						break;
				}
			}
			$return_array[]=$return_row;
		}
		return $return_array;
	}
	
	static function refresh($token){
		$return_array = array();
		$atr = Auth_Token::validate($token);
		if($atr->rc){
			$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
			$db_results = $database->get(
				array(
					'filter'=> ['$and'=>[
						['token'=>['$eq'=>$token]],
						['time_expire'=>['$gt'=> new MongoDB\BSON\UTCDateTime($now->getTimestamp()*1000)]]
					]]
				)
			);
			if(count($db_results['rows'])==1){
				$row = $db_results['rows'][0];
				// remove the old token
				$delete_rows=array();
				$delete_rows[] = ['filter'=>['token'=>['$eq'=>$row->token]]];
				$database->delete(array('is_bulk'=>true,'rows'=>$delete_rows));
				// create and return the new token
				return Auth_Token::create($row->auth_id,$row->email);
			}else{
				$delete_rows=array();
				$delete_rows[] = ['filter'=>['token'=>['$eq'=>$row->token]]];
				$return_array['rc'] = false;
				$return_array['status'] = 'error';
				$return_array['message'] = 'Token not found.';
				$return_array['cleanup'] = $database->delete(array('is_bulk'=>true,'rows'=>$delete_rows));
			}
		}else{
			return $atr;
		}
		return $return_array;
	}

	static function validate($token){
		Auth_Token::cleanup();
		global $enc_key;
		$return_array = array();
		$now = new DateTime();
		$database = new MDB(array('db'=>self::DB, 'collection'=>self::COLLECTION));
		$db_results = $database->get(
			array(
				'filter'=> ['$and'=>[
					['token'=>['$eq'=>$token]],
					['time_expire'=>['$gt'=> new MongoDB\BSON\UTCDateTime($now->getTimestamp()*1000)]]
				]]
			)
		);
		if(count($db_results['rows'])==1){
			$row = $db_results['rows'][0];
			$original_id = openssl_decrypt($row->token, $row->cipher, $enc_key, $options=0, base64_decode($row->iv), base64_decode($row->tag));
			if($original_id===false){
				$return_array['rc'] = false;
				$return_array['status'] = 'error';
				$return_array['message'] = 'Invalid token.';
			}else{
				///////////////////////////////////////////////////////////////////////////////
				$token_expire = $row->time_expire->toDateTime();
				if($now < $token_expire){
					$return_array['rc'] = true;
					$return_array['status'] = 'success';
					$return_array['message'] = 'Valid token.';
					$return_array['auth_id'] = $row->auth_id;
					$return_array['email'] = $row->email;
					if(isset($row->tags)){
						if(count($row->tags)>0){
							$return_array['tags'] = $row->tags;
						}
					}
					$tdiff = $now->diff($row->time_expire->toDateTime());
					header('Token_Expires: '.$row->time_expire->toDateTime()->format('U').'');
					// $return_array['tdiff'] = $now->diff($row->time_expire->toDateTime());
					$return_array['seconds_before_expire'] = ''.(((($tdiff->days * 24)+$tdiff->h)*60+$tdiff->i)*60+$tdiff->s).'';
				}else{
					$return_array['rc'] = false;
					$return_array['status'] = 'error';
					$return_array['message'] = 'Token expired.';
					$tdiff = $now->diff($row->time_expire->toDateTime());
					// $return_array['tdiff'] = $tdiff;
					$return_array['seconds_after_expire'] = ''.(((($tdiff->days * 24)+$tdiff->h)*60+$tdiff->i)*60+$tdiff->s).'';
				}
			}
		}elseif(count($db_results['rows']) > 1){
			$return_array['rc'] = false;
			$return_array['status'] = 'error';
			$return_array['message'] = 'Invalid token: duplicates found.';
			$return_array['debug'] = $db_results['rows'];
		}else{
			$return_array['rc'] = false;
			$return_array['status'] = 'error';
			$return_array['message'] = 'Token not found.';
		}
		if($return_array['rc']){
			header('Token-Valid: true');
		}else{
			header('Token-Valid: false');
		}
		return $return_array;
	}

}

?>