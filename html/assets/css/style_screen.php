<?php
header("Content-type: text/css");
$styles=array();

$styles['body']=array();
	$styles['body']['font-size']=array();
	$styles['body']['font-size']['_default']="";
	$styles['body']['font-size']['mobile']="2em";

$styles['h1']=array();
	$styles['h1']['margin-bottom']=array();
	$styles['h1']['margin-bottom']['_default']="0px";

$styles['h3']=array();
	$styles['h3']['font-size']=array();
	$styles['h3']['font-size']['_default']="1.25rem";

$styles['h4']=array();
	$styles['h4']['font-size']=array();
	$styles['h4']['font-size']['_default']="1.1rem";

$styles['p']=array();
	$styles['p']['margin-top']=array();
	$styles['p']['margin-top']['_default']="0px";

$styles['._page_header']=array();
	$styles['._page_header']['border']=array();
	$styles['._page_header']['border']['_default']="3px dashed black";

	$styles['._page_header']['opacity']=array();
	$styles['._page_header']['opacity']['_default']="0.9";

	$styles['._page_header']['padding']=array();
	$styles['._page_header']['padding']['_default']="15px";

	$styles['._page_header']['background-color']=array();
	$styles['._page_header']['background-color']['_default']="grey";

$styles['._page_header h1']=array();
	$styles['._page_header h1']['text-align']=array();
	$styles['._page_header h1']['text-align']['_default']="center";

$styles['.cron']=array();
	$styles['.cron']['display']=array();
	$styles['.cron']['display']['mobile']="none";

	$styles['.cron']['float']=array();
	$styles['.cron']['float']['_default']="right";
	$styles['.cron']['float']['mobile']="";

	$styles['.cron']['clear']=array();
	$styles['.cron']['clear']['_default']="both";
	$styles['.cron']['clear']['mobile']="";

$styles['.entry']=array();
	$styles['.entry']['min-width']=array();
	$styles['.entry']['min-width']['_default']="30%";
	$styles['.entry']['min-width']['mobile']="";

	$styles['.entry']['width']=array();
	$styles['.entry']['width']['_default']="30%";
	$styles['.entry']['width']['mobile']="100%";
	$styles['.entry']['width']['narrow']="100%";

	$styles['.entry']['margin']=array();
	$styles['.entry']['margin']['_default']="3px";

	$styles['.entry']['padding']=array();
	$styles['.entry']['padding']['_default']="3px";

	$styles['.entry']['float']=array();
	$styles['.entry']['float']['_default']="left";

	$styles['.entry']['opacity']=array();
	$styles['.entry']['opacity']['_default']="0.9";
	$styles['.entry']['opacity']['mobile']="0.7";
	$styles['.entry']['opacity']['narrow']="0.9";

$styles['.bg_body']=array();
	$styles['.bg_body']['display']=array();
	$styles['.bg_body']['display']['mobile']="none";
	$styles['.bg_body']['display']['print']="none";

	$styles['.bg_body']['background-repeat']=array();
	$styles['.bg_body']['background-repeat']['_default']="no-repeat";

	$styles['.bg_body']['background-attachment']=array();
	// $styles['.bg_body']['background-attachment']['_default']="fixed";

	$styles['.bg_body']['background-position']=array();
	$styles['.bg_body']['background-position']['_default']="left bottom";
	$styles['.bg_body']['background-size']=array();
	$styles['.bg_body']['background-size']['_default']="50%";

	$styles['.bg_body']['background-image']=array();
	$bg_file_name = "../../branding/logo.svg";
	if(file_exists($bg_file_name)){
		$styles['.bg_body']['background-image']['_default']="url('data:image/svg+xml;base64,".base64_encode(file_get_contents($bg_file_name))."')";
	}
	$styles['.bg_body']['background-image']['mobile']="";
	$styles['.bg_body']['background-image']['print']="";

	$styles['.bg_body']['opacity']=array();
	$styles['.bg_body']['opacity']['_default']="0.1";

	$styles['.bg_body']['position']=array();
	$styles['.bg_body']['position']['_default']="fixed";

	$styles['.bg_body']['top']=array();
	$styles['.bg_body']['top']['_default']="5px";

	$styles['.bg_body']['right']=array();
	$styles['.bg_body']['right']['_default']="5px";

	$styles['.bg_body']['bottom']=array();
	$styles['.bg_body']['bottom']['_default']="15px";

	$styles['.bg_body']['left']=array();
	$styles['.bg_body']['left']['_default']="15px";

	$styles['.bg_body']['z-index']=array();
	$styles['.bg_body']['z-index']['_default']="-1";

$styles['.content']['display']['_default']='flow-root';
$styles['.content']['padding-bottom']['_default']='50px';

$styles['.entry_edit']=array();
	$styles['.entry_edit']['height']=array();
	$styles['.entry_edit']['height']['_default']="50%";

$styles['.entry_edit_editor']=array();
	$styles['.entry_edit_editor']['border']=array();
	$styles['.entry_edit_editor']['border']['_default']="1px solid black";

$styles['.entry_edit_preview']=array();
	$styles['.entry_edit_preview']['border']=array();
	$styles['.entry_edit_preview']['border']['_default']="1px solid black";

$styles['.sidebar_wrapper']=array();
	// $styles['.sidebar_wrapper']['float']=array();
	$styles['.sidebar_wrapper']['position']['_default']='-webkit-sticky';
	$styles['.sidebar_wrapper']['position']['_default']='sticky';
	$styles['.sidebar_wrapper']['top']['_default']='0px';
	$styles['.sidebar_wrapper']['padding']['_default']='5px'; 
	$styles['.sidebar_wrapper']['float']['_default']='left';
	$styles['.sidebar_wrapper']['width']['_default']='200px';

	$styles['.sidebar_wrapper nav']['border-style']['_default']='solid';
	$styles['.sidebar_wrapper nav']['border-radius']['_default']='5px';
	$styles['.sidebar_wrapper nav']['margin']['_default']='5px'; 
	$styles['.sidebar_wrapper nav']['padding']['_default']='5px'; 
	$styles['.sidebar_wrapper nav']['background-color']['_default']='lightgrey';

	$styles['.sidebar_wrapper li']['text-align']['_default']='left';
	$styles['.sidebar_wrapper li span']['text-align']['_default']='left';
	$styles['.sidebar_wrapper']['float']['print']='';
	$styles['.sidebar_wrapper']['display']['print']="none";
foreach ($styles as $class => $attributes) {
	$include=false;
	$echo_class="";
	if(count($attributes)>0){
		$echo_class.="".$class."{".PHP_EOL;
		foreach ($attributes as $key => $properties) {
			if(array_key_exists($_GET['media'], $properties)){
				if(!empty($properties[$_GET['media']])){
					$include=true;
					$echo_class.="\t".$key.": ".$properties[$_GET['media']].";".PHP_EOL;
				}
			}elseif(array_key_exists('_default', $properties)){
				if(!empty($properties['_default'])){
					$include=true;
					$echo_class.="\t".$key.": ".$properties['_default'].";".PHP_EOL;
				}
			}
		}
		$echo_class.="}".PHP_EOL.PHP_EOL;
	}else{
		echo(count($attributes));
	}
	if($include){
		echo($echo_class);
	}
}
?>