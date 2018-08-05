# How to set  up your own CI or deploy using Kubernetes
While our deployment and infrastructure is defined as a code. You can use it for your own projects CI and deployment
with minimum effort.

## Prepositions
Even though manifests and deployment is part of a repository. There are some prepositions that needs to be ready
for deployment or continuous integration.

### CI
CI takes care of building branches that gets pushed and do all the work.

Right now we use [CircleCI](https://circleci.com/) as our CI thats why you can find `.circleci` folder in your project.

If you want to use different CI, it should not be problem, you just need to rewrite `.circleci/config.yml` to format that your CI understands,
commands their selves should not be different from one written in our config.

#### Configure environments variables
In our `.circleci/config.yml` we got couple of variables that are individual for each project.

| Environment variable           | Explenation                                                                            
| ------------------------------ | ------------ 
| **$DOCKER_USERNAME**           | your login to docker hub
| **$DOCKER_PASSWORD**           | your password to docker hub
| **$DOCKER_IMAGE**              | name under which you want your php-fpm image to be pushed to docker hub
| **$WWW_DATA_UID**              | id of user running the kubectl commands on cluster server
| **$WWW_DATA_GID**              | id of group running the kubectl commands on cluster server
| **$KUBERNETES_CONFIG_FILE**    | content of kube config ~/.kube/config located in home folder of user running kubernetes processes
| **$DEVELOPMENT_SERVER_DOMAIN** | domain name of your server where you want to deploy your application
| **$CLUSTER_SSH_LOGIN**         | ssh login to your cluster server
| **$SERVER_SSL_CERT**           | SSL certificate to your cluster server, must be associated with ssh login username

Set these variables as env variables in your CI

### Node server
Applications needs to be deployed somewhere that is why we need server that will carry all of our application containers.

#### Install node server
Our server is running CentOS 7. Following commands are for centOS and may be different on other distributions.

Install repositories required by docker and kubernetes:

```
yum install -y yum-utils device-mapper-persistent-data lvm2
```

Install Docker required by Kubernetes to work and enable it as a service:

Note: *Enabling Docker as a service causes that Docker is always started even after restart of the system*
```
yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

yum install -y docker-ce

systemctl enable docker && systemctl start docker
```


Disable security processes that are in conflict with Kubernetes.

```
setenforce 0

swapoff -a 
```

Install Kubernetes and tools for controlling it (Kubelet, Kubectl, Kubeadm):
```
yum install -y kubelet kubeadm kubectl --disableexcludes=kubernetes
```

Enable Kubelet as a service so it starts with system reboot
```
systemctl enable kubelet && systemctl start kubelet
```

Fix possible IP tables issues:
```
cat <<EOF >  /etc/sysctl.d/k8s.conf
net.bridge.bridge-nf-call-ip6tables = 1
net.bridge.bridge-nf-call-iptables = 1
EOF
sysctl --system
```

Create cluster on your server and define IP range for pods.
```
kubeadm init --pod-network-cidr=192.168.0.0/16
```

Allow your user to use `kubectl` commands.
Choose user that will be running all Kubernetes processes on your server. Please make sure that this
user matches with user used for logging to ssh in `.circleci/config.yml`. For example we created for this
purposes user called `www-data` so we will use him as a example:

```
mkdir -p /home/www-data/.kube
cp -i /etc/kubernetes/admin.conf /home/www-data/.kube/config
chown www-data:www-data /home/www-data/.kube/config
```

Start Calico networking plugin for enabling communication between pods and nodes.
```
kubectl apply -f https://docs.projectcalico.org/v3.1/getting-started/kubernetes/installation/hosted/kubeadm/1.7/calico.yaml
```

Make our server a master node:
```
kubectl taint nodes --all node-role.kubernetes.io/master-
```

#### Install ingress controller
Every node needs an entrypoint through which will be traffic going.
In our case we use ingress thanks to its easy listening to specific domains. This works simillar as a 
nginx on server. You specify on which server name should ingress listen and specify to which pod must
traffic go.

