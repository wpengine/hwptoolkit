#!/bin/bash

echo "Stopping wp-env containers..."
docker-compose -f ~/.wp-env/*/docker-compose.yml down 2>/dev/null || true

echo "Removing wp-env containers..."
docker ps -a -q -f name=wp-env | xargs -r docker rm -f

echo "Removing wp-env volumes..."
docker volume ls -q -f name=wp-env | xargs -r docker volume rm -f

echo "Removing WordPress volumes..."
docker volume ls -q -f name=wordpress | xargs -r docker volume rm -f

echo "Removing all test volumes..."
docker volume ls -q -f name=tests-wordpress | xargs -r docker volume rm -f

echo "Cleaning up networks..."
docker network prune -f

echo "Cleanup complete! You may need to wait a few seconds before starting wp-env again."
