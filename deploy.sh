#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${ROOT_DIR}/.env.deploy"

if ! command -v lftp >/dev/null 2>&1; then
  echo "Error: lftp is not installed."
  echo "Install on macOS: brew install lftp"
  echo "Install on Ubuntu/Debian: sudo apt-get install lftp"
  exit 1
fi

if [ ! -f "${ENV_FILE}" ]; then
  echo "Error: ${ENV_FILE} not found."
  exit 1
fi

set -a
# shellcheck disable=SC1090
source "${ENV_FILE}"
set +a

: "${FTP_HOST:?Missing FTP_HOST in .env.deploy}"
: "${FTP_USER:?Missing FTP_USER in .env.deploy}"
: "${FTP_PASS:?Missing FTP_PASS in .env.deploy}"
: "${FTP_PORT:=21}"
: "${FTP_REMOTE_DIR:=/htdocs}"

echo "Deploying ${ROOT_DIR} to ftp://${FTP_HOST}:${FTP_PORT}${FTP_REMOTE_DIR}"
echo "This upload does not delete remote files."

lftp -u "${FTP_USER},${FTP_PASS}" -p "${FTP_PORT}" "${FTP_HOST}" <<LFTP
set ftp:ssl-allow no
set net:max-retries 2
set net:timeout 30
set mirror:parallel-transfer-count 2
mkdir -p "${FTP_REMOTE_DIR}"
mirror --reverse --verbose --only-newer \
  --exclude-glob .git/ \
  --exclude-glob node_modules/ \
  --exclude-glob vendor/ \
  --exclude-glob storage/logs/ \
  --exclude-glob tests/ \
  --exclude-glob .env \
  --exclude-glob .env.deploy \
  --exclude-glob .DS_Store \
  --exclude-glob "*.log" \
  "${ROOT_DIR}" "${FTP_REMOTE_DIR}"
bye
LFTP

echo "Deploy finished."
