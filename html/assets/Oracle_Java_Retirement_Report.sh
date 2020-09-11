#/bin/bash
clear; clear;
#%apiurloverride%
#%AppAuthToken%
out_file=
version='0.0.1'

t_start=$(date +"%s")

return_json="{"
return_json="${return_json}\"time_start_utc_sec\": \"${t_start}\","
return_json="${return_json}\"script\": {\"type\":\"bash\",\"version\":\"${version}\"},"
return_json="${return_json}\"_object\": \"ojr\","

read -r -d '' message_usage << EOM
Usage: $0 [-v | [-V] [-id <owner_identifier_string>] ]
	-a <url> :            Reporting URL to POST data
    -v :                  Version Info (Ignores other arguments and exits)
    -id | --owner_id :    Identifier string that will link this computer to an owner
                            Please use a University email address or OrgID to simplify
                            Identification
    -V :                  Verbose
    -o <file-name>:       Output to file named <file-name>
EOM

hostname=$(hostname)
verbose=false
owner_id=""

while [[ "$1" =~ ^- && ! "$1" == "--" ]]; do case $1 in
		-v | --version )
				echo "Version: $version"
				exit 0
				;;
		-V | --verbose )
				verbose=true
				;;
		-id | --owner_id )
				shift; 
				owner_id=$1
				;;
		-a | --apiurl )
				shift; 
				api_endpoint=$1
				;;
		-t | --authtoken )
				shift; 
				app_auth_token=$1
				;;
		-o | --outfile )
				shift; 
				out_file=$1
				;;
		*)
				echo Unrecognized option: $1 
				echo "$message_usage"
				exit 1
				;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi

if [ "$verbose" = true ] ; then
	echo HostName: $hostname
	echo Owner_id: $owner_id
	echo $@
fi

return_json="${return_json}\"tags\": ["

for ARG in "$@"
do
	case "$ARG" in
		Oracle | Java | Retirement)
			return_json="${return_json}\"$ARG\","
			if [ "$verbose" = true ] ; then
				echo Adding argument, $ARG as tag
			fi
			# exit 1
			;;
		*)
			return_json="${return_json}\"$ARG\","
			tags+=($ARG)
			if [ "$verbose" = true ] ; then
				echo Adding argument, $ARG as tag
			fi
	esac
done
return_json=${return_json%,}
return_json="${return_json}],"
return_json="${return_json}\"hostname\": \"${hostname}\","

if [ "$owner_id" != "" ]; then
	return_json="${return_json}\"owner_id\": \"${owner_id}\","
fi

return_json="${return_json}\"os-info\": {"
kernel_name=$(uname -s > /dev/stdout 2> /dev/stdout)
return_json="${return_json}\"kernel-name\": \"$(uname -s > /dev/stdout 2> /dev/stdout)\","
return_json="${return_json}\"nodename\": \"$(uname -n > /dev/stdout 2> /dev/stdout)\","
return_json="${return_json}\"kernel-release\": \"$(uname -r > /dev/stdout 2> /dev/stdout)\","
return_json="${return_json}\"kernel-version\": \"$(uname -v > /dev/stdout 2> /dev/stdout)\","
return_json="${return_json}\"machine\": \"$(uname -m > /dev/stdout 2> /dev/stdout)\","
return_json="${return_json}\"processor\": \"$(uname -p > /dev/stdout 2> /dev/stdout)\","
case $kernel_name in 
	[Dd]arwin)
	;;
	*)
		return_json="${return_json}\"hardware-platform\": \"$(uname -i > /dev/stdout 2> /dev/stdout)\","
		return_json="${return_json}\"os\": \"$(uname -o > /dev/stdout 2> /dev/stdout)\","
	;;
esac
return_json=${return_json%,}
return_json=${return_json}"},"

java_files=$(find / -name "java" -type f -exec ls -s {} \; -exec {} -version \; -exec echo "valid-> "{} \; 2> /dev/null | grep "valid->"  )

return_json="${return_json}\"search-file\": ["
while IFS= read -r line; do
	line=$(echo $line | sed -e 's/valid-> //g' )
	return_json="${return_json}{\"file\": \"${line}\", \"version-info\": ["
	# echo "# --> $line"
	cmdline=$(echo $line | sed -e 's/ /\\ /g' )
	info=$(eval "$cmdline -version" > /dev/stdout 2> /dev/stdout)
	while IFF= read -r fline; do
		fline=$(echo $fline | sed -e 's/"/\\"/g' )
		return_json="${return_json}\"${fline}\","
		# echo $fline
	done <<< "$info"
	return_json=${return_json%,}
	return_json=${return_json}"]},"
done <<< "$java_files"
return_json=${return_json%,}
return_json=${return_json}"],"

t_end=$(date +"%s")
t_e=`expr $t_end - $t_start`

return_json="${return_json}\"time_stop_utc_sec\": \"${t_end}\","
return_json="${return_json}\"time_elapsed_sec\": ${t_e}"
return_json=${return_json}"}"

# return_json='{"time_start_utc_sec": "1597707504","hostname": "CastleInTheSky.local","os-info": {"kernel-name": "Darwin","nodename": "CastleInTheSky.local","kernel-release": "19.5.0","kernel-version": "Darwin Kernel Version 19.5.0: Tue May 26 20:41:44 PDT 2020; root:xnu-6153.121.2~2/RELEASE_X86_64","machine": "x86_64","processor": "i386","hardware-platform": "uname: illegal option -- i usage: uname [-amnprsv]","os": "uname: illegal option -- o usage: uname [-amnprsv]"},"search-file": [{"file": "/Library/Java/JavaVirtualMachines/jdk1.8.0_144.jdk/Contents/Home/jre/bin/java", "version-info": ["java version \"1.8.0_144\"","Java(TM) SE Runtime Environment (build 1.8.0_144-b01)","Java HotSpot(TM) 64-Bit Server VM (build 25.144-b01, mixed mode)"]},{"file": "/Library/Java/JavaVirtualMachines/jdk1.8.0_144.jdk/Contents/Home/bin/java", "version-info": ["java version \"1.8.0_144\"","Java(TM) SE Runtime Environment (build 1.8.0_144-b01)","Java HotSpot(TM) 64-Bit Server VM (build 25.144-b01, mixed mode)"]},{"file": "/Library/Internet Plug-Ins/JavaAppletPlugin.plugin/Contents/Home/bin/java", "version-info": ["java version \"1.8.0_161\"","Java(TM) SE Runtime Environment (build 1.8.0_161-b12)","Java HotSpot(TM) 64-Bit Server VM (build 25.161-b12, mixed mode)"]},{"file": "/System/Library/Frameworks/JavaVM.framework/Versions/A/Commands/java", "version-info": ["java version \"1.8.0_144\"","Java(TM) SE Runtime Environment (build 1.8.0_144-b01)","Java HotSpot(TM) 64-Bit Server VM (build 25.144-b01, mixed mode)"]},{"file": "/System/Volumes/Data/Library/Java/JavaVirtualMachines/jdk1.8.0_144.jdk/Contents/Home/jre/bin/java", "version-info": ["java version \"1.8.0_144\"","Java(TM) SE Runtime Environment (build 1.8.0_144-b01)","Java HotSpot(TM) 64-Bit Server VM (build 25.144-b01, mixed mode)"]},{"file": "/System/Volumes/Data/Library/Java/JavaVirtualMachines/jdk1.8.0_144.jdk/Contents/Home/bin/java", "version-info": ["java version \"1.8.0_144\"","Java(TM) SE Runtime Environment (build 1.8.0_144-b01)","Java HotSpot(TM) 64-Bit Server VM (build 25.144-b01, mixed mode)"]},{"file": "/System/Volumes/Data/Library/Internet Plug-Ins/JavaAppletPlugin.plugin/Contents/Home/bin/java", "version-info": ["java version \"1.8.0_161\"","Java(TM) SE Runtime Environment (build 1.8.0_161-b12)","Java HotSpot(TM) 64-Bit Server VM (build 25.161-b12, mixed mode)"]},{"file": "/System/Volumes/Data/Users/everett/Library/Application Support/minecraft/runtime/jre-x64/jre.bundle/Contents/Home/bin/java", "version-info": ["java version \"1.8.0_74\"","Java(TM) SE Runtime Environment (build 1.8.0_74-b02)","Java HotSpot(TM) 64-Bit Server VM (build 25.74-b02, mixed mode)"]},{"file": "/System/Volumes/Data/Applications/Couchbase Server.app/Contents/Resources/couchbase-core/lib/cbas/runtime/bin/java", "version-info": ["openjdk version \"11.0.7\" 2020-04-14","OpenJDK Runtime Environment AdoptOpenJDK (build 11.0.7+10)","OpenJDK 64-Bit Server VM AdoptOpenJDK (build 11.0.7+10, mixed mode)"]},{"file": "/System/Volumes/Data/Applications/Xcode.app/Contents/SharedFrameworks/ContentDeliveryServices.framework/Versions/A/itms/java/bin/java", "version-info": ["openjdk version \"1.8.0-u131-b11\"","OpenJDK Runtime Environment (build 1.8.0-u131-b11-iTMSTransporter-b02)","OpenJDK 64-Bit Server VM (build 25.71-b02, mixed mode)"]},{"file": "/System/Volumes/Data/Applications/Tableau Desktop 2020.2.app/Contents/Plugins/jre/bin/java", "version-info": ["openjdk version \"1.8.0_242\"","OpenJDK Runtime Environment (AdoptOpenJDK)(build 1.8.0_242-202001282020-b08)","OpenJDK 64-Bit Server VM (AdoptOpenJDK)(build 25.242-b08, mixed mode)"]},{"file": "/Users/everett/Library/Application Support/minecraft/runtime/jre-x64/jre.bundle/Contents/Home/bin/java", "version-info": ["java version \"1.8.0_74\"","Java(TM) SE Runtime Environment (build 1.8.0_74-b02)","Java HotSpot(TM) 64-Bit Server VM (build 25.74-b02, mixed mode)"]},{"file": "/Applications/Couchbase Server.app/Contents/Resources/couchbase-core/lib/cbas/runtime/bin/java", "version-info": ["openjdk version \"11.0.7\" 2020-04-14","OpenJDK Runtime Environment AdoptOpenJDK (build 11.0.7+10)","OpenJDK 64-Bit Server VM AdoptOpenJDK (build 11.0.7+10, mixed mode)"]},{"file": "/Applications/Xcode.app/Contents/SharedFrameworks/ContentDeliveryServices.framework/Versions/A/itms/java/bin/java", "version-info": ["openjdk version \"1.8.0-u131-b11\"","OpenJDK Runtime Environment (build 1.8.0-u131-b11-iTMSTransporter-b02)","OpenJDK 64-Bit Server VM (build 25.71-b02, mixed mode)"]},{"file": "/Applications/Tableau Desktop 2020.2.app/Contents/Plugins/jre/bin/java", "version-info": ["openjdk version \"1.8.0_242\"","OpenJDK Runtime Environment (AdoptOpenJDK)(build 1.8.0_242-202001282020-b08)","OpenJDK 64-Bit Server VM (AdoptOpenJDK)(build 25.242-b08, mixed mode)"]}],"time_stop_utc_sec": "1597707747","time_elapsed_sec": 243}'

if [ "$api_endpoint" != "" ] ; then
	if [ "$app_auth_token" != "" ] ; then
		curl -s --header "Content-Type: application/json" \
		  --header "Authorization: Bearer ${app_auth_token}" \
		  --request POST \
		  --data "${return_json}" \
		  ${api_endpoint}
	else
		curl -s --header "Content-Type: application/json" \
		  --request POST \
		  --data "${return_json}" \
		  ${api_endpoint}
	fi
else
	verbose=true
fi

if [ "$out_file" != "" ] ; then
	echo $return_json > $out_file
fi

if [ "$verbose" = true ] ; then
	echo $return_json
fi

