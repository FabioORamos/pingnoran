This section notes are dedicated to cover the AWS Account Management section from the SysOps Associate course by Stéphane Maarek.

# AWS Health Dashboards


# AWS Catalog

# AWS Billing Alarms

# AWS Cost Explorer

# AWS Budgets




# Route 53
To route domain traffic to an ELB load balancer, use Amazon Route 53 to create an alias record that points to your load balancer. An alias record is a Route 53 extension to DNS. It’s similar to a CNAME record, but you can create an alias record both for the root domain, such as example.com, and for subdomains, such as www.example.com. (You can create CNAME records only for subdomains). For EC2 instances, always use a Type A Record without an Alias. For ELB, Cloudfront and S3, always use a Type A Record with an Alias and finally, for RDS, always use the CNAME Record with no Alias.