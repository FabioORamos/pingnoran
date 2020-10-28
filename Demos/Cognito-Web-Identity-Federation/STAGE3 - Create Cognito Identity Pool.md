# Advanced Demo - Web Identity Federation

# STAGE 3A - Create a Cognito Identity Pool  

Move to the Cognito Console    
Click `Manage Identity Pools`   
Under `Create new identity pool`   
In `Identity pool name` enter `PetIDFIDPool`   
Expand `Authentication Providers` and click on `Google+`   
In the `Google Client ID` box, enter the Google Client ID you noted down in the previous step.  
Click `Create Pool`   

# STAGE 3B - Permissions  

Expand `View Details`    
This is going to create two IAM roles  
One for `Your authenticated identities` and another for your `Your unauthenticated identities`    
For now, we're just going to click on `Allow` we can review the roles later.    

You will be presented with your `Identity Pool ID`, note this down, you will need it later.   

# STAGE 3C - Adjust Permissions  

The serverless application is going to read images out of a private bucket created by the initial cloudformation template.    
The bucket is called `patchesprivatebucket`    
Move to the IAM Console   
Click `Roles`   
Locate and click on `Cognito_PetIDFIDPoolAuth_Role`  
Click on `Trust Relationships`  
See how this is assumable by `cognito-identity.amazonaws.com`  
With two conditions  
- `StringEquals` `cognito-identity.amazonaws.com:aud` `your congnito ID pool`  
- `ForAnyValue:StringLike` `cognito-identity.amazonaws.com:amr` `authenticated`  
This means to assume this role - you have to be authenticated by one of the ID providers defined in the cognito ID pool.    

When you use WEDIDF with Cognito, this role is assumed on your behalf by Cognito, and its what generates temporary AWS credentials which are used to access AWS resources.  

Click `permissions` - this defines what these credentials can do.  

The cloudformation template created a managed policy which can access the `privatepatches` bucket  
Click `Attach Policies`   
Type `PrivatePatches` in the search box  
Check the box next to `PrivatePatchesPermissions` and click `Attach Policy`    


# STAGE 3 - FINISH    

- Template front end app bucket  
- Configured Google API Project  
- Credentials to access it  
- Cognito ID Pool  
- IAM Roles for the ID Pool  





