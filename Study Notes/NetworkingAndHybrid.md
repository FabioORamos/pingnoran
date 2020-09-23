# VPC – Virtual Private Cloud

Virtual network or data centre inside AWS
You have full control of what happens inside your VPC
Logically isolated from other VPCs on AWS

Consists of: 
- Subnets – Public and Private
	- Each subnet must be associated with only one route table at any given time
- Internet Gateway (IGW)
- Helps VPC instances connect to the internet and other AWS services
- One VPC attaches to one IGW
- It performs Network Address Translation (NAT) between your Private and Public (or Elastic) IPv4 addresses
- Route Tables
- Implied router used to connect your subnets within your VPC
- Destination: where the packages are sent to
- Target: where to send traffic to
- The same route table can be associated with multiple subnets
- Route between subnets are default and you can’t change/delete
- Network Access Control Lists (NACLs)
- Security Groups
	- Virtual firewalls that protect your instances
- Function at the NIC (Network Interface Controller) level. In AWS its virtual component is referred as ENI (Elastic Network Interface) 

VPCs are region specific
VPCs cross multiple AZ within the same region

A Subnet is locked within an Availability Zone (AZ)


## Custom VPCs

When a new VPC is created the following items are created automatically:
-	Route Table
-	NACLs
-	Security Groups

You need to manually create:
-	Subnets
-	IGW


## Routing Policies
-	Simple routing
-	Weighted routing (i.e. 20% in one AZ/Region and 80% in another AZ/Region)
-	Latency based – fastest response time to the user
-	Failover – active/passive set up
-	Geolocation
-	Multivariate answer

## NAT Gateway vs NAT Instances

To allow traffic internet traffic from your Private Subnet you need: NAT Instances and NAT Gateways (managed by AWS).

NAT Gateway:
-	No need to patch
-	Not associated with security groups
-	IP automatic assigned (public)
-	Scale automatically up to 10Gbps

Bastion Hosts
-	Used to securely administer EC2 (jump boxes?)
-	Used to provide internet access to EC2 in private subnets
-	Use a Bastion Host to SSH into a private instance
-	Bastion is in a public subnet
-	Only allow Port 22 traffic from the IP you need

DNS Resolution Options: Route 53 and Private Zones
-	Private Zone on Route 53 and then resolve in your VPC

Private DNS Name: Enable DNS Hostnames and DNS Support

VPC Peering
-	Connect 2+ VPC, privately using the AWS Network
-	Behave same way as if they were one 
-	Must not have overlapping CIDR
-	Must update route tables in each VPCs subnets

Site to Site VPN

Virtual Private Gateway
-	VPN concentrator on the AWS side of the VPN Connection
-	VGW is created and attached to the VPC
-	Can customise ASN (Autonomous System Number)

Customer Gateway
-	Software application or physical device on customer side
-	IP Address is static and have an internet routable IP Address

## VPC Endpoints
-	Allow you to connect to AWS Services using a private network
-	Scale horizontally and are redundant
-	Remove the need of IGW and NAT

Endpoint Service

### Interface Endpoint – provisions an ENI (Elastic Network Interface = private IP address) as an entry point. Must attach security groups

### Gateway Endpoint – provisions a target and must be used in a route table – S3 and DynamoDB

# Direct Connect
-	Provides a dedicated private connection from a remote network to your VPC
-	Connection between your DC (Data Centre) and AWS Direct Connect Locations
-	Need a Virtual Private Gateway on your VPC

## Direct Connect Gateway
-	To connect with 2+ VPCs in many different regions (same account)
-	Connection types: 
o	Dedicated – dedicated physical ethernet port
	1 Gbps and 10 Gbps 
o	Hosted
	50 Mbps, 500 Mbps to 10 Gbps
-	Lead times are often longer than one month
-	Data in transit is not encrypted – just private

`AWS Direct Connect + VPN provides an IPsec-encrypted private connection`

Egress Only Internet Gateway
-	IPv6 only
-	Give access to the internet but can’t be reachable
-	IPv6 are all public addresses
-	Need to edit route table

AWS Private Link
-	Expose selected services from your VPC to other VPCs
-	No need to open all of your VPCs
-	Most secure and scalable way to expose a service to 1000s of VPC (own and other accounts)
-	Requires:
o	Network Load Balancer on the Service VPC
o	ENI on the Customer VPC

AWS VPN Cloud Hub
-	Provides secure communication between sites, if you have multiple VPN connections
-	Low cost hub-and-spoke model for primary or secondary connectivity between locations
-	It is a VPN connection, so it gets over the public internet

## VPC Flow Logs

Capture information about IP traffic going into your interfaces:
- VPC Flow Logs
- Subnet Flow Logs
- ENI Flow Logs
Flow Logs can go to S3/CloudWatch
Helps to monitor and troubleshoot connectivity issues
Capture network info from AWS managed interfaces:
- Srcaddr, dstaddr – help identify problematic IP
- Srcport, dstport – help identify problematic ports
Can be used for analytics on:
- Usage patterns
- Malicious behaviour
