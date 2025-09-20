#!/usr/bin/env bash
set -euo pipefail

HOST=${1:-}
PORT=${2:-}
TIMEOUT=${3:-30}

if [[ -z "$HOST" || -z "$PORT" ]]; then
  echo "Usage: $0 host port [timeout_seconds]"
  exit 2
fi

echo "Waiting for $HOST:$PORT (timeout ${TIMEOUT}s) ..."
SECS=0
until curl -sSf "http://${HOST}:${PORT}" >/dev/null 2>&1; do
  sleep 1
  SECS=$((SECS+1))
  if [[ $SECS -ge $TIMEOUT ]]; then
    echo "Timeout waiting for $HOST:$PORT"
    exit 1
  fi
done

echo "$HOST:$PORT is available"
