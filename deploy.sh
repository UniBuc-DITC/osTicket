#!/bin/bash

set -e

dir_name='osTicket'

# Read optional parameters
while [[ $# -gt 0 ]]; do
    case $1 in
        --configure-firewall)
          configure_firewall=true
          shift
          shift
          ;;
        --production)
          deploy_to_production=true
          shift
          shift
          ;;
        *) break
    esac
    shift
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
env_vars=$(cat ".env.$env_vars_suffix" | xargs)

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

echo "Deploying as root to $hostname"

# Upload the modified code
git ftp push --auto-init -u root "sftp://$hostname" --remote-root "/root/$dir_name"

# Rebuild and restart the containers
ssh root@$hostname "\
  cd $dir_name && \
  $env_vars docker-compose -f docker-compose-production.yml up --build -d"
