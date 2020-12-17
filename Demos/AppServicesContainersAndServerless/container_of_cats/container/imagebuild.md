# Container of dogs docker image

Ideally run this on an EC2 instance within AWS (running amazon linux)
Needs internet access for image upload to dockerhub
You will need a dockerhub account
To test the container ... tcp/80 will need to be open on the instance security group


## Download, Install and Configure docker and tools

bash
cd
sudo amazon-linux-extras install docker
sudo service docker start
sudo usermod -a -G docker ssm-user //allows ssm manager to interact with Docker

## Use Git to get the lesson files

sudo yum install git
git clone https://github.com/FabioORamos/pingnoran.git

## Build the docker image

cd pingnoran/Demos/AppServicesContainersAndServerless/container_of_cats/container
docker build -t containerofcats .
docker images --filter reference=containerofcats

## Test the image by running a container

docker run -t -i -p 80:80 containerofcats

## Upload image to docker hub

docker login --username YOUR_USER
docker images
docker tag IMAGEID YOUR_USER/containerofcats
docker push YOUR_USER/containerofcats