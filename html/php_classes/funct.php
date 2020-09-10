<?php
// require_once '../vendor/autoload.php';
// use MongoDB\Client as Mongo;
use Michelf\Markdown;

class Funct{
	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		if (method_exists($this,$f='__construct_'.$i)) { 
			call_user_func_array(array($this,$f),$a); 
		} else{
			return NULL;
		}
	}

	private function __construct_1() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						foreach ($a[0] as $key => $value) {
							define($key, $value);
						}
						break;
					default:
						# code...
						break;
				}
				break;
			
			default:
				return NULL;
				break;
		}
	}

	static function LoadClass($class){
		$file_name = ''.$class.'.php';
		$path = explode("/", "".__FILE__."");
		array_pop($path);
		$path[]=$file_name;
		$loaded=false;
		if(file_exists(implode("/", $path))){
			$loaded=true;
			require_once(implode("/", $path));
		}
		return $loaded;
	}

	function __toString(){
		$return_string = "[Defined Constants]".PHP_EOL;
		foreach (Funct::FlattenArray(get_defined_constants(true)) as $key => $value) {
			$return_string = $key." -> ".$value.PHP_EOL;
		}
		return $return_string;
	}

	static function AppendString2File(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$string = "";
		$path = "";
		switch ($i) {
			case 2:
				if(file_exists($a[1])){
					file_put_contents($a[1], $a[0], FILE_APPEND | LOCK_EX);
				}else{
					Funct::String2File(array('data'=>$a[0], 'path'=>$a[1]));
				}
				break;
			default:
				$error_message = "Usage: String2File('[string]','[path]')";
				trigger_error($error_message, E_USER_WARNING);
				break;
		}
	}

	static function Bin2Int($bin){
		return base_convert($bin, 2, 10);
	}

	static function Bin2IPv4($bin){
		$blen = strlen($bin);
		switch ($blen) {
			case (($blen > 32) ? true : false):
				echo("Too many bits for a 32bit address.");
				break;
			
			default:
				# code...
				break;
		}
		// return base_convert($bin, 2, 10);
	}

	static function Clear($n=130){
		for($i=0;$i<$n;$i++){
			echo(PHP_EOL);
		}
	}

	static function File2String(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$return_string="";
		switch ($i) {
			case 1:
				if(file_exists($a[0])){
					$return_string.=file_get_contents($a[0]);
				}else{
					$error_message = "File Not Found: " . $a[0] . "";
					trigger_error($error_message, E_USER_WARNING);
				}
				break;
			default:
				$error_message = "Usage: File2String('[path]')";
				trigger_error($error_message, E_USER_WARNING);
				break;
		}
		return $return_string;
	}

	static function FlattenArray(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$return_array=array();
		switch ($i) {
			case 1:
				if(is_array($a[0])){
					foreach ($a[0] as $key => $value) {
						switch(gettype($value)){
							case 'array':
								$return_array = array_merge($return_array, Funct::FlattenArray($key,$value));
								// $return_array.=Funct::FlattenArray($key,$value);
								break;
							default:
								$return_array[$key]=$value;
								break;
						}
					}
				}else{
					$error_message = "Parameter is not an Array: " . $a[0] . "";
					trigger_error($error_message, E_USER_WARNING);
				}
				break;
			case 2:
				$ta=NULL;
				$ts=NULL;
				if(is_array($a[0]) && is_string($a[1])){
					$ta=$a[0];
					$ts=$a[1];
				}elseif(is_array($a[1]) && is_string($a[0])){
					$ta=$a[1];
					$ts=$a[0];
				}
				if(!is_null($ta)){
					foreach ($ta as $key => $value) {
						switch(gettype($value)){
							case 'array':
								// $tmp_a = Funct::FlattenArray($ts."_".$key,$value);
								// $return_array = array_merge($return_array, $tmp_a);
								$return_array = array_merge($return_array, Funct::FlattenArray($ts."::".$key,$value));
								break;
							default:
								$return_array[$ts."::".$key]=$value;
								break;
						}
					}
				}
				break;
			default:
				$error_message = "Usage: FlattenArray([array])";
				trigger_error($error_message, E_USER_WARNING);
				break;
		}
		return $return_array;
	}

	static function HTMLMakeSafe($return_string){
		// $return_string = Funct::HTMLRemoveTags($return_string);
		$return_string = htmlentities($return_string);
		$return_string = Funct::HTMLRemoveOffensiveWords($return_string);
		$return_string = Funct::HTMLMarkDown($return_string);
		// $filter = array();$replacement = array();
		// $filter[]="/\r/iu";$replacement[]="";
		// $filter[]="/\n\n/iu";$replacement[]="</p><p>";
		// $filter[]="/\n/iu";$replacement[]="<br />";
		// $return_string = preg_replace ($filter, $replacement, $return_string);
		return $return_string;
	}

	static function HTMLMarkDown($return_string){
		if(Funct::LoadClass('Michelf/Markdown.inc')){
			$return_string = Markdown::defaultTransform($return_string);
		}else{
			$return_string = "<pre>Markdown didn't load</pre>";
		}
		return $return_string;
	}

	static function HTMLRemoveOffensiveWords($return_string){
		$filter = array();$replacement = array();
		# $filter[]="/fucked/iu";$replacement[]="f'd";
		$filter[]="/(fucking|fuckin|f\*\*\*\*\*g)/iu";$replacement[]="f'n";
		$filter[]="/fuck/i";$replacement[]="f***";
		$filter[]="/ shitty/";$replacement[]=" crappy";
		$filter[]="/ shit/";$replacement[]=" crap";
		$return_string = preg_replace ($filter, $replacement, $return_string);
		return $return_string;
	}

	static function HTMLRemoveTags($return_string){
		$filter = array();$replacement = array();
		$skip_tags = array();
		$skip_tags[]='b';
		$skip_tags[]='i';
		$skip_tags[]='div';
		$skip_tags[]='li';
		$skip_tags[]='ul';
		foreach ($skip_tags as $key => $tag) {
			$filter[]='/<'.$tag.'>/iu';$replacement[]="[".$tag."]";
			$filter[]='/<\/'.$tag.'>/iu';$replacement[]="[/".$tag."]";
		}
		$filter[]='/<[^>]*>/iu';$replacement[]=" ";
		foreach ($skip_tags as $key => $tag) {
			$filter[]='/\['.$tag.'\]/iu';$replacement[]="<".$tag.">";
			$filter[]='/\[\/'.$tag.'\]/iu';$replacement[]="</".$tag.">";
		}
		$return_string = preg_replace ($filter, $replacement, $return_string);
		$return_string = trim(preg_replace('/ {2,}/', ' ', $return_string));
		return $return_string;
	}

	static function ListFiles($dir,$recursive=false){
		$files=array();
		$tfiles = scandir($dir);
		foreach ($tfiles as $key => $filename) {
			switch ($filename) {
				case '.':
				case '..':
					break;
				default:
					if(is_dir($dir.$filename) && $recursive){
						$tmp=Funct::ListFiles($dir.$filename."/",$recursive);
						foreach ($tmp as $key => $value) {
							$files[$key]=$value;
						}
					}else{
						$files[$dir.$filename] = true;
					}
					break;
			}
		}
		// sort($files);
		return $files;
	}

	static function Int2Bin($int){
		return base_convert($int, 10, 2);
	}

	static function Int2IPv4($ip_int){
		return long2ip(-(4294967296-$ip_int));
	}

	static function IPv42Int($ip_string){
		return (int)sprintf("%u",ip2long($ip_string));
	}

	static function ipaddress(){
		return "<p>Your IP address is: ".print_r($_SERVER['REMOTE_ADDR'],true)."</p>";
	}

	static function isMongoObjectID($value){
		switch (gettype($value)) {
			case 'object':
				switch (get_class($value)) {
					case 'MongoDB\BSON\ObjectId':
						return true;
						break;
					default:
						break;
				}
			default:
				break;
		}
		return false;
	}

	static function milliseconds2Date($mil){
		switch (gettype($mil)) {
			case 'NULL':
				return NULL;
				break;
			
			default:
				echo(gettype($mil));
				break;
		}
		echo($mil);
		$mil=(string)(round($mil/1000));
		echo($mil);
		$return_time = new DateTime($mil);
		return $return_time->format('Y-m-d H:i:s e');
	}

	static function standardizeVariableName($string){
		$string = str_replace("_", " ", $string);
		$string = strtolower($string);
		$string = ucwords($string);
		$string = str_replace(" ", "_", $string);
		return $string;
	}

	static function String2File(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$string = "";
		$path = "";
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						file_put_contents($a[0]['path'], $a[0]['data'], LOCK_EX);
						break;
					default:
						# code...
						break;
				}
				break;
			case 2:
				echo("".$i."<pre>".print_r($a,true)."</pre>");
				file_put_contents($a[1], $a[0], LOCK_EX);
				break;
			default:
				$error_message = "Usage: String2File('[string]','[path]')";
				trigger_error($error_message, E_USER_WARNING);
				break;
		}
	}
	
}

?>