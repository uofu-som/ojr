<h1>Application Token Management</h1>
<p>Each script will need an application token in order for it to add to this database. If the script gets compromised you can delete the token and it will no longer be able to submit to this API. Also, by default, each app token expires after 2 years at which point it will no longer be able to add via the API and will be automatically removed.</p>
<table class="table">
	<thead>
		<tr>
			<th scope="col">Token</th>
			<th scope="col">Tags</th>
			<th scope="col">Delete</th>
		</tr>
	</thead>
	<tbody>
		<tr v-for="app_token in app_tokens">
			<th scope="row">
				<span class="btn btn-dark" v-if="!isBlank(app_token.token)">{{ app_token.token }}</span>
			</th>
			<td>
				<div v-if="!isBlank(app_token.token)">
					<span v-for="tag in app_token.tags"><span class="btn btn-info" class="btn btn-info">{{ tag }}</span>&nbsp;</span>
				</div>
				<div v-else>
					<div v-for="tag in app_token.tags">
						<input type="text" class="btn btn-light" v-model="app_token.tags[app_token.tags.indexOf(tag)]"></input>&nbsp;<i class="btn btn-danger" @click="app_token.tags.splice(app_token.tags.indexOf(tag),1)"><i class="fas fa-ban">&nbsp;</i>Remove Tag</i>
					</div>
					<i class="btn btn-success" @click="app_token.tags.push('')"><i class="fas fa-tags">&nbsp;</i>Add Tag</i>
					<!-- <input type="text" v-model="tag"></input> -->
				</div>
			</td>
			<td>
				<span v-if="isBlank(app_token.token)" class="btn btn-primary far fa-save" @click="app_token_add(app_token)">&nbsp;Save</span>
				<span v-else class="btn btn-danger fas fa-trash-alt" @click="app_token_delete(app_token.token)"></span>
			</td>
		</tr>
	</tbody>
	<tfoot>
			<th scope="col"></th>
			<th scope="col"></th>
			<th scope="col" @click="app_token_gui_add_row"><span class="btn btn-success"><i class="fas fa-plus"></i>&nbsp;Add&nbsp;App&nbsp;Token</span></th>
	</tfoot>
</table>
<div style="position: fixed; bottom:0px; right: 0px; left: 0px; background: grey; height: 5px;"></div>