<html>
<head>
	<title></title>
	<meta name="author" content="annon">
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<!-- <meta name="google-signin-client_id" content="509284590532-elvq72nt1ph18m1cjc3rkhifhnul46b7.apps.googleusercontent.com"> -->
	<link rel="shortcut icon" href="/vendor/fa/svgs/brands/java.svg" type="image/svg" />
	<link rel="stylesheet" type="text/css" href="vendor/fa/css/all.css" />
	<!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous"> -->
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css" />
	<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php" />
	<!-- <link rel="stylesheet" type="text/css" href="assets/css/style_screen.php" media="(min-width: 900px)" /> -->
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=narrow" media="(max-width: 900px)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=print" media="print" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile" media="only screen and (max-device-width: 375px)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile" media="media only screen and (min-device-width: 320px) and (max-device-width: 480px) and (-webkit-min-device-pixel-ratio: 2)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=p" media="media only screen and (min-device-width: 320px) and (max-device-width: 480px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=l" media="media only screen and (min-device-width: 320px) and (max-device-width: 480px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: landscape)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile" media="media only screen and (min-device-width: 320px) and (max-device-width: 568px) and (-webkit-min-device-pixel-ratio: 2)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=p" media="media only screen and (min-device-width: 320px) and (max-device-width: 568px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=l" media="media only screen and (min-device-width: 320px) and (max-device-width: 568px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: landscape)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile" media="media only screen and (min-device-width: 375px) and (max-device-width: 667px) and (-webkit-min-device-pixel-ratio: 2)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=p" media="media only screen and (min-device-width: 375px) and (max-device-width: 667px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=l" media="media only screen and (min-device-width: 375px) and (max-device-width: 667px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: landscape)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile" media="media only screen and (min-device-width: 414px) and (max-device-width: 736px) and (-webkit-min-device-pixel-ratio: 3)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=p" media="media only screen and (min-device-width: 414px) and (max-device-width: 736px) and (-webkit-min-device-pixel-ratio: 3) and (orientation: portrait)" />
	<link rel="stylesheet" type="text/css" href="assets/css/style_screen.php?media=mobile&o=l" media="media only screen and (min-device-width: 414px) and (max-device-width: 736px) and (-webkit-min-device-pixel-ratio: 3) and (orientation: landscape)" />
	<script src="vendor/lodash.js"></script>
	<script src="vendor/marked.js"></script>
	<script src="vendor/vuejs/vue_dev.js"></script>
	<link rel="stylesheet" type="text/css" href="vendor/dropzone/dropzone.css" />
	<script src="vendor/dropzone/dropzone.js"></script>
	<!-- <script src="https://apis.google.com/js/platform.js?onload=google_login_renderbutton" async defer></script> -->
</head>
<body>
	<div class="bg_body"></div>
	<div id='main' style="bottom: 0px;">
		<?PHP include('_view_header_title_bar.php'); ?>
		<div id='header_description' class="_page_header" v-if="!isBlank(header.description)">
			<p>{{ globals.site_description }}</p>
		</div>
		<?PHP include('__navigation.php'); ?>