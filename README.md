# WaaS Host & Client Plugins

Two plugins used to create a communication between a host and multiple tenants in a multi-tenant WordPress setup.

## Features

- Tenant Creation
- Subscription Management
- Role-based Plugins Activation
- Adding new Add-ons
- Changing Domains
- Root-Domain for created tenants
- Tenants domains
- Single-Sign On

## [YouTube Video](http://www.youtube.com/watch?v=hqpPOjLvIig)

[![Building Monday.com SaaS Clone using WordPress, WooCommerce, WPCS & k8s](http://img.youtube.com/vi/hqpPOjLvIig/0.jpg)](http://www.youtube.com/watch?v=xxxxx "Building Monday.com SaaS Clone using WordPress, WooCommerce, WPCS & k8s")

## Local Development

### Required tools

- Docker and Docker-Compose
- Composer
- `fswarch` & `rsync`

### Steps

- `git clone` the project
- Run `composer install` inside `src` directory
- Run to create dist directories  `mkdir dist && mkdir dist/host && mkdir dist/client`
- Run `docker-compose up`
- Run the following command to sync files between src and dist

## Building

Run the following command to build the plugins

```shell
bash scripts/build.sh
```
