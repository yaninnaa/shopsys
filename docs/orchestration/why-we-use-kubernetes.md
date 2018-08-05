# Deploy your application using code in your repository
In the past, infrastructure of application and its deployment was completely decoupled from development of application.
Application was most of the time written by developers in one company and infrastructure with deployment of app was done by another company.

This led to many problems.

Developers did not exactly know on which infrastructure is their application running on and their local infrastructure was different from 
one on the production server, this lead to application not running correctly on production environment while being flawless on developers local computer.
 
And this was not bad only for developers of application. If a customer decided to move to higher performance server,
whole infrastructure needed to be reconfigured to another server.

## Kubernetes
Kubernetes can use our docker infrastructure and images and deploy them as a whole onto servers. Since it uses docker images
used for local developments, you can be always sure that the application is deployed functional.

Lets go through how it works.
