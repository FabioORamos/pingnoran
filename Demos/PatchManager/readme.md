# Advanced Demo - Systems Manager in a Hybrid Environment (with a focus on patch manager)

In this advanced demo we will get the chance to experience AWS Systems Manager. 
The demo simulates a Hybrid AWS and On-premises environment - both using AWS.  

The demo consists of 5 stages, each implementing additional components of the architecture

- Stage 1 - Provision the environments
- Stage 2 - Configure AWS Based Managed Instances & fix missing SSM agent
- Stage 3 - Setup On-Prem Managed instances using Hybrid Activations
- Stage 4 - Configure Systems Manager Inventory & Patching
- Stage 5 - Verify and Demo Teardown

# Stage 1 - Provision the environments

Make sure you are logged into AWS and in `ap-southeast-2`. By the end of this stage we will have the AWS and Simulated On-premises environment running, including:

- A Windows, Centos and Ubuntu instance/server running in both VPCs
- A Jumpbox running in both.  


## Apply CloudFormation (CFN) Stack  

Before applying the stack below, make sure you have a SSH Keypair created and you have the .pem part downloaded to your local machine  

Click `Create Key pair`  
Give it a name   
For file format select `pem` (if you use windows and putty you can pick `ppk`, if you run windows and any WSL2 or other terminal apps which use standard eys pick `pem`)  
Click `Create Key Pair`  
it will download the `.pem` file to your local machine, keep this safe and in the same folder as you run any commands later.  

Wait for the stack to move into a `CREATE_COMPLETE` status.

## Use CloudFormation to create the Systems Manager Endpoints & Systems Manager Role  

Wait for the `SSMVPCE` template to move into a `CREATE_COMPLETE` status   

At this stage you have the `AWS` and Simulated `On-Premises` environment created.  


# Stage 2 - Configure AWS Based Managed Instances & fix missing SSM agent

To connect to Systems Manager instances need two things  
1) Connectivity to the systems manager endpoint (AWS Public Zone)  
2) Permisssions to interact with the endpoint.  

In this stage we will provide the AWS side instance permissions via an IAM role and diagnose any SSM issues which arise.  

## Attach Role and Verify Managed Instances  

Click `Instances`  
Click `Name` Column to sort by name  
Select AWS-CENTOS, `right click`, `instance settings`, Select `Attach/Replace Iam Role`  
Click dropdown and select role which contains `SSMInstanceProfile`  
Click `Apply`, then `Close`  
Select AWS-WIN, `right click`, `instance settings`, Select `Attach/Replace Iam Role`   
Click dropdown and select role which contains `SSMInstanceProfile`  
Click `Apply`, then `Close`  
Select AWS-UBUNTU, `right click`, `instance settings`, Select `Attach/Replace Iam Role`  
Click dropdown and select role which contains `SSMInstanceProfile`  
Click `Apply`, then `Close`  

To ensure the Instance are able to connect to the SSM Agent, you are going to restart them  

Select AWS-CENTOS, `right click`, `instance state`, Select `Reboot`  
Click `Yes, Reboot`  
Select AWS-WIN, `right click`, `instance state`, Select `Reboot`  
Click `Yes, Reboot`  
Select AWS-UBUNTU, `right click`, `instance state`, Select `Reboot`  
Click `Yes, Reboot`  

Now lets check systems manager  

Under `Instances & Nodes` click `Managed Instances`  
This will show any instances which have permissions to Systems manager & connectivity to systems manager  
Instances which have the agent and permissions register themselves to become `Managed Instances`  
You should see two instances `AWS-WIN` and `AWS-UBUNTU`  
Note you **DON'T** see `AWS-CENTOS`  

Many AMI's come with the agent installed ... ready to be used given connectivity and permissions. The CENTOS AMI used, is one which doesn't and thats the next thing to fix, by installing the agent.  


## Manually install the Systems Manager Agent on the CENTOS AWS Instance


You're going to be connecting to the `AWS-CENTOS` instance, via the `AWS-JUMPBOX`.
AWS Publish a guide for various different operating systems here https://aws.amazon.com/blogs/security/securely-connect-to-linux-instances-running-in-a-private-amazon-vpc/  
You need an SSH Agent running on your local machine with your Pingo SSH Key loaded.  
This means when you connect to the jumpbox, and then to the centos instance, the agent running on your machine can be used for authentication. It means you dont have to load the SSH key onto the jumpbox to use to connect to the AWS-CENTOS box.

For Windows - follow the instructions in the link above for Putty and Pageant  
For MacOS and linux make sure ssh-agent is running with   

``` eval `ssh-agent` ```  

In your terminal, run: 

`chmod 400 Pingo.pem` (if you are using macos or linux)  
`ssh-add -K Pingo.pem`

ssh -A ec2-user@THEDNSNAMEOFTHE_AWS_JUMPBOX (this will look something like ec2-34-228-229-225.compute-1.amazonaws.com )  

Answer yes to any identity verification. If you get an error here **be sure** you have used eval ssh-agent above AND added your ssh key  

This will connect you into the jumpbox, the `-A` means that it allows the authentication to be used for the `AWS-CENTOS` instance too. 
  
Select the AWS-CENTOS instance  
Copy the instance private IP into your clipboard, it should be 10.16.X.Y  

run `ssh centos@PRIVATEIP_OF_AWS-CENTOS`  
This will connect you into the `AWS-CENTOS` instance  

For CentOS the command to install the Systems Manager Agent is   

`sudo dnf install -y https://s3.us-east-1.amazonaws.com/amazon-ssm-us-east-1/latest/linux_amd64/amazon-ssm-agent.rpm`  
then run  
`sudo systemctl enable amazon-ssm-agent`  
`sudo systemctl start amazon-ssm-agent`  

The last step is to check that the instance has registred itself in systems manager  

Move to the systems manager console  
Click `Managed Instances` under `Instances & Nodes`  
Verify that the `AWS-CENTOS` instance is now visible in the list of managed instances, you should have a total of 3 now.

# Stage 3 - Setup On-Prem Managed instances using Hybrid Activations

## Connect to the Onprem JUMPBOX  

Move to the EC2 Console
Click `Instances`  
Select `Pingnoran-Jumpbox`  
Note down its public DNS name   
Select the `Pingnoran-WIN` instance and note down its private IP address  

Make sure you have completed the SSH-Agent or Pageant components from the previous stage  

run for macOS and Linux run  

`ssh -A -L 127.0.0.1:1234:Pingnoran-WIN-IP:3389 ec2-user@Pingnoran-JUMPBOXDNS`   

## Create a Managed Instances Activation  

Move to the systems manager console 
Click `Hybrid Activations` under `Instances & Nodes`  
Click `Create an Activation`  
For description enter `Pingnoran-ONPREM`  
for `instance limit` enter `10`  

Under IAM role this is where the permissions are defined which the instances essentially `get`, instead of AWS where EC2 assumes a role and uses that to communicate with Systems Manager.

With Hybrid activations - the activation gives the server the right to use this role which you specify here.

Leave as the default of `Create a system default command execution role that has the required permissions`  

You could optionally create an `Activation Expiry Date`, but for now just click `Create Activation`  
  
Note down the `Activation Code` and `Activation ID`.   

## Install the agent on the Pingnoran-CENTOS server  
  
Click `Instances`  
Select `Pingnoran-CENTOS`  
Note down the `Private IP`  

From the Pingnoran-JUMPBOX  
run  
`ssh centos@PRIVATE_IP_OF_Pingnoran-CENTOS`  

``` 
mkdir /tmp/ssm
curl https://s3.amazonaws.com/ec2-downloads-windows/SSMAgent/latest/linux_amd64/amazon-ssm-agent.rpm -o /tmp/ssm/amazon-ssm-agent.rpm
sudo dnf install -y /tmp/ssm/amazon-ssm-agent.rpm
sudo systemctl stop amazon-ssm-agent
sudo amazon-ssm-agent -register -code "activation-code" -id "activation-id" -region "ap-southeast-2"
sudo systemctl start amazon-ssm-agent

```

If you see any of these errors its fine  
Error occurred fetching the seelog config file path:  open /etc/amazon/ssm/seelog.xml: no such file or directory  
Initializing new seelog logger  
New Seelog Logger Creation Complete  
2020-07-25 23:35:19 ERROR error while loading server info%!(EXTRA *errors.errorString=Failed to load instance info from vault. RegistrationKey does not exist.)  


## Install the agent on the Pingnoran-UBUNTU server  

Select `Pingnoran-UBUNTU`  
Note down the `Private IP`  

From the Pingnoran-JUMPBOX  
run  
`ssh ubuntu@PRIVATE_IP_OF_Pingnoran-UBUNTU`  

```
mkdir /tmp/ssm
curl https://s3.amazonaws.com/ec2-downloads-windows/SSMAgent/latest/debian_amd64/amazon-ssm-agent.deb -o /tmp/ssm/amazon-ssm-agent.deb
sudo dpkg -i /tmp/ssm/amazon-ssm-agent.deb
sudo service amazon-ssm-agent stop
sudo amazon-ssm-agent -register -code "activation-code" -id "activation-id" -region "ap-southeast-2" 
sudo service amazon-ssm-agent start

```

These errors are fine  

Error occurred fetching the seelog config file path:  open /etc/amazon/ssm/seelog.xml: no such file or directory  
Initializing new seelog logger  
New Seelog Logger Creation Complete  
2020-07-25 23:40:33 ERROR error while loading server info%!(EXTRA *errors.errorString=Failed to load instance info from vault. RegistrationKey does not exist.)  

## Install the agent on the Pingnoran-WINDOWS server  

** this is a pretty complex part ... it does work but ONLY if you have done all the steps above **  
if any of this fails ... join https://techstudyslack.com and message `Adrian`  
  
Open the EC2 Console    
Click `Instances`  
select `Pingnoran-WIN` right click, click `Connect`  
Click `Get Password`  
Click `Choose File`  
Find and Select `Pingnoran`  
Click `Decrypt Password`  

Note down the `User name` and `Password`  
You wont be using the instance IP ... because you will be connecting via the jumpbox using a port forward via SSH  

Open your remote desktop client  
  for windows `mstsc`  
  for macOS istall microsoft remote desktop client from the app store  
  Linux ... find a remote desktop client  

With whatever client you choose  
Connect to  
`127.0.0.1` on port `1234`  

This is connecting via a forwarded port on your local machine `1234` through the jumpbox, and is being forwarded to the Pingnoran-WIN server.

Login using the `Username` and `Password` you noted down above **THIS MIGHT BE SLOW, ITS A t2.micro .... to keep it free **  
Answer `Yes` to any network prompts  

open https://learn-cantrill-labs.s3.amazonaws.com/aws-patch-manager/uninstall.ps1  
Select all, copy  
This is used to cleanup any previously installed or configured agent (if it exists)  


Click the Search icon on the bar at the bottom  
Type `PowerShell`  
locate , under apps, `Windows Powershell` right click, select `Run as Administrator`  
paste in the contents you copied above  

if powershell closes  
    Click the Search icon on the bar at the bottom  
    Type `PowerShell`  
    locate , under apps, `Windows Powershell` right click, select `Run as Administrator`  

Run the code below, line by line, replacing activation-code , actiovation-id with the values you noted down ealier  

```
$code = "activation-code"
$id = "activation-id"
$region = "us-east-1"
$dir = $env:TEMP + "\ssm"
New-Item -ItemType directory -Path $dir -Force
cd $dir
(New-Object System.Net.WebClient).DownloadFile("https://amazon-ssm-$region.s3.amazonaws.com/latest/windows_amd64/AmazonSSMAgentSetup.exe", $dir + "\AmazonSSMAgentSetup.exe")
Start-Process .\AmazonSSMAgentSetup.exe -ArgumentList @("/q", "/log", "install.log", "CODE=$code", "ID=$id", "REGION=$region") -Wait
Get-Content ($env:ProgramData + "\Amazon\SSM\InstanceData\registration")
Get-Service -Name "AmazonSSMAgent"
```


## Verify they appear in Systems Manager  

Verify you see all A4L and AWS Instances except the jumpboxes  


# Stage 4 - Configure Systems Manager Inventory & Patching

## Configure Inventory  

Go to the systems manager console  
Clic Inventory under `Instances and Nodes`  
Click `Setup Inventory`  
For Name `Pingnoran-INVENTORY`  
Select `Selecting all managed instances in this account`  
Schedule `Collected inventory data every 30 minutes`  
you could log to an S3 bucket, but were going to just click `Setup Inventory`  
 
Click `View Details`  
This creates an association ... running the job `AWS-GatherSoftwareInventory` on all managed instances every 30 minutes  
Click `Resources` tab ad you can see it as it runs on all managed instances  

for one which shows `success` click `view output`  
See execution summary ...  
Click the `X`  

Click on `Inventory` again under `instances and nodes`  
Scroll down  
Click one of the Instances   
Click on the `Inventory` tab ... information is now starting to populate from managed instances  


## CONFIGURE PATCHING CENTOS

Click `Patch Manager`  
Click `Configure Patching`  
Select `Select instances manually`  
Select Both Centos Instances  
Scroll Down to Patching Schedule  
At this stage you could define a schedule ... select `Schedule in a new Maintenance Window`  
and configure start times and dates, window duration... and that would be used every time  
Check `Use a CRON schedule builder`  
Check `Every 30 minutes`  
for Maintanance window name pick `Centos-every-30-mins`  
Select `Scan and install`  
Expand `Additional Settings`  

read this text at the bottom   
`If any instance you selected belongs to a patch group, Patch Manager patches your instances using the registered patch baseline of that patch group. If an instance is not configured to use a patch group, Patch Manager uses the default patch baseline for the operating system type of the instance.`  

Click `Configure Patching`  
Click `View Details`  

## CONFIGURE PATCHING UBUNTU  

Click `Patch Manager`  
Click `Configure Patching`  
Select `Select instances manually`  
Select Both Ubuntu Instances  
Scroll Down to Patching Schedule  
At this stage you could define a schedule ... select `Schedule in a new Maintenance Window`  
and configure start times and dates, window duration... and that would be used every time  
Check `Use a CRON schedule builder`  
Check `Every 30 minutes`  
for Maintanance window name pick `Ubuntu-every-30-mins`  
Select `Scan and install`  
Expand `Additional Settings`  

Click `Configure Patching`  
Click `View Details`  


## CONFIGURE PATCHING WIN  

Click `Patch Manager`  
Click `Configure Patching`  
Select `Select instances manually`   
Select Both Win Instances  
Scroll Down to Patching Schedule  
At this stage you could define a schedule ... select `Schedule in a new Maintenance Window`  
and configure start times and dates, window duration... and that would be used every time  
Check `Use a CRON schedule builder`  
Check `Every 30 minutes`  
for Maintanance window name pick `Win-every-30-mins`  
Select `Scan and install`  
Expand `Additional Settings`  


Click `Configure Patching`  
Click `View Details`  

# Stage 5 - Verify and Demo Teardown

Wait for one of the maintanance windows to finish  
Open the maintanamce windows console  

Click the `Windows` window  
Check `Next execution time`  
Wait for that time _note that its in UTC_  

Click on the `History` tab  
Select the item in history  
CLick `View Details`  
Select the Task Invocation ... click `view details`  
Pick one of the instanceID's, select it, click `view output`  
Expand output ....  
Verify that the process is working as expected  


## CLEANUP  

Delete the maintanance windows  
select each, and delete  

Click `State Manager` , chec the `Pingnoran-INVENTORY` associaton, click `Delete` and `Delete again`  

Click `Hybrid Actvations`, selecet it, Click `Delete`, Click `Delete Activations`  

open `Managed Instances`
Select each of the items starting with `mi-` clcik actions => `Deregister this managed instances`, then `Deregister`  


Open CloudFormation   
Select SSMVPCE stack, Click `Delete`, then `Delete Stack`   

Wait for this to finish deleting  
Select SSMBASE stack, Click `Delete`, then `Delete Stack`  
