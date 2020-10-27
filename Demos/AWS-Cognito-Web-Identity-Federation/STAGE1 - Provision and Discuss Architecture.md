# Advanced Demo - Web Identity Federation

# STAGE 1A - Login to an AWS Account    

Load WEBIDF.yaml file on CloudFormation to configure the VPC which WordPress will run from.

Click `Create Stack`
Wait for the STACK to move into the `CREATE_COMPLETE` state before continuing.  

# STAGE 1B - Verify S3 bucket  
   
Open the bucket starting `webidf-appbucket`   
It should have objects within it, including `index.html` and `scripts.js`  
Click the `Permissions` Tab  
Verify `Block all public access` is set to `Off`  
Click `Bucket Policy`  
Verify there is a bucket policy  
Click `Properties Tab`  
Click `Static Website Hosting`  
Verify this is enabled  

Note down the `Endpoint` as the `APP BUCKET ENDPOINT` you will need later.  

# STAGE 1C - Verify privatebucket
Open the bucket starting `webidf-patchesprivatebucket-`  
Load the objects in the bucket so you are aware of the contents  
Verify there is no bucket policy and the bucket is entirely private.   

# STAGE 1 - FINISH  
At this stage you have the base infrastructure in place including:-

- front end app bucket
- privatepatches bucket  

In stage 2 you will create a google API project which will be the `ID Provider` for this serverless application. 