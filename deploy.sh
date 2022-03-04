#!/bin/bash

set -e

dir_name='osTicket'

# Read optional parameters
while [[ $# -gt 0 ]]; do
    case $1 in
        --configure-firewall)
          configure_firewall=true
          shift
          ;;
        --delete-setup-directory)
          delete_setup_directory=true
          shift
          ;;
        --renew-certificates)
          renew_certificates=true
          shift
          ;;
        --production)
          deploy_to_production=true
          shift
          ;;
        -*)
          echo "Unknown option $1"
          exit 1
          ;;
        *)
          shift
          ;;
    esac
done

if [ "$deploy_to_production" = true ]; then
  hostname='helpdesk.unibuc.ro'
  env_vars_suffix='production'
else
  hostname='staging.helpdesk.unibuc.ro'
  env_vars_suffix='staging'
fi

# Test the connection
ssh -T "root@$hostname" true

# Read the environment variables from an env file
echo "Reading machine env vars"
env_file=".env.$env_vars_suffix"
env_vars=$(xargs < $env_file)
env_vars="SERVER_NAME='$hostname' $env_vars"

# If requested, configure firewall
if [[ "$configure_firewall" = true ]]
then
  ssh "root@$hostname" "\
    ufw allow ssh && \
    ufw allow http && \
    ufw allow https && \
    ufw allow 6556/tcp && \
    ufw allow 161/udp && \
    ufw allow out 587/tcp && \
    ufw allow out 465/tcp && \
    ufw allow out 465/udp && \
    ufw show added"

  read -p 'Is firewall config good? (y/N)' -n 1 -r
  echo

  if [[ $REPLY =~ ^[Yy]$ ]]
  then
    echo "Rules confirmed, enabling firewall"
    ssh "root@$hostname" "ufw enable"
  fi

  exit
fi

if [[ "$renew_certificates" = true ]]
then
  echo 'Running certbot to renew certificates...'

  source $env_file

  ssh "root@$hostname" "\
        cd $dir_name && \
        docker-compose -f docker-compose-production.yml exec -T osticket \
        certbot --non-interactive --apache --agree-tos --email $LETS_ENCRYPT_EMAIL_ADDRESS --domains $hostname"

  echo 'Done'

  exit
fi

if [[ "$delete_setup_directory" = true ]]
then
  echo 'Deleting setup directory...'

  ssh "root@$hostname" "\
    cd $dir_name && \
    docker-compose -f docker-compose-production.yml exec -T osticket rm -r /var/www/html/setup/"

  echo 'Done'

  exit
fi

echo "Deploying as root to $hostname"

# Upload the modified code
git ftp push --auto-init -u root "sftp://$hostname" --remote-root "/root/$dir_name"

# Rebuild and restart the containers
ssh root@$hostname "\
  cd $dir_name && \
  $env_vars docker-compose -f docker-compose-production.yml up --build -d"
