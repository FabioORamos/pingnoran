# Advanced Hybrid Directory Demo

In this part of the demo you will be connecting to the simulated ONPREMISES Jumpbox. You will:
- Install a Remote Desktop Client Application  
- Setup the Remote Desktop Connection  
- Connect to the Jumpbox  
- Access the Simulated Client Machine  
- Verify the On-Premises File Server is working ok.  


# Install a MS Remote Desktop Application

macOS : https://apps.apple.com/us/app/microsoft-remote-desktop/id1295203466?mt=12  
Windows : built in, Search for `Remote Desktop`  
Linux : Various 3rd party solutions available   

# Locate the Jumpbox Details  

In the EC2 console select `JumpBox`  
Locate the `Public DNS (IPv4)` for the Jumpbox and note down the DNS name  
Right Click and Select `Connect`  
Note down the `username` it should be `Administrator` (note this down as JumpBox Username)  
Click on `Get Password`  
Click on `Choose file`  
Locate the `Ping.pem` file you downloaded earlier and click `Open`  
Click on `Decrypt Password` and note down the password as `JumpBox Password`  

# Connect to the Jumpbox  

Use the remote desktop application to connect to the Jumpbox  
You will need :-  
- server address (this might be called differently, its the `Public DNS` value above)   
- Username ... should be `Administrator`  
- Password ... the `Jumpbox Password` you noted down above  

If there are any resolution settings `DONT` use fullscreen and set a resolution lower than your screen resolution (so you can see the instructions)  

# Record the other connection information for other instances  

For each of the following instances :-  

- Client  
- FileServer  
- DC1  
- DC2  

Record the value from `Private IPs` in the EC2 console, and record which Instance name it relates to.  

e.g. DC1 PrivateIP `192.168.12.5` (make sure you use your own private IPs)  

# Connect to the 'client' instance  

This instance simulates a client machine within the A4L Onpremises environment  
Click `windows start button`  
Type `mstsc` or `remote desktop`  
Open the application  
connect to `Client` by entering the PrivateIP for `Client` you noted down above in the `computer` box  
Username `Admin@ad.animals4life.org`  
Password `YOUR_DOMAIN_ADMIN_PASSWORD`  
Click `OK`  
Answer `Yes` to ID/Certificate warning  
Move the RDP tab at the top to the far left ... this will mean when closing things down... you know which is your connection to the client machine ...   

# Browse to the Pingo FileServer  

From the Client instance  
Click Start ... type \\FileServer\PingoFiles  
See that we have a (small) FileShare here  

Create a text document  
'What animal are the best.txt'  
Add your favorite animals in there  

# STAGE 2 - FINISH  
Once you have connected ... you can finish this part of the DEMO  

