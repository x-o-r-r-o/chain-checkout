#!/usr/bin/env bash
# Build a clean WordPress plugin ZIP (top-level folder: chain-checkout/).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

VERSION="$(grep -E "define\( 'CHAIN_CHECKOUT_VERSION'" chain-checkout.php | sed -E "s/.*'([0-9.]+)'.*/\1/")"
if [[ -z "${VERSION}" ]]; then
	echo "Could not read CHAIN_CHECKOUT_VERSION" >&2
	exit 1
fi

OUT_DIR="${1:-$(dirname "$ROOT")}"
STAGE="${TMPDIR:-/tmp}/chain-checkout-dist-$$"
ZIP_PATH="${OUT_DIR}/chain-checkout.zip"
ZIP_VERSIONED="${OUT_DIR}/chain-checkout-${VERSION}.zip"

rm -rf "${STAGE}"
mkdir -p "${STAGE}/chain-checkout"

# Prefer rsync + .distignore-style excludes (keep runtime plugin files only).
rsync -a \
	--exclude='.git/' \
	--exclude='.github/' \
	--exclude='.gitignore' \
	--exclude='.gitattributes' \
	--exclude='.DS_Store' \
	--exclude='**/.DS_Store' \
	--exclude='tests/' \
	--exclude='bin/' \
	--exclude='.phpunit*' \
	--exclude='.phpcs*' \
	--exclude='composer.json' \
	--exclude='composer.lock' \
	--exclude='phpunit.xml' \
	--exclude='phpunit.xml.dist' \
	--exclude='node_modules/' \
	--exclude='package.json' \
	--exclude='package-lock.json' \
	--exclude='.editorconfig' \
	--exclude='.eslintrc*' \
	--exclude='.prettierrc*' \
	--exclude='.cursor/' \
	--exclude='.distignore' \
	--exclude='*.zip' \
	./ "${STAGE}/chain-checkout/"

test -f "${STAGE}/chain-checkout/chain-checkout.php"
test -f "${STAGE}/chain-checkout/readme.txt"
test ! -d "${STAGE}/chain-checkout/tests"
test ! -d "${STAGE}/chain-checkout/.git"

rm -f "${ZIP_PATH}" "${ZIP_VERSIONED}"
( cd "${STAGE}" && zip -r -q "${ZIP_PATH}" chain-checkout )
cp -f "${ZIP_PATH}" "${ZIP_VERSIONED}"
shasum -a 256 "${ZIP_PATH}" > "${ZIP_VERSIONED}.sha256"

rm -rf "${STAGE}"

echo "Built WordPress ZIP v${VERSION}"
echo "  ${ZIP_PATH}"
echo "  ${ZIP_VERSIONED}"
echo "  ${ZIP_VERSIONED}.sha256"
ls -lh "${ZIP_PATH}" "${ZIP_VERSIONED}"
