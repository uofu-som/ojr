<?php
class CSVException extends Exception { }

class CSVRow{
	protected $data = array();

	function __construct($cols){ // $cols was vector<string>
		foreach ($cols as $i => $value) {
			$this->data[]=$value;
		}
	}

	function addColumn(){ // returns int
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 0:
				return $this->addColumn_String("");
				break;
			case 1:
				switch (gettype($a[0])) {
					case 'string':
						return $this->addColumn_String($a[0]);
						break;
					default:
						break;
				}
				break;
			
			default:
				break;
		}
	}

	function addColumn_String($default_text){ // returnts int $default_text was string
		$this->data[]=$default_text;
		return (count($this->data)-1);
	}

	function addColumnAt(){ // $n was int
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'integer':
						$this->addColumnAt_String("",$a[0]);
						break;
					default:
						break;
				}
				break;
			case 2:
				switch (gettype($a[0])) {
					case 'integer':
						switch (gettype($a[1])) {
							case 'string':
								return $this->addColumnAt_String($a[1],$a[0]);
								break;
							default:
								break;
						}
						break;
					case 'string':
						switch (gettype($a[1])) {
							case 'integer':
								return $this->addColumnAt_String($a[0],$a[1]);
								break;
							default:
								break;
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

	function addColumnAt_String($default_text, $n){ // $default_text was string, $n was int
		array_splice($this->data, $n, 0 , $default_text);
	}

	function at($i){ // returns string; $i was int
		if(($i < count($this->data)&&($i>=0))){
			return $this->data[$i];
		}else{
			return "";
		}
	}

	function setValueAt(){
		// function setValueAt(string value, int n){
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 2:
				switch (gettype($a[0])) {
					case 'integer':
						switch (gettype($a[1])) {
							case 'string':
							case 'integer':
								$this->data[$a[0]]=$a[1];
								break;
							default:
								break;
						}
						break;
					case 'string':
						switch (gettype($a[1])) {
							case 'integer':
								$this->data[$a[1]]=$a[0];
								break;
							default:
								break;
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

	function size(){ // int
		return count($this->data);
	}

	function toArray(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'array':
						$return_array=array();
						foreach ($a[0] as $key => $value) {
							$return_array[$value]=$this->data[$key];
						}
						return $return_array;
						break;
					default:
						return $this->data;
						break;
				}
				break;
			default:
				return $this->data;
				break;
		}
		return $this->data;
	}

	function export(){ // string
		$return_string = "";
		$is_first = true;
		foreach ($this->data as $i => $value) {
			if(!$is_first){
				$return_string .= ",";
			}else{
				$is_first=false;
			}
			if(strlen($value)>0){
				$return_string .= "\"". preg_replace("/\"/", "\"\"", $value)."\"";
			}
		}
		return $return_string;
	}
}

class Csv{
	protected $has_header = false;
	public $rows = array(); // $rows was vector<CSVRwo>
	private $delimiter = ",";
	private $is_quoted = true;
	private $escape_char = "\\";

	function __construct(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		ini_set('memory_limit','250M');

		switch ($i) {
			case 0:
				$this->init_2("",false);
				break;
			case 1:
				switch (gettype($a[0])) {
					case 'string':
						$this->init_2($a[0],false);
						break;
					case 'array':
						$path="";
						foreach ($a[0] as $key => $value) {
							switch ($key) {
								case 'delimiter':
									if(!empty($value)){
										$this->delimiter=$value{0};
									}
									break;
								case 'has_header':
									switch ($value) {
										case "1":
										case "t":
										case "true":
										case 1:
										case true:
											switch (gettype($value)) {
												case 'array':
													$this->has_header=false;
													break;
												default:
													if($value===0){
														$this->has_header=false;
													}else{
														$this->has_header=true;
													}
													break;
											}
											break;
										case "0":
										case "f":
										case "false":
										case false:
											$this->has_header=false;
											break;
										default:
											break;
									}
									break;
								case 'is_quoted':
									switch ($value) {
										case "1":
										case 't':
										case 'true':
										case true:
										case 1:
											$this->is_quoted=true;
											break;
										case "0":
										case 'f':
										case 'false':
										case 0:
										case false:
											$this->is_quoted=false;
											break;
										default:
											break;
									}
									break;
								case 'path':
									if(file_exists($value)){
										$path=$value;
									}
									break;
								default:
									# code...
									break;
							}
						}
						if(!empty($path)&&file_exists($path)){
							$tmp_csv = file_get_contents($path);
							$this->init_2($tmp_csv,$this->has_header);
						}
						break;
					default:
						break;
				}
				break;
			case 2:
				switch (gettype($a[0])) {
					case 'boolean':
						switch (gettype($a[1])) {
							case 'string':
								$this->init_2($a[1],$a[0]);
								break;
							default:
								break;
						}
						break;
					case 'string':
						switch (gettype($a[1])) {
							case 'boolean':
								$this->init_2($a[0],$a[1]);
								break;
							default:
								break;
						}
						break;
					default:
						break;
				}
				break;
			default:
				throw new CSVException("Error: Constructor method not found...", 1);
				break;
		}
	}

	protected function init_2($csv_string,$header){ // was init_2(string csv_string,bool header)

		$this->has_header=$header;

		$pos = 0; // $pos was int
		$is_in_string = false; // $is_in_string was bool
		$tmp_string = ""; // $tmp_string was string
		$tmp_col = array(); // $tmp_col was vector<string>

		while($pos < strlen($csv_string)){
			switch($csv_string{$pos}){
				case "\r":
					break;
				case ',':
					if($is_in_string){
						$tmp_string.=$csv_string{$pos};
					}else{
						$tmp_col[]=$tmp_string;
						$tmp_string="";
					}
				break;
				case '"':
					if($is_in_string){
						switch($csv_string{$pos+1}){
							case '"':
								$tmp_string.="\"";
								$pos++;
								break;
							default:
								$is_in_string=false;
								break;
						}
					}else{
						$is_in_string=true;
					}
				break;
				case "\n":
					if($is_in_string){
						$tmp_string.=$csv_string{$pos};
					}else{
						if((strlen($tmp_string)==0) && (count($tmp_col)==0)){
						}else{
							$tmp_col[]=$tmp_string;
							$tmp_string="";
							$this->rows[] = new CSVRow($tmp_col);
							$tmp_col = array();
						}
					}
					break;
				default:
					$tmp_string.=$csv_string{$pos};
					break;
			}
			$pos++;
		}
		if($this->has_header){
			$check_header=array();
			foreach ($this->rows[0]->toArray() as $key => $value) {
				if(!empty($value) && !(strcmp($value, "")==0)){
					if(array_key_exists($value, $check_header)){
						$as_keys = array_flip($this->rows[0]->toArray());
						$newindex=1;
						$new_name=$value."_".$newindex;
						while(array_key_exists($new_name, $as_keys)){
							$newindex++;
							$new_name=$value."_".$newindex;
						}
						$this->rows[0]->setValueAt($new_name,$key);
						// $this->has_header=false;
						$error_message='has_header = true && multiple columns with same name: Renamed '.$value.' to '.$new_name.'';
						trigger_error($error_message, E_USER_WARNING);
						// throw new CSVException($error_message);
					}else{
						$check_header[$value]=true;
					}
				}else{
					$as_keys = array_flip($this->rows[0]->toArray());
					$newindex=1;
					$colname="_blank";
					$new_name=$colname;
					while(array_key_exists($new_name, $as_keys)){
						$newindex++;
						$new_name=$colname."_".$newindex;
					}
					$this->rows[0]->setValueAt($new_name,$key);
					$error_message='has_header = true && column has empty name: Renamed to '.$new_name.'';
					trigger_error($error_message, E_USER_WARNING);
				}
			}
		}
	}

	function addColumn($colname){ // returns int, was addColumn(string colname)
		$col_num = 0; // was int
		$i = 0;
		if($this->has_header && (count($this->rows)>0)){
			if(strcmp($colname, "")==0){
				$colname="_blank";
			}
			$as_keys = array_flip($this->rows[0]->toArray());
			if(array_key_exists($colname, $as_keys)){
				$newindex=1;
				$new_name=$colname."_".$newindex;
				while(array_key_exists($new_name, $as_keys)){
					$newindex++;
					$new_name=$colname."_".$newindex;
				}
				$colname=$new_name;
			}
			$col_num = $this->rows[0]->addColumn($colname);
			$i++;
		}
		for(; $i < count($this->rows); $i++){
			if(($this->has_header && ($i!=0))||(!$this->has_header)){
				$col_num = $this->rows[$i]->addColumn("");
			}
		}
		return $col_num;
	}

	function addColumnAt(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		switch ($i) {
			case 1:
				switch (gettype($a[0])) {
					case 'integer':
						$this->addColumnAt_String_Int("",$a[0]);
						break;
					case 'string':
						$this->addColumn($a[0]);
						break;
					default:
						break;
				}
				break;
			case 2:
				switch (gettype($a[0])) {
					case 'integer':
						switch (gettype($a[1])) {
							case 'string':
								return $this->addColumnAt_String_Int($a[1],$a[0]);
								break;
							default:
								break;
						}
						break;
					case 'string':
						switch (gettype($a[1])) {
							case 'integer':
								return $this->addColumnAt_String_Int($a[0],$a[1]);
								break;
							default:
								break;
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

	protected function addColumnAt_String_Int($colname, $n){ // was addColumnAt(string colname, int n)
		$i = 0;
		if($this->has_header && (count($this->rows)>0)){
			$as_keys = array_flip($this->rows[0]->toArray());
			if(strcmp($colname,"")==0){
				$colname="_blank";
			}
			if(array_key_exists($colname, $as_keys)){
				$newindex=1;
				$new_name=$colname."_".$newindex;
				while(array_key_exists($new_name, $as_keys)){
					$newindex++;
					$new_name=$colname."_".$newindex;
				}
				$error_message='has_header = true && column has empty name: Renamed to '.$new_name.'';
				trigger_error($error_message, E_USER_WARNING);
				$colname=$new_name;

			}
			$this->rows[0]->addColumnAt($colname,$n);
			$i++;
		}
		for(; $i < count($this->rows); $i++){
			if(($this->has_header && ($i!=0))||(!$this->has_header)){
				$this->rows[$i]->addColumnAt("",$n);
			}
		}
	}

	static function array2csv(){
		$a = func_get_args(); 
		$i = func_num_args();
		switch ($i) {
			case 1:
				if(strcmp(gettype($a[0]), 'array')==0){
					return Csv::a2csv($a[0],false);
				}else{
					trigger_error("Expecting (array) data", E_USER_WARNING);
				}
				break;
			case 2:
				if(strcmp(gettype($a[0]), 'array')==0 && strcmp(gettype($a[1]), 'boolean')==0){
					return Csv::a2csv($a[0],$a[1]);
				}elseif (strcmp(gettype($a[1]), 'array')==0 && strcmp(gettype($a[0]), 'boolean')==0) {
					return Csv::a2csv($a[1],$a[0]);
				}else{
					trigger_error("Expecting (array) data and (bool) has_header", E_USER_WARNING);
				}
				break;
			
			default:
				trigger_error("[Un]Expected Parameters", E_USER_WARNING);
				break;
		}
	}

	static function a2csv($array, $has_header){
		if(strcmp(gettype($array),'array')==0){}else{
			trigger_error("First parameter needs to be an Array", E_USER_WARNING);
		}
		if(strcmp(gettype($has_header),'boolean')==0){}else{
			trigger_error("Second parameter needs to be a Boolean", E_USER_WARNING);
		}
		$return_string="";
		if($has_header){
			$is_first_row=true;
			// $headers=$array[0];
			$headers=array_keys($array[0]);
			//////////// Write out header ////////////
			$is_first_col=true;
			foreach ($headers as $key => $value) {
				if($is_first_col){
					$is_first_col=false;
				}else{
					$return_string.=",";
				}
				if(!empty($value) || $value===0){
					$return_string.="\"".str_replace("\"", "\"\"", $value)."\"";
				}
			}
			$return_string.="\n";
			///////////////////////////////////////////////////////
			foreach ($array as $row_index => $row) {
				if($is_first_row){
					$is_first_row=false;
					$is_first_col=true;
					foreach ($row as $key => $value) {
						if($is_first_col){
							$is_first_col=false;
						}else{
							$return_string.=",";
						}
						if(!empty($value) || $value===0){
							$return_string.="\"".str_replace("\"", "\"\"", $value)."\"";
						}
					}
					$return_string.="\n";
				}else{
					$is_first_col=true;
					foreach ($headers as $id => $key) {
						if($is_first_col){
							$is_first_col=false;
						}else{
							$return_string.=",";
						}
						if(isset($row[$key])){
							$value=$row[$key];
							if(!empty($value) || $value===0){
								$return_string.="\"".str_replace("\"", "\"\"", $value)."\"";
							}
						}
					}
					$return_string.="\n";
				}
			}
		}else{
			$max_cols=0;
			foreach ($array as $key => $row) {
				if(count($row)>$max_cols){
					$max_cols=count($row);
				}
			}
			foreach ($array as $row_index => $row) {
				$is_first=true;
				$col_count=0;
				foreach ($row as $key => $value) {
					$col_count++;
					if($is_first){
						$is_first=false;
					}else{
						$return_string.=",";
					}
					if(!empty($value) || $value===0){
						$return_string.="\"".str_replace("\"", "\"\"", $value)."\"";
					}
				}
				if($col_count<$max_cols){
					for($i = $col_count; $i < $max_cols; $i++){
						$return_string.=",";
					}
				}
				$return_string.="\n";
			}
		}
		return $return_string;
	}

	function at(){
		$a = func_get_args(); 
		$i = func_num_args();
		if($this->has_header){
			switch ($i) {
				case 1:
					if((0 <= $a[0]) && ($a[0] < (count($this->rows)-1))){
						return $this->rows[$a[0]+1];
					}else{
						return false;
					}
					break;
				
				case 2:
					if((0 <= $a[0]) && ($a[0] < (count($this->rows)-1))){
						return $this->rows[$a[0]+1]->at[$a[1]];
					}else{
						return false;
					}
					break;
				default:
					return false;
					break;
			}
		}
		switch ($i) {
			case 1:
				if($this->has_header){
				}else{
					if((0 <= $a[0]) && ($a[0] < count($this->rows))){
						return $this->rows[$a[0]];
					}else{
						return false;
					}
				}
				break;
			case 2:
				if($this->has_header){
					if((0 <= $a[0]) && ($a[0] < (count($this->rows)-1))){
						return $this->rows[$a[0]+1]->at[$a[1]];
					}else{
						return false;
					}
				}else{
					if((0 <= $a[0]) && ($a[0] < count($this->rows))){
						return $this->rows[$a[0]]->at[$a[1]];
					}else{
						return false;
					}
				}
				break;
			default:
				return false;
				break;
		}
	}

	function getColumnNameAt($n){ // returns string, was getColumnNameAt(int n)
		if($this->has_header && (count($this->rows)>0)){
			return $this->rows[0]->at($n);
		}else{
			return "";
		}
	}

	function getColumnNameIndex($n){ // returns int, was getColumnNameIndex(string n)
		if($this->has_header && (count($this->rows)>0)){
			for($i = 0; $i < $this->rows[0]->size(); $i++){
				if(strcmp($this->rows[0]->at($i),$n)==0){
					return $i;
				}
			}
		}
		return -1;
	}

	function getHeaders(){ // This will only return headers if has_headers is set and will only return headers that aren't empty strings.
		if($this->has_header){
			$return_array=array();
			foreach ($this->rows[0]->toArray() as $key => $value) {
				if(!empty($value) && !(strcmp($value, "")==0)){
					$return_array[]=$value;
				}
			}
			if(count($return_array)>0){
				return $return_array;
			}else{
				$error_message='No headers/columns exist.';
				trigger_error($error_message, E_USER_WARNING);
			}
		}
		return null;
	}

	static function keyedarray2csv($array){
		$out = array();
		$keys = array();
		foreach ($array as $row_num => $row) {
			switch (gettype($row)) {
				case 'array':
					foreach ($row as $key => $value) {
						$keys[$key]=1;
					}
					break;
				default:
					// $out[] = "".gettype($row)."";
					break;
			}
		}
		$keys = array_keys($keys);
		sort($keys);
		$out[] = $keys;
		foreach ($array as $row_num => $row) {
			$newrow = array();
			foreach ($keys as $key) {
				if(isset($row[$key])){
					$newrow[]=trim($row[$key]);
				}else{
					$newrow[]="";
				}
			}
			$out[]=$newrow;
		}
		return CSV::array2csv($out,false);
		// return $out;
	}

	function size(){
		if($this->has_header){
			return count($this->rows)-1;
		}else{
			return count($this->rows);
		}
	}

	function toArray(){
		$a = func_get_args(); 
		$i = func_num_args(); 
		$return_array = array();
		if($this->has_header){
			$first=true;
			foreach ($this->rows as $key => $value) {
				if($first){
					$first=false;
				}else{
					// $tmp_array=$value->toArray($this->rows[0]->toArray());
					$tmp_array=$value->toArray($this->rows[0]->toArray());
					$tmp_return_array=array();
					if($i==1 && (strcmp(gettype($a[0]),'array')==0)){
						foreach ($a[0] as $key => $value) {
							$tmp_return_array[$value]=$tmp_array[$value];
						}
					}else{
						foreach ($this->getHeaders() as $key => $value) {
							$tmp_return_array[$value]=$tmp_array[$value];
						}
					}
					$return_array[]=$tmp_return_array;
				}
			}
		}else{
			foreach ($this->rows as $key => $value) {
				$return_array[]=$value->toArray();
			}
		}
		return $return_array;
	}

	function export(){ // string
		$return_string = "";
		for($i = 0; $i < count($this->rows); $i++){
			$return_string .= $this->rows[$i]->Export() . "\n";
		}
		return $return_string;
	}

	function asHtmlTable(){
		$return_string="";
		$return_string.="<table>";
		$is_even = false;
		if($this->has_header){
			$a = func_get_args(); 
			$i = func_num_args();
			$csv_headers=array();
			if($i==1 && gettype($a[0])==='array'){
				$csv_headers=$a[0];
			}else{
				$csv_headers=$this->getHeaders();
			}
			$return_string.="<tr class=\"header\">";
			foreach ($csv_headers as $value) {
				$return_string.="<th>".$value."</th>";
			}
			$return_string.="</tr>";
			foreach ($this->toArray() as $value) {
				if($is_even){
					$return_string.="<tr class=\"even\">";
				}else{
					$return_string.="<tr class=\"odd\">";
				}
				foreach ($csv_headers as $key) {
					if(array_key_exists($key, $value)){
						$return_string.="<td>".$value[$key]."</td>";
					}else{
						$return_string.="<td></td>";
					}
				}
				$return_string.="<tr>";
				$is_even = !$is_even;
			}
		}else{
			foreach ($this->toArray() as $value) {
				if($is_even){
					$return_string.="<tr class=\"even\">";
				}else{
					$return_string.="<tr class=\"odd\">";
				}
				foreach ($value as $key) {
					$return_string.="<td>".$key."</td>";
				}
				$return_string.="<tr>";
				$is_even = !$is_even;
			}
		}
		$return_string.="</table>";
		return $return_string;
	}
}

?>