# Deployment instructions

Deploying the app into production is done using the `deploy.sh` script:

```sh
./deploy.sh
```

The deployment scripts expects you to have a file `.env.staging` or `.env.production` in this directory,
with the environment variables associated with this deployment.

The env vars which must be defined are:

```sh
MARIADB_ROOT_PASSWORD=# Database root user password
MARIADB_PASSWORD=# Database osTicket user password
LETS_ENCRYPT_EMAIL_ADDRESS=# E-mail address for Let's Encrypt notifications
```

The deployment script is configured to deploy to the staging environment
([staging.helpdesk.unibuc.ro](https://staging.helpdesk.unibuc.ro)) by default. You can instead deploy to production
by appending the `--production` flag.

You can also provide the deployment script with the following parameters:
- `--production`: makes the script work with the production environment, as described above.
- `--configure-firewall`: makes the script configure and turn on the target machine's firewall,
  instead of deploying anything.
- `--renew-certificates`: ask `certbot` to renew the TLS certificates for the target server.
  After running this once, assuming the container doesn't get deleted, a script will be configured by `certbot`
  to automatically renew the certificates when needed.
- `--delete-setup-directory`: deletes the `setup` directory of the osTicket install. Useful after app updates
  or (re)deployments.
