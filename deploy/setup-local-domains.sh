#!/bin/bash
set -e
HOSTS_FILE=/etc/hosts
for host in theme1.ecommerce.test theme2.ecommerce.test; do
  if ! grep -q "$host" "$HOSTS_FILE"; then
    echo "127.0.0.1 $host" >> "$HOSTS_FILE"
    echo "Added $host"
  else
    echo "Already present: $host"
  fi
done
/Applications/XAMPP/xamppfiles/xampp reloadapache
echo "Domains ready:"
echo "  http://theme1.ecommerce.test/  (or http://theme1.localhost/)"
echo "  http://theme2.ecommerce.test/  (or http://theme2.localhost/)"
