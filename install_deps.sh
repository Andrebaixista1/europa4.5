#!/usr/bin/env bash
set -euo pipefail

# Installs PHP DOM extension and project dependencies.

sudo apt-get update
sudo apt-get install -y php-xml
composer install
