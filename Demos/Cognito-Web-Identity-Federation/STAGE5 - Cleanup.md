# Advanced Demo - Web Identity Federation

# Delete the Google API Project & Credentials
https://console.developers.google.com/cloud-resource-manager 
Select `PetIDF` and click `DELETE`  
Type in the ID of the project, which might have a slightly different name (shown above the text box) click `Shut Down`  


# Delete the Cognito ID Pool
Move to the cognito console  
Click `Manage Identity Pools`  
Click on `PetIDFIDPool`  
Click `Edit Identity Pool`  
Locate and expand `Delete identity pool`  
Click `Delete Identity Pool`  
Click `Delete Pool`  

# Delete the IAM Roles
Move to the IAM Console  
Select `Roles`  
Select both `Cognito_PetIDF*` roles  
Click `Delete Role`  
Click `Yes Delete`  

# Delete the CloudFormation Stack
Move to the cloud formation console 
Select `WEBIDF`, click `Delete` then `Delete Stack`  