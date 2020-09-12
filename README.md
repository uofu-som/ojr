# ojr
This is a tool to help in the discovery of Java on various machines, with the aim to bring organizations into compliance with the new licensing requirements with Oracle Java.

## Intro
As part of the Oracle Java Retirement commity we were trying to find tools and ways to identify Java in our computing environments. At first we were trying to agree on a common endpoint management system that could report on and remove Oracle Java from any and every computing environment. But with the diverse needs and cost obligations that exist we decided that writing scripts and deploying those scripts with whatever tool was currently in place, might be most beneficial. This could mean mass deploying the script through SCCM or even as simple as putting the script on a thumbdrive and manually running it on each computer.

Because of the complexity of Java and the various versions and flavors that exist we can't assume that every instance of Java is Oracle Java and even if it is Oracle Java we can't assume that it isn't already licensed either as part of another product or if the computer itself has been licensed. As such we've decided to first help with the identification of Java in our environments and this is what this tool is focused on helping out with.

## The Scripts
I've developed a PowerShell script and a Bash script that searches the filesystem and if applicable the registry for any versions of Java and then tries to get it's version information. I've also tried to gather a little information on the system that these scripts are running on so that I can debug and adapt the scripts for the different environments that they run in.

Running the scripts on a single computer is only the first step. In order to know what the java needs are, we need to collect that information centrally somewhere we can identify patterns and common Java locations. I've created a database application with an API and web frontend that can serve as a collection point for this information.

Because of security concerns, in the web interface you can create Application Tokens and then download the scripts with the Application Tokens already embedded. This is important because the API associated with the database application will not allow any subissions without a valid Application Token. These application tokens have a default expiration of 2 years but can be removed via the Web Interface to immediately disable them if you suspect they are being missused.

## The Project
The project is currently hosted in a Private Repository on GitHub. It is accompained by a docker-compose file. And the code is meant to run in a docker-based container. If you have Docker Desktop and would like to test it out in your own environment, it is as simple as checking out the repository, changing to it's directroy and running a 'docker-compose up' command. I've used this code and the images to deploy an instance in DCOS Mesosphere so I believe it should be possible to deploy in many diffenent container management systems as well.

After starting it up, access the web interface using a FQDN or IP address. The address typed into the browser will be used to embed into the script so that it knows where the API is to report back to. So beware of using localhost to download the scripts as the scripts will most like fail if run on another host.

When you first access the application instance it will ask you to create an admin user. This will only appear if there currently is no admin user in the system. (At the time of producing this tutorial that is the only user availabe. I'm currently working on User Management and is a feature yet to come.) Once you submit that form you will be presented the main Web application and you can login using the information you just supplied.

The first thing you'll need to do, is go to 'System Administration' and then click on 'App Token Management'. This is where you can create as many Application Tokens as you require. You can add custom tags to each application token for helping classify different subsets of computer deployments. Tags can also be added via the command line when deploying but the Tags associated with the token will be automatically be added when the report is submitted.

Currently, there is a rudimentary bashboard and one report. You won't see much of anything in there until you actually run the scripts on some machines. I have plans to further develop and add to these reports as this project advances.