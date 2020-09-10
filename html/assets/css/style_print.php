<?php
header("Content-type: text/css");
?>
h1{
	margin-bottom:0px;
}

h3{
	font-size: 3rem;
}

p{      
	margin-top:0px;
	font-size: 2rem;
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
	display:none;
}

.pagebreak{
	page-break-after: always;
}

.entry{
	width: 100%;
	margin: 3px;
	padding: 3px;
	float:left;
	opacity:0.7;
	page-break-inside: avoid;
}

.entry h3{
	font-size: 2.75rem;
	padding-left: 15px;
	padding-right: 15px;
}

.entry p{
	font-size: 2.75rem;
	page-break-inside: avoid;
	padding-left: 30px;
	padding-right: 15px;
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
	<!-- background-image: url('data:image/svg+xml;base64,<\?php echo(base64_encode(file_get_contents("emlp_busted.svg")));?>'); -->
}

video {
	display:none;
}