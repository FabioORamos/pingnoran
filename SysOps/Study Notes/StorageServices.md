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

## S3
* Standard IA
    * Minimum storage duration: 30 days
    * Minimum capacity charge: 128KB per object
    * Data retrieval fee charged on a per GB basis

* One Zone IA
    * Same as Standard IA, but data is stored in only ONE AZ
    * Used for long-lived data, which is non-critical and replaceable

* S3 Glacier
    * Objects are not readily available
    * Requires user to start a retrieval process
    * Data is retrieved to S3 Standard-IA temporarily
    * Retrievals mode:
        * Expedited (1-5 minutes)
        * Standard (3-5 hours)
        * Bulk (5-12 hours)
    * First byte latency: minutes or hours
    * 40KB min size
    * 90 day min duration

* S3 Glacier Deep Archive
    * Cheapest form of S3 storage
    * Similar to S3 Glacier but:
        * 180 day min duration
        * Retrievals mode:
            * Standard (12 hours)
            * Bulk (up to 48 hours)
            * First byte latency: hours or days

* S3 Intelligent-Tiering
    * Two tiers:
        * Frequent and Infrequent Access
    * Monitoring and Automation cost
    * Should only be used for long-lived dta, with changing or unknown patterns

* S3 Replication

Different AWS accounts
* Destination bucket needs to have a **bucket policy**  granting the IAM Role permission to write/replicate objects from the Source bucket.

### S3 Replication options

* Cross-Region Replication (CRR) vs Same-Region Replication (SRR)
* Al objects or a subset
* Storage class: default is to maintain
* Ownership: default is the source account
* Replication Time Control (RTC) if you need to meet compliance of having objects replicated within 15 mins
* Replication considerations
    * Not retroactive
    * Versioning must be ON
    * Can replicate unencrypted, SSE-S3 and SSE-KMS
    * Can't replicate objects:
        * Encrypted with SSE-C
        * Events related to systems events (i.e. life cycle policies), Glacier or Glacier Deep Archive 
        * No deletes are replicated
* Use cases for SRR:
    * Log aggregation
    * Prod and Test Sync
    * Resilience with strict sovereignty
* Use cases for CRR:
    * Global resilience improvements
    * Latency reduction
  