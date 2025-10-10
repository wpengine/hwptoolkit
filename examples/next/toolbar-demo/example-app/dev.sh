#!/bin/bash
set -a
source .env
set +a
npx next dev --port "$PORT"
