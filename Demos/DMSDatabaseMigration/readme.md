# Advanced Demo - Migrating a database with the Database MIgration Service

In this advanced demo we will be migrating a simple web application (wordpress) from an on-premises environment into AWS. The on-premises environment is a virtual web server (simulated using EC2) and a self-managed MariaDB database server (also simulated via EC2). We will be migrating this into AWS and running the architecture on an EC2 webserver and RDS managed SQL database.  

This demo consists of 6 stages :-

- STAGE 1 : Provision the environment and review tasks
- STAGE 2 : Establish Private Connectivity Between the environments (VPC Peer)
- STAGE 3 : Create & Configure the AWS Side infrastructure (App and DB)
- STAGE 4 : Migrate Database & Cutover
- STAGE 5 : Cleanup the account

# STAGE 1 - Provision the environment and review tasks

## Infrastructure Creation

The DMSMigrationaInfra.ymal file was used to build the base lab infrastructure. The original lab created by Adrian Cantrill (https://github.com/acantril/learn-cantrill-io-labs/tree/master/aws-dms-database-migration), uses AMIs in the us-east-1. I've copied those AMIs to ap-southeast-2. 

You should take note of the `parameter` values 

- DBName
- DBPassword
- DBRootPassword
- DBUser

# STAGE 2 - Establish Private Connectivity Between the environments (VPC Peer)

## Create a VPC peer between On-Premises and AWS

Move to the VPC Console
Click on `Peering Connections` under `Virtual Private Cloud`  
Click `Create Peering Connection`  
for `Peering connection name tag` choose `A4L-ON-PREMISES-TO-AWS`  
for `VPC (Requester)` choose `onpremVPC`  
for `VPC (Accepter)` choose `awsVPC`  
Scroll down and click `Create Peering Connection`  
Click `OK`  
Select the peering connection, then click `Actions` and then `Accept Request`  
Click `Yes, Accept`  
Click `Close`  

## Create Routes on the On-premises side

Move to the route tabes console 
Locate the `onpremPublicRT` route table and select it using the checkbox.  
Click on the `Routes` Tab.  
You're going to add a route pointing at the AWS side networking, using the VPC Peer.  
Click `Edit Routes`  
Click `Add Route`  
For Destination enter `10.16.0.0/16`  
Click the `Target` dropdown & click `Peering Connection` and select the `A4L-ON-PREMISES-TO-AWS` then click `Save routes`  
Click `Close`  
The Onpremises network can now route to the AWS Network, but as data transfer requires bi-directional traffic flow, you need to do the same at the other side.


## Create Routes on the AWS side

Move to the route tabes console   
Locate the `awsPublicRT` route table and select it using the checkbox.  
Click on the `Routes` Tab.  
You're going to add a route pointing at the AWS side networking, using the VPC Peer.  
Click `Edit Routes`  
Click `Add Route`  
For Destination enter `192.168.10.0/24`  
Click the `Target` dropdown & click `Peering Connection` and select the `A4L-ON-PREMISES-TO-AWS` then click `Save routes`  
Click `Close`  

Move to the route tabes console 
Locate the `awsPrivateRT` route table and select it using the checkbox.  
Click on the `Routes` Tab.  
You're going to add a route pointing at the AWS side networking, using the VPC Peer.  
Click `Edit Routes`  
Click `Add Route`  
For Destination enter `192.168.10.0/24`  
Click the `Target` dropdown & click `Peering Connection` and select the `A4L-ON-PREMISES-TO-AWS` then click `Save routes`  
Click `Close`  

# STAGE 3 - Create & Configure the AWS Side infrastructure (App and DB)

## CREATE THE RDS INSTANCE

Move to the RDS Console 
Click `Subnet Groups`  
Click `Create DB Subnet Group`  
For `Name` call it `A4LDBSNGROUP`
enter the same for description
in the `VPC` dropdown, choose `awsVPC`  
Under availability zones, choose `ap-southeast-2a` and `ap-southeast-2b`  
for subnets check the box next to `10.16.32.0/20` which is privateA and `10.16.96.0/20` which is privateB  
Scroll down and click `Create`  

Click on `Databases`  
Click `create Database`  
Choose `Standard Create`  
Choose `MariaDB`
Choose `Free Tier` for `Templates`  

You will be using the same database names and credentials to keep things simple for this demo lesson, but note that in production this could be different.

for `DB instance identifier` enter `a4lwordpress`  
for `Master username` choose `a4lwordpress`  
for `Masterpassword` enter the DBPassword parameter for cloudformation which you noted down in stage of this demo
enter that same password in the `Confirm password` box  
Scroll down to `Connectivity`  
for `Virtual private cloud (VPC)` choose `awsVPC`  
expand `Additional connectivity configuration` 
make sure `Subnet Groups` is set toe `a4ldbsngroup`  
for `public access` choose `No`  
for `Existing VPC security groups` choose `***-awsSecurityGroupDB-***` (*** aren't important)  
remove the `Default` security group by clicking the `X`    
Scroll down and expand `Additional configuration`  
Under `Initial database name` enter `a4lwordpress`  
Scroll down and click `Create Database`  

This will take some time, and you cant continue to `Stage 4` until the database is in a ready state.

## CREATE THE EC2 INSTANCE

Move to the EC2 Console  
Click `Instances`  
Click `Launch Instances`  
Ensuring the architecture is set to `64-bit (x86)` click `Select` next to `Amazon Linux 2 AMI`  
Choose the `Free Tier eligable` instance (should be t2.micro or t3.micro)  
Click `Next: Configure Instance Details`  
For `network` pick `awsVPC`  
For `Subnet` pick `aws-PublicA`  
for `IAM Role` pick `***-awsInstanceProfile-****` (*** aren't important)  
Click `Next: Add Storage`  
Click `Next: Add Tags`  
Click `Add Tag`  
Key = `Name`  
Value = `awsCatWeb` 
Click `Next: Configure Security Group`  
Select `Select an existing security group`  
Check the box next to `***-awsSecurityGroupWeb-***` (*** aren't important)    
Click `Review and Launch`  
If you see any warning dialogue click `Continue`  
Click `Launch`  
Choose `Proceed without key pair`  
Check the `I acknowledge....` box  
Click `Launch Instances`  
Click `View Instances`  

Wait for the `awsCatWeb` instance to be in a `Running` state with `2/2 checks` before continuing.

## INSTALL WORDPRESS Requirements

Select the `awsCatWeb` instance, right click, `Connect`  
Select `Session Manager` and click `Connect`  
When connected type `sudo bash` to run a privileged bash shell
then update the instance with a `yum -y update` and wait for it to complete.  
Then install the apache web server with `yum -y install httpd mariadb`  (the mariadb part is for the mysql tools)
Then install php with `amazon-linux-extras install -y php7.2`  
then make sure apache is running and set to run at startup with 

```
systemctl enable httpd
systemctl start httpd
```

You now have a running apache web server with the ability to connect to the wordpress database (currently running onpremises).

## MIGRATE WORDPRESS Content over

You're going to edit the SSH config on this machine to allow password authentication on a temporary basis.  
You will use this to copy the wordpress data across to the awsCatWeb machine from the on-premises CatWeb Machine  

run a `nano /etc/ssh/sshd_config`  
locate `PasswordAuthentication no` and change to `PasswordAuthentication yes` , then `ctrl+o` to save and `ctrl+x` to exit.  
then set a password on the ec2-user user  
run a `passwd ec2-user` and enter the `DBPassword` you noted down at the start of the demo.  
**this is only temporary.. we're using the same password throughout the demo to make things easier and less prone to mistakes**

restart SSHD to make those changes with `service sshd restart`  


Return back to the EC2 console  
with the `awsCatWeb` instance selected, note down its `Private IPV4 Address` you will need this in a moment.  

Select the `CatWeb` instance, right click, `Connect`  
Select `Session Manager` and click `Connect`  
When connected type `sudo bash` to run a privileged bash shell  

move to the webroot folder by typing `cd /var/www/`  

run a `scp -rp html ec2-user@privateIPofawsCatWeb:/home/ec2-user` and answer `yes` to the authenticity warning.  
this will copy the wordpress local files from `CatWeb` (on-premises) to `awsCatWeb` (aws)

**now move back to the `CatWeb` server, if you dont have it open still, reconnect as per below**

Select the `awsCatWeb` instance, right click, `Connect`  
Select `Session Manager` and click `Connect`  
When connected type `sudo bash` to run a privileged bash shell

move to the `ec2-user` home folder by doing a `cd /home/ec2-user`  
then do an `ls -la` and you should see the html folder you just copied.  
`cd html`  
next copy all of these files into the webroot of `awsCatWeb` by doing a `cp * -R /var/www/html/`


## Fix Up Permissions & verify `awsCatWeb` works

run the following commands to enforce the correct permissions on the files you've just copied across

```
usermod -a -G apache ec2-user   
chown -R ec2-user:apache /var/www
chmod 2775 /var/www
find /var/www -type d -exec chmod 2775 {} \;
find /var/www -type f -exec chmod 0664 {} \;
```

Move to the EC2 running instances console https://console.aws.amazon.com/ec2/v2/home?region=us-east-1#Instances:  
Select the `awsCatWeb` instance  
copy down its `public IPv4 DNS` into your clipboard and open it in a new tab  
if working, this Web Instance (aws) is now loading using the on-premises database.

At this point you have a functional AWS based wordpress application instance. We have migrated content from the on-premises virtual machine (simulated) using SCP, and you have tested that its connects to the on-premises DB.

# STAGE 4 - Migrate Database & Cutover

## CREATE THE DMS SUBNET GROUP

Go to the DMS console
Click `Create Subnet Group`  
For Name and Description use `A4LDMSSNGROUP`
for `VPC` choose `awsVPC`  
for `Add Subnets` choose `aws-privateA` and `aws-privateB`  
Click `Create subnet group` 
 

## CREATE THE DMS REPLICATION INSTANCE

Move to the DMS Console  
Click `create Replication Instance`  
for name enter `A4LONPREMTOAWS`
use the same for `Description`  
for `Instance Class` choose `dms.t2.micro`  
for `VPC` choose `awsVPC`  
Uncheck `MultiAZ` and `Pubicly Accessing`  
Expand `Advanced security and network configuration`  
Ensure `a4ldmssngroup` is selected in `Replication subnet group`  
For `VPC security group(s)` choose `***-awsSecurityGroupDB-***`  
Click `Create`  

## CREATE THE DMS SOURCE ENDPOINT

Click `Create Endpoint`  
For `Endpoint type` choose `Source Endpoint`  
Under `Endpoint configuration` set `Endpoint identifier` to be `CatDBOnpremises`
Under `Source Engine` set `Mariadb`  
Under `Server Name` use the PrivateIPv4 address of `CatDB` (get it from EC2 console)  
Port `3306`  
Username `a4lwordpress`  
Password user the DBPassword you noted down in stage 1  
click `create endpoint`  

## CREATE THE DMS DESTINATION ENDPOINT (RDS)  

Click `Create Endpoint`  
For `Endpoint type` choose `Target Endpoint`  
Check `Select RDS DB Instance`  
Select `a4lwordpress` in the dropdown  
It will prepopulate the boxes  
For `Password` enter the DBPassword you noted down in stage1  
Scroll down and click `Create Endpoint`  

## Migrate

Move to migration tasks  
Click `Create task`  
for `Task identifier` enter `A4LONPREMTOAWSWORDPRESS`
for `Replication instance` pick the replication instance you just created  
for `Source database endpoint` pick `catdbonpremises`  
for `Target database endpoint` pick `a4lwordpress`  
for `Migration type` pick `migrate existing data` **you could pick and replicate changes here if this were a high volume production DB**  
for `Table mappings` pick `Guided UI`  
Click `Add new selection rule`  
in `Schema` box select `Enter a Schema`  
in `Schema Name` type `a4lwordpress`  
Scroll down and click `Create Task`  

THis starts the replication task and does a full load from `catdbonpremises` to the RDS Instance.  
It will create the task  
then start the task  
then it will be in the `Running` State until it moves into `Load complete`  

At this point the data has been migrated into the RDS instance  

## Cutover the application instance

Move to the RDS Console 
Click `Databases`  
Click `a4lwordpress`  
under `Endpoint & Port` copy the `endpoint` dns name into your clipboard  

Move to the EC2 console  
Select the `awsCatWeb` instance, right click, `Connect`  
Select `Session Manager` and click `Connect`  
When connected type `sudo bash` to run a privileged bash shell
run `cd /var/www/html`
run `nano wp-config.php`  
Locate the define DB_HOST line, and replace the IP address with the RDS Host you just copied down into you clipboard  
run `ctrl+o` to save and `ctrl+x` to exit.  

Run the script below, to update the wordpress database with the new instance DNS name

```
#!/bin/bash
source <(php -r 'require("/var/www/html/wp-config.php"); echo("DB_NAME=".DB_NAME."; DB_USER=".DB_USER."; DB_PASSWORD=".DB_PASSWORD."; DB_HOST=".DB_HOST); ')
SQL_COMMAND="mysql -u $DB_USER -h $DB_HOST -p$DB_PASSWORD $DB_NAME -e"
OLD_URL=$(mysql -u $DB_NAME -h $DB_HOST -p$DB_PASSWORD $DB_NAME -e 'select option_value from wp_options where option_id = 1;' | grep http)
HOST=$(curl http://169.254.169.254/latest/meta-data/public-hostname)
$SQL_COMMAND "UPDATE wp_options SET option_value = replace(option_value, '$OLD_URL', 'http://$HOST') WHERE option_name = 'home' OR option_name = 'siteurl';"
$SQL_COMMAND "UPDATE wp_posts SET guid = replace(guid, '$OLD_URL','http://$HOST');"
$SQL_COMMAND "UPDATE wp_posts SET post_content = replace(post_content, '$OLD_URL', 'http://$HOST');"
$SQL_COMMAND "UPDATE wp_postmeta SET meta_value = replace(meta_value,'$OLD_URL','http://$HOST');"

```

Move to the EC2 console and stop the `CatDB` and `CatWeb` instances.  
Select `awsCatWeb` and get its `Public IPv4 DNS` and open this in a new tab. It should still show the application. This is now pointing at the RDS instance after a full migration.

We have created a VPC peer between the simulated On-premises environment and AWS.
We have fully migrated the Wordpress application files from on-premises (simulated) into AWS.  
We have provisioned an RDS DB Instance and we have used DMS to perform a simple migration of the database from on-premises (simulated) to AWS.

## STAGE 5 : Cleanup the account

Delete all of the infrastructure created in the reverse order of creation.