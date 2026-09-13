#!/usr/bin/env bash
set -euo pipefail

if [ -n "${WP_TESTS_DIR:-}" ] && [ -d "${WP_TESTS_DIR}" ]; then
    chmod -R u+rwX "${WP_TESTS_DIR}" 2>/dev/null || true
fi
