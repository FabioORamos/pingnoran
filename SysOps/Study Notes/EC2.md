# EC2

## Placement Group can be:
* Cluster
* Spread
* Partition

* Elastic IPs are fixed IP address that are owned by you

* Instance Launch Types
    * On Demand
    * Reserved Instances – use for long workloads
    * Convertible Reserved Instances
    * Schedule Reserved Instances – launch within time window
    * Spot Instances – short workloads, cheap, can lose instances
    * Dedicated Instances – no other customers will share your hardware 
    * Dedicated Hosts – book an entire physical server, control instance placement


## EC2 Launch Troubleshooting
* Instance Terminates Immediately (goes from pending to terminated):
    * You've reached your EBS volume limit
    * An EBS snapshot is corrupt
    * The root EBS volume is encrypted and you do not have permissions to access the KMS key for decryption
    * The instance store-backed AMI that you used to launch the instance is missing a required part
* To find the exact reason, go to EC2 console in the Description tab and note the reason on the State transition label

## EC2 

## Security Groups
* Control how traffic is allowed in or out of EC2 machines
* SSH is TCP on port 22
	
* Regulate
    * Access to Ports
    * Authorised IP ranges 
    * Control of inbound network (from other to the instance)
    * Control of outbound network (from the instance to other)

* Default
    * Inbound = Allow ALL traffic from within the security group
    * Outbound = ALL traffic is allowed

* Non-default
    * Inbound = Nothing is allowed
    * Outbound = ALL traffic is allowed


## Advanced Networking

ENI (Elastic Network Interface) is the virtual component of the NIC (Network Interface Controller) level

The primary ENI cannot be removed from the EC2.
Secondary ENI can be moved (detached and attached) between instances but subnets need to be in the same AZ.
* Multiple ENIs offers multiple security zones or traffic types.
* Each ENI can have a different Security Group.
* Each ENI can also be protected by a NACL around its subnet.

A primary private IPv4 address is allocated to the ENI and will remain static for the duration of the EC2 instance

## Bootstrapping vs AMI Baking