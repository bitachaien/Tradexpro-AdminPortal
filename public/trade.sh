#!/bin/bash

limit=${1:-3}
i=0
while [[ $i -lt limit ]]; do
  sleep 2.5
  cd ../ && php artisan trading:bot &
  i=$((i+1))
done