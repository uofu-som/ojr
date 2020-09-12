<?php 
	$return_headers=array();
	$return_headers[]='Content-Type: application/json';
	require_once("___site_settings.php"); 

	$return_message=array('rc'=>false,'status'=>'null', 'message'=> ["That didn't seem to work! Just give up right now!"], 'data' => []);
	$debug=array();

	$path = explode("/", "".__FILE__."");
	array_pop($path);
	array_pop($path);
	$path[]='php_classes';
	$path[]='funct.php';
	$loaded=false;
	if(file_exists(implode("/", $path))){
		$loaded=true;
		require_once(implode("/", $path));
	}else{
		$loaded = false;
	}

	function _return_error(){
		$a=func_get_args();
		$i=func_num_args();
		$funct_return_message=array();
		$funct_return_message['rc']='false';
		$funct_return_message['status']='error';
		switch ($i) {
			case 1:
				if(!empty($a[0]['message'])){
					$funct_return_message['message']=$a[0]['message'];
				}else{
					$funct_return_message['message']='Unknown Method';
				}
				break;
			default:
				$funct_return_message['message']='Unknown Method';
				break;
		}
		$funct_return_message['backtrace']=debug_backtrace();
		return $funct_return_message;
	}

	$data = file_get_contents("php://input");

	$data_decoded = json_decode($data,true);

		if(is_null($data)){
			$return_message['data'][]="[is null]";
		}else{
			$return_message['data'][]=gettype($data);
			$return_message['data'][]=substr($data,0,5);
		}
			foreach($return_headers as $header) {
				header($header);
			}
			echo(json_encode($return_message,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
			exit();
			
	$data_decoded_orig = $data_decoded;
	$d0="{}";
	if(is_null($data_decoded)){
		if(!empty(trim($data))){

			$return_message['rc'] = false;
			$return_message['status']="error";
			$return_message['message'][]="Data didn't decode and it's not empty...";
			$return_message['data']=$data;

			// $d1 = substr($data, 1, -1);
			// $d1 = explode(",",$d1);
			// $d0 = "{";
			// $rpattern = array(); $rplace = array();
			// $rpattern[]='/^["\']{0,1}([\w\s]*)["\']{0,1}$/';$rplace[]='\1';
			// $is_first=true;
			// foreach ($d1 as $value) {
			// 	$d2 = explode(":", $value);
			// 	if($is_first){
			// 		$is_first=false;
			// 		$d0 .= '"'.preg_replace($rpattern,$rplace,$d2[0]).'":"'.preg_replace($rpattern,$rplace,$d2[1]).'"';
			// 	}else{
			// 		$d0 .= ',"'.preg_replace($rpattern,$rplace,$d2[0]).'":"'.preg_replace($rpattern,$rplace,$d2[1]).'"';
			// 	}
			// 	$d0 = preg_replace('/""/', '"', $d0);
			// }
			// $d0 .= "}";
			// $data_decoded = json_decode($d0,true);
		}else{
			$data="{}";
			$d0="{}";
			$data_decoded = json_decode("{}",true);
		}
	}

	// Check Header for Bearer Token
	$token = null;
	$token_attr = array();
	foreach (getallheaders() as $name => $value) {
		switch (strtolower($name)) {
			case 'authorization':
				$tp = explode(' ', $value);
				if(count($tp)==2){
					Funct::LoadClass('auth_token');
					$atr = Auth_Token::validate($tp[1]);
					if($atr['rc']){
						$token = $tp[1];
						foreach ($atr as $key => $value) {
							switch ($key) {
								case '_id':
								case 'auth_id':
								case 'email':
								case 'tags':
									$token_attr[$key]=$value;
									break;
								default:
									$token_attr[$key]=$value;
									break;
							}
						}
					}
				}
				break;
			default:
				# Ignore headers that I'm not interested in.
				break;
		}
	}
	if(is_null($token)){
		$debug['has_valid_token']=false;
		goto invalid_token;
	}else{
		$debug['has_valid_token']=true;
		goto valid_token;
	}

valid_token:
	switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
		case 'GET':
			$tmp1 = (strcmp($data,'{}') == 0);
			if( $tmp1 && !empty($_GET) ){
				foreach ($_GET as $key => $value) {
					$data_decoded[$key] = $value;
				}
			}
			if(!empty($data_decoded['_object'])){
				switch ($data_decoded['_object']) {
					case 'apptoken':
						switch ($data_decoded['_action']) {
							case 'all':
								$return_message=array(
									'rc'=>true,
									'status'=>'success', 
									'message'=>["Get all app tokens."], 
									'data' => Auth_Token::getUserAppTokens($token_attr['auth_id'])
								);
								break;
							case 'delete':
								$return_message['rc'] = true;
								$return_message['status']="success";
								$return_message['message'][]=Auth_Token::Delete($data_decoded['token']);
								$return_message['data']=Auth_Token::getUserAppTokens($token_attr['auth_id']);
								break;
							default:
								# code...
								break;
						}
						break;
					case 'auth':
						Funct::LoadClass('auth');
						$auth = new Auth();
						switch ($data_decoded['_action']) {
							case 'users_all':
								$return_message = Auth::search();
								break;
							case 'logout':
								$return_message = Auth::logout($token);
								break;
							default:
								break;
						}
						break;
					case 'dashboard':
						Funct::LoadClass('auth');
						Funct::LoadClass('ojr');
						switch ($data_decoded['_action']) {
							case 'hosts':
								$return_message['rc']=1;
								$return_message['status']="success";
								$return_message['message']=["Get hosts for dashboard"];
								$return_message['data']=OJR::listUniqueHosts();
								break;
							case 'tags':
								$return_message['rc']=1;
								$return_message['status']="success";
								$return_message['message']=["Get tags for dashboard"];
								$return_message['data']=OJR::listUniqueTags();
								break;
							case 'users':
								$return_message['rc']=1;
								$return_message['status']="success";
								$return_message['message']=["Get users for dashboard"];
								$return_message['data']=Auth::listUsersAll();
								break;
							default:
								break;
						}
						break;
					case 'reports':
						Funct::LoadClass('ojr');
						switch ($data_decoded['_action']) {
							case 'main':
								$return_message['rc']=1;
								$return_message['status']="success";
								$return_message['message']=[];
								$return_message['data']=OJR::listUniqueFiles();
								break;
							
							default:
								# code...
								break;
						}
						break;
					case 'roles':
						Funct::LoadClass('role');
						$roles_raw=Role::search();
						$roles=[];
						foreach ($roles_raw['rows'] as $key => $role) {
							$roles[(string)$role->_id]=$role->name;
						}
						$return_message['rc']=1;
						$return_message['status']="success";
						$return_message['message']=[];
						$return_message['data']=$roles;
						break;
					case 'script':
						Funct::LoadClass('ojr');
						if(empty($data_decoded['url'])){ $data_decoded['url'] = "";}
						if(empty($data_decoded['type'])){ $data_decoded['type'] = "";}
						if(empty($data_decoded['app_token_id'])){ $data_decoded['app_token_id'] = "";}
						// if(!empty($data_decoded['url']) && !empty($data_decoded['type']) && !empty($data_decoded['app_token_id'])){
							$return_message=array(
								'rc'=>true,
								'status'=>'success', 
								'message'=>["Get script for provided app_token."], 
								'data' => OJR::getScript($data_decoded['type'],$data_decoded['url'],$data_decoded['app_token_id'])
							);
							$return_message['message']['data_decoded']=$data_decoded;
						// }else{
						// 	$return_message=array(
						// 		'rc'=>false,
						// 		'status'=>'failure', 
						// 		'message'=>"something wasn't right.", 
						// 		'data' => $data_decoded
						// 	);
						// }
						break;
					case 'settings':
						Funct::LoadClass('settings');
						switch ($data_decoded['_action']) {
							case 'get':
								$return_message['rc'] = true;
								$return_message['status']="success";
								unset($return_message['message']);
								$return_message['settings'] = Settings::get();
								break;
							default:
								break;
						}
						break;
					default:
						$return_message=_return_error(array('VARS'=>$data));
						break;
				}
			}else{
				$return_message['message'][]='$data_decoded is empty';
			}
			break;

		case 'POST':
			if(!empty($data_decoded)){
				if(!empty($data_decoded['_object'])){
					switch ($data_decoded['_object']) {
						case 'auth':
							Funct::LoadClass('auth');
							$auth = new Auth();
							switch ($data_decoded['_action']) {
								case 'create':
									$return_message = $auth->add($data_decoded['email'],$data_decoded['password']);
									break;
								case 'logout':
									$return_message = Auth::logout($token);
									break;
								default:
									break;
							}
							break;
						case 'apptoken':
							switch ($data_decoded['_action']) {
								case 'create':
									$return_message['rc']=1;
									$return_message['status']="success";
									$return_message['message']=["Add App Token"];
									// $return_message['message'][]=$token;
									// $return_message['message'][]=$token_attr;
									$send_args = [];
									$send_args['tags']=$data_decoded['tags'];
									$send_args['auth_id']=$token_attr['auth_id'];
									$send_args['email']=$token_attr['email'];
									$send_args['is_app']=true;
									$return_message['response']=Auth_Token::create($send_args);
									$return_message['data']=Auth_Token::getUserAppTokens($token_attr['auth_id']);
									break;
								default:
									# code...
									break;
							}
							break;
						case 'ojr':
							Funct::LoadClass('ojr');
							$return_message['message'][] = OJR::add($data_decoded,$token_attr);
							break;
						default:
							$return_message['message']='Object unknown: '.$data_decoded['_object'].' <'.__FILE__.':'.__LINE__.'>';
							break;
					}
				}else{
					$return_message['message']='Object not set. <'.__FILE__.':'.__LINE__.'>';
				}
			}else{
				$return_message['message']=array();
				$return_message['message'][]='$d0 is: '.json_encode($d0).'';
				$return_message['message'][]='$data is: '.json_encode($data).'';
				$return_message['message'][]='$data_decoded is: '.json_encode($data_decoded).'';
				$return_message['message'][]='$data_decoded_orig is: '.json_encode($data_decoded_orig).'';
			}
			break;

		case 'PUT':
			if(!empty($data_decoded)){
				switch ($data_decoded['_object']) {
					case 'auth':
						Funct::LoadClass('auth');
						$auth = new Auth();
						switch ($data_decoded['_action']) {
							case 'update':
								// $return_message = $auth->add($data_decoded['email'],$data_decoded['password']);
								break;
							default:
								break;
						}
						break;
					default:
						$return_message=_return_error(array('VARS'=>$data_decoded));
						break;
				}
			}
			break;

		case 'DELETE':
			$return_message['message']['_GET']=$_GET;
			$return_message['message']['_POST']=$_POST;
			$return_message['message']['data_decoded']=$data_decoded;
			if(!empty($data_decoded)){
				switch ($data_decoded['_object']) {
					case 'auth':
						Funct::LoadClass('auth');
						$auth = new Auth();
						switch ($data_decoded['_action']) {
							case 'delete':
								break;
							default:
								break;
						}
						break;
					case 'apptoken':
						switch ($data_decoded['_action']) {
							case 'delete':
								$return_message['rc'] = true;
								$return_message['status']="success";
								$return_message['message'][]=Auth_Token::destroy($data_decoded);
								$return_message['data']=Auth_Token::getUserAppTokens($token_attr['auth_id']);
								break;
							default:
								break;
						}
						break;
					default:
						$return_message=_return_error(array('VARS'=>$data_decoded));
						break;
				}
			}else{
				$return_message['message'][]='data_decoded is empty';
			}
			break;

		default:
			$return_args = array();
			if($data_decoded){
				$return_args['message'][] = 'The method "'.strtoupper($_SERVER['REQUEST_METHOD']).'" is not a supported request method.';
				$return_args['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
				$return_args['data_decoded'] = $data_decoded;
			}else{
				$return_args['message'][] = 'Invalid data sent to an unsupported request method, "'.strtoupper($_SERVER['REQUEST_METHOD']).'".';
				$return_args['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
				$return_args['data'] = $data;
			}
			$return_message = _return_error($return_args);
			break;
	}
	goto end;

invalid_token:
	// $return_message['message'][]='InValid Token';
	switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
		case 'GET':
			$debug['Get Method']='php://input';
			$tmp1 = (strcmp($data,'{}') == 0);
			if( $tmp1 && !empty($_GET) ){
				$debug['Get Method']='$_GET';
				foreach ($_GET as $key => $value) {
					$data_decoded[$key] = $value;
				}
			}
			if(!empty($data_decoded['_action'])){
				switch ($data_decoded['_object']) {
					case 'auth':
						Funct::LoadClass('auth');
						$auth = new Auth();
						switch ($data_decoded['_action']) {
							case 'login':
								switch ($data_decoded['_source']) {
									case 'google':
										$return_message = $auth->login_google($data_decoded['token']);
										break;
									default:
										// default to local login
										$return_message = $auth->login($data_decoded['email'],$data_decoded['password']);
										break;
								}
								break;
							case 'logout':
								break;
							default:
								break;
						}
						break;
					case 'settings':
						Funct::LoadClass('settings');
						switch ($data_decoded['_action']) {
							case 'get':
								$return_message['rc'] = true;
								$return_message['status']="success";
								unset($return_message['message']);
								$return_message['settings'] = Settings::get();
								break;
							default:
								break;
						}
						break;
					default:
						$return_message=_return_error(array('VARS'=>$data));
						break;
				}
			}else{
				$debug['_action'] = "Is empty";
			}
			break;

		case 'POST':
			if(!empty($data_decoded)){
				if(!empty($data_decoded['_object'])){
					switch ($data_decoded['_object']) {
						case 'auth':
							Funct::LoadClass('auth');
							$auth = new Auth();
							switch ($data_decoded['_action']) {
								case 'login':
									$return_message = $auth->login($data_decoded['email'],$data_decoded['password']);
									break;
								default:
									break;
							}
							break;
						default:
							$return_message=_return_error(array('VARS'=>$data_decoded));
							break;
					}
				}else{
					$return_message=_return_error(array('VARS'=>$data_decoded));
				}
			}
			break;

		default:
			$return_args = array();
			if($data_decoded){
				$return_args['message'] = 'The method "'.strtoupper($_SERVER['REQUEST_METHOD']).'" is not a supported request method.';
				$return_args['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
				$return_args['data_decoded'] = $data_decoded;
			}else{
				$return_args['message'] = 'Invalid data sent to an unsupported request method, "'.strtoupper($_SERVER['REQUEST_METHOD']).'".';
				$return_args['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
				$return_args['data'] = $data;
			}
			$return_message = _return_error($return_args);
			break;
	}
	goto end;

end:
	if($verbose){
		$debug['data_decoded']=$data_decoded;
		$debug['data_decoded_orig']=$data_decoded_orig;
		$debug['request_method']=strtoupper($_SERVER['REQUEST_METHOD']);
		$return_message['debug']=$debug;
	}
	foreach($return_headers as $header) {
		header($header);
	}
	echo(json_encode($return_message,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
?>
