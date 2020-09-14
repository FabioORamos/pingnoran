# Storage Services
## FSx for Windows File Server
* VSS - user-driven restores
* Native file system accessible over SMB
* Windows permission model
* Supports DFS
* Managed - no file server admin
* Integrates with DS and your own directory
* Accessible using VPC, Peering, VPN, Direct Connect 

## FSx for Lustre
* Managed Lustre - Designed for HPC - Linux Clients (POSIX)
* Machine Learning, Big Data, Financial Modelling
* Deployment types:
    * Persistent: longer term, HA (in one AZ), self-healing
    * Scracth: highly optimised for short term, no replication, fast
* Accessible over VPN or Direct Connect



## EFS
* EFS is an implementation of NFSv4
* Mounted in Linux
* Shared between many EC2 instances
* Private service, via mount targets inside a VPC
* Can be accessed from on-premises: VPN or DX 
* When using an EFS to be accessed by multiple EC2 instances, your EC2 instances need an ENI
* POSIX permissions
* Use EFS for normal share data loads

* Max/IO: good for parallel work loads, big data, genomics
 increase in latency compared with General Purpose
 