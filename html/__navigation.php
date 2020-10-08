<div class="sidebar_wrapper">
	<nav id="sidebar">
		<div class="sidebar-header">
			<h3>Site Navigation</h3>
		</div>

		<ul class="list-unstyled components">
			<li class="btn btn-link col-sm" @click="page_nav_set('home')">Home</li>
			<li class="btn btn-link col-sm" @click="page_nav_set('dashboard')" v-if="!isBlank(auth_user.token)">Dashboard</li>
			<li v-if="!isBlank(auth_user.token)">
				<span class="btn btn-link col-sm" href="#reportsSubmenu" data-toggle="collapse" aria-expanded="false">Reports</span>
				<ul class="collapse list-unstyled" id="reportsSubmenu" style="margin-left: 15px">
					<li class="btn btn-link col-sm" @click="page_nav_set('reports_main');" v-if="!isBlank(auth_user.token)">Main</li>
					<li class="btn btn-link col-sm" @click="page_nav_set('reports_hosts');" v-if="!isBlank(auth_user.token)">Hosts</li>
					<!-- <li>Page</li>
					<li>Page</li> -->
				</ul>
			</li>
			<li class="btn btn-link col-sm" @click="page_nav_set('scripts')" v-if="!isBlank(auth_user.token)">Get Scripts</li>
			<li v-if="!isBlank(auth_user.token)">
				<span class="btn btn-link col-sm" href="#sysadminSubmenu" data-toggle="collapse" aria-expanded="false">System Administration</span>
				<ul class="collapse list-unstyled" id="sysadminSubmenu" style="margin-left: 15px">
					<li class="btn btn-link col-sm" @click="page_nav_set('user_management')" v-if="!isBlank(auth_user.token)">User Management</li>
					<li class="btn btn-link col-sm" @click="page_nav_set('app_token_management')" v-if="!isBlank(auth_user.token)">App Token Management</li>
				</ul>
			</li>
		</ul>
	</nav>
</div>