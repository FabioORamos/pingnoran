# Advanced Demo - Web App - Single Server to Elastic Evolution

# Create the VPC stack

Login to an AWS account using a user with admin privileges and ensure your region is set to `ap-southeast-2` 

Run the PingoVPC.yaml file and wait for the STACK to move into the `CREATE_COMPLETE` state before continuing.

# Create Parameters in the Parameter Store

Storing configuration information within the SSM Parameter store scales much better than attempting to script them in some way.
In this sub-section we are going to create parameters to store the important configuration items for the platform you are building.  

## Create SSM Parameter Store values for wordpress

### Create Parameter - DBUser (the login for the specific wordpress DB)  
Click `Create Parameter`
Set Name to `/Pingo/Wordpress/DBUser`
Set Description to `Wordpress Database User`  
Set Tier to `Standard`  
Set Type to `String`  
Set Data type to `text`  
Set `Value` to `pingouser`  
Click `Create parameter`  

### Create Parameter - DBName (the name of the wordpress database)  
Click `Create Parameter`
Set Name to `/Pingo/Wordpress/DBName`
Set Description to `Wordpress Database Name`  
Set Tier to `Standard`  
Set Type to `String`  
Set Data type to `text`  
Set `Value` to `pingodb`  
Click `Create parameter` 

### Create Parameter - DBEndpoint (the endpoint for the wordpress DB .. )  
Click `Create Parameter`
Set Name to `/Pingo/Wordpress/DBEndpoint`
Set Description to `Wordpress Endpoint Name`  
Set Tier to `Standard`  
Set Type to `String`  
Set Data type to `text`  
Set `Value` to `localhost`  
Click `Create parameter`  

### Create Parameter - DBPassword (the password for the DBUser)  
Click `Create Parameter`
Set Name to `/Pingo/Wordpress/DBPassword`
Set Description to `Wordpress DB Password`  
Set Tier to `Standard`  
Set Type to `SecureString`  
Set `KMS Key Source` to `My Current Account`  
Leave `KMS Key ID` as default
Set `Value` to your chosen database password (make sure its complex)
Click `Create parameter`  

### Create Parameter - DBRootPassword (the password for the database root user, used for self-managed admin)  
Click `Create Parameter`
Set Name to `/Pingo/Wordpress/DBRootPassword`
Set Description to `Wordpress DBRoot Password`  
Set Tier to `Standard`  
Set Type to `SecureString`  
Set `KMS Key Source` to `My Current Account`  
Leave `KMS Key ID` as default
Set `Value` to your chosen database password (make sure its complex)
Click `Create parameter`  

## Create the Launch Template

In this stage we are going to create a launch template which can automate the build of WordPress.  
The architecture will use the single instance for both the WordPress application and database.  
Any level of automation/self-healing or scaling architecture will need a bootstrapped or AMI-baked build to function effectively.

Open the EC2 console   
Click `Launch Templates` under `Instances` on the left menu  
Click `Create Launch Template`  
Under `Launch Template Name` enter `Wordpress`  
Under `Templace version description` enter `Single server DB and App`  
Check the `Provide guidance to help me set up a template that I can use with EC2 Auto Scaling` box  

Under `Amazon machine image (AMI) - required` click and locate `Amazon Linux 2 AMI (HVM), SSD Volume Type, Architecture: 64-bit (x86)`  
Under `Instance Type` select `t2.micro` (or whichever is listed as free tier eligible)  
Under `Key pair (login)` select `Don't include in launch template`  
Under `networking Settings` make sure `Virtual Private Cloud (VPC)` is selected
Under `Security Groups` select `VPC-SGWordpress`  
Expand `Advanced Details`
Under `IAM instance profile` select `VPC-WordpressInstanceProfile`  
Under `T2/T3 Unlimited` select `Enable`  

### Add Userdata

At this point we need to add the configuration which will build the instance
Enter the user data below into the `User Data` box

```
#!/bin/bash -xe

DBPassword=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBPassword --with-decryption --query Parameters[0].Value)
DBPassword=`echo $DBPassword | sed -e 's/^"//' -e 's/"$//'`

DBRootPassword=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBRootPassword --with-decryption --query Parameters[0].Value)
DBRootPassword=`echo $DBRootPassword | sed -e 's/^"//' -e 's/"$//'`

DBUser=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBUser --query Parameters[0].Value)
DBUser=`echo $DBUser | sed -e 's/^"//' -e 's/"$//'`

DBName=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBName --query Parameters[0].Value)
DBName=`echo $DBName | sed -e 's/^"//' -e 's/"$//'`

DBEndpoint=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBEndpoint --query Parameters[0].Value)
DBEndpoint=`echo $DBEndpoint | sed -e 's/^"//' -e 's/"$//'`

yum -y update
yum -y upgrade

yum install -y mariadb-server httpd wget
amazon-linux-extras install -y lamp-mariadb10.2-php7.2 php7.2
amazon-linux-extras install epel -y
yum install stress -y

systemctl enable httpd
systemctl enable mariadb
systemctl start httpd
systemctl start mariadb

mysqladmin -u root password $DBRootPassword

wget http://wordpress.org/latest.tar.gz -P /var/www/html
cd /var/www/html
tar -zxvf latest.tar.gz
cp -rvf wordpress/* .
rm -R wordpress
rm latest.tar.gz

sudo cp ./wp-config-sample.php ./wp-config.php
sed -i "s/'database_name_here'/'$DBName'/g" wp-config.php
sed -i "s/'username_here'/'$DBUser'/g" wp-config.php
sed -i "s/'password_here'/'$DBPassword'/g" wp-config.php
sed -i "s/'localhost'/'$DBEndpoint'/g" wp-config.php

usermod -a -G apache ec2-user   
chown -R ec2-user:apache /var/www
chmod 2775 /var/www
find /var/www -type d -exec chmod 2775 {} \;
find /var/www -type f -exec chmod 0664 {} \;

echo "CREATE DATABASE $DBName;" >> /tmp/db.setup
echo "CREATE USER '$DBUser'@'localhost' IDENTIFIED BY '$DBPassword';" >> /tmp/db.setup
echo "GRANT ALL ON $DBName.* TO '$DBUser'@'localhost';" >> /tmp/db.setup
echo "FLUSH PRIVILEGES;" >> /tmp/db.setup
mysql -u root --password=$DBRootPassword < /tmp/db.setup
rm /tmp/db.setup


```

Ensure to leave a blank line at the end  
Click `Create Launch Template`  
Click `View Launch Templates`


### Launch an instance using it

Select the launch template in the list ... it should be called `Wordpress`  
Click `Actions` and `Launch instance from template`
Scroll down to `Network settings` and under `Subnet` select `sn-pub-A`  
Scroll to `Resource Tags` click `Add tag`
Set `Key` to `Name` and `Value` to `Wordpress-LT`
Scroll to the bottom and click `Launch Instance from template`  
Click the instance id in the `Success` box

## Test

Open the EC2 console  
Select the `Wordpress-LT` instance  
copy the `IPv4 Public IP` into your clipboard  
Open that IP in a new tab  
You should see the WordPress welcome page  

## Perform Initial Configuration and make a post

`Site Title` enter `Pingnoran`  
In `Username` enter the name stored on `DBUser`
In `Password` enter the name stored on `DBPassword`
In `Your Email` enter your email address  
Click `Install WordPress`
Click `Log In`  
In `Username or Email Address` enter `admin`  
In `Password` enter the previously noted down strong password 
Click `Log In`  

Play with site and add a few pages and images

This is your working, auto built WordPress instance
** don't terminate the instance this time - we're going to migrate the database in stage 3**

# This stage is finished

This configuration has several limitations :-

- ~~The application and database are built manually, taking time and not allowing automation~~ FIXED  
- ~~^^ it was slow and annoying ... that was the intention.~~ FIXED  

- The database and application are on the same instance, neither can scale without the other
- The database of the application is on an instance, scaling IN/OUT risks this media
- The application media and UI store is local to an instance, scaling IN/OUT risks this media
- Customer Connections are to an instance directly ... no health checks/auto healing
- The IP of the instance is hardcoded into the database ....

# Create RDS Subnet Group

In this stage we will be splitting out the database functionality from the EC2 instance. Running MariaDB to an RDS instance running the MySQL Engine. This will allow the DB and Instance to scale independently, and will allow the data to be secure past the lifetime of the EC2 instance.  

A subnet group is what allows RDS to select from a range of subnets to put its databases inside. In this case we will give it a selection of 3 subnets sn-db-A / B and C. RDS can then decide freely which to use.  

Go to the RDS Console
Click `SubNet Groups`  
Click `Create DB Subnet Group`  
Under `Name` enter `WordPressRDSSubNetGroup`  
Under `Description` enter `RDS Subnet Group for WordPress`  
Under `VPC` select `A4LVPC`  

Under `Add subnets`
In `Availability Zones` select `all of the subnets. 

Under `Subnets` check the box next to 

- 10.16.16.0/20 (this is sn-db-A)
- 10.16.80.0/20 (this is sn-db-B)
- 10.16.144.0/20 (this is sn-db-C)

Click `Create`  

# Create RDS Instance

In this sub stage of the demo, you are going to provision an RDS instance using the subnet group to control placement within the VPC. Normally you would use multi-az for production, to keep costs low, for now you should use a single AZ as per the instructions below.  

Click `Databases`  
Click `Create Database`  
Click `Standard Create`  
Click `MySql`  
Under `Version` select `MySQL 5.6.46` (best aurora compatability for snapshot migrations)  

Scroll down and select `Free Tier` under templates
_this ensures there will be no costs for the database but it will be single AZ only_

Under `DB instance identifier` enter `WordPress`
Under `Master Username` and `Master Password` enter enter the values previously recorded in the parameter store.
Under `DB Instance size`, then `DB instance class`, then `Burstable classes (includes t classes)` make sure db.t2.micro is selected  
Scroll down, under `Connectivity`, `Virtual private cloud (VPC)` select `Pingnoran`.
  
Expand `Additional connectivity configuration` 
Ensure under `Subnet group` that `wordpressrdssubnetgroup` is selected  
Make sure `Publicly accessible` is set to `No`  
Under `Existing VPC security groups` add `SG-Database` and remove `Default`  
Under `Availability Zone` set `ap-southeast-2a`

Scroll down and expand `Additional configuration`  
In the `Initial database name` box enter the value DBName from the parameter store.
Scroll to the bottom and click `create Database`.

** this will take anywhere up to 30 minutes to create ... it will need to be fully ready before you move to the next step - coffee time !!!! **

## Migrate WordPress data from MariaDB to RDS

Open the EC2 Console
Click `Instances`  
Locate the `WordPress-LT` instance, right click, `Connect` and choose `Session Manager` and then click `Connect`  
Type `bash`  
Type `cd`  
Type `clear`  

### Populate Environment Variables

You're going to do an export of the SQL database running on the local EC2 instance.

First run these commands to populate variables with the data from Parameter store, it avoids having to keep locating passwords  
```
DBPassword=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBPassword --with-decryption --query Parameters[0].Value)
DBPassword=`echo $DBPassword | sed -e 's/^"//' -e 's/"$//'`

DBRootPassword=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBRootPassword --with-decryption --query Parameters[0].Value)
DBRootPassword=`echo $DBRootPassword | sed -e 's/^"//' -e 's/"$//'`

DBUser=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBUser --query Parameters[0].Value)
DBUser=`echo $DBUser | sed -e 's/^"//' -e 's/"$//'`

DBName=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBName --query Parameters[0].Value)
DBName=`echo $DBName | sed -e 's/^"//' -e 's/"$//'`

DBEndpoint=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBEndpoint --query Parameters[0].Value)
DBEndpoint=`echo $DBEndpoint | sed -e 's/^"//' -e 's/"$//'`

```

## Take a Backup of the local DB

To take a backup of the database run

```
mysqldump -h $DBEndpoint -u $DBUser -p $DBPassword $DBName > PingWordPress.sql
```
** In production we wouldn't put the password in the CLI like this, its a security risk since a ps -aux can see it .. but security isnt the focus of this demo its the process of rearchitecting **

## Restore that Backup into RDS

Move to the RDS Console  
Click the `WordPressdb` instance  
Copy the `endpoint` into your clipboard  
Move to the Parameter store   
Check the box next to `/Pingo/Wordpress/DBEndpoint` and click `Delete`
Click `Create Parameter`  

Under `Name` enter `/Pingo/Wordpress/DBEndpoint`  
Under `Descripton` enter `WordPress Endpoint Name`  
Under `Tier` select `Standard`    
Under `Type` select `String`  
Under `Data Type` select `text`  
Under `Value` enter the RDS endpoint endpoint you just copied  
Click `Create Parameter`  

Update the DbEndpoint environment variable with 

```
DBEndpoint=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/DBEndpoint --query Parameters[0].Value)
DBEndpoint=`echo $DBEndpoint | sed -e 's/^"//' -e 's/"$//'`
```

Restore the database export into RDS using

```
mysql -h $DBEndpoint -u $DBUser -p$DBPassword $DBName < PingoWordPress.sql 
```

## Change the WordPress config file to use RDS

this command will substitute `localhost` in the config file for the contents of `$DBEndpoint` which is the RDS instance

```
sed -i "s/'localhost'/'$DBEndpoint'/g" /var/www/html/wp-config.php
```

## Stop the MariaDB Service

```
sudo systemctl disable mariadb
sudo systemctl stop mariadb
```


## Test WordPress

Move to the EC2 Console  
Select the `WordPress-LT` Instance  
copy the `IPv4 Public IP` into your clipboard  
Open the IP in a new tab  
You should see the blog, working, even though MariaDB on the EC2 instance is stopped and disabled
Its now running using RDS  

# Update the LT so it doesnt install 

Move to the EC2 Console  
Under `Instances` click `Launch Templates`  
Select the `WordPress` launch Template (select, dont click)
Click `Actions` and `Modify Template (Create new version)`  
This template version will be based on the existing version ... so many of the values will be populated already  
Under `Template version description` enter `Single server App Only`

Scroll down to `Advanced details` and expand it  
Scroll down to `User Data` and expand the text box as much as possible


Locate and remove the following lines

```
systemctl enable mariadb
systemctl start mariadb
mysqladmin -u root password $DBRootPassword

echo "CREATE DATABASE $DBName;" >> /tmp/db.setup
echo "CREATE USER '$DBUser'@'localhost' IDENTIFIED BY '$DBPassword';" >> /tmp/db.setup
echo "GRANT ALL ON $DBName.* TO '$DBUser'@'localhost';" >> /tmp/db.setup
echo "FLUSH PRIVILEGES;" >> /tmp/db.setup
mysql -u root --password=$DBRootPassword < /tmp/db.setup
rm /tmp/db.setup

```

Click `Create Template Version`  
Click `View Launch Template`  
Select the template again (dont click)
Click `Actions` and select `Set Default Version`  
Under `Template version` select `2`  
Click `Set as default version`  

# STAGE 3 - FINISH  

This configuration has several limitations :-

- ~~The application and database are built manually, taking time and not allowing automation~~ FIXED  
- ~~^^ it was slow and annoying ... that was the intention.~~ FIXED  
- ~~The database and application are on the same instance, neither can scale without the other~~ FIXED  
- ~~The database of the application is on an instance, scaling IN/OUT risks this media~~ FIXED  

- The application media and UI store is local to an instance, scaling IN/OUT risks this media
- Customer Connections are to an instance directly ... no health checks/auto healing
- The IP of the instance is hardcoded into the database ....

# Create EFS File System

In this stage we will be creating an EFS file system designed to store the wordpress locally stored media. This area stores any media for posts uploaded when creating the post as well as theme data. By storing this on a shared file system it means that the data can be used across all instances in a consistent way, and it lives on past the lifetime of the instance.  

## File System Settings

Move to the EFS Console  
Click on `Create file System`  
We're going to step through the full configuration options, so click on `Customize`  
For `Name` type `PINGO-WORDPRESS-CONTENT`  
This is critical data so .. ensure `Enable Automatic Backups` is enabled.  
for `LifeCycle management` leave as the default of `30 days since last access`  
You have two `performance modes` to pick, choose `General Purpose` as MAX I/O is for very spefific high performance scenarios.  
for `Throughput mode` pick `bursting` wich links performance to how much space you consume. The more consumed, the higher performance. The other option Provisioned allows for performance to be specified independant of consumption.  
Untick `Enable encryption of data at rest` .. in production you would leave this on, but for this demo which focusses on architecture it simplifies the implementation.  
Click `Next`

## Network Settings

In this part you will be configuing the EFS `Mount Targets` which are the network interfaces in the VPC which your instances will connect with.  

In the `Virtual Private Cloud (VPC)` dropdown select `A4LVPC`  
You should see 3 rows.  
Make sure `ap-southeast-2a`, `ap-southeast-2b` & `ap-southeast-2c` are selected in each row.  
In `ap-southeast-2a` row, select `sn-App-A` in the subnet ID dropdown, and in the security groups dropdown select `SGEFS` & remove the default security group  
In `ap-southeast-2b` row, select `sn-App-B` in the subnet ID dropdown, and in the security groups dropdown select `SGEFS` & remove the default security group  
In `ap-southeast-2c` row, select `sn-App-C` in the subnet ID dropdown, and in the security groups dropdown select `SGEFS` & remove the default security group  

Click `next`  
Leave all these options as default and click `next`  
We wont be setting a file system policy so click `Create`  

The file system will start in the `Creating` State and then move to `Available` once it does..  
Click on the file system to enter it and click `Network`  
Scroll down and all the mount points will show as `creating` keep hitting refresh and wait for all 3 to show as available before moving on.  

Note down the `fs-XXXXXXXX` file system ID once visible at the top of this screen, you will need it in the next step.  


## Add an FSID to parameter store

Now that the file system has created, you need to add another parameter store value for the file system ID .. so that the automatically building instance(s) can load this safely.  

Move to the Systems Manager console   
Click on `Parameter Store` on the left menu  
Click `Create Parameter`  
Under `Name` enter `/Pingo/Wordpress/EFSFSID` 
Under `Description` enter `File System ID for Wordpress Content (wp-content)`  
for `Tier` set `Standard`  
For `Type` set `String`  
for `Data Type` set `text`  
for `Value` set the file system ID `fs-XXXXXXX` which you just noted down (use your own file system ID)  
Click `Create Parameter`  


## Connect the file system to the EC2 instance & copy data

Open the EC2 console and go to running instances
Select the `Wordpress-LT` instance, right click, `Connect`, Select `Session Manager` and click `Connect`  
type `sudo bash` and press enter   
type `cd` and press enter  
type `clear` and press enter  

First we need to install the amazon EFS utilities to allow the instance to connect to EFS. EFS is based on NFS which is standard but the EFS tooling makes things easier.  

```
sudo yum -y install amazon-efs-utils
```
Next you need to migrate the existing media content from wp-content into EFS, and this is a multi step process.

First, copy the content to a temporary location and make a new empty folder.

```
cd /var/www/html
sudo mv wp-content/ /tmp
sudo mkdir wp-content
```

Then get the efs file system ID from parameter store

```
EFSFSID=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/EFSFSID --query Parameters[0].Value)
EFSFSID=`echo $EFSFSID | sed -e 's/^"//' -e 's/"$//'`
```

Next, add a line to /etc/fstab to configure the EFS file system to mount as /var/www/html/wp-content/

```
echo -e "$EFSFSID:/ /var/www/html/wp-content efs _netdev,tls,iam 0 0" >> /etc/fstab
mount -a -t efs defaults
```

Now we need to copy the origin content data back in and fix permissions

```
mv /tmp/wp-content/* /var/www/html/wp-content/
chown -R ec2-user:apache /var/www/

```

# Test that the wordpress app can load the media

Run the following command to reboot the EC2 wordpress instance
```
reboot
```
Once it restarts, ensure that you can still load the wordpress blog which is now loading the media from EFS.  


## Update the launch template with the config to automate the EFS part

Next we will update the launch template so that it automatically mounts the EFS file system during its provisioning process. This means that in the next stage, when you add autoscaling, all instances will have access to the same media store ...allowing the platform to scale.

Go to the EC2 console   
CLick `Launch Templates`  
Check the box next to the `Wordpress` launch template, click `Actions` and click `Modify Template (Create New Version)`  
for `Template version description` enter `App only, uses EFS filesystem defined in /Pingo/Wordpress/EFSFSID`  
Scroll to the bottom and expand `Advanced Details`  
Scroll to the bottom and find `User Data` expand the entry box as much as possible.  

After `#!/bin/bash -xe` position cursor at the end & press enter twice to add new lines
paste in this

```
EFSFSID=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/EFSFSID --query Parameters[0].Value)
EFSFSID=`echo $EFSFSID | sed -e 's/^"//' -e 's/"$//'`

```

fine the line which says `yum install -y mariadb-server httpd wget`
after `wget` add a space and paste in `amazon-efs-utils`  
it should now look like `yum install -y mariadb-server httpd wget amazon-efs-utils`  

locate `systemctl start httpd` position cursor at the end & press enter twice to add new lines  

Paste in the following

```
mkdir -p /var/www/html/wp-content
chown -R ec2-user:apache /var/www/
echo -e "$EFSFSID:/ /var/www/html/wp-content efs _netdev,tls,iam 0 0" >> /etc/fstab
mount -a -t efs defaults
```

Scroll down and click `Create template version`  
Click `View Launch Template`  
Select the template again (dont click)
Click `Actions` and select `Set Default Version`  
Under `Template version` select `3`  
Click `Set as default version`  

## FINISH  

This configuration has several limitations :-

- ~~The application and database are built manually, taking time and not allowing automation~~ FIXED  
- ~~^^ it was slow and annoying ... that was the intention.~~ FIXED  
- ~~The database and application are on the same instance, neither can scale without the other~~ FIXED  
- ~~The database of the application is on an instance, scaling IN/OUT risks this media~~ FIXED  
- ~~The application media and UI store is local to an instance, scaling IN/OUT risks this media~~ FIXED  

- Customer Connections are to an instance directly ... no health checks/auto healing
- The IP of the instance is hardcoded into the database ....

# Create the load balancer

In this stage 5 we will be adding an auto scaling group to provision and terminate instances automatically based on load on the system. We have already performed all of the preperation steps required, by moving data storage onto RDS, media storage onto EFS and created a launch template to automatically build the wordpress application servers.  

Move to the EC2 console  
Click `Load Balancers` under `Load Balancing`  
Click `Create Load Balancer`  
Click `Create` under `Application Load Balancer`  
Under name enter `WORDPRESSALB`  
Ensure `internet-facing` is selected  
ensure `ipv4` selected for `IP Address type`  

Under `Listeners` `HTTP` and `80` should be selected for `Load Balancer Protocol` and `Load Balancer Port`  

Scroll down, under `Availability Zones` 
for VPC ensure `A4LVPC` is selected  
Check boxes next to `ap-southeast-2a`, `ap-southeast-2b` and `ap-southeast-2c`  
Select `sn-pub-A`, `sn-pub-B` and `sn-pub-C` for each.  

Scroll down and click `Next: Configure Security Settings`  
because we're not using HTTP we can move past this  
Click `Next: Configure Security Groups`  
Check `Select an existing security group` and select `A4LVPC-SGLoadBalancer` it will have some random at the end and thats ok.  

Click `Next: Configure Routing`  

for `Target Group` choose `New Target Group`  
for Name choose `WORDPRESSALBTG`  
for `Target Type` choose `Instance`  
For `Protocol` choose `HTTP`  
For `Port` choose `80`  
Under `Health checks`
for `Protocol` choose `HTTP`
and for `Path` choose `/`  
Click `Next: Register Targets`  
We wont register any right now, click `Next: Review`  
Click `Create`  

Click on the `A4LWORDPRESSALB` link  
Scroll down and copy the `DNS Name` into your clipboard  

# STAGE 5B - Create a new Parameter store value with the ELB DNS name

Move to the systems manager console
Click `Paramater Store`  
Click `Create Parameter`  
Under `Name` enter `/Pingo/Wordpress/ALBDNSNAME` 
Under `Description` enter `DNS Name of the Application Load Balancer for wordpress`  
for `Tier` set `Standard`  
For `Type` set `String`  
for `Data Type` set `text`  
for `Value` set the DNS name of the load balancer you copied into your clipboard
Click `Create Parameter` 

# Update the Launch template to wordpress is updated with the ELB DNS as its home

Go to the EC2 console  
CLick `Launch Templates`  
Check the box next to the `Wordpress` launch template, click `Actions` and click `Modify Template (Create New Version)`  
for `Template version description` enter `App only, uses EFS filesystem defined in /Pingo/Wordpress/EFSFSID, ALB home added to WP Database`  
Scroll to the bottom and expand `Advanced Details`  
Scroll to the bottom and find `User Data` expand the entry box as much as possible.  

After `#!/bin/bash -xe` position cursor at the end & press enter twice to add new lines
paste in this

```
ALBDNSNAME=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/ALBDNSNAME --query Parameters[0].Value)
ALBDNSNAME=`echo $ALBDNSNAME | sed -e 's/^"//' -e 's/"$//'`

```

Move all the way to the bottom of the `User Data` and paste in this block

```
cat >> /home/ec2-user/update_wp_ip.sh<< 'EOF'
#!/bin/bash
source <(php -r 'require("/var/www/html/wp-config.php"); echo("DB_NAME=".DB_NAME."; DB_USER=".DB_USER."; DB_PASSWORD=".DB_PASSWORD."; DB_HOST=".DB_HOST); ')
SQL_COMMAND="mysql -u $DB_USER -h $DB_HOST -p$DB_PASSWORD $DB_NAME -e"
OLD_URL=$(mysql -u $DB_USER -h $DB_HOST -p$DB_PASSWORD $DB_NAME -e 'select option_value from wp_options where option_id = 1;' | grep http)

ALBDNSNAME=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingo/Wordpress/ALBDNSNAME --query Parameters[0].Value)
ALBDNSNAME=`echo $ALBDNSNAME | sed -e 's/^"//' -e 's/"$//'`

$SQL_COMMAND "UPDATE wp_options SET option_value = replace(option_value, '$OLD_URL', 'http://$ALBDNSNAME') WHERE option_name = 'home' OR option_name = 'siteurl';"
$SQL_COMMAND "UPDATE wp_posts SET guid = replace(guid, '$OLD_URL','http://$ALBDNSNAME');"
$SQL_COMMAND "UPDATE wp_posts SET post_content = replace(post_content, '$OLD_URL', 'http://$ALBDNSNAME');"
$SQL_COMMAND "UPDATE wp_postmeta SET meta_value = replace(meta_value,'$OLD_URL','http://$ALBDNSNAME');"
EOF

chmod 755 /home/ec2-user/update_wp_ip.sh
echo "/home/ec2-user/update_wp_ip.sh" >> /etc/rc.local
/home/ec2-user/update_wp_ip.sh
```

Scroll down and click `Create template version`  
Click `View Launch Template`  
Select the template again (dont click)
Click `Actions` and select `Set Default Version`  
Under `Template version` select `4`  
Click `Set as default version`  


## Create an auto scaling group (no scaling yet)

Move to the EC2 console  
under `Auto Scaling`  
click `Auto Scaling Groups`  
Click `Create an Auto Scaling Group`  
For `Auto Scaling group name` enter `WORDPRESSASG`  
Under `Launch Template` select `Wordpress`  
Under `Version` select `Latest`  
Scroll down and click `Next`  
for `Purchase options and instance types` leave the default of `Adhere to launch template` selected.  
For `Network` `VPC` select `A4LVPC`  
For `Subnets` select `sn-Pub-A`, `sn-pub-B` and `sn-pub-C`  
Click `next`  

## Integrate ASG and ALB

Its here where we integrate the ASG with the Load Balanacer. Load balancers actually work (for EC2) with static instance registrations. What ASG does, it link with a target group, any instances provisioned by the ASG are added to the target group, anything terminated is removed.  

Check the `Enable Load balancing` box  
Ensure `Application Load Balancer or Network Load Balancer` is selected.  
for `Choose a target group for your load balancer` sekect `A4LWORDPRESSALBTG`  
Under `health Checks - Optional` choose `ELB`  
Scroll down and click `Next`  

For now leave `Desired` `Mininum` and `Maximum` at `1`   
For `Scaling policies - optional` leave it on `None`  
Make sure `Enable instance scale-in protection` is **NOT** checked  
Click `Next`  
We wont be adding notifications so click `Next` Again  
Click `Add Tag`  
for `Key` enter `Name` and for `Value` enter `Wordpress-ASG` 
make sure `Tag New instances` is checked
Click `Next` 
Click `Create Auto Scaling Group`  

Right click on instances and open in a new tab  
Right click Wordpress-LT, `Instance State`, `Terminate` and click `Yes Terminate`  
This removes the old manually created wordpress instance
Click `Refresh` and you should see a new instance being created... `Wordpress-ASG` this is the one created automatically by the ASG using the launch template  


## Add scaling

Move to the AWS Console  
Click `Auto Scaling Groups`  
Click the `LWORDPRESSASG` ASG  
Click the `Automatic SCaling` Tab  

We're going to add two policies, scale in and scale out.

### SCALEOUT when CPU usage on average is above 40%

Click `Add Policy`  
For policy `type` select `Simple scaling`  
for `Scaling Policy name` enter `HIGHCPU`  
Click `Create a CloudWatch Alarm`  
Click `Select Metric`  
Click `EC2'  
Click `By Auto Scaling Group`
Check `WORDPRESSASG CPU Utilization`  
Click `Select Metric`  
Scroll Down... select `Greater` and enter `40` in the `than` box and click `next`
Click `Remove` next to notification
Click `Next`
Enter `WordpressHIGHCPU` in `Alarm Name`  
Click `Next`  
Click `Create Alarm`  
Go back to the AutoScalingGroup tab and click the `Refresh SYmbol` next to Cloudwatch Alarm  
Click the dropdown and select `WordpressHIGHCPU`  
For `Take the action` choose `Add` `1` Capacity units  
Click `Create`


### SCALEIN when CPU usage on average ie below 40%

Click `Add Policy`  
For policy `type` select `Simple scaling`  
for `Scaling Policy name` enter `LOWCPU`  
Click `Create a CloudWatch Alarm`  
Click `Select Metric`  
Click `EC2'  
Click `By Auto Scaling Group`
Check `WORDPRESSASG CPU Utilization`  
Click `Select Metric`  
Scroll Down... select `Lower` and enter `40` in the `than` box and click `next`
Click `Remove` next to notification
Click `Next`
Enter `WordpressLOWCPU` in `Alarm Name`  
Click `Next`  
Click `Create Alarm`  
Go back to the AutoScalingGroup tab and click the `Refresh SYmbol` next to Cloudwatch Alarm  
Click the dropdown and select `WordpressLOWCPU`  
For `Take the action` choose `Remove` `1` Capacity units  
Click `Create`

### ADJUST ASG Values

Click `Details Tab`  
Click `Edit`  
Set `Desired 1`, Minimum `1` and Maximum `3`  
Click `Update`  

## Test Scaling & Self Healing

Open Auto Scaling Groups in a new tab  
Open that Auto scaling group in that tab and click on `Activity` tab
Go to running instances in the EC2 Console in a new tab  

Simulate some load on the wordpress instance  

Select the/one running `Wordpress-ASG` instance, right click, `Connect`, Select `Session Manager` and click `Connect`  
type `sudo bash` and press enter   
type `cd` and press enter  
type `clear` and press enter  

run `stress -c 2 -v -t 3000`

this stresses the CPU on the instance, while running go to the ASG tag, and refresh the activities tab. it might take a few minutes, but the ASG will detect high CPU load and begin provisioning a new EC2 instance. 
if you want to see the monitoring stats, change to the monitoring tag on the ASG Console
Check `enable` next to Auto Scaling Group Metrics Collection

At some point another instance will be added. This will be auto built based on the launch template, connect to the RDS instance and EFS file system and add another instance of capacity to the platform.
Try terminating one of the EC2 instances ...
Watch what happens in the activity tab of the auto scaling group console.
This is an example of self-healing, a new instance is provisioned to take the old ones place.

We have fixed all the limitations of this configuration:
- The database is on RDS and automated
- The instances are behind an ALB and can scale.
- The wp-content folder is on an EFS
- Route 53 is receiving the traffic and directing it to the load balancer.
