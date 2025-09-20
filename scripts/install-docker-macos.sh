#!/usr/bin/env bash
set -euo pipefail

if [[ "$(uname)" != "Darwin" ]]; then
  echo "This script is for macOS only."
  exit 1
fi

if command -v docker >/dev/null 2>&1; then
  echo "Docker is already installed: $(docker --version)"
  exit 0
fi

if command -v brew >/dev/null 2>&1; then
  echo "Homebrew found. Installing Docker Desktop via brew cask..."
  brew install --cask docker
  echo "Docker Desktop installed. Please open Docker.app from /Applications and allow it to start."
  echo "You may need to give Docker permission in System Preferences > Security & Privacy."
  exit 0
fi

cat <<'MSG'
Docker is not installed and Homebrew was not found.

Manual install steps:
1) Install Docker Desktop for Mac: https://www.docker.com/products/docker-desktop
2) Open the downloaded .dmg and move Docker.app to /Applications
3) Launch Docker.app and wait for it to initialize (whale icon in menu bar)
4) Verify in terminal:
   docker --version
   docker compose version

If you want Homebrew first, install it from https://brew.sh and re-run this script:
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   brew install --cask docker

MSG

exit 0
