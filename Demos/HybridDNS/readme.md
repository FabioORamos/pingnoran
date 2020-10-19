Commands used in this demo:

Add a VPC Peering connection and then modify the route tables to accept the connections.

You need to change the named.conf file at /etc

On the ONPREM server type:

sudo nano /etc/named.conf
add the following lines at the end:

zone "aws.pingnoran.com" { 
  type forward; 
  forward only;
  forwarders { 10.16.39.181; 10.16.103.186; }; 
};

After updating and saving the file. Run:

sudo service named restart

The zone name was created in the cloud template
The IP addresses came from the created 'Inbound endpoints' on Route 53.

In the APP instance you need to modify network-eth0 file to include the two IPs from the DNS resolver instances

Type:
sudo nano /etc/sysconfig/network-scripts/ifcfg-eth0

at the end of the script include:
DNS1: PRIVATEIPADDRESSFROMINSTANCEA
DNS2: PRIVATEIPADDRESSFROMINSTANCEB

sudo reboot the instance.


* Create an outbound endpoint to establish connection between AWS and ONPREM servers.

Under Route 53 / Outbound endpoints

Set Rules