In this project I've tried to use as much AWS as possible.

In this site, I've used:

- Route 53 to route traffic from www.pingnoran.com to an Application Load Balancer. The domain is hosted on AWS.
- Application Load Balancer with an Auto Scaling Group. There are no scaling policies, due to non expectant traffic, but the EC2 is self healing as it has a required instance of 1 with a minimum of 1 and a maximum of 1. 
- The EC2 uses a regular AMI with user data configuration, that install all of the required dependencies.
- CloudFormation has been used to provision the VPC structure (VPC, Security Groups, etc).
- EFS is used to store the WP-Content folder
- MySQL is used to store
- The parameter store has been used to store configuration parameters (DB User, DB password, etc).