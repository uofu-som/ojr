<?php
header("Content-type: text/css");
?>
h1{
	margin-bottom:0px;
}

h3{
	font-size: 1.25rem;
}

p{      
	margin-top:0px;
}

._page_header{
	border: 3px dashed black;
	opacity:0.9;
	padding:15px;
	background-color:grey;
}

._page_header h1{
	text-align:center;
}
.cron{
	float:right;
	clear:both;
}
.entry{
	width: 100%;
	margin: 3px;
	padding: 3px;
	float:left;
	opacity:0.9;
}

.bg_body {
	background-repeat: no-repeat;
	background-attachment: fixed;
	background-position: center;
	opacity: 0.2;
	position: fixed;
	top:5px;
	right:5px;
	bottom:5px;
	left:5px;
	z-index:-1;
	background-image: url('data:image/svg+xml;base64,<?php echo(base64_encode(file_get_contents("emlp_busted.svg")));?>');
}

.entry_edit {
	height: 50%;
}

.entry_edit_editor {
	border: 1px solid black;
}

.entry_edit_preview {
	border: 1px solid black;
}