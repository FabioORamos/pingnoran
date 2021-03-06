# Advanced Demo - Web App - Single Server to Elastic Evolution

In stage 1 of this advanced demo you will:
- Setup the environment which WordPress will run from. 
- Configure some SSM Parameters which the manual and automatic stages of this advanced demo series will use
- and perform a manual install of wordpress and a database on the same EC2 instance. 

This is the starting point .. the common wordpress configuration which you will evolve over the coming demo stages.

## Create a stack using the yaml file  

Wait for the STACK to move into the `CREATE_COMPLETE` state before continuing.

## Create an EC2 Instance to run wordpress

Click `Launch Instance`  
Locate the `Amazon Linux 2 AMI (HVM), SSD Volume Type` AMI  
ensure `64-bit (x86)` is selected  
Click `Select`
Select whatever instance shows as `Free tier eligible`  
Click `Next: Configure Instance Details`  
For `Network` select `Pingnoran`  
for `Subnet` select `sn-Pub-A`  
For `IAM role` select `VPC-WordpressInstanceProfile`  
Enable `T2/T3 Unlimited`  
_Even though it says Additional Changes may apply thats only if the rolling 24 hour average exceeds baseline, it won't_  
Click `Next: Add Storage`  
Click `Next: Add Tags`  
Click `Add Tag`  
Set `Key` to `Name` & set `Value` to `Wordpress-Manual`  
Click `Next: Configure Security Group`  
Check `Select an existing security group`  
Select `VPC-SGWordpress` it will have randomness after it, thats ok :)  
Click `Review and Launch`  
Click `Continue` to the port 22 warning, thats ok  
Click `Launch`  
Select `Proceed Without a key pair` and check the acknowledge box  
Click `Launch Instances`  
Click `View Instances`  

Wait for the instance to be in a `RUNNING` state  
_you can continue to stage 1B below while the instance is provisioning_

## Create SSM Parameter Store values for wordpress

Storing configuration information within the SSM Parameter store scales much better than attempting to script them in some way.
In this sub-section you are going to create parameters to store the important configuration items for the platform you are building.  

### Create Parameter - DBUser (the login for the specific wordpress DB)  
Click `Create Parameter`
Set Name to `/Pingnoran/Wordpress/DBUser`
Set Description to `Wordpress Database User`  
Set Tier to `Standard`  
Set Type to `String`  
Set Data type to `text`  
Set `Value` to `framos`  
Click `Create parameter`  

### Create Parameter - DBName (the name of the wordpress database)  
Click `Create Parameter`
Set Name to `/Pingnoran/Wordpress/DBName`
Set Description to `Wordpress Database Name`  
Set Tier to `Standard`  
Set Type to `String`  
Set Data type to `text`  
Set `Value` to `pingnorandb`  
Click `Create parameter` 

### Create Parameter - DBEndpoint (the endpoint for the wordpress DB .. )  
Click `Create Parameter`
Set Name to `/Pingnoran/Wordpress/DBEndpoint`
Set Description to `Wordpress Endpoint Name`  
Set Tier to `Standard`  
Set Type to `String`  
Set Data type to `text`  
Set `Value` to `localhost`  
Click `Create parameter`  

### Create Parameter - DBPassword (the password for the DBUser)  
Click `Create Parameter`
Set Name to `/Pingnoran/Wordpress/DBPassword`
Set Description to `Wordpress DB Password`  
Set Tier to `Standard`  
Set Type to `SecureString`  
Set `KMS Key Source` to `My Current Account`  
Leave `KMS Key ID` as default
Set `Value` to your chosen database password (make sure its complex)
Click `Create parameter`  

### Create Parameter - DBRootPassword (the password for the database root user, used for self-managed admin)  
Click `Create Parameter`
Set Name to `/Pingnoran/Wordpress/DBRootPassword`
Set Description to `Wordpress DBRoot Password`  
Set Tier to `Standard`  
Set Type to `SecureString`  
Set `KMS Key Source` to `My Current Account`  
Leave `KMS Key ID` as default
Set `Value` to your chosen database password (make sure its complex)
Click `Create parameter`  

# Connect to the instance and install a database and wordpress

Right click on `Wordpress-Manual` choose `Connect`
Choose `Session Manager`  
Click `Connect`  
type `bash` and press enter  
type `cd` and press enter  
type `clear` and press enter

## Bring in the parameter values from SSM

Run the commands below to bring the parameter store values into ENV variables to make the manual build easier.  

```
DBPassword=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingnoran/Wordpress/DBPassword --with-decryption --query Parameters[0].Value)
DBPassword=`echo $DBPassword | sed -e 's/^"//' -e 's/"$//'`

DBRootPassword=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingnoran/Wordpress/DBRootPassword --with-decryption --query Parameters[0].Value)
DBRootPassword=`echo $DBRootPassword | sed -e 's/^"//' -e 's/"$//'`

DBUser=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingnoran/Wordpress/DBUser --query Parameters[0].Value)
DBUser=`echo $DBUser | sed -e 's/^"//' -e 's/"$//'`

DBName=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingnoran/Wordpress/DBName --query Parameters[0].Value)
DBName=`echo $DBName | sed -e 's/^"//' -e 's/"$//'`

DBEndpoint=$(aws ssm get-parameters --region ap-southeast-2 --names /Pingnoran/Wordpress/DBEndpoint --query Parameters[0].Value)
DBEndpoint=`echo $DBEndpoint | sed -e 's/^"//' -e 's/"$//'`

```

## Install updates

```
sudo yum -y update
sudo yum -y upgrade

```

## Install Pre-Reqs and Web Server

```
sudo yum install -y mariadb-server httpd wget
sudo amazon-linux-extras install -y lamp-mariadb10.2-php7.2 php7.2
sudo amazon-linux-extras install epel -y
sudo yum install stress -y

```

## Set DB and HTTP Server to running and start by default

```
sudo systemctl enable httpd
sudo systemctl enable mariadb
sudo systemctl start httpd
sudo systemctl start mariadb
```

## Set the MariaDB Root Password

```
sudo mysqladmin -u root password $DBRootPassword
```

## Download and extract Wordpress

```
sudo wget http://wordpress.org/latest.tar.gz -P /var/www/html
cd /var/www/html
sudo tar -zxvf latest.tar.gz
sudo cp -rvf wordpress/* .
sudo rm -R wordpress
sudo rm latest.tar.gz
```

## Configure the wordpress wp-config.php file 

```
sudo cp ./wp-config-sample.php ./wp-config.php
sudo sed -i "s/'database_name_here'/'$DBName'/g" wp-config.php
sudo sed -i "s/'username_here'/'$DBUser'/g" wp-config.php
sudo sed -i "s/'password_here'/'$DBPassword'/g" wp-config.php
```

## Fix Permissions on the filesystem

```
sudo usermod -a -G apache ec2-user   
sudo chown -R ec2-user:apache /var/www
sudo chmod 2775 /var/www
sudo find /var/www -type d -exec chmod 2775 {} \;
sudo find /var/www -type f -exec chmod 0664 {} \;
```

## Create Wordpress User, set its password, create the database and configure permissions

```
sudo echo "CREATE DATABASE $DBName;" >> /tmp/db.setup
sudo echo "CREATE USER '$DBUser'@'localhost' IDENTIFIED BY '$DBPassword';" >> /tmp/db.setup
sudo echo "GRANT ALL ON $DBName.* TO '$DBUser'@'localhost';" >> /tmp/db.setup
sudo echo "FLUSH PRIVILEGES;" >> /tmp/db.setup
sudo mysql -u root --password=$DBRootPassword < /tmp/db.setup
sudo rm /tmp/db.setup
```

## Test Wordpress is installed

Open the EC2 instance using the DNS name 

## Perform Initial Configuration and make a post

Enter the name of your site in  `Site Title`. 
In `Username` enter the name stored on DBUser
In `Password` enter the name stored on DBPassword
in `Your Email` enter your email address  
Click `Install WordPress`
Click `Log In` and enter the User and Password

Play around with adding pages and posts.

This is your working, manually installed and configured wordpress

# FINISH  

This configuration has several limitations which you will resolve one by one within this lesson :-

- The application and database are built manually, taking time and not allowing automation
- ^^ it was slow and annoying ... that was the intention.
- The database and application are on the same instance, neither can scale without the other
- The database of the application is on an instance, scaling IN/OUT risks this media
- The application media and UI store is local to an instance, scaling IN/OUT risks this media
- Customer Connections are to an instance directly ... no health checks/auto healing
- The IP of the instance is hardcoded into the database ....
- Stop and Restart the EC2 instance
- the IP address has changed ... which is bad
- Try browsing to it ...
- What about the images....?
- The images are pointing at the old IP address...
- Right click `Wordpress-Manual` , `Instance State`, `Terminate`, `Yes, Terminate`

You can now move onto STAGE2

Bottom line = These actions are not recommended.




