# Advanced Hybrid Directory Demo

In this part of the demo you will be provisioning the Hybrid environment you will be using for the remainder of the activity.  
The CloudFormation template below is a NESTED STACK. 

- ONPREM-VPC - the simulated On-premises environment.  
- AWS-VPC - the AWS Environment (including a VPC Peer between AWS and On-Premises - to simulate a VPN/DX)  
- ONPREM-AD - Creates the Self-Managed On-Premises Active Directory  
- ONPREM-COMPUTE - Creates the On-Premises Jumpbox, Client, FileServer which are joined to the On-Premises Domain  

Provisioning will take around 60 minutes +/- 20 minutes  
 
# Create an EC2 Key Pair  

Create an EC2 Key pair to use for this demo.

# APPLY CloudFormation (CFN) Nested Stack  

These are the CloudFormation templates used in this demo:

https://aws-hybrid-activedirectory.s3-ap-southeast-2.amazonaws.com/01_HYBRIDDIR.yaml
https://aws-hybrid-activedirectory.s3-ap-southeast-2.amazonaws.com/02_HYBRIDDIR-NESTED-ONPREM-VPC.yaml
https://aws-hybrid-activedirectory.s3-ap-southeast-2.amazonaws.com/03_HYBRIDDIR-NESTED-ONPREM-AD.yaml
https://aws-hybrid-activedirectory.s3-ap-southeast-2.amazonaws.com/04_HYBRIDDIR-NESTED-ONPREM-COMPUTE.yaml
https://aws-hybrid-activedirectory.s3-ap-southeast-2.amazonaws.com/05_HYBRIDDIR-NESTED-AWS-VPC.yaml


You will need to pick a `Domain Admin Password` to use for the on-premises directory and a `KeyPair` to use  

Let the NESTED Stack apply and then continue to STAGE 2 of the DEMO.

