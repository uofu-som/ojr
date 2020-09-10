
		<div id="header_title_bar" class="container bg-dark text-white w-100 mw-100" style="padding:10px; padding-right: 30px;">
			<div class="row">
				<div class="col-sm-auto"><span class="fab fa-java" style="font-size:50px">&nbsp;</span></div>
				<!-- <div class="col-sm-auto"><span class="fas fa-feather-alt" style="font-size:50px">&nbsp;</span></div> -->
				<div class="col-sm"><h1>{{ globals.site_title }}</h1></div>
				<div class="col-sm-auto my-auto">&nbsp;</div>
				<div v-if="show_login()" class="col-md align-self-right">
					<div v-if="show_login_creds()" class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text dropdown-toggle"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Login:</span>
							<?php include('_view_header_title_bar_login_method_dropdown.php'); ?>
						</div>
						<input v-if="show_login_creds()" id="auth_un" tabindex="1" type="text" class="form-control" placeholder="User Name" v-on:keyup="login_keypressed" v-model="login.un">
						<input v-if="show_login_creds()" id="auth_pass" tabindex="2" type="password" class="form-control" placeholder="Password" v-on:keyup="login_keypressed" v-model="login.pass">
						<div v-if="show_login_creds()" class="input-group-append">
							<button class="btn btn-secondary" tabindex="3" type="button" @click="auth_login"><span class="fas fa-sign-in-alt">&nbsp;</span></button>
						</div>		
					</div>
					<div v-else class="input-group">
						<div class="input-group">
							<span class="input-group-text dropdown-toggle" style="position: absolute; right: 0px" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Login:</span>
							<?php include('_view_header_title_bar_login_method_dropdown.php'); ?>
						</div>
						<div v-if="login.method=='google'" id="google_auth_button" ></div>
					</div>
				</div>
				<div v-else class="col-md align-self-right">
					<div v-if="login.method=='local'" class="input-group">
						<button class="btn btn-primary form-control" type="button"><span style="text-align:center;" >{{ auth_user.firstName }} {{ auth_user.lastName }} ({{ auth_user.email }})</span></button>
						<div class="input-group-append" @click="auth_logout">
							<button class="btn btn-secondary" type="button" alt="logout" title="Logout"><span class="fas fa-sign-out-alt">&nbsp;</span></button>
						</div>		
					</div>
					<!-- <div v-else-if="login.method=='google'">
						<button class="btn btn-primary form-control" type="button"><span style="text-align:center;" >{{ auth_user.firstName }} {{ auth_user.lastName }} ({{ auth_user.email }})</span></button>
						<div class="input-group-append" @click="auth_logout">
							<button class="btn btn-secondary" type="button" alt="logout" title="Logout"><span class="fas fa-sign-out-alt">&nbsp;</span></button>
						</div>
					</div> -->
				</div>
			</div>
		</div>
