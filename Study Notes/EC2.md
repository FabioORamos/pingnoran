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

# CloudWatch

With CloudWatch you can do:
* Metrics: Collect and track key metrics
* Logs: Collect, monitor, analyse and store log files
* Events: send notifications when certain events happen in your AWS
* Alarms: react in real-time to metrics/events


## CloudWatch Metrics
* EC2 RAM is not a built-in metric

CloudWatch Alarms:
- Can trigger actions: 
    - EC2 action (reboot, stop, terminate, recover)
    - Auto Scaling
    - SNS. SNS can then send this info to:
        - SQS through Fan-Out
        - Lambda function to react to it
    - Alarm events can be intercepted by CloudWatch Events
        - Info can then be sent to:
            - Compute: Lambda, Batch, ECS Task
            - Orchestration: Step Functions, CodePipeline, CodeBuild
            - Integration: SQS, SNS, Kinesis DataStreams, Kinesis FireHose
            - Maintenance: SSM, EC2 Actions
Monitoring period:
- AWS Provided metrics 
    - Basic (default): 5min
    - Detailed monitoring (paid): 1min
    - Includes: CPU, Network, Disk and Status check metrics
- Custom metrics
    - Basic: 1min (in custom metrics this is the standard)
    - High resolution: up to 1s (StorageResolution parameter)
    - Includes: RAM, application level metrics
