<h1>Scripts</h1>
<p>The purpose of this script download section is to inject your owner-id into the scripts that are downloaded. This automatically links the information reported from these scripts to your user account for easier analysis.</p>
<div>
	<h2>App Token Linked Scripts</h2>
	<div v-if="app_tokens.length == 0">
		<p>Please create App Tokens to associate with each script <span class="btn-link" @click="page_nav_set('app_token_management')">here</span>.</p>
	</div>
	<div v-if="false">
		<table class="table">
			<thead>
				<tr>
					<th scope="col">Token</th>
					<th scope="col">PowerShell (Windows)</th>
					<th scope="col">Bash (Mac, Linux, etc)</th>
					<th scope="col">Tags</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="app_token in app_tokens">
					<th scope="row">
						<span class="btn btn-dark">{{ app_token.token }}</span>
					</th>
					<td>
						<span class="btn btn-success" @click="script_get(app_token.token,'powershell')"><i class="fas fa-download">&nbsp;</i>Powershell</span>
					</td>
					<td>
						<span class="btn btn-success" @click="script_get(app_token.token,'bash')"><i class="fas fa-download">&nbsp;</i>Bash</span>
					</td>
					<td>
						<span class="btn btn-info" style="padding: 2px; margin: 2px;" v-for="tag in app_token.tags">{{ tag }}</span>
					</td>
				</tr>
			</tbody>
			<tfoot>
					<th scope="col"></th>
					<th scope="col"></th>
					<th scope="col"></th>
			</tfoot>
		</table>
	</div>
	<div v-else v-for="app_token in app_tokens">
		<h3>Token: {{ app_token.token }}</h3>
		<ul>
			<!-- <li class="btn btn-light">Downloads:</li> -->
			<li class="btn btn-success" style="margin: 2px;" @click="script_get(app_token.token,'powershell')"><i class="fas fa-download">&nbsp;</i>Powershell (Windows)</li>
			<li class="btn btn-success" style="margin: 2px;" @click="script_get(app_token.token,'bash')"><i class="fas fa-download">&nbsp;</i>Bash (Mac, Linux, etc)</li>
			<!-- <li class="btn btn-light">Tags:</li> -->
			<li class="btn btn-info" style="margin: 2px;" v-for="tag in app_token.tags"><i class="fas fa-tags">&nbsp;</i>{{ tag }}</li>
		</ul>
	</div>
	<h2>Generic Scripts</h2>
	<p>Please note that without passing in a valid app token these scripts will not be accepted by this server. These are being provided if you'd like to use them with a different API endpoint.</p>
	<ul>
		<li class="btn btn-warning" @click="script_get('','powershell')"><i class="fas fa-download">&nbsp;</i>Powershell (Windows)</li>
		<li class="btn btn-warning" @click="script_get('','bash')"><i class="fas fa-download">&nbsp;</i>Bash (Mac, Linux, etc)</li>
	</ul>

</div>
<div style="position: fixed; bottom:0px; right: 0px; left: 0px; background: grey; height: 5px;"></div>