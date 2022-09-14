# Monday.com SaaS Clone using WordPress, WooCommerce, WPCS & k8s

This is a Monday.com Clone build using various technologies to illustrate how easy it is to build SaaS products using
WordPress

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
