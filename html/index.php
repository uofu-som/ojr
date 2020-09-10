<?php require_once("___site_settings.php"); ?>
<?php require_once("init.php"); ?>
<?php include("__header.php"); ?>
		<div id="content" class="content" v-if="view.main=='home'"><?php include("_view_home.php"); ?></div>
		<div id="content" class="content" v-else-if="view.main=='dashboard' && auth_user.roles.includes('admin')"><?php include("_view_dashboard.php"); ?></div>
		<div id="content" class="content" v-else-if="view.main=='scripts' && auth_user.roles.includes('admin')"><?php include("_view_scripts.php"); ?></div>
		<div id="content" class="content" v-else-if="view.main=='reports_main' && auth_user.roles.includes('admin')"><?php include("_view_reports_main.php"); ?></div>
		<div id="content" class="content" v-else-if="view.main=='user_management' && auth_user.roles.includes('admin')"><?php include("_view_sysadmin_users.php"); ?></div>
		<div id="content" class="content" v-else-if="view.main=='app_token_management' && auth_user.roles.includes('admin')"><?php include("_view_sysadmin_apptoken.php"); ?></div>
		<div id="content" class="content" v-else ><?php include("_view_unknown.php"); ?></div>
<?php include("__footer.php"); ?>