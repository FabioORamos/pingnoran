
To save time later in the DEMO we should populate this before configuring the ONPREM environment.  
There are two VPN connections ... one between AWS and ONPREM ROUTER1 and one between AWS and ONPREM ROUTER2  
For each of those there are two tunnels ... AWS Endpoint A -> ONPREMROUTER  
and AWS endpointB -> ONPREMROUTER  

Those are the details we will populate  

# SHARED VALUES

ROUTER1_PRIVATE_IP                  = 192.168.12.31	
ROUTER2_PRIVATE_IP                  = 192.168.12.95
ONPREM BGP ASN                      = 65016  
AWS BGP ASN                         = 64512  

# CONNECTION1 - AWS => ON PREM ROUTER1

CONN1_TUNNEL1_PresharedKey          = .pwYcpX0O66m_Ro1X.d_NQ7BVotyaz3P
CONN1_TUNNEL1_ONPREM_OUTSIDE_IP     = 13.238.141.116 
CONN1_TUNNEL1_AWS_OUTSIDE_IP        = 13.248.186.6 
CONN1_TUNNEL1_ONPREM_INSIDE_IP      = 169.254.202.6/30 
CONN1_TUNNEL1_AWS_INSIDE_IP         = 169.254.202.5/30 
CONN1_TUNNEL1_AWS_BGP_IP            = 169.254.202.5

CONN1_TUNNEL2_PresharedKey          = RTkbTgbx3OPvt0qfPod8_5G6K1iAtW3o 
CONN1_TUNNEL2_ONPREM_OUTSIDE_IP     = 13.238.141.116 
CONN1_TUNNEL2_AWS_OUTSIDE_IP        = 99.83.182.204 
CONN1_TUNNEL2_ONPREM_INSIDE_IP      = 169.254.124.2/30 
CONN1_TUNNEL2_AWS_INSIDE_IP         = 169.254.124.1/30 
CONN1_TUNNEL2_AWS_BGP_IP            = 169.254.124.1 


# CONNECTION2 - AWS => ON PREM ROUTER2

CONN2_TUNNEL1_PresharedKey          = xIOlOenCkaeSLOjcWHcW_efBf.f_yu6P 
CONN2_TUNNEL1_ONPREM_OUTSIDE_IP     = 13.211.209.197 
CONN2_TUNNEL1_AWS_OUTSIDE_IP        = 75.2.62.167 
CONN2_TUNNEL1_ONPREM_INSIDE_IP      = 169.254.204.138/30 
CONN2_TUNNEL1_AWS_INSIDE_IP         = 69.254.204.137/30 
CONN2_TUNNEL1_AWS_BGP_IP            = 169.254.204.137 

CONN2_TUNNEL2_PresharedKey          = Xy7S6ZTv_PS005ZuThzvmBIX12VATAoU 
CONN2_TUNNEL2_ONPREM_OUTSIDE_IP     = 13.211.209.197 
CONN2_TUNNEL2_AWS_OUTSIDE_IP        = 99.83.179.221 
CONN2_TUNNEL2_ONPREM_INSIDE_IP      = 169.254.62.54/30 
CONN2_TUNNEL2_AWS_INSIDE_IP         = 169.254.62.53/30 
CONN2_TUNNEL2_AWS_BGP_IP            = 169.254.62.53 



# INSTRUCTIONS ON GETTING THE VALUES

We will be locating values for a specific connection `CONN1` or `CONN2` and a specific tunnel .. `TUNNEL1` or `TUNNEL2`  

For anything starting with CONN1 .. Look in the `CONNECTION1CONFIG.TXT` file  
For anything starting with CONN2 .. Look in the `CONNECTION2CONFIG.TXT` file  
In each of the above files, for anything showing TUNNEL1 fine the section `IPSec Tunnel #1` in the above files (THE TOP HALF)  
In each of the above files, for anything showing TUNNEL2 fine the section `IPSec Tunnel #2` in the above files (THE BOTTOM HALF)  

For `ROUTER1_PRIVATE_IP` its the 192.168.12.SOMETHING Private IPv4 Address for `ROUTER1` - Check the `Outputs` of the `ONPREM` CFN Stack for `Private IP of Router1`  
For `ROUTER2_PRIVATE_IP` its the 192.168.12.SOMETHING Private IPv4 Address for `ROUTER2` - Check the `Outputs` of the `ONPREM` CFN Stack for `Private IP of Router2`  

For `CONN1_TUNNEL1_PresharedKey` open `CONNECTION1CONFIG.TXT`, Locate `IPSec Tunnel #1`, Locate `- Pre-Shared Key` Your key is there  
For `CONN1_TUNNEL2_PresharedKey` open `CONNECTION1CONFIG.TXT`, Locate `IPSec Tunnel #2`, Locate `- Pre-Shared Key` Your key is there  
For `CONN2_TUNNEL1_PresharedKey` open `CONNECTION2CONFIG.TXT`, Locate `IPSec Tunnel #1`, Locate `- Pre-Shared Key` Your key is there  
For `CONN2_TUNNEL2_PresharedKey` open `CONNECTION2CONFIG.TXT`, Locate `IPSec Tunnel #2`, Locate `- Pre-Shared Key` Your key is there  

For `CONN1_TUNNEL1_ONPREM_OUTSIDE_IP` its the PublicIPv4 Address of `ROUTER1`  
    `CONN1_TUNNEL2_ONPREM_OUTSIDE_IP` its the PublicIPv4 Address of `ROUTER1`  
    `CONN2_TUNNEL1_ONPREM_OUTSIDE_IP` its the PublicIPv4 Address of `ROUTER2`  
    `CONN2_TUNNEL2_ONPREM_OUTSIDE_IP` its the PublicIPv4 Address of `ROUTER2`  

For `CONN1_TUNNEL1_AWS_OUTSIDE_IP` open `CONNECTION1CONFIG.TXT`, locate `IPSec Tunnel #1`, locate `#3: Tunnel Interface Configuration`, locate `Outside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  
For `CONN1_TUNNEL2_AWS_OUTSIDE_IP` open `CONNECTION1CONFIG.TXT`, locate `IPSec Tunnel #2`, locate `#3: Tunnel Interface Configuration`, locate `Outside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  
For `CONN2_TUNNEL1_AWS_OUTSIDE_IP` open `CONNECTION2CONFIG.TXT`, locate `IPSec Tunnel #1`, locate `#3: Tunnel Interface Configuration`, locate `Outside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  
For `CONN2_TUNNEL2_AWS_OUTSIDE_IP` open `CONNECTION2CONFIG.TXT`, locate `IPSec Tunnel #2`, locate `#3: Tunnel Interface Configuration`, locate `Outside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  

For `CONN1_TUNNEL1_ONPREM_INSIDE_IP` open `CONNECTION1CONFIG.TXT`, locate `IPSec Tunnel #1`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Customer Gateway` the value is there  
For `CONN1_TUNNEL2_ONPREM_INSIDE_IP` open `CONNECTION1CONFIG.TXT`, locate `IPSec Tunnel #2`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Customer Gateway` the value is there  
For `CONN2_TUNNEL1_ONPREM_INSIDE_IP` open `CONNECTION2CONFIG.TXT`, locate `IPSec Tunnel #1`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Customer Gateway` the value is there  
For `CONN2_TUNNEL2_ONPREM_INSIDE_IP` open `CONNECTION2CONFIG.TXT`, locate `IPSec Tunnel #2`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Customer Gateway` the value is there  

For `CONN1_TUNNEL1_AWS_INSIDE_IP` open `CONNECTION1CONFIG.TXT`, locate `IPSec Tunnel #1`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  
For `CONN1_TUNNEL2_AWS_INSIDE_IP` open `CONNECTION1CONFIG.TXT`, locate `IPSec Tunnel #2`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  
For `CONN2_TUNNEL1_AWS_INSIDE_IP` open `CONNECTION2CONFIG.TXT`, locate `IPSec Tunnel #1`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  
For `CONN2_TUNNEL2_AWS_INSIDE_IP` open `CONNECTION2CONFIG.TXT`, locate `IPSec Tunnel #2`, locate `#3: Tunnel Interface Configuration`, locate `Inside IP Addresses:`, locate `- Virtual Private Gateway` the value is there  

For `CONN1_TUNNEL1_AWS_BGP_IP` the value is the same as `CONN1_TUNNEL1_AWS_INSIDE_IP`  
For `CONN1_TUNNEL2_AWS_BGP_IP` the value is the same as `CONN1_TUNNEL2_AWS_INSIDE_IP`  
For `CONN2_TUNNEL1_AWS_BGP_IP` the value is the same as `CONN2_TUNNEL1_AWS_INSIDE_IP`  
For `CONN2_TUNNEL2_AWS_BGP_IP` the value is the same as `CONN2_TUNNEL2_AWS_INSIDE_IP`  


