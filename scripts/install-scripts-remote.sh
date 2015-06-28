#!/bin/bash

mkdir -p /usr/local/src/sogo-ispconfig/

cd /usr/local/src/sogo-ispconfig/

wget --no-check-certificate http://raw.githubusercontent.com/cmjnisse/sogo-ispconfig/master/scripts/sogo-ispconfig-checkout-master.sh -O /usr/local/src/sogo-ispconfig/sogo-ispconfig-checkout-master.sh

wget --no-check-certificate http://raw.githubusercontent.com/cmjnisse/sogo-ispconfig/master/scripts/sogo-ispconfig-checkout-testing.sh -O /usr/local/src/sogo-ispconfig/sogo-ispconfig-checkout-testing.sh

wget --no-check-certificate http://raw.githubusercontent.com/cmjnisse/sogo-ispconfig/master/scripts/sogo-ispconfig-clone.sh -O /usr/local/src/sogo-ispconfig/sogo-ispconfig-clone.sh

wget --no-check-certificate http://raw.githubusercontent.com/cmjnisse/sogo-ispconfig/master/scripts/sogo-ispconfig-full-update.sh -O /usr/local/src/sogo-ispconfig/sogo-ispconfig-full-update.sh

wget --no-check-certificate http://raw.githubusercontent.com/cmjnisse/sogo-ispconfig/master/scripts/sogo-ispconfig-pull.sh -O /usr/local/src/sogo-ispconfig/sogo-ispconfig-pull.sh

wget --no-check-certificate http://raw.githubusercontent.com/cmjnisse/sogo-ispconfig/master/scripts/sogo-ispconfig-update.sh -O /usr/local/src/sogo-ispconfig/sogo-ispconfig-update.sh

chmod +x -R /usr/local/src/sogo-ispconfig/