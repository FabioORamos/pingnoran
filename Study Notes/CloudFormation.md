# CloudFormation

## Resources
* Core of the CloudFormation template (Mandatory)
* Represent the different AWS components that will be created and configured
    * Form: AWS::aws-product-name::data-type-name

## Parameters
* Provide inputs to your AWS CloudFormation
* Important if you want to reuse templates across the company
    * Fn::Ref = function can be leveraged to reference parameters
* Parameters can be used anywhere in a template
    * Shorthand for this in YAML is !Ref
        * !Ref can also reference resources

## Mappings
* Fixed variables within your CloudFormation template
* Values are hardcoded within the template
* Used to differentiate between different environments (prod vs dev), regions (AWS Regions), AMI types…
    * Fn::FindInMap – to access mapping values
    * !FindInMap [Map Name, TopLevelKey, SecondLevelKey]

## Outputs
* Declares optional outputs values that we can import into other stacks
    * Needs to export them first
* Best way to perform collaboration cross stack
* Can’t delete a CloudFormation Stack if its outputs are being referenced by another CloudFormation 

## Conditions
* Used to control the creation of resources or outputs based on a condition
* Common conditions:
    * Environment (dev/test/prod)
    * AWS Region
    * Any parameter value
* Each condition can reference another condition, parameter value or mapping
* Intrinsic function (logical) can be any of the following:
    * Fn::And	
    * Fn::Equals 	
    * Fn::If		
    * Fn::Not	
    * Fn::Or

## Stack
* Cross Stack Reference
    * Create a second template that leverages the security group
    * Use Fn::ImportValue function

## ChangeSets
* You get a prompt informing you of the changes that will occur on your updated stack
* It won’t say if the update will be successful


# NestedStack
* Stacks that are part of other stacks
* Allow you to isolate repeated patterns/common components in separate stacks and call them from other stacks
* Considered best practices
* To update a nested stack, always update the parent (root stack)

# CrossStack
* Helpful when stacks have different life cycles
* When you need to pass export values to many stacks 

# StackSets
* Create, update or delete stacks across multiple accounts and regions with a single operation
* Administrator account to create StackSets
* Trusted accounts to create, update, delete stack instances from StackSets
* When you update a stack set, all associated stack instances are updated throughout all accounts and regions 


## Functions

### Fn::Base64
* Pass user data to the EC2 instance
* User data script log is in **/var/log/cloud-init-output.log** 
* Basic bash script

### Fn::cnf-init
* AWS::CloudFormation::Init must be in the Metadata of a resource
* Helps make complex EC2 configurations readable
* Logs go to **/var/log/cfn-init.log**

### Fn::cnf-signal
* This commands signal CloudFormation to act based on the results of cnf-init
* Tells CloudFormation what to do after success/fail and how long it should wait for an answer
* Need to define **WaitCondition**
    * Template is blocked unitl a signal is received
    * Attach a **CreationPolicy**

## Retaining Data on Deletes
* By default all resources created by CloudFormation are deleted, when you delete the CloudFormation Stack
* To retain resources you can:
    * **DeletionPolicy=Retain**
        * On the resources specify which items to preserve/backup
        * To keep a resource, specify **Retain**
    * **DeletionPolicy=Snapshot**
        * Creates a snapshot of your resources before deleting it
        EBS Volume, ElastiCache Cluster, ElastiCache ReplicationGroup
        * RDS DBInstance, RDS DBCluster, Redshift Cluster

## Update Policies
* To update auto scaling activities, you can use the **UpdatePolicy** attribute
* Use the AWS::AutoScaling::AutoScalingGroups resources
    * AutoScalingReplacingUpdate = Immutable update
    * AutoScalingRollingUpdate
    * AutoScalingScheduledAction