# Security and Compliance

## AWS Shared Responsibility Model
* AWS is responsible to the security of the cloud
* Have a look at the Responsibility Model Diagram
    * Protecting infrastructure (hardward, software, facilities, networking)
    * Managed services - S3, DynamoDB, RDS, etc
* Customers are responsible for the security in the cloud
    * Guest OS management

## AWS Shield Standard and Advanced
* Protects against DDoS attacks for your website and applications
* Distributed Denial-of-Service
* Shield Standard is already included at no additional cost

## AWS WAF
* Protects your web applications from common web exploits (Layer 7)
* Can be deployed on:
    * Application Load Balancer – localised rules
    * API Gateway – rules running at the regional or edge level
    * CloudFront – rules globally on edge locations 
* WAF is not for DDoS protection
* Define customizable web security rules:
    * control which traffic to allow or block
    * rules can include: IP addresses, HTTP headers, HTTP body or URI strings
    * protects against bots, bad user agents
    * size constraints
    * geo match

## AWS Firewall Manager
* Manage rules in all accounts of an AWS Organisation
* Common set of security rules:
    * WAF Rules (ALB, API Gateway, CloudFront)
    * AWS Shield Advanced (ALB, CLB, Elastic IP, CloudFront)
    * Security Groups for EC2 and ENI resources in VPC

## AWS Config
* Helps with auditing and recording compliance of your AWS resources
* Helps record configurations and changes over time
* Doesn’t prevent actions from happening	

## AWS Inspector
* Only for EC2 instances
* AWS Inspector Agent must be installed on OS in EC2 instances
* Analyse against unintended network accessbility and known vulnerabilities
* Define template (rules package, duration, attibutes, SNS Topics)
* No own custom rules possible, only AWS managed rules
* You get a report after the analysis