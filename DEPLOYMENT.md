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
- `--staging`: makes the script work with the staging environment (the default).
- `--production`: makes the script work with the production environment, as described above.
- `--verbose`: makes the script run in verbose mode.
- `--insecure`: tells `git-ftp` to ignore the server's certificates.
- `--configure-firewall`: makes the script configure and turn on the target machine's firewall,
  instead of deploying anything.
- `--renew-certificates`: ask `certbot` to renew the TLS certificates for the target server.
  After running this once, assuming the container doesn't get deleted, a script will be configured by `certbot`
  to automatically renew the certificates when needed.
- `--delete-setup-directory`: deletes the `setup` directory of the osTicket install. Useful after app updates
  or (re)deployments.
It will also use the `SSH_KEY_PATH` environment, if set, to determine which SSH key to use for connecting to the server.

After deploying a new version of the app, you should run `--renew-certificates` to make sure all the TLS certificates
are fresh and configured, and then `--delete-setup-directory`, since otherwise osTicket will complain.

Therefore, the full command you should run for **updating** an existing installation is:

```sh
./deploy.sh && ./deploy.sh --renew-certificates && ./deploy.sh --delete-setup-directory
```

## Installing plugins and language packs

The production `compose.yaml` file is configured to mount the `include/plugins` and `include/i18n` directories
from the host machine as read-only.

Any plugins/language packs installed into these directories will be available for activation
from the osTicket admin console.
