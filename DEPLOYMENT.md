# Deployment instructions

Deploying the app into production is done using the `deploy.sh` script:

```sh
./deploy.sh
```

The deployment script is configured to deploy to the staging environment
([staging.helpdesk.unibuc.ro](https://staging.helpdesk.unibuc.ro)) by default. You can instead deploy to production
by appending the `--production` flag.

You can also provide the deployment script with the following parameters:
- `--production`: makes the script work with the production environment, as described above.
- `--configure-firewall`: makes the script configure and turn on the target machine's firewall,
  instead of deploying anything.
- `--delete-setup-directory`: deletes the `setup` directory of the osTicket install. Useful after app updates
  or (re)deployments.
