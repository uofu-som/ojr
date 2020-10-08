<h1>Reports - Hosts</h1>
<div v-if="dashboard.hosts.data.length > 0" class="container">
	<div class="row">
		<div class="col-sm-5 col-md-4 col-lg-3">
			<table>
				<tr>
					<th @click="sort_reports_main('path')">Host Name</th>
					<th @click="sort_reports_main('count')"># of Records</th>
				</tr>
				<tr v-for="host in dashboard.hosts.data" @click="report_host_get(host.hostname)">
					<td>{{ host.hostname }}</td>
					<td>{{ host.count }}</td>
				</tr>
			</table>
		</div>
		<div class="col-sm-7 col-md-8 col-lg-9">
			<div v-if="!isBlank(reports.host.hostname)">
				<h3>Host Records: {{ reports.host.hostname }}</h3>
				<div v-for="record in reports.host.data" class="container">
					<div class="row"v-if="!isBlank(record.script)">
						<div class="col-3">{{ record.script.type }} : {{ record.script.version }}</div>
						<div class="col-9" v-if="record.script.type.normalize() === 'PowerShell'">{{ record.Time.Start.DateTime }}</div>
						<div class="col-9" v-else-if="record.script.type.normalize() === 'bash'">{{ new Date( ( record.time_start_utc_sec * 1000 ) ) }}</div>
						<div class="col-9" v-else>What's up, Doc?!</div>
					</div>
					<div class="row" v-if="!isBlank(record['search-registry'])">
						<div class="col-sm-12 col-md-6 col-lg-4" style="text-align: center; margin-top: auto; margin-bottom: auto; background-color: lightgrey;">Registry Records</div>
						<div class="col-12" v-for="reg in record['search-registry']" style="border-bottom: 1px solid; border-right: 1px solid; background-color: grey; padding: -5px">
							{{ reg.ProgramName }}<br />
							{{ reg.RegPath }}<br />
							<span v-if="!isBlank(reg.installlocation)">Installation Location: &quot;{{ reg.installlocation }}&quot;<br /></span>
							<span v-if="!isBlank(reg.Uninstallstring)">Uninstallation Command: &quot;{{ reg.Uninstallstring }}&quot;</span>
						</div>
					</div>
					<div class="row" v-if="!isBlank(record['search-file'])">
						<div class="col-sm-12 col-md-6 col-lg-4" style="text-align: center; margin-top: auto; margin-bottom: auto; background-color: lightgrey;">File Search Results</div>
						<div class="col-12" v-for="file in record['search-file']" style="border-bottom: 1px solid; border-right: 1px solid; background-color: grey; padding: -5px">
							<div>{{ file.file }}</div>
							<div v-if="!isBlank(file.BuiltinVersionInfo)">
								<h4>PowerShell Info:</h4>
								<ul>
									<li>{{ file.BuiltinVersionInfo.CompanyName }}</li>
									<li>{{ file.BuiltinVersionInfo.ProductName }}</li>
									<li>{{ file.BuiltinVersionInfo.ProductVersion }}</li>
								</ul>
							</div>
							<div v-if="!isBlank(file['version-info'])">
								<h4>--version output:</h4>
								<ul>
									<li v-for="ln in file['version-info']">{{ ln }}</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div v-else>Select a host from the left to see its records.</div>
		</div>
	</div>
</div>
<div v-else>
	<h3>No Data to Display</h3>
</div>
<div style="position: fixed; bottom:0px; right: 0px; left: 0px; background: lightgrey; height: 5px;"></div>