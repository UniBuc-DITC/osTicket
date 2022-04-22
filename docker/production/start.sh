#!/usr/bin/env bash

set -ex

# Boot up cron
service cron start

# Start the HTTP server
exec apache2-foreground
