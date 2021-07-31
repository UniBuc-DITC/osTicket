# osTicket - custom version

This is a customized version of osTicket, adapted for the University of Bucharest's needs.

The original `README` can be found [here](README.original.md).

## Running locally with Docker

You can run a local instance of osTicket using [Docker](https://www.docker.com/) and [docker-compose](https://docs.docker.com/compose/).

If it's the first time you're creating the containers, or after you've deleted the database,
you should copy the `include/ost-sampleconfig.php` file to `docker/ost-config.php`
to run the installer.

Start the services using:

```sh
docker-compose up
```

This starts a container with an Apache web server and a container with a MySQL database.
You can access the local instance at `http://localhost:8080`.

Make sure you specify the following database settings:

- MySQL Hostname: `mysql`
- MySQL Database: `osticket`
- MySQL Username: `osticket`
- MySQL Password: `osticket_pwd`

(this is to match the settings on the `mysql` container)

When creating the admin account, it's recommended to use some easy-to-remember credentials such as:

- Email: `admin@example.com`
- Username: `administrator` (osTicket forbids the `admin` username)
- Password: `Test1234`

## Debugging with Xdebug

The development Docker image supports step-by-step debugging using [the Xdebug plugin](https://xdebug.org/).

Before using this feature, you might want to review and change the configuration settings in `docker/php/conf.d/xdebug.ini`

You will also need to configure your IDE. For VS Code you can use [the PHP Debug extension](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug). For PhpStorm you can follow [these instructions](https://phauer.com/2017/debug-php-docker-container-idea-phpstorm/).
