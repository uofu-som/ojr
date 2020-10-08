//////////////////////////////////////////////////////////////
///////////////////	Global Variables /////////////////////////
//////////////////////////////////////////////////////////////

	var host = window.location.hostname;
	var host_prot = window.location.protocol;
	
	var delay_error_expire = 3000;
	var delay_update_data = 60000*2;

	var api_base_url = window.location.href;
	api_base_url = api_base_url.substring(0, api_base_url.length-1);
	api_base_url = api_base_url+"/api.php";
	var verbose = false;
	var main_data_template = {
		app_tokens: [],
		auth_user: {
			"_id":false,
			"email":false,
			"username":false,
			"firstName":false,
			"lastName":false,
			"image":false,
			"token":false,
			"token_expire":false,
			"token_min2expire":800,
			"roles":[]
		},
		branding: {},
		dashboard: {
			hosts:{
				data:[]
			},
			tags:{
				data:[]
			},
			users:{
				data:[]
			}
		},
		form_data: {
			current: {},
			entry_edit:{
				textarea_height:15,
				title:"",
				entry:"",
				date:null
			},
		},
		globals:{
			site_title:"Oracle Java Retirement",
			site_description:"",
		},
		header: {
			title:"Oracle Java Retirement",
			description:"",
		},
		headers: {},
		login:{
			method: '__null__',
			pass: "",
			un: ""
		},
		messages:[],
		messages_action: [],
		messages_display: [],
		reports: {
			host:{
				_sortAsc: true,
				_sortBy: "hostname",
				hostname: null,
				data:[]
			},
			main:{
				_sortAsc: true,
				_sortBy: "path",
				data:[]
			}
		},
		roles:{
			data: []
		},
		view: {
			main: 'home',
			sub: null
		}
	};
	var main_data = main_data_template;
	var vue_instance = new Vue({
		el: '#main',
		data: main_data,
		methods: {
			"app_token_add": function(app_token){
				app_token.token="pending";
				app_token_add(app_token);
			},
			"app_token_delete": function(app_token){
				app_token_delete(app_token);
			},
			"app_token_gui_add_row": function(){
				this.app_tokens.push({token:"",tags:[]});
			},
			"auth_login": function(){
				api_login();
			},
			"auth_logout": function(){
				switch(main_data.login.method){
					case 'local':
						api_logout();
						break;
					case 'google':
						google_logout();
						break;
					default:
						break;
				}
			},
			"isBlank": function(str){
				return isBlank(str);
			},
			"login_display": function(method){
				login_display(method);
			},
			"login_keypressed": function (event) {
				if (event.which == 13 || event.keyCode == 13) {
					switch(event.srcElement.tabIndex){
						case 1:
							var next = document.getElementById('auth_pass');
							next.focus();
							break;
						case 2:
							api_login();
							break;
						case 3:
							api_login();
							break;
					}
					return false;
				}
			},
			"object_length": function(the_object){
				switch(toType(the_object)){
					case 'undefined':
						return 0;
						break;
					case 'object':
					case 'boolean':
					case 'number':
					case 'bigint':
					case 'string':
					case 'symbol':
					case 'function':
					default:
						console.log(toType(the_object));
						break;
				}
				return Object.keys(the_object).length;
			},
			"page_nav_set": function(view) {
				switch(view){
					case 'neverevergethere':
						roles_get();
						break;
					case 'home':
					case 'user_management':
						this.view.main=view;
						break;
					case 'app_token_management':
					case 'scripts':
						app_token_get_all();
						this.view.main=view;
						break;
					case 'dashboard':
						dashboard_hosts_get();
						dashboard_users_get();
						dashboard_tags_get();
						this.view.main=view;
						break;
					case 'reports_main':
						report_main_get();
						this.view.main=view;
						break;
					case 'reports_hosts':
						dashboard_hosts_get();
						this.reports.host.data=[];
						this.reports.host.hostname=null;
						this.view.main=view;
						break;
					default:
						console.log(view);
						break;
				}
				cache_set();
			},
			"page_title_set": function() {
				document.title=this.globals.site_title;
			},
			"report_host_get": function(hostname){
				report_host_get(hostname);
			},
			"report_main_get": function() {
				report_main_get();
			},
			"script_get":function(app_token_id,type){
				script_get(app_token_id,type);
			},
			"setColors": function(id) {
				setColors(id);
			},
			"show_login": function(){
				switch(typeof main_data.auth_user.token){
					case 'undefined':
					case 'boolean':
					case 'number':
					case 'symbol':
					case 'function':
						return true;
						break;
					case 'string':
					case 'object':
						if(isBlank(main_data.auth_user.token))
						return false;
						break;
					default:
						return true;
						break;
				}
			},
			"show_login_creds": function(){
				switch(main_data.login.method){
					case 'local':
						return true;
					case 'google':
						return false;
					default:
						return false;
				}
			},
			"sort_reports_main": function(by){
				sort_reports_main(by);
			},
			"update": _.debounce(function (e) {
				this.form_data.entry_edit.entry = e.target.value
			}, 300 ),
			"window_open": function(url) {
				window.open(url,'_blank');
			}
		},
		computed: { 
			compiledMarkdown: function () {
				return HTML_Markdown_Sanatize(this.form_data.entry_edit.entry);
			},
			form_data_entry_edit: function() {
				var style = {};
				var htb = document.getElementById("header_title_bar");
				var hd = document.getElementById("header_description");
				style["height"]=(window.innerHeight - htb.offsetHeight - hd.offsetHeight);
				return style;
			}
		}
	});
	vue_instance.page_title_set();

//////////////////////////////////////////////////////////////
/////////////////// Generic Ajax Calls ///////////////////////
//////////////////////////////////////////////////////////////

	function api_delete(data, callback){
		data = JSON.stringify(data);
		var xhr = new XMLHttpRequest();
		var url = api_base_url;

		xhr.open("DELETE", url);
		// xhr.setRequestHeader("Content-Type", "application/json");
		if(main_data.auth_user.token){
			if(main_data.auth_user.token.length > 0){
				xhr.setRequestHeader("Authorization", "Bearer "+main_data.auth_user.token);
			}
		}
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				if(!isBlank(callback)){
					callback(xhr.status, "DELETE "+url, xhr.responseText, data, headers);
				}
			}
			if(xhr.readyState == xhr.HEADERS_RECEIVED) {
				// Get the raw header string and process it
				headers = api_headers(xhr.getAllResponseHeaders());
			}
		};
		xhr.send(data);
	}

	function api_get(data, callback){
		var xhr = new XMLHttpRequest();
		var url = api_base_url;
		var headers = {};
		switch(typeof data) {
			case "string":
				url = url + "?" + data;
				break;
			case "object":
				var tmp = ""
				var tmp_first = true;
				for (var property in data) {
					if(tmp_first){
						tmp_first=false;
						tmp = tmp + property + "=" + encodeURIComponent(data[property]);
					}else{
						tmp = tmp + "&" + property + "=" + encodeURIComponent(data[property]);
					}
				}
				if(tmp.length > 0){
					url = url + "?" + tmp;
				}
				break;
			case "function":
			case "symbol":
			case "number":
			case "boolean":
			case "undefined":
			default:
				break;
		}
		data = JSON.stringify(data);
		xhr.open("GET", url, true);
		xhr.setRequestHeader("Content-Type", "application/json");
		if(main_data.auth_user.token){
			if(main_data.auth_user.token.length > 0){
				xhr.setRequestHeader("Authorization", "Bearer "+main_data.auth_user.token);
			}
		}
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				if(!isBlank(callback)){
					callback(xhr.status, "GET "+url, xhr.responseText, data, headers);
				}
			}
			if(xhr.readyState == xhr.HEADERS_RECEIVED) {
				// Get the raw header string and process it
				headers = api_headers(xhr.getAllResponseHeaders());
			}
		};
		// console.log(data);
		xhr.send(data);
	}

	function api_get_bin(data, callback) {
		data = JSON.stringify(data);
		url = api_base_url;
		// switch(typeof data) {
		// 	case "string":
		// 		url = url + "?" + data;
		// 		break;
		// 	case "object":
		// 		var tmp = ""
		// 		var tmp_first = true;
		// 		for (var property in data) {
		// 			if(tmp_first){
		// 				tmp_first=false;
		// 				tmp = tmp + property + "=" + data[property];
		// 			}else{
		// 				tmp = tmp + "&" + property + "=" + data[property];
		// 			}
		// 		}
		// 		if(tmp.length > 0){
		// 			url = url + "?" + tmp;
		// 		}
		// 		break;
		// 	case "function":
		// 	case "symbol":
		// 	case "number":
		// 	case "boolean":
		// 	case "undefined":
		// 	default:
		// 		// code block
		// 		break;
		// }
		var xhr = new XMLHttpRequest();
		xhr.onload = function() {
			var reader = new FileReader();
			reader.onloadend = function() {
				if(!isBlank(callback)){
					callback(reader.result);
				}
			}
			reader.readAsDataURL(xhr.response);
		};
		xhr.open('GET', url);
		if(main_data.auth_user.token){
			if(main_data.auth_user.token.length > 0){
				xhr.setRequestHeader("Authorization", "Bearer "+main_data.auth_user.token);
			}
		}
		xhr.responseType = 'blob';
		xhr.send(data);
	}

	function api_headers(raw_headers){
		var arr = raw_headers.trim().split(/[\r\n]+/);

		var headers = {};
		arr.forEach(function (line) {
			var parts = line.split(': ');
			var header = parts.shift();
			var value = parts.join(': ');
			headers[header] = value;
		});
		if(verbose){
			log('info',headers);
		}
		return headers;
	}

	function api_post(data, callback){
		data = JSON.stringify(data);
		var xhr = new XMLHttpRequest();
		var url = api_base_url;
		var headers = {};

		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-Type", "application/json");
		if(main_data.auth_user.token){
			if(main_data.auth_user.token.length > 0){
				xhr.setRequestHeader("Authorization", "Bearer "+main_data.auth_user.token);
			}
		}
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				if(!isBlank(callback)){
					callback(xhr.status, "POST "+url, xhr.responseText, data, headers);
				}
			}
			if(xhr.readyState == xhr.HEADERS_RECEIVED) {
				// Get the raw header string and process it
				headers = api_headers(xhr.getAllResponseHeaders());
			}
		};
		xhr.send(data);
	}

	function api_post_get_file(data, callback) {
		data = JSON.stringify(data);
		var xhr = new XMLHttpRequest();
		var url = api_base_url;
		var headers = null;

		xhr.open('POST', url, true);
		xhr.responseType = 'blob';
		xhr.setRequestHeader('Content-Type', 'application/json');
		if (main_data.auth_user.token)
			if (main_data.auth_user.token.length > 0) {
				xhr.setRequestHeader('Authorization', 'Bearer ' + main_data.auth_user.token);
			}
		xhr.onload = function () {
			var reader = new FileReader();
			reader.onloadend = function () {
				callback(xhr.status, 'POST ' + url, reader.result, data, headers);
			};
			reader.readAsDataURL(xhr.response);
		};
		xhr.send(data);
	}

	function api_put(data, callback){
		data = JSON.stringify(data);
		var xhr = new XMLHttpRequest();
		var url = api_base_url;
		var headers = {};

		xhr.open("PUT", url);
		xhr.setRequestHeader("Content-Type", "application/json");
		if(main_data.auth_user.token){
			if(main_data.auth_user.token.length > 0){
				xhr.setRequestHeader("Authorization", "Bearer "+main_data.auth_user.token);
			}
		}
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				if(!isBlank(callback)){
					callback(xhr.status, "PUT "+url, xhr.responseText, data, headers);
				}
			}
			if(xhr.readyState == xhr.HEADERS_RECEIVED) {
				// Get the raw header string and process it
				headers = api_headers(xhr.getAllResponseHeaders());
			}
		};
		xhr.send(data);
	}

//////////////////////////////////////////////////////////////
/////////////////// Cache Control Calls //////////////////////
//////////////////////////////////////////////////////////////

	function cache_set(){
		if (typeof(window.localStorage) !== "undefined") {
			for(key in main_data){
				switch(key){
					// Keys to not cache
					case "messages_display":
						break;
					// Keys to cache as json strings
					case "app_tokens":
					case "auth_user":
					case "branding":
					case "dashboard":
					case "form_data":
					case "globals":
					case "header":
					case "headers":
					case "login":
					case "messages":
					case "messages_action":
					case "view":
					case "reports":
					case "roles":
						window.localStorage.setItem('ORJ.'+key, JSON.stringify(main_data[key]));
						break;
					default:
						window.localStorage.setItem('ORJ.'+key, main_data[key]);
						break;
				}
			}
		} else {
			log('info',"localStorage / cache is not working");
		}
	}

	function cache_get(){
		if (typeof(window.localStorage) !== "undefined") {
			for(key in main_data){
				var tmp = window.localStorage.getItem('ORJ.'+key);
				if((typeof tmp !== "undefined") && (tmp !== null)){
					switch(key){
						// Keys to not cache
						case "messages_display":
							break;
						// Keys to convert from json strings
						case "app_tokens":
						case "auth_user":
						case "branding":
						case "dashboard":
						case "form_data":
						case "globals":
						case "header":
						case "headers":
						case "login":
						case "messages":
						case "messages_action":
						case "view":
						case "reports":
						case "roles":
							main_data[key]=JSON.parse(tmp);
							break;
						// Vars that need to be converted to Boolean
						case "view_delete":
							if(tmp==='false'){
								main_data[key]=false;
							}else{
								main_data[key]=true;
							}
							break;
						default:
							main_data[key]=tmp;
							break;
					}
				}
				
			}
		} else {
			log('info',"Storage is Sore'age :( ... I.E. I know it hurts, but you can't get that from here!");
		}
	}

	function cache_clear(){
		if (typeof(window.localStorage) !== "undefined") {
			for(key in main_data){
				switch(key){
					// case "background_class":
					// 	break;
					default:
						// log('info','ORJ.'+key+" Removed");
						window.localStorage.removeItem('ORJ.'+key);
				}
			}
		} else {
			log('info',"There's nothing here to see folks!");
		}
	}

//////////////////////////////////////////////////////////////
/////////////////// Timers ///////////////////////////////////
//////////////////////////////////////////////////////////////

	function timer_error_expire(){
		if(main_data.auth_user.token != false && main_data.auth_user.token != undefined){
			if(main_data.messages_display.length>0){
				var l = main_data.messages_display.length;
				var j = 0;
				for(var i = 0; i < l; i++){
					if(!isBlank(main_data.messages_display[j]['time'])){
						if((Date.now()-delay_error_expire+1) > main_data.messages_display[j]['time']){
							var tmp = main_data.messages_display.splice(j,1);
							if(Array.isArray(main_data.messages)){
								(main_data.messages).push(tmp);
							}else{
								main_data.messages=[];
								main_data.messages.push(tmp);
							}
						}else{
							j++;
						}
					}else{
						var tmp = main_data.messages_display.splice(j,1);
					}
				}
				// setTimeout(timer_error_expire,delay_error_expire);
			}else{
				// setTimeout(timer_error_expire,delay_error_expire*3);
			}
		}else{
			main_data.messages_display=[];
		}
	}

	function timer_token(){
		if(!isBlank(main_data.auth_user.token)){
			var token_decode_split = main_data.auth_user.token.split(".");
			var token_decode_json = JSON.parse(base64_decode(token_decode_split[1]));
			var minutes_left = Math.round((new Date((token_decode_json.exp*1000)) - Date.now())/1000/60);

			main_data.auth_user.token_min2expire = minutes_left;
			// document.getElementById("min2exp").innerHTML = minutes_left;
			if((minutes_left < token_timeleft_display_logout_warning) && !main_data.logout_info.is_warning_displayed){
				main_data.logout_info.is_warning_displayed=!main_data.logout_info.is_warning_displayed;
				log_action("Session is almost over",[{"name":"Extend","action":"session_extend", "class":{"btn":true,"btn-success":true}},{"name":"Logout","action":"logout", "class":{"btn":true,"btn-warning":true}}]);
			}
			if(minutes_left < token_timeleft_logout){
				// log_display('error',"Logout would have just occurred");
				api_logout();
			}
			timer_error_expire();
			setTimeout(timer_token,1000*60);
		}
	}

	function timer_update_data(){
		if(main_data.auth_user.token != false && main_data.auth_user.token != undefined){
			update_data();
			setTimeout(timer_update_data,delay_update_data);
		}
	}

	function update_data(){
		api_apps_get();
		api_agents_get();
		api_users_get();
		api_resource_help_get();
	}

//////////////////////////////////////////////////////////////
///////////////////	Basic Functions //////////////////////////
//////////////////////////////////////////////////////////////

	function base64_encode(data) {
		var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
		ac = 0,
		enc = '',
		tmp_arr = [];
		if (!data) {
			return data;
		}
		do { // pack three octets into four hexets
			o1 = data.charCodeAt(i++);
			o2 = data.charCodeAt(i++);
			o3 = data.charCodeAt(i++);
			bits = o1 << 16 | o2 << 8 | o3;
			h1 = bits >> 18 & 0x3f;
			h2 = bits >> 12 & 0x3f;
			h3 = bits >> 6 & 0x3f;
			h4 = bits & 0x3f;
			// use hexets to index into b64, and append result to encoded string
			tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
		} while (i < data.length);
		enc = tmp_arr.join('');
		var r = data.length % 3;
		return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
	}

	function base64_decode(data) {
		var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
		ac = 0,
		dec = '',
		tmp_arr = [];
		if (!data) {
			return data;
		}
		data += '';
		do {
			h1 = b64.indexOf(data.charAt(i++));
			h2 = b64.indexOf(data.charAt(i++));
			h3 = b64.indexOf(data.charAt(i++));
			h4 = b64.indexOf(data.charAt(i++));
			bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;
			o1 = bits >> 16 & 0xff;
			o2 = bits >> 8 & 0xff;
			o3 = bits & 0xff;
			if (h3 == 64) {
				tmp_arr[ac++] = String.fromCharCode(o1);
			} else if (h4 == 64) {
				tmp_arr[ac++] = String.fromCharCode(o1, o2);
			} else {
				tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
			}
		} while (i < data.length);
		dec = tmp_arr.join('');
		return dec.replace(/\0+$/, '');
	}
	
	function countKeys(obj){
		var i = 0;
		for (var key in obj) {
			i=i+1;
		}
		return i;
	}

	function htmlEntities(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	function HTML_Markdown_Sanatize(str){
		// str = htmlEntities(str);
		str = marked(str, { sanitize: true })
		// str = String(str).replace(/\n\n/g, '').replace(/\n/g, '<br>');
		return str;
	}
	
	function input_verify(id, inputFilter, min, max) {
		["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
			id.addEventListener(event, function(e) {
				// console.log(e);
				if(e.target.value!==""){
					if(inputFilter(this.value,min,max)){
						e.target.classList.remove("is-invalid");
						e.target.classList.add("is-valid");
					}else{
						e.target.classList.remove("is-valid");
						e.target.classList.add("is-invalid");
					}
				}else{
					e.target.classList.remove("is-invalid");
					e.target.classList.remove("is-valid");
				}
			});
		});
	}

	function input_verify_int(value,min,max){
		if(value===0){value="0";}
		if(min===0){min="0";}
		if(max===0){max="0";}
		if(value && min && max){
			return /^-?\d*$/.test(value) && (value === "" || (parseInt(value) >= parseInt(min)) && (parseInt(value) <= parseInt(max)));
		}else if(value && min){
			return /^-?\d*$/.test(value) && (value === "" || parseInt(value) >= parseInt(min));
		}else if(value && max){
			return /^-?\d*$/.test(value) && (value === "" || parseInt(value) <= parseInt(max));
		}else if(value){
			return /^-?\d*$/.test(value);
		}else{
			return false;
		}
	};
	function input_verify_uint(value,min,max){
		if(value===0){value="0";}
		if(min===0){min="0";}
		if(max===0){max="0";}
		if(value && min && max){
			return /^\d*$/.test(value) && (value === "" || (parseInt(value) >= parseInt(min)) && (parseInt(value) <= parseInt(max)));
		}else if(value && min){
			return /^\d*$/.test(value) && (value === "" || parseInt(value) >= parseInt(min));
		}else if(value && max){
			return /^\d*$/.test(value) && (value === "" || parseInt(value) <= parseInt(max));
		}else if(value){
			return /^\d*$/.test(value);
		}else{
			return false;
		}
	};
	function input_verify_float(value,min,max){
		if(value===0){value="0";}
		if(min===0){min="0";}
		if(max===0){max="0";}
		if(value && min && max){
			return /^-?\d*[.,]?\d*$/.test(value) && (value === "" || (parseFloat(value) >= parseFloat(min)) && (parseFloat(value) <= parseFloat(max)));
		}else if(value && min){
			return /^-?\d*[.,]?\d*$/.test(value) && (value === "" || parseFloat(value) >= parseFloat(min));
		}else if(value && max){
			return /^-?\d*[.,]?\d*$/.test(value) && (value === "" || parseFloat(value) <= parseFloat(max));
		}else if(value){
			return /^-?\d*[.,]?\d*$/.test(value);
		}else{
			return false;
		}
	};
	function input_verify_percent(value,min,max){
		if(value===0){value="0";}
		if(min===0){min="0";}
		if(max===0){max="0";}
		if(value && min && max){
			return /^-?\d*[.,]?\d*%?$/.test(value) && (value === "" || (parseFloat(value) >= parseFloat(min)) && (parseFloat(value) <= parseFloat(max)));
		}else if(value && min){
			return /^-?\d*[.,]?\d*%?$/.test(value) && (value === "" || parseFloat(value) >= parseFloat(min));
		}else if(value && max){
			return /^-?\d*[.,]?\d*%?$/.test(value) && (value === "" || parseFloat(value) <= parseFloat(max));
		}else if(value){
			return /^-?\d*[.,]?\d*%?$/.test(value);
		}else{
			return false;
		}
	};
	function input_verify_hex(value,min,max){ return /^[0-9a-f]*$/i.test(value); };
	function input_verify_regex(value,min,max){
		// min is the regex string
		// max is not used
		var match = min.match(new RegExp('^/(.*?)/([gimy]*)$'));
		var regex = new RegExp(match[1], match[2]);
 		return regex.test(value);
 	};

	function isBlank(str) {
		if(str===undefined){
			return true;
		}
		if(str===null){
			return true;
		}
		if(!str){
			return true;
		}
		return (/^\s*$/.test(str));
	}

	function log(type, message){
		var entry = {}
		entry['type']=type;
		entry['message']=message;
		main_data.messages.push(entry);
		cache_set();
	}

	function log_action(message, button_array){
		// Assumption button_array is an array of objects that contain name and action to perform [["anme"]]
		var entry = {}
		entry['type']='action';
		entry['message']=message;
		entry['action']=button_array;
		main_data.messages_action.push(entry);
		cache_set();
	}

	function log_display(type, message){
		var entry = {}
		entry['type']=type;
		entry['message']=message;
		entry['time']=Date.now();
		main_data.messages_display.push(entry);
		setTimeout(timer_error_expire,delay_error_expire);
	}

	function log_callback(status, endpoint, return_message, original_data, headers){
		if((typeof status != 'undefined')||(typeof endpoint != 'undefined')){
			switch(status){
				case '200':
					log('success',""+status+": "+endpoint+"");
					log('success',return_message);
					log('success',original_data);
					break;
				default:
					log('error',""+status+": "+endpoint+"");
					log('error',return_message);
					log('error',original_data);
					break;
			}
		}
	}

	function log_reset(){
		main_data.messages=[];
		main_data.messages_action=[];
		main_data.messages_display=[];
	}

	function setColors(id){
		var bgc = '#FFFFFF';
		var tc = '#000000';
		var black_num=parseInt("0x00");
		var white_num=parseInt("0xFF");
		var con_r = 'FF';
		var con_g = 'FF';
		var con_b = 'FF';
		var element = document.getElementById(id);
		bgc = "#"+(Math.random()*0xFFFFFF<<0).toString(16).padStart(6, "0")+"";
		var bgc_num = parseInt("0x"+bgc);
		var bgc_r_num = parseInt("0x"+bgc.substr(1,2));
		var bgc_g_num = parseInt("0x"+bgc.substr(3,2));
		var bgc_b_num = parseInt("0x"+bgc.substr(5,2));
		if(parseInt("0x"+bgc.substr(1,2))>=128){con_r='00';}
		if(parseInt("0x"+bgc.substr(3,2))>=128){con_g='00';}
		if(parseInt("0x"+bgc.substr(5,2))>=128){con_b='00';}
		var tc_num=parseInt("0x"+con_r+con_g+con_b);
		var avg = (parseInt("0x"+bgc.substr(1,2))+parseInt("0x"+bgc.substr(3,2))+parseInt("0x"+bgc.substr(5,2)))/3;
		var bgc_distance_black = Math.hypot(Math.hypot(bgc_r_num,bgc_g_num),bgc_b_num);
		var bgc_distance_white = Math.hypot(Math.hypot((white_num-bgc_r_num),(white_num-bgc_g_num)),(white_num-bgc_b_num));
		var bgc_distance_tc = Math.hypot(Math.hypot(Math.abs(tc_num-bgc_r_num),Math.abs(tc_num-bgc_g_num)),Math.abs(tc_num-bgc_b_num));
		if((bgc_distance_black >= bgc_distance_white) && (bgc_distance_black >= bgc_distance_tc)){
			tc = 'black';
			// console.log('black: '+id);
		}else if(((bgc_distance_white >= bgc_distance_black) && (bgc_distance_white >= bgc_distance_tc)) || (avg <= 128) ){
			tc = 'white';
			// console.log('white: '+id);
		}else{
			tc="#"+con_r+con_g+con_b+"";
			// console.log('tc: '+id);
		}
		element.style.backgroundColor = bgc;
		element.style.color = tc;
	}

	function toType(obj){
		return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
	}

	function whocares_callback(status, endpoint, return_message, original_data, headers){
		// Don't do anything with the results becasue, I don't
	}

//////////////////////////////////////////////////////////////
/////////////////// Form Generation //////////////////////////
//////////////////////////////////////////////////////////////

	function form_generate(target,json_txt){
		var div_main = document.createElement("div");
		div_main.classList.add('container');

		var div_main_title = document.createElement("h2");
		div_main_title.textContent = "Create Form Template";
		div_main.appendChild(div_main_title);
		
		var div_main_intro = document.createElement("p");
		div_main_intro.textContent = "Click on the \"Add Field\" button to add a form element";
		div_main.appendChild(div_main_intro);

		var div_main_add_button_top = document.createElement("button");
		div_main_add_button_top.type = 'button';
		div_main_add_button_top.classList.add('btn');
		div_main_add_button_top.classList.add('btn-outline-success');
		div_main_add_button_top.classList.add('far');
		div_main_add_button_top.classList.add('fa-plus-square');
		div_main_add_button_top.textContent = " Add Field";
		div_main.appendChild(div_main_add_button_top);

		var form_fields = document.createElement("form");
		form_fields.classList.add('needs');
		// form_fields.textContent = " Add Field";
		div_main.appendChild(form_fields);

		var div_main_add_button_bottom = document.createElement("button");
		div_main_add_button_bottom.type = 'button';
		div_main_add_button_bottom.classList.add('btn');
		div_main_add_button_bottom.classList.add('btn-outline-success');
		div_main_add_button_bottom.classList.add('far');
		div_main_add_button_bottom.classList.add('fa-plus-square');
		div_main_add_button_bottom.textContent = " Add Field";
		div_main.appendChild(div_main_add_button_bottom);

		var rownum = 0;

		["keyup", "mouseup"].forEach(function(event) {
			[div_main_add_button_top, div_main_add_button_bottom].forEach(function(button) {
				button.addEventListener(event,function(e){
					switch(event){
						case 'keyup':
							switch(e.key){
								case 'Enter':
									form_fields.appendChild(form_generate_add_field(rownum));
									rownum++;
									break;
								default:
									break;
							}
							break;
						case 'mouseup':
							form_fields.appendChild(form_generate_add_field(rownum));
							rownum++;
							break;
						default:
							break;
					}
				});
			});
		});

		var json = null;
		try {
			if(json_txt){
				json = JSON.parse(json_txt);
			}
		} catch (e) {
			console.log("Malformed JSON");
			console.log(e);
			console.log(json_txt);
			return false;
		}

		if(target){
			target.innerHTML = '';
			target.appendChild(div_main);
			return true;
		}else{
			return false;
		}
	}

	function form_generate_add_field(rownum){
		var field_div = document.createElement("div");
		field_div.classList.add('row');
		field_div.classList.add('border-top');
		field_div.classList.add('border-bottom');
		field_div.classList.add('border-dark');

			// var drop_zone = document.createElement("div");
			// drop_zone.classList.add('col-sm-12');

			var field_name_input = document.createElement("input");
			field_name_input.name = "label";
			field_name_input.rownum = rownum;
			field_name_input.classList.add('form-control');
			field_name_input.classList.add('col-sm-5');
			field_name_input.placeholder = "Field Name";
			field_div.appendChild(field_name_input);

			var field_data_name_input = document.createElement("input");
			field_data_name_input.name = "field_name";
			field_data_name_input.rownum = rownum;
			field_data_name_input.classList.add('form-control');
			field_data_name_input.classList.add('col-sm-4');
			field_data_name_input.placeholder = "Data Name (Optional)";
			field_div.appendChild(field_data_name_input);

			var field_types = {
				'':{'label':'[Select Field Type]'},
				'string':{'label':'String','placeholder':true,'regex':true},
				'int':{'label':'Integer','placeholder':true,'min':true,'max':true},
				'float':{'label':'Float (Decimal)','placeholder':true,'min':true,'max':true},
				'percent':{'label':'Percent (Decimal)','placeholder':true,'min':true,'max':true},
				'date':{'label':'Date'},
				'select':{'label':'Select','multi-value':true},
				'radio':{'label':'Radio','multi-value':true},
				'dynamic-multi-table':{'label':'Dynamic Table','recursive':true},
				'dynamic-multi-card':{'label':'Dynamic Cards','recursive':true}
			};

			var field_type_select = document.createElement("select");
			field_type_select.name = "field_type";
			field_type_select.rownum = rownum;
			field_type_select.classList.add('form-control');
			field_type_select.classList.add('col-sm-3');
				Object.keys(field_types).forEach(function(type){
					// console.log(type);
					// console.log(field_types[type]);
					var field_type_select_option = document.createElement('option');
					field_type_select_option.rownum = rownum;
					field_type_select_option.value = type;
					field_type_select_option.textContent=""+field_types[type].label+"";
					field_type_select.appendChild(field_type_select_option);
				});

			field_div.appendChild(field_type_select);

			var newline_div_01 = document.createElement("div");
			newline_div_01.rownum = rownum;
			newline_div_01.classList.add('col-sm-12');
			field_div.appendChild(newline_div_01);

			var indent_div_01 = document.createElement("div");
			indent_div_01.rownum = rownum;
			indent_div_01.classList.add('col-sm-1');
			// indent_div_01.classList.add('bg-dark');
			field_div.appendChild(indent_div_01);

			var field_div_options = document.createElement("div");
			field_div_options.rownum = rownum;
			field_div_options.classList.add('col-sm-11');
			field_div_options.classList.add('row');
			field_div.appendChild(field_div_options);

			["change","select"].forEach(function(event) {
				// console.log("adding event listener..."+event+"");
				field_type_select.addEventListener(event,function(e){
					var selected_field_type = field_types[e.target.value];
					field_div_options.innerHTML='';

					if (selected_field_type['placeholder']){
						switch(e.target.value){
							case 'string':
							case 'int':
							case 'float':
							case 'percent':
								var placeholder_input = document.createElement("input");
								placeholder_input.name = 'placeholder';
								placeholder_input.rownum = rownum;
								placeholder_input.classList.add('form-control');
								placeholder_input.classList.add('col-sm-4');
								placeholder_input.placeholder="Placeholder";
								field_div_options.appendChild(placeholder_input);
								break;
							default:
								break;
						}
					}

					if (selected_field_type['regex']){
						switch(e.target.value){
							case 'string':
							case 'int':
							case 'float':
							case 'percent':
								var regex_input = document.createElement("input");
								regex_input.name = 'regex';
								regex_input.rownum = rownum;
								regex_input.classList.add('form-control');
								regex_input.classList.add('col-sm-4');
								regex_input.placeholder="Custom Validation RegEx";
								field_div_options.appendChild(regex_input);
								break;
							default:
								break;
						}
					}

					if (selected_field_type['min']){
						var min_input = document.createElement("input");
						min_input.name = 'min';
						min_input.rownum = rownum;
						min_input.classList.add('form-control');
						min_input.classList.add('col-sm-2');
						min_input.placeholder="Min Value";
						switch(e.target.value){
							case 'int':
								input_verify(min_input, input_verify_int, null, null);
								field_div_options.appendChild(min_input);
								break;
							case 'float':
							case 'percent':
								input_verify(min_input, input_verify_float, null, null);
								field_div_options.appendChild(min_input);
								break;
							default:
								break;
						}
					}

					if (selected_field_type['max']){
						var max_input = document.createElement("input");
						max_input.name = 'max';
						max_input.rownum = rownum;
						max_input.classList.add('form-control');
						max_input.classList.add('col-sm-2');
						max_input.placeholder="Max Value";
						switch(e.target.value){
							case 'int':
								input_verify(max_input, input_verify_int, null, null);
								field_div_options.appendChild(max_input);
								break;
							case 'float':
							case 'percent':
								input_verify(max_input, input_verify_float, null, null);
								field_div_options.appendChild(max_input);
								break;
							default:
								break;
						}
					}

					if (selected_field_type['multi-value']){
						var indent_div_02 = document.createElement("div");
						indent_div_02.rownum = rownum;
						indent_div_02.classList.add('col-sm-1');
						field_div_options.appendChild(max_input);

						// var rows_div
						var max_input = document.createElement("input");
						max_input.name = 'max';
						max_input.rownum = rownum;
						max_input.classList.add('form-control');
						max_input.classList.add('col-sm-2');
						max_input.placeholder="Max Value";
						switch(e.target.value){
							case 'int':
								input_verify(max_input, input_verify_int, null, null);
								field_div_options.appendChild(max_input);
								break;
							case 'float':
							case 'percent':
								input_verify(max_input, input_verify_float, null, null);
								field_div_options.appendChild(max_input);
								break;
							default:
								break;
						}
					}

					switch(e.target.value){
						case 'string':
							break;
						case 'date':
							break;
						case 'int':
							break;
						case 'float':
						case 'percent':
							break;
						case 'select':
						case 'radio':
							break;
						case 'dynamic-multi-table':
						case 'dynamic-multi-card':
							break;
						default:
							break;
					}
					console.log(selected_field_type);
				});
			});

		return field_div;
	}

//////////////////////////////////////////////////////////////
/////////////////// Form Rendering ///////////////////////////
//////////////////////////////////////////////////////////////

	function form_values(event){
		event.preventDefault();
		var return_object = {};
		var dynamic_multi_objects = {};
		Object.keys(event.target.elements).forEach(function(k){
			switch(event.target.elements[k].field_type){
				case 'radio':
					switch(typeof event.target.elements[k].rownum){
						case 'number':
							var parent_name = event.target.elements[k].parentNode.name;
							var my_name = event.target.elements[k].name;
							var my_row = event.target.elements[k].rownum;
							if (dynamic_multi_objects[parent_name]) {
							}else{
								dynamic_multi_objects[parent_name] = {};
							}
							if (dynamic_multi_objects[parent_name][my_row]) {
							}else{
								dynamic_multi_objects[parent_name][my_row] = {};
							}
							if(event.target.elements[k].checked){
								dynamic_multi_objects[parent_name][my_row][my_name] = event.target.elements[k].value;
							}
							break
						default:
							if(event.target.elements[k].checked){
								return_object[event.target.elements[k].name] = event.target.elements[k].value;
							}
							break;
					}
					break;
				case 'int':
					switch(typeof event.target.elements[k].rownum){
						case 'number':
							var parent_name = event.target.elements[k].parentNode.name;
							var my_name = event.target.elements[k].name;
							var my_row = event.target.elements[k].rownum;
							if (dynamic_multi_objects[parent_name]) {
							}else{
								dynamic_multi_objects[parent_name] = {};
							}
							if (dynamic_multi_objects[parent_name][my_row]) {
							}else{
								dynamic_multi_objects[parent_name][my_row] = {};
							}
							if(event.target.elements[k].value){
								dynamic_multi_objects[parent_name][my_row][my_name] = parseInt(event.target.elements[k].value);
							}
							break;
						default:
							if(event.target.elements[k].value){
								return_object[event.target.elements[k].name] = parseInt(event.target.elements[k].value);
							}
							break;
					}
					break;
				case 'float':
				case 'percent':
					switch(typeof event.target.elements[k].rownum){
						case 'number':
							var parent_name = event.target.elements[k].parentNode.name;
							var my_name = event.target.elements[k].name;
							var my_row = event.target.elements[k].rownum;
							if (dynamic_multi_objects[parent_name]) {
							}else{
								dynamic_multi_objects[parent_name] = {};
							}
							if (dynamic_multi_objects[parent_name][my_row]) {
							}else{
								dynamic_multi_objects[parent_name][my_row] = {};
							}
							if(event.target.elements[k].value){
								dynamic_multi_objects[parent_name][my_row][my_name] = parseFloat(event.target.elements[k].value);
							}
							break;
						default:
							if(event.target.elements[k].value){
								return_object[event.target.elements[k].name] = parseFloat(event.target.elements[k].value);
							}
							break;
					}
					break;
				default:
					switch(typeof event.target.elements[k].rownum){
						case 'number':
							var parent = event.target.elements[k].parentNode;
							while(parent.name===undefined){
								parent = parent.parentNode;
							}
							var parent_name = parent.name;
							var my_name = event.target.elements[k].name;
							var my_row = event.target.elements[k].rownum;
							if (dynamic_multi_objects[parent_name]) {
							}else{
								dynamic_multi_objects[parent_name] = {};
							}
							if (dynamic_multi_objects[parent_name][my_row]) {
							}else{
								dynamic_multi_objects[parent_name][my_row] = {};
							}
							if(event.target.elements[k].value){
								dynamic_multi_objects[parent_name][my_row][my_name] = event.target.elements[k].value;
							}
							break;
						default:
							if(event.target.elements[k].value){
								return_object[event.target.elements[k].name] = event.target.elements[k].value;
							}
							break;
					}
					break;
			}
		});
		
		// console.log(dynamic_multi_objects);
		Object.keys(dynamic_multi_objects).forEach(function(parent_name){
			// if(return_object[parent_name]){console.log(parent_name+" Found");console.log(return_object[parent_name]);}else{return_object[parent_name]=Array();}
			return_object[parent_name]=Array();
			Object.keys(dynamic_multi_objects[parent_name]).forEach(function(rownum){
				var row = {};
				Object.keys(dynamic_multi_objects[parent_name][rownum]).forEach(function(fieldname){
					row[fieldname]=dynamic_multi_objects[parent_name][rownum][fieldname];
				});
				return_object[parent_name].push(row);
			});
		});
		console.log(return_object);
		// event.target.parentElement.innerHTML="";
	}

	function form_render(title,json_txt,target){
		var json = null;
		try {
			json = JSON.parse(json_txt);
		} catch (e) {
			console.log("Malformed JSON");
			console.log(e);
			console.log(json_txt);
			return false;
		}
		main_data.form_data.current = json;

		var div_main = document.createElement('form');
		div_main.addEventListener('submit',form_values);
		div_main.classList.add('form-horizontal');
		div_main.classList.add('container');
		div_main.classList.add('needs');
		var div_title = document.createElement('h2');
		div_title.textContent=title;
		div_main.appendChild(div_title);

		Object.keys(main_data.form_data.current).forEach(function(k){
			switch(main_data.form_data.current[k]['field_type']){
				case 'dynamic-multi':
				case 'dynamic-multi-table':
					div_main.appendChild(form_render_dynamic_multi_table(k,main_data.form_data.current[k]));
					break;
				case 'dynamic-multi-card':
					div_main.appendChild(form_render_dynamic_multi_card(k,main_data.form_data.current[k]));
					break;
				case 'date':
					div_main.appendChild(form_render_date(k,main_data.form_data.current[k]));
					break;
				case 'float':
					div_main.appendChild(form_render_float(k,main_data.form_data.current[k]));
					break;
				case 'int':
					div_main.appendChild(form_render_int(k,main_data.form_data.current[k]));
					break;
				case 'percent':
					div_main.appendChild(form_render_percent(k,main_data.form_data.current[k]));
					break;
				case 'radio':
					div_main.appendChild(form_render_radio(k,main_data.form_data.current[k]));
					break;
				case 'select':
					div_main.appendChild(form_render_select(k,main_data.form_data.current[k]));
					break;
				case 'string':
					div_main.appendChild(form_render_string(k,main_data.form_data.current[k]));
					break;
				default:
					console.log(main_data.form_data.current[k]['field_type']);
					break;
			}
		});
		var submit_button = document.createElement('button');
		submit_button.type='submit';
		submit_button.classList.add('btn');
		submit_button.classList.add('btn-primary');
		submit_button.textContent='Submit';
		div_main.appendChild(submit_button)
		target.innerHTML = '';
		target.appendChild(div_main);
		return true;
	}

	function form_render_dynamic_multi_card(label,json,rownum,parentType){

		var info = null;
		var field_name = null;

		var span_main = document.createElement('div');
		span_main.classList.add('form-group');
		span_main.classList.add('row');

		var span_label = document.createElement('label');
		span_label.classList.add('col-sm-3');
		span_label.classList.add('col-form-label');
		span_label.textContent=label;
		// span_main.appendChild(span_label);

		var span_add_card = document.createElement('button');
		span_add_card.type='button';
		span_add_card.classList.add('btn');
		span_add_card.classList.add('btn-outline-success');
		span_add_card.classList.add('far');
		span_add_card.classList.add('fa-plus-square');
		span_add_card.classList.add('col-sm-5');
		span_add_card.textContent=' Add '+label;
		span_main.appendChild(span_add_card);

		var span_spacer_01 = document.createElement('span');
		span_spacer_01.classList.add('col-sm-12');
		span_main.appendChild(span_spacer_01);

		var cards_div = document.createElement('div');
		cards_div.className='';
		cards_div.classList.add('row');
		cards_div.classList.add('col-sm-12');

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		["keyup", "mouseup"].forEach(function(event) {
			span_add_card.addEventListener(event,function(e){
				switch(event){
					case 'keyup':
						switch(e.key){
							case 'Enter':
								cards_div.appendChild(form_render_dynamic_multi_card_add(field_name,json.values));
								break;
							default:
								break;
						}
						break;
					case 'mouseup':
						cards_div.appendChild(form_render_dynamic_multi_card_add(field_name,json.values));
						break;
					default:
						break;
				}
				
			});
		});

		if(info){
			var info_small = document.createElement('small');
			info_small.textContent=info;
			span_main.appendChild(info_small);
		}

		span_main.appendChild(cards_div);

		return span_main;
	}

	function form_render_dynamic_multi_card_add(label,json){
		var rownum = null;
		if(main_data.form_data.current[label]){}else{main_data.form_data.current[label]={};}
		if(main_data.form_data.current[label].rownum){main_data.form_data.current[label].rownum++;}else{main_data.form_data.current[label].rownum=1;}
		rownum=main_data.form_data.current[label].rownum;
		var even = true;
		if(rownum%2==1){
			even = false;
		}else{
			even = true;
		}

		var card = document.createElement('div');
		card.classList.add('card');
		var card_div = document.createElement('div');
		card_div.classList.add('card-body');
		// card_div.classList.add('col-sm-4');
		// card_div.classList.add('col-sm-12');
		card.appendChild(card_div);

		Object.keys(json).forEach(function(k){
			var child = null;
			switch(json[k]['field_type']){
				case 'dynamic-multi':
				case 'dynamic-multi-table':
					child = form_render_dynamic_multi_table(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'date':
					child = form_render_date(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'float':
					child = form_render_float(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'int':
					child = form_render_int(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'percent':
					child = form_render_percent(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'radio':
					child = form_render_radio(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'select':
					child = form_render_select(k,json[k],rownum,"dynamic-multi-card");
					break;
				case 'string':
					child = form_render_string(k,json[k],rownum,"dynamic-multi-card");
					break;
				default:
					console.log(json[k]['field_type']);
					break;
			}
			if(child){
				if(even){
					// card_div.classList.add("bg-dark");
				}else{
					// card_div.classList.add("bg-light");
				}
				child.name=label;
				card_div.appendChild(child);
			}
		});

		var delete_buttton = document.createElement('button');
		delete_buttton.type="button";
		delete_buttton.classList.add("btn");
		delete_buttton.classList.add("btn-outline-danger");

		["keyup", "mouseup"].forEach(function(event) {
			delete_buttton.addEventListener(event,function(e){
				var go = false;
				switch(event){
					case 'keyup':
						switch(e.key){
							case 'Enter':
								go = true;
								break;
							default:
								break;
						}
						break;
					case 'mouseup':
						go = true;
						break;
					default:
						break;
				}
				if(go){
					// console.log((""+e.target.nodeName+"").toLowerCase());
					switch((""+e.target.nodeName+"").toLowerCase()){
						case 'span':
							// console.log(e.target.parentNode.parentNode);
							e.target.parentNode.parentNode.parentNode.remove();
							break;
						case 'button':
							// console.log(e.target.parentNode);
							e.target.parentNode.parentNode.remove();
							break;
						default:
							console.log((""+e.target.nodeName+"").toLowerCase());
							break;
					}
				}
			});
		});

		var delete_span = document.createElement('span');
		delete_span.classList.add("fas");
		delete_span.classList.add("fa-trash-alt");
		if(even){
			// delete_span.classList.add("text-light");
		}else{
			// delete_span.classList.add("text-dark");
		}
		delete_span.innerHTML='&nbsp;Remove '+label;
		delete_buttton.appendChild(delete_span);

		card_div.appendChild(delete_buttton);

		return card;
	}

	function form_render_dynamic_multi_table(label,json,rownum,parentType){

		var info = null;
		var field_name = null;

		var span_main = document.createElement('div');
		span_main.classList.add('form-group');
		span_main.classList.add('row');
		var span_label = document.createElement('label');
		span_label.classList.add('col-sm-3');
		span_label.classList.add('col-form-label');
		span_label.textContent=label;
		// span_main.appendChild(span_label);

		var table = document.createElement('table');
		table.classList.add('col-sm-12');
		table.classList.add('table');
		table.classList.add('table-striped');
		// table.classList.add('table-hover');
		var table_header = document.createElement('thead');
		table_header.classList.add('thead-dark');
		var table_header_tr = document.createElement('tr');

		var header_th_add = document.createElement('th');
			var header_th_add_span = document.createElement('button');
			header_th_add_span.type='button';
			header_th_add_span.classList.add('btn');
			header_th_add_span.classList.add('btn-outline-success');
			header_th_add_span.classList.add('far');
			header_th_add_span.classList.add('fa-plus-square');
			header_th_add_span.classList.add('col-sm-5');
			// header_th_add_span.classList.add('col-form-label');
			header_th_add_span.innerHTML='&nbsp; Add '+label;
			span_main.appendChild(header_th_add_span);
		table_header_tr.appendChild(header_th_add);

		Object.keys(json.values).forEach(function(key){
			var header_th = document.createElement('th');
			header_th.textContent=key;
			table_header_tr.appendChild(header_th);
		});

		var header_th_delete = document.createElement('th');
		header_th_delete.innerHTML='&nbsp;';
		table_header_tr.appendChild(header_th_delete);

		table_header.appendChild(table_header_tr);
		table.appendChild(table_header);

		var table_tbody = document.createElement('tbody');

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		["keyup", "mouseup"].forEach(function(event) {
			header_th_add_span.addEventListener(event,function(e){
				switch(event){
					case 'keyup':
						switch(e.key){
							case 'Enter':
								table_tbody.appendChild(form_render_dynamic_multi_table_add(field_name,json.values));
								break;
							default:
								break;
						}
						break;
					case 'mouseup':
						table_tbody.appendChild(form_render_dynamic_multi_table_add(field_name,json.values));
						break;
					default:
						break;
				}
				
			});
		});

		table.appendChild(table_tbody);

		if(info){
			var info_small = document.createElement('small');
			info_small.textContent=info;
			span_main.appendChild(info_small);
		}

		span_main.appendChild(table);

		return span_main;
	}

	function form_render_dynamic_multi_table_add(label,json){
		var rownum = null;
		if(main_data.form_data.current[label]){}else{main_data.form_data.current[label]={};}
		if(main_data.form_data.current[label].rownum){main_data.form_data.current[label].rownum++;}else{main_data.form_data.current[label].rownum=1;}
		rownum=main_data.form_data.current[label].rownum;
		var even = true;
		if(rownum%2==1){
			even = false;
		}else{
			even = true;
		}
		var row = document.createElement('tr');
		// row.classList.add('col-sm-12');

		var add_th = document.createElement('th');
		row.appendChild(add_th);

		Object.keys(json).forEach(function(k){
			var child = null;
			switch(json[k]['field_type']){
				case 'dynamic-multi':
				case 'dynamic-multi-table':
					child = form_render_dynamic_multi_table(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'date':
					child = form_render_date(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'float':
					child = form_render_float(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'int':
					child = form_render_int(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'percent':
					child = form_render_percent(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'radio':
					child = form_render_radio(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'select':
					child = form_render_select(k,json[k],rownum,"dynamic-multi-table");
					break;
				case 'string':
					child = form_render_string(k,json[k],rownum,"dynamic-multi-table");
					break;
				default:
					console.log(json[k]['field_type']);
					break;
			}
			if(child){
				if(even){
					// row.classList.add("bg-dark");
				}else{
					// row.classList.add("bg-light");
				}
				child.name=label;
				row.appendChild(child);
			}
		});

		var delete_th = document.createElement('th');
		var delete_button = document.createElement('button');
		delete_button.type='button';
		delete_button.classList.add('btn');
		delete_button.classList.add('btn-outline-danger');


		["keyup", "mouseup"].forEach(function(event) {
			delete_th.addEventListener(event,function(e){
				var go = false;
				switch(event){
					case 'keyup':
						switch(e.key){
							case 'Enter':
								go = true;
								break;
							default:
								break;
						}
						break;
					case 'mouseup':
						go = true;
						break;
					default:
						break;
				}
				if(go){
					switch((""+e.target.nodeName+"").toLowerCase()){
						case 'span':
							e.target.parentNode.parentNode.parentNode.remove();
							break;
						case 'button':
							e.target.parentNode.parentNode.remove();
							break;
						case 'th':
							e.target.parentNode.remove();
							break;
						default:
							console.log((""+e.target.nodeName+"").toLowerCase());
							break;
					}
				}
			});
		});

		var delete_span = document.createElement('span');
		delete_span.classList.add("fas");
		delete_span.classList.add("fa-trash-alt");
		if(even){
			// delete_span.classList.add("text-light");
		}else{
			// delete_span.classList.add("text-dark");
		}
		delete_span.innerHTML='&nbsp;';
		delete_button.appendChild(delete_span);
		delete_th.appendChild(delete_button);
		row.appendChild(delete_th);

		return row;
	}

	function form_render_float(label,json,rownum,parentType){

		var min = null;
		var max = null;
		var info = null;
		var field_name = null;
		var placeholder = null;

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'min':
					min = ""+parseInt(json[k])+"";
					break;
				case 'max':
					max = ""+parseInt(json[k])+"";
					break;
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				case 'placeholder':
					placeholder = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var span_main = null;

		switch(parentType){
			case 'dynamic-multi-table':
				span_main = document.createElement('td');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.rownum=rownum;
				input_element.field_type = 'float';
				input_verify(input_element, input_verify_float, min, max);

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				var info_txt = "";
				if(info){
					info_txt += info;
				}
				if(min||max){
					info_txt += " (";
				}
				if(min){
					info_txt += "Min: "+min+"";
				}
				if(min&&max){
					info_txt += " - ";
				}
				if(max){
					info_txt += "Max: "+max+"";
				}
				if(min||max){
					info_txt += ") ";
				}
				if(info||min||max){
					input_element.title = info_txt;
				}

				span_main.appendChild(input_element);

				break;
			case 'dynamic-multi-card':
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var element_label = document.createElement('label');
				element_label.classList.add('col-sm');
				element_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	element_label.classList.add('text-light');
				// }
				element_label.textContent=label;
				span_main.appendChild(element_label);

				var input_div = document.createElement('div');
				input_div.classList.add('col-sm-12');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.rownum=rownum;
				input_element.field_type = 'float';
				input_verify(input_element, input_verify_float, min, max);
				input_div.appendChild(input_element);

				span_main.appendChild(input_div);
				var info_small = document.createElement('small');
				info_small.classList.add('form-text');
				info_small.classList.add('text-muted');

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				if(info){
					info_small.append(info);
				}
				if(min||max){
					info_small.append(" (");
				}
				if(min){
					info_small.append("Min: "+min+"");
				}
				if(min&&max){
					info_small.append(" - ");
				}
				if(max){
					info_small.append("Max: "+max+"");
				}
				if(min||max){
					info_small.append(") ");
				}
				if(info||min||max){
					input_div.appendChild(info_small);
				}
				break;
			default:
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var element_label = document.createElement('label');
				element_label.classList.add('col-sm-3');
				element_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	element_label.classList.add('text-light');
				// }
				element_label.textContent=label;
				span_main.appendChild(element_label);

				var input_div = document.createElement('div');
				input_div.classList.add('col-sm-9');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.field_type = 'float';
				input_verify(input_element, input_verify_float, min, max);
				input_div.appendChild(input_element);

				span_main.appendChild(input_div);
				var info_small = document.createElement('small');
				info_small.classList.add('form-text');
				info_small.classList.add('text-muted');

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				if(info){
					info_small.append(info);
				}
				if(min||max){
					info_small.append(" (");
				}
				if(min){
					info_small.append("Min: "+min+"");
				}
				if(min&&max){
					info_small.append(" - ");
				}
				if(max){
					info_small.append("Max: "+max+"");
				}
				if(min||max){
					info_small.append(") ");
				}
				if(info||min||max){
					input_div.appendChild(info_small);
				}
				break;
		}
		return span_main;
	}

	function form_render_int(label,json,rownum,parentType){

		var min = null;
		var max = null;
		var info = null;
		var field_name = null;
		var placeholder = null;

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'field_type':
					break;
				case 'min':
					min = ""+parseInt(json[k])+"";
					break;
				case 'max':
					max = ""+parseInt(json[k])+"";
					break;
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				case 'placeholder':
					placeholder = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var span_main = document.createElement('div');

		switch(parentType){
			case 'dynamic-multi-table':
				span_main = document.createElement('td');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.rownum=rownum;
				input_element.field_type = 'int';
				input_verify(input_element, input_verify_int, min, max);

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				var info_txt = "";
				if(info){
					info_txt += info;
				}
				if(min||max){
					info_txt += " (";
				}
				if(min){
					info_txt += "Min: "+min+"";
				}
				if(min&&max){
					info_txt += " - ";
				}
				if(max){
					info_txt += "Max: "+max+"";
				}
				if(min||max){
					info_txt += ") ";
				}
				if(info||min||max){
					input_element.title = info_txt;
				}

				span_main.appendChild(input_element);
				
				break;

			case 'dynamic-multi-card':
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var element_label = document.createElement('label');
				element_label.classList.add('col-sm');
				element_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	element_label.classList.add('text-light');
				// }
				element_label.textContent=label;
				span_main.appendChild(element_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-12');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.rownum=rownum;
				input_element.field_type = 'int';
				input_verify(input_element, input_verify_int, min, max);
				input_div.appendChild(input_element);

				span_main.appendChild(input_div);
				var info_small = document.createElement('small');
				info_small.classList.add('form-text');
				info_small.classList.add('text-muted');

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				if(info){
					info_small.append = info;
				}
				if(min||max){
					info_small.append(" (");
				}
				if(min){
					info_small.append("Min: "+min+"");
				}
				if(min&&max){
					info_small.append(" - ");
				}
				if(max){
					info_small.append("Max: "+max+"");
				}
				if(min||max){
					info_small.append(") ");
				}
				if(info||min||max){
					input_div.appendChild(info_small);
				}
				break;

			default:
				span_main = document.createElement('div');
				span_main.classList.add('row');
				span_main.classList.add('form-group');
				var element_label = document.createElement('label');
				element_label.classList.add('col-sm-3');
				element_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	element_label.classList.add('text-light');
				// }
				element_label.textContent=label;
				span_main.appendChild(element_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-9');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				// input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.field_type = 'int';
				input_verify(input_element, input_verify_int, min, max);
				input_div.appendChild(input_element);

				span_main.appendChild(input_div);
				var info_small = document.createElement('small');
				info_small.classList.add('form-text');
				info_small.classList.add('text-muted');

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				if(info){
					info_small.append = info;
				}
				if(min||max){
					info_small.append(" (");
				}
				if(min){
					info_small.append("Min: "+min+"");
				}
				if(min&&max){
					info_small.append(" - ");
				}
				if(max){
					info_small.append("Max: "+max+"");
				}
				if(min||max){
					info_small.append(") ");
				}
				if(info||min||max){
					input_div.appendChild(info_small);
				}
				break;
		}
		return span_main;
	}

	function form_render_percent(label,json,rownum,parentType){

		var min = null;
		var max = null;
		var info = null;
		var field_name = null;
		var placeholder = null;

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'min':
					min = ""+parseInt(json[k])+"";
					break;
				case 'max':
					max = ""+parseInt(json[k])+"";
					break;
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				case 'placeholder':
					placeholder = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var span_main = null;

		switch(parentType){
			case 'dynamic-multi-table':
				span_main = document.createElement('td');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.rownum=rownum;
				input_element.field_type = 'percent';
				input_verify(input_element, input_verify_percent, min, max);

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				var info_txt = "";
				if(info){
					info_txt += info;
				}
				if(min||max){
					info_txt += " (";
				}
				if(min){
					info_txt += "Min: "+min+"";
				}
				if(min&&max){
					info_txt += " - ";
				}
				if(max){
					info_txt += "Max: "+max+"";
				}
				if(min||max){
					info_txt += ") ";
				}
				if(info||min||max){
					input_element.title = info_txt;
				}

				span_main.appendChild(input_element);

				break;
			case 'dynamic-multi-card':
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var element_label = document.createElement('label');
				element_label.classList.add('col-sm');
				element_label.classList.add('col-form-label');
				element_label.textContent=label;
				span_main.appendChild(element_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-12');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.rownum=rownum;
				input_element.field_type = 'percent';
				input_verify(input_element, input_verify_percent, min, max);
				input_div.appendChild(input_element);

				span_main.appendChild(input_div);
				var info_small = document.createElement('small');
				info_small.classList.add('form-text');
				info_small.classList.add('text-muted');

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				if(info){
					info_small.append(info);
				}
				if(min||max){
					info_small.append(" (");
				}
				if(min){
					info_small.append("Min: "+min+"");
				}
				if(min&&max){
					info_small.append(" - ");
				}
				if(max){
					info_small.append("Max: "+max+"");
				}
				if(min||max){
					info_small.append(") ");
				}
				if(info||min||max){
					input_div.appendChild(info_small);
				}
				break;
			default:
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var element_label = document.createElement('label');
				element_label.classList.add('col-sm-3');
				element_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	element_label.classList.add('text-light');
				// }
				element_label.textContent=label;
				span_main.appendChild(element_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-9');

				var input_element = document.createElement('input');
				input_element.classList.add('form-control');
				input_element.name = field_name;
				input_element.field_type = 'percent';
				input_verify(input_element, input_verify_percent, min, max);
				input_div.appendChild(input_element);

				span_main.appendChild(input_div);
				var info_small = document.createElement('small');
				info_small.classList.add('form-text');
				info_small.classList.add('text-muted');

				if(placeholder){
					input_element.placeholder=placeholder;
				}
		
				if(info){
					info_small.append(info);
				}
				if(min||max){
					info_small.append(" (");
				}
				if(min){
					info_small.append("Min: "+min+"");
				}
				if(min&&max){
					info_small.append(" - ");
				}
				if(max){
					info_small.append("Max: "+max+"");
				}
				if(min||max){
					info_small.append(") ");
				}
				if(info||min||max){
					input_div.appendChild(info_small);
				}
				break;
		}
		return span_main;
	}

	function form_render_radio(label,json,rownum,parentType){
		var default_value = null;
		var info = null;
		var field_name = null;

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'default':
					default_value = json[k];
					break;
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var span_main = null;

		switch(parentType){
			case 'dynamic-multi-table':
				return form_render_select(label,json,rownum,parentType);
				break;

			case 'dynamic-multi-card':
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var span_label = document.createElement('label');
				span_label.classList.add('col-sm');
				span_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	span_label.classList.add('text-light');
				// }
				span_label.textContent=label;
				span_main.appendChild(span_label);

				var div_main = document.createElement('div');
				div_main.classList.add('col-sm-12');
				var i = 0;
				json.values.forEach(function(k){
					var div_element = document.createElement('div');
					div_element.classList.add('form-check');
					div_element.classList.add('form-check-inline');
						var input_element = document.createElement('input');
						input_element.classList.add('form-check-input');
						input_element.type = 'radio';
						input_element.name = field_name;
						input_element.rownum=rownum;
						input_element.field_type = 'radio';
						input_element.value = k;
						if(k===default_value){
							input_element.checked = true;
						}
						div_element.appendChild(input_element);

						var label_element = document.createElement('label');
						label_element.classList.add('form-check-label');
						label_element.textContent=""+k+"";
						div_element.appendChild(label_element);
					div_main.appendChild(div_element);
				});
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					span_main.appendChild(info_small);
				}
				span_main.appendChild(div_main);
				break;

			default:
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');
				var span_label = document.createElement('label');
				span_label.classList.add('col-sm-3');
				span_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	span_label.classList.add('text-light');
				// }
				span_label.textContent=label;
				span_main.appendChild(span_label);

				var div_main = document.createElement('div');
				div_main.classList.add('col-sm-9');
				var i = 0;
				json.values.forEach(function(k){
					var div_element = document.createElement('div');
					div_element.classList.add('form-check');
					div_element.classList.add('form-check-inline');
						var input_element = document.createElement('input');
						input_element.classList.add('form-check-input');
						input_element.type = 'radio';
						input_element.name = field_name;
						input_element.field_type = 'radio';
						input_element.value = k;
						if(k===default_value){
							input_element.checked = true;
						}
						div_element.appendChild(input_element);

						var label_element = document.createElement('label');
						label_element.classList.add('form-check-label');
						label_element.textContent=""+k+"";
						div_element.appendChild(label_element);
					div_main.appendChild(div_element);
				});
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					span_main.appendChild(info_small);
				}
				span_main.appendChild(div_main);
				break;
		}

		return span_main;
	}

	function form_render_select(label,json,rownum,parentType){
		var default_value = null;
		var info = null;
		var field_name = null;

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'default':
					default_value = json[k];
					break;
				case 'info':
					info = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var div_main = null;

		switch(parentType){
			case 'dynamic-multi-table':
				div_main = document.createElement('td');
				// assuming that it will be rendered in a table cell

				var span_select = document.createElement('select');
				span_select.name = field_name;
				span_select.classList.add('form-control');
				span_select.field_type = 'select';
				span_select.rownum = rownum;
				if(isBlank(default_value)){
					var input_element = document.createElement('option');
					input_element.textContent="";
					input_element.selected = true;
					span_select.appendChild(input_element);
				}
				json.values.forEach(function(k){
					var input_element = document.createElement('option');
					input_element.value = k;
					input_element.textContent=""+k+"";
					if(k===default_value){
						input_element.selected = true;
					}
					span_select.appendChild(input_element);
				});
				if(info){
					span_select.title=info;
				}
				div_main.appendChild(span_select);
				break;

			case 'dynamic-multi-card':
				div_main = document.createElement('div');
				div_main.classList.add('form-group');
				div_main.classList.add('row');
				var span_label = document.createElement('label');
				span_label.textContent=label;
				span_label.classList.add('col-sm');
				span_label.classList.add('col-form-label');
				div_main.appendChild(span_label);

				var div_select = document.createElement('div');
				div_select.classList.add('col-sm-12');
				div_select.classList.add('row');

				var span_select = document.createElement('select');
				span_select.name = field_name;
				span_select.classList.add('form-control');
				span_select.field_type = 'select';
				span_select.rownum=rownum;

				if(isBlank(default_value)){
					var input_element = document.createElement('option');
					input_element.textContent="";
					input_element.selected = true;
					span_select.appendChild(input_element);
				}

				json.values.forEach(function(k){
					var input_element = document.createElement('option');
					input_element.value = k;
					input_element.textContent=""+k+"";
					if(k===default_value){
						input_element.selected = true;
					}
					span_select.appendChild(input_element);
				});

				div_select.appendChild(span_select);
				div_main.appendChild(div_select);
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					div_main.appendChild(info_small);
				}
				break;

			default:
				div_main = document.createElement('div');
				div_main.classList.add('form-group');
				div_main.classList.add('row');
				var span_label = document.createElement('label');
				span_label.textContent=label;
				span_label.classList.add('col-sm-3');
				span_label.classList.add('col-form-label');
				div_main.appendChild(span_label);

				var div_select = document.createElement('div');
				div_select.classList.add('col-sm-9');
				div_select.classList.add('row');

				var span_select = document.createElement('select');
				span_select.name = field_name;
				span_select.classList.add('form-control');
				span_select.field_type = 'select';

				if(isBlank(default_value)){
					var input_element = document.createElement('option');
					input_element.textContent="";
					input_element.selected = true;
					span_select.appendChild(input_element);
				}

				json.values.forEach(function(k){
					var input_element = document.createElement('option');
					input_element.value = k;
					input_element.textContent=""+k+"";
					if(k===default_value){
						input_element.selected = true;
					}
					span_select.appendChild(input_element);
				});

				div_select.appendChild(span_select);
				div_main.appendChild(div_select);
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					div_main.appendChild(info_small);
				}
				break;
		}

		return div_main;
	}

	function form_render_string(label,json,rownum,parentType){
		var default_value = null;
		var info = null;
		var regex = null;
		var field_name = null;
		var placeholder = null;

		// input_verify_regex

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'default':
					default_value = json[k];
					break;
				case 'info':
					info = json[k];
					break;
				case 'regex':
					regex = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				case 'placeholder':
					placeholder = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var span_main = null;

		switch(parentType){
			case 'dynamic-multi-table':
				span_main = document.createElement('td');
				var input_string = document.createElement('input');
				input_string.name = field_name;
				input_string.classList.add('form-control');
				input_string.rownum=rownum;
				input_string.field_type = 'string';

				if(placeholder){
					input_string.placeholder=placeholder;
				}
		
				if(info){
					input_string.title=info;
				}
				if(regex){
					input_verify(input_string, input_verify_regex, regex, null);
				}
				span_main.appendChild(input_string);
				break;

			case 'dynamic-multi-card':
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');

				var span_label = document.createElement('label');
				span_label.classList.add('col-sm');
				span_label.classList.add('col-form-label');
				span_label.textContent=label;
				span_main.appendChild(span_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-12');

				var input_string = document.createElement('input');
				input_string.classList.add('col-sm-12');
				input_string.name = field_name;
				input_string.rownum=rownum;
				input_string.field_type = 'string';
				if(regex){
					input_verify(input_string, input_verify_regex, regex, null);
				}
				input_div.appendChild(input_string);
				
				if(placeholder){
					input_string.placeholder=placeholder;
				}
		
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					input_div.appendChild(info_small);
				}
				span_main.appendChild(input_div);
				break;

			default:
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');

				var span_label = document.createElement('label');
				span_label.classList.add('col-sm-3');
				span_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	span_label.classList.add('text-light');
				// }
				span_label.textContent=label;
				span_main.appendChild(span_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-9');

				var input_string = document.createElement('input');
				input_string.classList.add('col-sm-12');
				input_string.name = field_name;
				input_string.field_type = 'string';
				if(regex){
					input_verify(input_string, input_verify_regex, regex, null);
				}
				input_div.appendChild(input_string);
				
				if(placeholder){
					input_string.placeholder=placeholder;
				}
		
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					input_div.appendChild(info_small);
				}
				span_main.appendChild(input_div);
				break;
		}
		return span_main;
	}

	function form_render_date(label,json,rownum,parentType){
		var default_value = null;
		var info = null;
		var regex = null;
		var field_name = null;

		// input_verify_regex

		Object.keys(json).forEach(function(k){
			switch(k){
				case 'default':
					default_value = json[k];
					break;
				case 'info':
					info = json[k];
					break;
				case 'regex':
					regex = json[k];
					break;
				case 'field_name':
					field_name = json[k];
					break;
				default:
					break;
			}
		});
		
		if(isBlank(field_name)){
			field_name=label;
		}

		var span_main = null;

		switch(parentType){
			case 'dynamic-multi-table':
				span_main = document.createElement('td');
				var input_string = document.createElement('input');
				input_string.type='date';
				input_string.name = field_name;
				input_string.classList.add('form-control');
				input_string.rownum=rownum;
				input_string.field_type = 'date';
				if(info){
					input_string.title=info;
				}
				if(regex){
					input_verify(input_string, input_verify_regex, regex, null);
				}
				span_main.appendChild(input_string);
				break;

			case 'dynamic-multi-card':
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');

				var span_label = document.createElement('label');
				span_label.classList.add('col-sm');
				span_label.classList.add('col-form-label');
				span_label.textContent=label;
				span_main.appendChild(span_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-12');

				var input_string = document.createElement('input');
				input_string.type='date';
				input_string.classList.add('col-sm-12');
				input_string.name = field_name;
				input_string.rownum=rownum;
				input_string.field_type = 'date';
				if(regex){
					input_verify(input_string, input_verify_regex, regex, null);
				}
				input_div.appendChild(input_string);
				
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					input_div.appendChild(info_small);
				}
				span_main.appendChild(input_div);
				break;

			default:
				span_main = document.createElement('div');
				span_main.classList.add('form-group');
				span_main.classList.add('row');

				var span_label = document.createElement('label');
				span_label.classList.add('col-sm-3');
				span_label.classList.add('col-form-label');
				// if(rownum%2==0){
				// 	span_label.classList.add('text-light');
				// }
				span_label.textContent=label;
				span_main.appendChild(span_label);

				var input_div = document.createElement('div');
				input_div.classList.add('row');
				input_div.classList.add('col-sm-9');

				var input_string = document.createElement('input');
				input_string.type='date';
				input_string.classList.add('col-sm-12');
				input_string.name = field_name;
				input_string.field_type = 'date';
				if(regex){
					input_verify(input_string, input_verify_regex, regex, null);
				}
				input_div.appendChild(input_string);
				
				if(info){
					var info_small = document.createElement('small');
					info_small.textContent=info;
					input_div.appendChild(info_small);
				}
				span_main.appendChild(input_div);
				break;
		}
		return span_main;
	}

//////////////////////////////////////////////////////////////
/////////////////// Settings Settings ////////////////////////
//////////////////////////////////////////////////////////////

	function settings_load(){
		var send_params = {};
		send_params['_object'] = 'settings';
		send_params['_action'] = 'get';
		api_get(send_params, settings_load_callback);
	}

	function settings_load_callback(status, endpoint, return_message, original_data, headers){
		if(return_message.length > 0){
			// console.log(headers);
			var settings = JSON.parse(return_message);
			// console.log(JSON.parse(return_message));
			for(key in settings.settings){
				switch(key){
					case 'view':
						break;
					default:
						main_data.globals[key]=settings.settings[key];
						break;
				}
			}
			vue_instance.page_title_set();
		}
	}

	function settings_save(){
		var send_params = {
			'endpoint': 'settings_save',
			'data': JSON.stringify(main_data)
		};
		api_post(send_params, log_callback);
	}
	
	function settings_save_callback(status, endpoint, return_message, original_data, headers){
		// Maybe do error checking here.
	}
	
//////////////////////////////////////////////////////////////
/////////////////// Login Functions //////////////////////////
//////////////////////////////////////////////////////////////

	function api_login(user, pass){
		if(!isBlank(main_data.login.un) && !isBlank(main_data.login.pass)){
			var myJSON = {"_object":"auth","_action":"login","_source":main_data.login.method,"email":main_data.login.un,"password":main_data.login.pass};
			api_get(myJSON,api_login_callback);
		}
	}

	function api_login_callback(status, endpoint, return_message, original_data, headers){
		switch(status){
			case 200:
				// log_display('info',JSON.stringify(return_message));
				var tmp_auth = JSON.parse(return_message);
				original_data = JSON.parse(original_data);
				// for (var property in tmp_auth) {
				// 	log('info',""+property+": "+tmp_auth[property]+"");
				// }
				if(tmp_auth.rc === true){
					main_data.auth_user.email = tmp_auth.email;
					main_data.auth_user.token = tmp_auth.token;
					main_data.auth_user.firstName = tmp_auth.given_name;
					main_data.auth_user.lastName = tmp_auth.family_name;
					// console.log(tmp_auth);
					main_data.auth_user.roles = tmp_auth.roles;
					if(main_data.auth_user.roles.includes('admin')){
						roles_get();
						dashboard_hosts_get();
						dashboard_users_get();
						dashboard_tags_get();
						app_token_get_all();
					}else{
						console.log("Not an Admin");
					}
					vue_instance.page_nav_set('dashboard');
					// main_data.view.main='dashboard';
					cache_set();
				} else {
					// console.log(tmp_auth);
					api_logout();
				}
				break;
			default:
				var return_message_json = JSON.parse(return_message);
				if(!isBlank(return_message_json.message)){
					log_display('error',"Error Logging In: "+return_message_json.message+"");
				}else{
					log_display('error',"Error Logging In");
				}
				log_callback(status, endpoint, return_message, original_data, headers);
				break;
		}
	}

	function login_display(method){
		switch(method){
			case 'local':
				main_data.login.method='local';
				break;
			case 'google':
				main_data.login.method='google';
				google_login_renderbutton();
				break;
			default:
				main_data.login.method='__null__';
				break;
		}
	}

	function api_logout(){
		var myJSON = {"_object":"auth","_action":"logout","_source":main_data.login.method};
		api_get(myJSON,api_logout_callback);
	}

	function api_logout_callback(status, endpoint, return_message, original_data, headers){
		return_message = JSON.parse(return_message);
		switch(return_message.status){
			case 'success':
				cache_clear();
				location.reload();
				break;
			default:
				cache_clear();
				location.reload();
				break;
		}
		
	}

	function google_login(googleUser) {
		if(main_data.login.method==='google'){
			var profile = googleUser.getBasicProfile();
			var gtoken = googleUser.getAuthResponse().id_token;
			var myJSON = {
					"_object":"auth",
					"_action":"login",
					// "_source":main_data.login.method,
					"_source":'google',
					"email":profile.getEmail(),
					"firstName":profile.getGivenName(),
					"lastName":profile.getFamilyName(),
					"img":profile.getImageUrl(),
					"token":gtoken,
				};
			// console.log(profile);
			// console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
			// console.log('Name: ' + profile.getName());
			// console.log('Image URL: ' + profile.getImageUrl());
			// console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
			api_get(myJSON,api_login_callback);
		}
	}

	function google_login_renderbutton() {
		gapi.signin2.render('google_auth_button', {
			'scope': 'profile email',
			'width': 240,
			'height': 50,
			'longtitle': true,
			'theme': 'dark',
			'onsuccess': google_login,
			'onfailure': api_logout
		});
	}

	function google_logout() {
		var auth2 = gapi.auth2.getAuthInstance();
		auth2.signOut().then(function () {
			console.log('User signed out.');
			cache_clear();
		});
	}

//////////////////////////////////////////////////////////////
///////////////////	Data Sorting /////////////////////////////
//////////////////////////////////////////////////////////////

	function gui_sort_cameras(){
		vue_instance.cameras.sort(sort_cameras);
		camera_reindex();
	}

	function sort_cameras(a,b){
		if (a.name < b.name)
			return -1;
		if (a.name > b.name)
			return 1;
		if (a.active && !b.active)
			return 1;
		if (!a.active && b.active)
			return -1;
		return 0;
	}

	function sort_by_hostname_asc(a,b){
		if (a.hostname.toLowerCase() < b.hostname.toLowerCase())
			return -1;
		if (a.hostname.toLowerCase() > b.hostname.toLowerCase())
			return 1;
		return 0;
	}

	function sort_by_hostname_dsc(a,b){
		if (a.hostname.toLowerCase() < b.hostname.toLowerCase())
			return 1;
		if (a.hostname.toLowerCase() > b.hostname.toLowerCase())
			return -1;
		return 0;
	}

	function sort_reports_main(by){
		if(main_data.reports.main._sortBy === by){
			main_data.reports.main._sortAsc = !main_data.reports.main._sortAsc;
		}
		main_data.reports.main._sortBy = by;
		switch(by){
			case 'count':
				main_data.reports.main.data.sort(sort_reports_main_count);
				break;
			case 'path':
			default:
				main_data.reports.main.data.sort(sort_reports_main_path);
				break;
		}
		
	}

	function sort_reports_main_path(a,b){
		if(main_data.reports.main._sortAsc){
			if (a.path < b.path)
				return -1;
			if (a.path > b.path)
				return 1;
			if (a.count > b.count)
				return 1;
			if (a.count < b.count)
				return -1;
		}else{
			if (a.path < b.path)
				return 1;
			if (a.path > b.path)
				return -1;
			if (a.count > b.count)
				return -1;
			if (a.count < b.count)
				return 1;
		}
		return 0;
	}

	function sort_reports_main_count(a,b){
		if(main_data.reports.main._sortAsc){
			if (a.count > b.count)
				return 1;
			if (a.count < b.count)
				return -1;
			if (a.path < b.path)
				return -1;
			if (a.path > b.path)
				return 1;
		}else{
			if (a.count > b.count)
				return -1;
			if (a.count < b.count)
				return 1;
			if (a.path < b.path)
				return 1;
			if (a.path > b.path)
				return -1;
		}
		return 0;
	}

//////////////////////////////////////////////////////////////
///////////////////	Application Functions ////////////////////
//////////////////////////////////////////////////////////////
	function app_token_add(app_token){
		// var send_params = {};
		app_token['_object'] = 'apptoken';
		app_token['_action'] = 'create';
		api_post(app_token, app_token_add_callback);
	}

	function app_token_add_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		var tmp = main_data.app_tokens;
		main_data.app_tokens=return_message.data;
		tmp.forEach(function (appt){
			if(isBlank(appt.token)){
				main_data.app_tokens.push(appt);
			}
		});
		cache_set();
	}

	function app_token_delete(app_token){
		var send_params = {};
		send_params['_object'] = 'apptoken';
		send_params['_action'] = 'delete';
		send_params['token'] = app_token;
		api_delete(send_params, app_token_delete_callback);
	}

	function app_token_delete_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		var tmp = main_data.app_tokens;
		main_data.app_tokens=return_message.data;
		tmp.forEach(function (appt){
			if(isBlank(appt.token)){
				main_data.app_tokens.push(appt);
			}
		});
		cache_set();
	}

	function app_token_get_all(){
		var send_params = {};
		send_params['_object'] = 'apptoken';
		send_params['_action'] = 'all';
		api_get(send_params, app_token_get_all_callback);
	}

	function app_token_get_all_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.app_tokens=return_message.data;
		cache_set();
	}

	function dashboard_hosts_get(){
		var send_params = {};
		send_params['_object'] = 'dashboard';
		send_params['_action'] = 'hosts';
		api_get(send_params, dashboard_hosts_get_callback);
	}

	function dashboard_hosts_get_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.dashboard.hosts.data=return_message.data;
		main_data.dashboard.hosts.data.sort(sort_by_hostname_asc);
		cache_set();
	}

	function dashboard_tags_get(){
		var send_params = {};
		send_params['_object'] = 'dashboard';
		send_params['_action'] = 'tags';
		api_get(send_params, dashboard_tags_get_callback);
	}

	function dashboard_tags_get_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.dashboard.tags.data=return_message.data;
		cache_set();
	}

	function dashboard_users_get(){
		var send_params = {};
		send_params['_object'] = 'dashboard';
		send_params['_action'] = 'users';
		api_get(send_params, dashboard_users_get_callback);
	}

	function dashboard_users_get_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.dashboard.users.data=return_message.data;
		cache_set();
	}

	function roles_get(){
		var send_params = {};
		send_params['_object'] = 'roles';
		send_params['_action'] = 'get';
		api_get(send_params, roles_get_callback);
	}

	function roles_get_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.roles.data=return_message.data;
		cache_set();
	}

	function report_main_get(){
		var send_params = {};
		send_params['_object'] = 'reports';
		send_params['_action'] = 'main';
		api_get(send_params, report_main_get_callback);
	}

	function report_main_get_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.reports.main.data=return_message.data;
		cache_set();
	}

	function report_host_get(hostname){
		var send_params = {};
		send_params['_object'] = 'reports';
		send_params['_action'] = 'host';
		send_params['hostname'] = hostname;
		main_data.reports.host.hostname = hostname;
		main_data.reports.host.data = [];
		api_get(send_params, report_host_get_callback);
	}

	function report_host_get_callback(status,endpoint,return_message,original_data,headers){
		return_message = JSON.parse(return_message);
		main_data.reports.host.data=return_message.data;
		cache_set();
	}

	function script_get(app_token_id,type){
		var send_params = {};
		send_params['_object'] = 'script';
		send_params['url'] = api_base_url;
		send_params['type'] = type;
		send_params['app_token_id'] = app_token_id;
		// api_get_file(endpoint, send_params, api_agent_download_callback);
		api_get(send_params, script_get_callback);
	}
	
	function script_get_callback(status,endpoint,return_message,original_data,headers){
		// console.log(original_data);
		// console.log(return_message);
		// console.log("sanity check---");
		// return false;
		return_message = JSON.parse(return_message);
		original_data = JSON.parse(original_data);
		var return_text = 'data:text/plain;charset=utf-16le;base64,';
		// var tmp_text = '\ufeff';
		var tmp_text = return_message.data;

		tmp_text = btoa(unescape(encodeURIComponent(tmp_text)));

		var download_link = document.createElement('a');

		download_link.setAttribute('href', return_text + '' + tmp_text);

		switch(original_data.type){
			case 'windows':
			case 'powershell':
			case 'win':
				download_link.setAttribute('download', 'Oracle_Java_Retirement_Search.ps1');
				break;
			default:
				download_link.setAttribute('download', 'Oracle_Java_Retirement_Search.sh');
				break;
		}
		download_link.style.display = 'none';
		document.body.appendChild(download_link);

		download_link.click();

		document.body.removeChild(download_link);

		return true;

		// window.open(return_message.data, '_blank');
	}

//////////////////////////////////////////////////////////////
///////////////////	<________________> ///////////////////////
//////////////////////////////////////////////////////////////

cache_get();
settings_load();
