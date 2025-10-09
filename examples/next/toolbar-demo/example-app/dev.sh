#!/bin/bash
set -a
source .env
set +a
next dev --port "$PORT"
