# EC2 running Wordpress

You can determine status code returned by the backend instance by performing a curl or net-cat operation on the health check target page from an instance in the same subnet as that of the load balancer or any other host machine if the security groups and network ACLs are open. Run the below command from such an instance and analyze the output :

`curl -vo /dev/null http://IP address/index.html`

If the WP gets changed to 'static website', the Load Balancer will fail because it will look for a index.html on `/var/www/html`
