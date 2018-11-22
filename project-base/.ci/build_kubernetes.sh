#!/bin/sh -ex

# For details about this script, see https://github.com/shopsys/shopsys/blob/v7.0.0-beta2/docs/kubernetes/continuous-integration-using-kubernetes.md

# Set domain names for 2 domains by git branch name and server domain
FIRST_DOMAIN_HOSTNAME=${JOB_NAME}.${DEVELOPMENT_SERVER_DOMAIN}
SECOND_DOMAIN_HOSTNAME=2.${JOB_NAME}.${DEVELOPMENT_SERVER_DOMAIN}

# Set domain name into ingress controller so ingress can listen on domain name
yq write --inplace kubernetes/ingress.yml spec.rules[0].host ${FIRST_DOMAIN_HOSTNAME}
yq write --inplace kubernetes/ingress.yml spec.rules[1].host ${SECOND_DOMAIN_HOSTNAME}

# Set domain into webserver hostnames
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.hostAliases[0].hostnames[+] ${FIRST_DOMAIN_HOSTNAME}
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.hostAliases[0].hostnames[+] ${SECOND_DOMAIN_HOSTNAME}

# Create configmaps for configuration files used by pods
kubectl create configmap nginx-configuration --from-file docker/nginx/nginx.conf --dry-run --output=yaml > kubernetes/nginx-configuration.yml
kubectl create configmap postgres-configuration --from-file docker/postgres/postgres.conf --dry-run --output=yaml > kubernetes/postgres-configuration.yml

# Set parameters.yml file and domains_urls
cp app/config/domains_urls.yml.dist app/config/domains_urls.yml
cp app/config/parameters_test.yml.dist app/config/parameters_test.yml
cp app/config/parameters.yml.dist app/config/parameters.yml
yq write --inplace app/config/domains_urls.yml domains_urls[0].url http://${FIRST_DOMAIN_HOSTNAME}
yq write --inplace app/config/domains_urls.yml domains_urls[1].url http://${SECOND_DOMAIN_HOSTNAME}

# Change "overwrite_domain_url" parameter for Selenium tests as containers "webserver" and "php-fpm" are bundled together in a pod "webserver-php-fpm"
yq write --inplace app/config/parameters_test.yml parameters.overwrite_domain_url http://webserver-php-fpm:8080

# Pull or build Docker images for the current commit
DOCKER_IMAGE_TAG=ci-commit-${GIT_COMMIT}

## Docker image for application php-fpm container
docker image pull ${DOCKER_REPOSITORY}/php-fpm:${DOCKER_IMAGE_TAG} || (
    echo "Image not found (see warning above), building it instead..." &&
    docker image build --build-arg github_oauth_token=${GITHUB_OAUTH_TOKEN} \
        --tag ${DOCKER_REPOSITORY}/php-fpm:${DOCKER_IMAGE_TAG} \
        -f docker/php-fpm/ci/Dockerfile \
        . &&
    docker image push ${DOCKER_REPOSITORY}/php-fpm:${DOCKER_IMAGE_TAG}
)

# Replace docker images for php-fpm of application and microservices
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].image ${DOCKER_REPOSITORY}/php-fpm:${DOCKER_IMAGE_TAG}
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[0].image ${DOCKER_REPOSITORY}/php-fpm:${DOCKER_IMAGE_TAG}

# Deploy application using kubectl
kubectl delete namespace ${JOB_NAME} || true
kubectl create namespace ${JOB_NAME}
kubectl apply --namespace=${JOB_NAME} --recursive -f kubernetes

# Wait for containers to rollout
kubectl rollout status --namespace=${JOB_NAME} deployment/adminer --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/elasticsearch --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/postgres --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/redis --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/redis-admin --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/selenium-server --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/smtp-server --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/webserver-php-fpm --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/microservice-product-search --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/microservice-product-search-export --watch

# Find running php-fpm container
PHP_FPM_POD=$(kubectl get pods --namespace=${JOB_NAME} -l app=webserver-php-fpm -o=jsonpath='{.items[0].metadata.name}')

# Run phing build targets for build of the application
kubectl exec ${PHP_FPM_POD} --namespace=${JOB_NAME} ./phing db-create test-db-create build-demo-ci
