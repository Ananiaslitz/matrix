#!/usr/bin/env bash
# test-e2e.sh — testa isolamento multi-tenant da lib matrix via HTTP
set -e

HOST="${1:-http://localhost:9501}"
PASS=0
FAIL=0

check() {
    local label="$1"
    local expected_status="$2"
    local actual_status="$3"
    local body="$4"

    if [ "$actual_status" = "$expected_status" ]; then
        echo "  ✅ $label (HTTP $actual_status)"
        PASS=$((PASS+1))
    else
        echo "  ❌ $label — esperado HTTP $expected_status, recebeu $actual_status"
        echo "     Body: $body"
        FAIL=$((FAIL+1))
    fi
}

echo ""
echo "═══════════════════════════════════════════════════════"
echo "  Matrix Multi-Tenant E2E Tests"
echo "  Target: $HOST"
echo "═══════════════════════════════════════════════════════"

# ── Tenant: ACME ────────────────────────────────────────────
echo ""
echo "▶ Tenant: acme"
R=$(curl -s -w "\n%{http_code}" -H "X-Tenant-ID: acme" "$HOST/tenant/current")
STATUS=$(echo "$R" | tail -1); BODY=$(echo "$R" | head -1)
check "GET /tenant/current" "200" "$STATUS" "$BODY"
echo "   $BODY"

R=$(curl -s -w "\n%{http_code}" -H "X-Tenant-ID: acme" "$HOST/tenant/ping")
STATUS=$(echo "$R" | tail -1); BODY=$(echo "$R" | head -1)
check "GET /tenant/ping (db alive)" "200" "$STATUS" "$BODY"
echo "   $BODY"

# ── Tenant: Globo ────────────────────────────────────────────
echo ""
echo "▶ Tenant: globo"
R=$(curl -s -w "\n%{http_code}" -H "X-Tenant-ID: globo" "$HOST/tenant/current")
STATUS=$(echo "$R" | tail -1); BODY=$(echo "$R" | head -1)
check "GET /tenant/current" "200" "$STATUS" "$BODY"
echo "   $BODY"

R=$(curl -s -w "\n%{http_code}" -H "X-Tenant-ID: globo" "$HOST/tenant/ping")
STATUS=$(echo "$R" | tail -1); BODY=$(echo "$R" | head -1)
check "GET /tenant/ping (db alive)" "200" "$STATUS" "$BODY"
echo "   $BODY"

# ── Tenant: isolamento — conexões devem ser diferentes ───────
echo ""
echo "▶ Isolamento de conexões"
CONN_ACME=$(curl -s -H "X-Tenant-ID: acme"  "$HOST/tenant/current" | grep -o '"connection":"[^"]*"')
CONN_GLOB=$(curl -s -H "X-Tenant-ID: globo" "$HOST/tenant/current" | grep -o '"connection":"[^"]*"')
if [ "$CONN_ACME" != "$CONN_GLOB" ]; then
    echo "  ✅ Conexões isoladas: acme=$CONN_ACME | globo=$CONN_GLOB"
    PASS=$((PASS+1))
else
    echo "  ❌ Conexões NÃO isoladas: ambas retornaram $CONN_ACME"
    FAIL=$((FAIL+1))
fi

# ── Tenant inválido — deve retornar 404 ─────────────────────
echo ""
echo "▶ Tenant inválido: fantasma"
R=$(curl -s -w "\n%{http_code}" -H "X-Tenant-ID: fantasma" "$HOST/tenant/current")
STATUS=$(echo "$R" | tail -1); BODY=$(echo "$R" | head -1)
check "GET /tenant/current (deve ser 404)" "404" "$STATUS" "$BODY"
echo "   $BODY"

# ── Sem header — deve retornar 404 ──────────────────────────
echo ""
echo "▶ Sem header X-Tenant-ID"
R=$(curl -s -w "\n%{http_code}" "$HOST/tenant/current")
STATUS=$(echo "$R" | tail -1); BODY=$(echo "$R" | head -1)
check "GET /tenant/current (sem header, deve ser 404)" "404" "$STATUS" "$BODY"
echo "   $BODY"

# ── Resultado final ──────────────────────────────────────────
echo ""
echo "═══════════════════════════════════════════════════════"
TOTAL=$((PASS+FAIL))
echo "  Resultado: $PASS/$TOTAL passaram"
if [ "$FAIL" -eq 0 ]; then
    echo "  🎉 Todos os testes passaram!"
else
    echo "  ⚠️  $FAIL teste(s) falharam"
fi
echo "═══════════════════════════════════════════════════════"
echo ""
