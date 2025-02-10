#!/bin/bash

# Stop and remove all Docker containers with wp-env prefix
docker ps -a -q -f name=wp-env | xargs -r docker rm -f

# Remove wp-env state directories
sudo rm -rf ~/.wp-env/*

# Prune Docker networks
docker network prune -f
