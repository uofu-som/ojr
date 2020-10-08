<h1>Dashboard</h1>
<div v-if="dashboard.users.data.length > 0">
	<h3>Users</h3>
	<ul>
		<li v-for="user in dashboard.users.data">{{user.first_name}} {{user.last_name}} &lt;{{user.email}}&gt; <span style="padding: 2px; padding: 5px;" class="btn btn-danger" v-for="role in user.roles">{{ roles.data[role['$oid']] }}</span></li>
	</ul>
</div>
<div v-if="dashboard.tags.data.length > 0">
	<h3>Tags</h3>
	<ul>
		<li v-for="tag in dashboard.tags.data">{{ tag.tag }} <span v-html>&#123;</span>{{ tag.count }}<span v-html>&#125;</span></li>
	</ul>
</div>
<div v-if="dashboard.hosts.data.length > 0">
	<h3 @click="page_nav_set('reports_hosts');">Unique Hosts: {{ dashboard.hosts.data.length }}</h3>
</div>
<div style="position: fixed; bottom:0px; right: 0px; left: 0px; background: grey; height: 5px;"></div>