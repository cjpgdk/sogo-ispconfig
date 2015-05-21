#!/bin/bash

if [ ! -d "/usr/local/src/" ]; then
    mkdir -p /usr/local/src/
fi

git clone https://github.com/cmjnisse/sogo-ispconfig.git /usr/local/src/sogo-ispconfig/