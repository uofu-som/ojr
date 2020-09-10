<h1>Reports - Main</h1>
<div v-if="reports.main.data.length > 0">
	<table>
		<tr>
			<th @click="sort_reports_main('path')">Unique Path</th>
			<th @click="sort_reports_main('count')">Count</th>
		</tr>
		<tr v-for="file in reports.main.data">
			<td>{{ file.path }}</td>
			<td>{{ file.count }}</td>
		</tr>
	</table>
</div>
<div v-else>
	<h3>No Data to Display</h3>
</div>
<div style="position: fixed; bottom:0px; right: 0px; left: 0px; background: lightgrey; height: 5px;"></div>