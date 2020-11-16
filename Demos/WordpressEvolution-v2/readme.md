# Advanced Demo - Web App - Single Server to Elastic Evolution

Tools used:

- Route 53 is routing the traffic to an Apllication Load Balancer in ap-southeast-2.

Used so far:
Route 53
ALB
Auto scaling
RDS database 
EFS to store the wp-content
Cloudformation to provision the network configuration
User data in the Launch template is used to load the site configuration and install dependencies

To do:
work on the website UI

Functionalities to add:
Have part of the site where users need to sign on (to make comments) - Cognito
S3 and Cloudfront to serve some sort of static content
Include some CICD functionality. At the moment, the website is managed using the Word Press UI.
WAF




In this advanced demo lesson you are going to evolve the architecture of a popular web application wordpress
The architecture will start with a manually built single instance, running the application and database
over the stages of the demo you will evolve this until its a scalable and resilient architecture

The demo consists of 7 stages, each implementing additional components of the architecture  

- Stage 1 - Setup the environment and manually build wordpress  
- Stage 2 - Automate the build using a Launch Template  
- Stage 3 - Split out the DB into RDS and Update the LT 
- Stage 4 - Split out the WP filesystem into EFS and Update the LT
- Stage 5 - Enable elasticity via a ASG & ALB and fix wordpress (hardcoded WPHOME)
- Stage 6a - Optional .. move to Aurora and DB HA  
- Stage 7 - Cleanup  

![Architecture](https://github.com/acantril/learn-cantrill-io-labs/raw/master/aws-elastic-wordpress-evolution/ArchitectureEvolutionAll.png)

## Instructions

