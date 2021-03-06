# Installation Using Docker

These guides show how to use prepared Docker Compose configuration to simplify the installation process.
You do not need to install and configure the whole server stack (Nginx, PostgreSQL, etc.) in order to run and develop Shopsys Framework on your machine.

## How it works
All the services needed by Shopsys Framework like Nginx or PostgreSQL are run in Docker.
Your source code is automatically synchronized between your local machine and Docker container in both ways.

That means that you can normally use your IDE to edit the code while it is running inside a Docker container.

## Supported systems
- [Linux](installation-using-docker-linux.md)
- [MacOS](installation-using-docker-macos.md)
- [Windows 10 Pro and higher](installation-using-docker-windows-10-pro-higher.md)

## Other systems
If your system is not listed above do not worry, you can still [install the application natively](native-installation.md).
