This section notes are dedicated to cover the AWS Networking and Hybrid section from the SA Pro course by Adrian Cantrill. Some notes were also derived from the SA Associate course by Stéphane Maarek.

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

## Site to Site VPN

Logical connection between a VPC and on-premises network encrypted using IPSec, running over the public internet
- You can run a VPN over Direct Connect to avoid going over the public internet

Static vs Dynamic VPN
- Static network
    - Simple to setup but there are limits with highly availability and direct connect setup
    - Routes for remote side added to route tables as static routes
    - Networks for remote side statically configured on the VPN connection
    - No load balancing and multi-connection fail over
- Dynamic VPN uses BGP
    - Routes for remote side added to route tables as static routes
    - BGP is configured on both the customer and AWS side using ASN.
    - Networks are exchanged via BGP
    - Multiple VPN connections provide HA and traffic distribution

`AWS Direct Connect + VPN provides an IPsec-encrypted private connection`

### Virtual Private Gateway (VGW)
- VPN concentrator on the AWS side of the VPN Connection
- VGW is created and attached to the VPC
- Can customise ASN (Autonomous System Number)

### Customer Gateway (CGW)
- Software application or physical device on customer side
- IP Address is static and have an internet routable IP Address

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


# Border Gateway Protocol

Autonomous system (AS): Routers controlled by one entity
ASN are unique and allocated by IANA
BGP is a *path-vector* protocol 
The best path to destination is determined by the least number of hops. If you need to trick the system in using another path (i.e. the other path has better connection), you can do AS Path Prepending.
Path is called **ASPATH**

# Transit Gateway (TGW)

Network Transit Hub to connect VPCs to on premises networks
Significantly reduces network complexity
Connections are transitive
Attachments to other network types
VPC, Site-to-site VPN, Direct Connect Gateway

# Accelerated Site-to-Site VPN
Use Edge locations
Acceleration can be enabled when creating a TGW VPN attachment
Not compatible with VPNs unsing a VGW

# Network: Layer 4 - Transport Layer

Both uses IP as transfer

## UDP  User Datagram Protocol
* Faster
* Less Reliable
* Less overhead compared to TCP
* All about performance

## TCP - Transmission Control Protocol
* Reliability 
* Error Correction
* Order of data
* Most common

TCP segments are encapsulated within IP packets
Segments don't have SRC and DST IPs

TCP segments have:
* Source Port
* Destination Port
* Sequence Number
* Acknowledgment - how each side indicates they have received the package
* Flags - establish connections, send data and terminate connections.
    * URG: urgent pointer. When this bit is set, the data should be treated as priority over other data.
    * ACK: used for the acknowledgment.
    * PSH: this is the push function. This tells an application that the data should be transmitted immediately and that we don’t want to wait to fill the entire TCP segment.
    * RST: this resets the connection, when you receive this you have to terminate the connection right away. This is only used when there are unrecoverable errors and it’s not a normal way to finish the TCP connection.
    * SYN: we use this for the initial three way handshake and it’s used to set the initial sequence number.
    * FIN: this finish bit is used to end the TCP connection. TCP is full duplex so both parties will have to use the FIN bit to end the connection. This is the normal method how we end an connection.
* Window
* Checksum
* Urgent Pointer 
* Options
* Padding
* Data

All of those items, except the Data are part of the TCP Header
