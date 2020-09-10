Param(
	[string]$a#%apiurloverride%, 
	[string]$t#%AppAuthToken%, 
	[switch]$suppressFileSearch, 
	[switch]$v
)

clear
$version = "0.0.1"
Function captureOutput{
	param($path, $fileName, $params)
	cd "$path"
	$output = & cmd /c ".\$fileName $params 2>&1"
	return $output
}

Function Get-RemoteProgram {
	<#
	.Synopsis
	Generates a list of installed programs on a computer

	.DESCRIPTION
	This function generates a list by querying the registry and returning the installed programs of a local or remote computer.

	.NOTES   
	Name	   : Get-RemoteProgram
	Author	 : Jaap Brasser
	Version	: 1.5
	DateCreated: 2013-08-23
	DateUpdated: 2019-08-02
	Blog	   : http://www.jaapbrasser.com

	.LINK
	http://www.jaapbrasser.com

	.PARAMETER ComputerName
	The computer to which connectivity will be checked

	.PARAMETER Property
	Additional values to be loaded from the registry. Can contain a string or an array of string that will be attempted to retrieve from the registry for each program entry

	.PARAMETER IncludeProgram
	This will include the Programs matching that are specified as argument in this parameter. Wildcards are allowed. Both Include- and ExcludeProgram can be specified, where IncludeProgram will be matched first

	.PARAMETER ExcludeProgram
	This will exclude the Programs matching that are specified as argument in this parameter. Wildcards are allowed. Both Include- and ExcludeProgram can be specified, where IncludeProgram will be matched first

	.PARAMETER ProgramRegExMatch
	This parameter will change the default behaviour of IncludeProgram and ExcludeProgram from -like operator to -match operator. This allows for more complex matching if required.

	.PARAMETER LastAccessTime
	Estimates the last time the program was executed by looking in the installation folder, if it exists, and retrieves the most recent LastAccessTime attribute of any .exe in that folder. This increases execution time of this script as it requires (remotely) querying the file system to retrieve this information.

	.PARAMETER ExcludeSimilar
	This will filter out similar programnames, the default value is to filter on the first 3 words in a program name. If a program only consists of less words it is excluded and it will not be filtered. For example if you Visual Studio 2015 installed it will list all the components individually, using -ExcludeSimilar will only display the first entry.

	.PARAMETER SimilarWord
	This parameter only works when ExcludeSimilar is specified, it changes the default of first 3 words to any desired value.

	.PARAMETER DisplayRegPath
	Displays the registry path as well as the program name

	.PARAMETER MicrosoftStore
	Also queries the package list reg key, allows for listing Microsoft Store products for current user

	.EXAMPLE
	Get-RemoteProgram

	Description:
	Will generate a list of installed programs on local machine

	.EXAMPLE
	Get-RemoteProgram -ComputerName server01,server02

	Description:
	Will generate a list of installed programs on server01 and server02

	.EXAMPLE
	Get-RemoteProgram -ComputerName Server01 -Property DisplayVersion,VersionMajor

	Description:
	Will gather the list of programs from Server01 and attempts to retrieve the displayversion and versionmajor subkeys from the registry for each installed program

	.EXAMPLE
	'server01','server02' | Get-RemoteProgram -Property Uninstallstring

	Description
	Will retrieve the installed programs on server01/02 that are passed on to the function through the pipeline and also retrieves the uninstall string for each program

	.EXAMPLE
	'server01','server02' | Get-RemoteProgram -Property Uninstallstring -ExcludeSimilar -SimilarWord 4

	Description
	Will retrieve the installed programs on server01/02 that are passed on to the function through the pipeline and also retrieves the uninstall string for each program. Will only display a single entry of a program of which the first four words are identical.

	.EXAMPLE
	Get-RemoteProgram -Property installdate,uninstallstring,installlocation -LastAccessTime | Where-Object {$_.installlocation}

	Description
	Will gather the list of programs from Server01 and retrieves the InstallDate,UninstallString and InstallLocation properties. Then filters out all products that do not have a installlocation set and displays the LastAccessTime when it can be resolved.

	.EXAMPLE
	Get-RemoteProgram -Property installdate -IncludeProgram *office*

	Description
	Will retrieve the InstallDate of all components that match the wildcard pattern of *office*

	.EXAMPLE
	Get-RemoteProgram -Property installdate -IncludeProgram 'Microsoft Office Access','Microsoft SQL Server 2014'

	Description
	Will retrieve the InstallDate of all components that exactly match Microsoft Office Access & Microsoft SQL Server 2014

	.EXAMPLE
	Get-RemoteProgram -Property installdate -IncludeProgram '*[10*]*' | Format-Table -Autosize > MyInstalledPrograms.txt

	Description
	Will retrieve the ComputerName, ProgramName and installdate of the programs matching the *[10*]* wildcard and using Format-Table and redirection to write this output to text file

	.EXAMPLE
	Get-RemoteProgram -IncludeProgram ^Office -ProgramRegExMatch

	Description
	Will retrieve the InstallDate of all components that match the regex pattern of ^Office.*, which means any ProgramName starting with the word Office

	.EXAMPLE
	Get-RemoteProgram -DisplayRegPath

	Description
	Will retrieve list of programs from the local system and displays the registry path

	.EXAMPLE
	Get-RemoteProgram -DisplayRegPath -MicrosoftStore

	Description
	Will retrieve list of programs from the local system, while also retrieving Microsoft Store package and displaying the registry path
	#>
	[CmdletBinding(SupportsShouldProcess=$true)]
	param(
		[Parameter(ValueFromPipeline			  =$true,
				   ValueFromPipelineByPropertyName=$true,
				   Position=0
		)]
		[string[]]
			$ComputerName = $env:COMPUTERNAME,
		[Parameter(Position=0)]
		[string[]]
			$Property,
		[string[]]
			$IncludeProgram,
		[string[]]
			$ExcludeProgram,
		[switch]
			$ProgramRegExMatch,
		[switch]
			$LastAccessTime,
		[switch]
			$ExcludeSimilar,
		[switch]
			$DisplayRegPath,
		[switch]
			$MicrosoftStore,
		[int]
			$SimilarWord
	)

	begin {
		$RegistryLocation = 'SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\',
							'SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\'

		if ($psversiontable.psversion.major -gt 2) {
			$HashProperty = [ordered]@{}	
		} else {
			$HashProperty = @{}
			$SelectProperty = @('ComputerName','ProgramName')
			if ($Property) {
				$SelectProperty += $Property
			}
			if ($LastAccessTime) {
				$SelectProperty += 'LastAccessTime'
			}
		}
	}

	process {
		foreach ($Computer in $ComputerName) {
			try {
				$socket = New-Object Net.Sockets.TcpClient($Computer, 445)
				if ($socket.Connected) {
					'LocalMachine', 'CurrentUser' | ForEach-Object {
						$RegName = if ('LocalMachine' -eq $_) {
							'HKLM:\'
						} else {
							'HKCU:\'
						}

						if ($MicrosoftStore) {
							$MSStoreRegPath = 'Software\Classes\Local Settings\Software\Microsoft\Windows\CurrentVersion\AppModel\Repository\Packages\'
							if ('HKCU:\' -eq $RegName) {
								if ($RegistryLocation -notcontains $MSStoreRegPath) {
									$RegistryLocation = $MSStoreRegPath
								}
							}
						}
						
						$RegBase = [Microsoft.Win32.RegistryKey]::OpenRemoteBaseKey([Microsoft.Win32.RegistryHive]::$_,$Computer)
						$RegistryLocation | ForEach-Object {
							$CurrentReg = $_
							if ($RegBase) {
								$CurrentRegKey = $RegBase.OpenSubKey($CurrentReg)
								if ($CurrentRegKey) {
									$CurrentRegKey.GetSubKeyNames() | ForEach-Object {
										Write-Verbose -Message ('{0}{1}{2}' -f $RegName, $CurrentReg, $_)

										$DisplayName = ($RegBase.OpenSubKey("$CurrentReg$_")).GetValue('DisplayName')
										if (($DisplayName -match '^@{.*?}$') -and ($CurrentReg -eq $MSStoreRegPath)) {
											$DisplayName = $DisplayName  -replace '.*?\/\/(.*?)\/.*','$1'
										}

										$HashProperty.ComputerName = $Computer
										$HashProperty.ProgramName = $DisplayName
										
										if ($DisplayRegPath) {
											$HashProperty.RegPath = '{0}{1}{2}' -f $RegName, $CurrentReg, $_
										} 

										if ($IncludeProgram) {
											if ($ProgramRegExMatch) {
												$IncludeProgram | ForEach-Object {
													if ($DisplayName -notmatch $_) {
														$DisplayName = $null
													}
												}
											} else {
												$IncludeProgram | Where-Object {
													$DisplayName -notlike ($_ -replace '\[','`[')
												} | ForEach-Object {
														$DisplayName = $null
												}
											}
										}

										if ($ExcludeProgram) {
											if ($ProgramRegExMatch) {
												$ExcludeProgram | ForEach-Object {
													if ($DisplayName -match $_) {
														$DisplayName = $null
													}
												}
											} else {
												$ExcludeProgram | Where-Object {
													$DisplayName -like ($_ -replace '\[','`[')
												} | ForEach-Object {
														$DisplayName = $null
												}
											}
										}

										if ($DisplayName) {
											if ($Property) {
												foreach ($CurrentProperty in $Property) {
													$HashProperty.$CurrentProperty = ($RegBase.OpenSubKey("$CurrentReg$_")).GetValue($CurrentProperty)
												}
											}
											if ($LastAccessTime) {
												$InstallPath = ($RegBase.OpenSubKey("$CurrentReg$_")).GetValue('InstallLocation') -replace '\\$',''
												if ($InstallPath) {
													$WmiSplat = @{
														ComputerName = $Computer
														Query		= $("ASSOCIATORS OF {Win32_Directory.Name='$InstallPath'} Where ResultClass = CIM_DataFile")
														ErrorAction  = 'SilentlyContinue'
													}
													$HashProperty.LastAccessTime = Get-WmiObject @WmiSplat |
														Where-Object {$_.Extension -eq 'exe' -and $_.LastAccessed} |
														Sort-Object -Property LastAccessed |
														Select-Object -Last 1 | ForEach-Object {
															$_.ConvertToDateTime($_.LastAccessed)
														}
												} else {
													$HashProperty.LastAccessTime = $null
												}
											}

											if ($psversiontable.psversion.major -gt 2) {
												[pscustomobject]$HashProperty
											} else {
												New-Object -TypeName PSCustomObject -Property $HashProperty |
												Select-Object -Property $SelectProperty
											}
										}
										$socket.Close()
									}

								}

							}

						}
					}
				}
			} catch {
				Write-Error $_
			}
		}
	}
}

clear
$return_info = New-Object -TypeName psobject

$time = New-Object -TypeName psobject
$time | Add-Member -MemberType NoteProperty -Name Start -Value (Get-Date)

$script = New-Object -TypeName psobject
$script | Add-Member -MemberType NoteProperty -Name type -Value "PowerShell"
$script | Add-Member -MemberType NoteProperty -Name version -Value $version
$return_info | Add-Member -MemberType NoteProperty -Name script -Value $script 
$return_info | Add-Member -MemberType NoteProperty -Name _object -Value "ojr" 

$computerInfo		= Get-CimInstance Win32_ComputerSystem
$diskInfo			= Get-CimInstance Win32_LogicalDisk

$return_info | Add-Member -MemberType NoteProperty -Name hostname -Value $computerInfo.DNSHostName 
$return_info | Add-Member -MemberType NoteProperty -Name domain -Value $computerInfo.Domain 
$return_info | Add-Member -MemberType NoteProperty -Name Workgroup -Value $computerInfo.Workgroup 
$return_info | Add-Member -MemberType NoteProperty -Name HypervisorPresent -Value $computerInfo.HypervisorPresent 
#$return_info | Add-Member -MemberType NoteProperty -Name ComputerInfo -Value $computerInfo 
#$return_info | Add-Member -MemberType NoteProperty -Name DiskInfo -Value $diskInfo 

$java_registry = Get-RemoteProgram -IncludeProgram *java* -DisplayRegPath -Property DisplayVersion,VersionMajor,installlocation,Uninstallstring -LastAccessTime
$return_info | Add-Member -MemberType NoteProperty -Name search-registry -Value $java_registry 

$java_files = @()
#$java_files = New-Object -TypeName psobject
if(!$suppressFileSearch){
	foreach($drive in $diskInfo){
		if($v){
			$drive.Caption
		}
		switch($drive.MediaType){
		   12 {
				#"SEARCH -> "+$drive.Caption
				$path = $drive.Caption+"\"
				$java_files_tmp = Get-Childitem –Path $path -Include java.exe -Recurse -ErrorAction SilentlyContinue
				foreach($file in $java_files_tmp){
					$jfile = New-Object -TypeName psobject
					$jfile | Add-Member -MemberType NoteProperty -Name file -Value $file.FullName
					$jfile | Add-Member -MemberType NoteProperty -Name BuiltinVersionInfo -Value $file.VersionInfo
					$command = ''+$file.Name+''
					$path = ''+$file.Directory+''
					$params = '-version'
					$rtrnval = captureOutput -path $path -fileName $command -params $params
					$jfile | Add-Member -MemberType NoteProperty -Name "version-info" -Value $rtrnval
					$java_files+=$jfile
					#$java_files | Add-Member -MemberType NoteProperty -Name $file.FullName -Value $rtrnval
				}
				Continue
		   }
		   default {}
		}
	}
	$return_info | Add-Member -MemberType NoteProperty -Name search-file -Value $java_files 
}

$time | Add-Member -MemberType NoteProperty -Name End -Value (Get-Date)
$time | Add-Member -MemberType NoteProperty -Name Elapsed -Value $($time.End - $time.Start)
$return_info | Add-Member -MemberType NoteProperty -Name Time -Value $time 
cd $PSScriptRoot
$return_json = ($return_info | ConvertTo-Json -Depth 100 -Compress)
if($v){
	$return_info
}
if($a -ne ""){
	#$Cred = Get-Credential
	#Invoke-RestMethod -Method 'Post' -Uri $a -Credential $Cred -Body $return_json -OutFile output_json.csv
	$headers = @{}
	if($t -ne ""){
		$headers = @{
			'Content-Type' = 'application/json'
			'Authorization' = 'Bearer '+$t+''
		}
	}else{
		$headers = @{
			'Content-Type' = 'application/json'
		}
	}
	# Invoke-RestMethod -Method 'Post' -Headers $headers -Uri $a -Body $return_json -OutFile output_json.csv
	Invoke-RestMethod -Method 'Post' -Headers $headers -Uri $a -Body $return_json 
}else{
	$return_json > Java.json
	$return_json
}

