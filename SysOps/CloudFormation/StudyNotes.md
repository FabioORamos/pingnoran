## CloudFormation

# Resources
* Core of the CloudFormation template (Mandatory)
* Represent the different AWS components that will be created and configured
    * Form: AWS::aws-product-name::data-type-name

# Parameters
-	Provide inputs to your AWS CloudFormation
-	Important if you want to reuse templates across the company
-	Fn::Ref function can be leveraged to reference parameters
-	Parameters can be used anywhere in a template
-	Shorthand for this in YAML is !Ref
o	!Ref can also reference resources

# Mappings
-	Fixed variables within your CloudFormation template
-	Values are hardcoded within the template
-	Used to differentiate between different environments (prod vs dev), regions (AWS Regions), AMI types…
-	Fn::FindInMap – to access mapping values
-	!FindInMap [Map Name, TopLevelKey, SecondLevelKey]

#Outputs
-	Declares optional outputs values that we can import into other stacks
o	Needs to export them first
-	Best way to perform collaboration cross stack
-	Can’t delete a CloudFormation Stack if its outputs are being referenced by another CloudFormation 

#Stack
-	Cross Stack Reference
o	Create a second template that leverages the security group
o	Use Fn::ImportValue function

#Conditions
-	Used to control the creation of resources or outputs based on a condition
-	Common conditions:
o	Environment (dev/test/prod)
o	AWS Region
o	Any parameter value
-	Each condition can reference another condition, parameter value or mapping
-	Intrinsic function (logical) can be any of the following:
o	Fn::And	Fn::Equals 	Fn::If		Fn::Not	Fn::Or

#   ChangeSets
-	You get a prompt informing you of the changes that will occur on your updated stack
-	It won’t say if the update will be successful

NestedStack
-	Stacks that are part of other stacks
-	Allow you to isolate repeated patterns/common components in separate stacks and call them from other stacks
-	Considered best practices
-	To update a nested stack, always update the parent (root stack)

CrossStack
-	Helpful when stacks have different life cycles
-	When you need to pass export values to many stacks 


StackSets
-	Create, update or delete stacks across multiple accounts and regions with a single operation
-	Administrator account to create StackSets
-	Trusted accounts to create, update, delete stack instances from StackSets
-	When you update a stack set, all associated stack instances are updated throughout all accounts and regions 

