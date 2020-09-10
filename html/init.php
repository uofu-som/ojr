<?php
	require_once("___site_settings.php");
	Funct::LoadClass('role');
	Funct::LoadClass('auth');
	Funct::LoadClass('ojr');
	Funct::LoadClass('settings');
	if(isset($_GET['verbose'])){
		echo("<pre>".PHP_EOL);
	}

role_add:
	$roles_existing_raw=Role::getMap();
	$roles=[];
	foreach ($roles_existing_raw as $role) {
		$roles[$role['name']]= new MongoDB\BSON\ObjectId("".$role['_id']."");
	}

	if(array_key_exists('admin', $roles)){}else{
		$role_admin = new Role(array("name"=>"admin","description"=>"Administrators: Full Access, Create Users"));
	}

	if(array_key_exists('contributor', $roles)){}else{
		$role_contributor = new Role(array("name"=>"contributor","description"=>"Contributor: Can access and update their own data"));
	}

	if(array_key_exists('audit', $roles)){}else{
		$role_audit = new Role(array("name"=>"audit","description"=>"Read Only Access for Auditing/Reporting"));
	}

	$roles_existing_raw=Role::getMap();
	$roles=[];
	foreach ($roles_existing_raw as $role) {
		$roles[$role['name']]= new MongoDB\BSON\ObjectId("".$role['_id']."");
	}

	if(isset($_GET['verbose'])){
		echo("".print_r($roles,true).PHP_EOL);
	}

settings_add:
	if(isset($_ENV['enc_key'])){
		Settings::add("enc_key",$_ENV['enc_key'],true);
		$enc_key = $_ENV['enc_key'];
	}elseif (empty($enc_key)) {
		# Check if $enc_key is set and put something in if not.
		$enc_key = "3ncrypt@nyth1ngfun";
		Settings::add("enc_key",$enc_key,true);
	}
	

user_admin_add:
	$admin_users=Auth::search(["roles"=>$roles['admin']]);
	if(isset($_GET['verbose'])){
		echo("".print_r($admin_users,true).PHP_EOL);
	}

	if(count($admin_users)>0){}else{
		# This is where we now create a new user
		if(!empty($_POST)){
			# Create the new admin user
			$user_default_admin_array = array(
				"username"=>$_POST['email'],
				"password"=>$_POST['pswrd'],
				"source"=>"local",
				"first_name"=>$_POST['fname'],
				"last_name"=>$_POST['lname']
			);
			$user_default_admin=Auth::add($user_default_admin_array);
			$role_add=Auth::addRole($user_default_admin['message']['_id'],$roles['admin']);
			# Assign Admin Role to new user
			if(isset($_GET['verbose'])){
				echo("_POST: ".print_r($_POST,true).PHP_EOL);
				echo("User Create: ".print_r($user_default_admin,true).PHP_EOL);
				echo("Role Add: ".print_r($role_add,true).PHP_EOL);
			}
		}else{
			# Present the new-admin form
			echo('<h1>Admin User Creation</h1>'.PHP_EOL);
			echo("<p>It's not pretty but it gets the job done.</p>".PHP_EOL);
			echo('<form method="post">'.PHP_EOL);
			echo('e-mail: <input type="text" name="email">'.PHP_EOL);
			echo('password: <input type="password" name="pswrd">'.PHP_EOL);
			echo('First Name: <input type="text" name="fname">'.PHP_EOL);
			echo('Last Name: <input type="text" name="lname">'.PHP_EOL);
			echo('<input type="submit" value="Create User">'.PHP_EOL);
			echo('</form>'.PHP_EOL);
			exit();
		}
	}
	goto end;

skip:
	// echo("".print_r($_ENV,true).PHP_EOL);
	echo(print_r(OJR::listUniqueTags(),true));
	echo(print_r(OJR::listUniqueFiles(),true));
	// echo(print_r(OJR::listUniqueTags([['tags'=>'Test']]),true));
	// echo(print_r(OJR::listUniqueTags([['tags'=>'Test'],['tags'=>'Windows']]),true));
	// echo(print_r(OJR::search(['tags'=>'everett.pilling@hsc.utah.edu']),true));
	// echo(print_r(Role::getMap(),true));
	$role_map=Role::getMap();
	echo(json_encode($role_map).PHP_EOL);
	foreach ($role_map as $role) {
		echo(Role::nameByID($role['_id']).PHP_EOL);
	}

end:
	if(isset($_GET['verbose'])){
		echo("</pre>".PHP_EOL);
	}
?>