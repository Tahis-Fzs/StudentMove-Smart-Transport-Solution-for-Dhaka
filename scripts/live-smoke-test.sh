#!/usr/bin/env bash
set -uo pipefail
BASE="${1:-https://studentmove-app-d866.onrender.com}"
COOKIE="/tmp/sm-smoke-cookies.txt"
rm -f "$COOKIE"

pass=0
fail=0
warn=0

check() {
  local name="$1" expected="$2" actual="$3"
  if [[ "$actual" == "$expected" ]]; then
    echo "PASS $name ($actual)"
    ((pass++))
  else
    echo "FAIL $name expected=$expected got=$actual"
    ((fail++))
  fi
}

warn_if() {
  local name="$1" cond="$2"
  if [[ "$cond" == "1" ]]; then
    echo "WARN $name"
    ((warn++))
  else
    echo "OK   $name"
  fi
}

echo "=== Live smoke test: $BASE ==="

paths=(
  "/"
  "/login"
  "/register"
  "/health.html"
  "/next-bus-arrival"
  "/subscription"
  "/route-suggestion"
  "/admin/login"
  "/driver/login"
  "/forgot-password"
  "/email-setup"
  "/manifest.webmanifest"
  "/sw.js"
)

for p in "${paths[@]}"; do
  code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 "$BASE$p" 2>/dev/null || echo ERR)
  check "GET $p" "200" "$code"
done

# Auth redirects
for p in "/dashboard" "/offers" "/chat" "/feedback" "/bookings" "/profile" "/notifications" "/subscription/history"; do
  code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 "$BASE$p" 2>/dev/null || echo ERR)
  check "GET $p (guest)" "302" "$code"
done

# Admin protected
code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 "$BASE/admin/dashboard" 2>/dev/null || echo ERR)
check "GET /admin/dashboard (guest)" "302" "$code"

# Driver protected
code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 "$BASE/driver/dashboard" 2>/dev/null || echo ERR)
check "GET /driver/dashboard (guest)" "302" "$code"

# Content checks
driver_html=$(curl -sS -c "$COOKIE" --max-time 25 "$BASE/driver/login" 2>/dev/null || true)
if echo "$driver_html" | grep -q 'BUS-001'; then
  echo "PASS driver login lists BUS-001"
  ((pass++))
else
  echo "FAIL driver login missing BUS-001"
  ((fail++))
fi

map_html=$(curl -sS --max-time 25 "$BASE/next-bus-arrival" 2>/dev/null || true)
if echo "$map_html" | grep -q 'BUS-001'; then
  echo "PASS live map includes BUS-001"
  ((pass++))
else
  echo "FAIL live map missing BUS-001"
  ((fail++))
fi

login_html=$(curl -sS --max-time 25 "$BASE/login" 2>/dev/null || true)
if echo "$login_html" | grep -q 'studentmove-dev-edec3'; then
  echo "PASS Firebase project configured"
  ((pass++))
else
  echo "FAIL Firebase project wrong/missing"
  ((fail++))
fi

sub_html=$(curl -sS --max-time 25 "$BASE/subscription" 2>/dev/null || true)
if echo "$sub_html" | grep -qi 'sslcommerz'; then
  echo "PASS subscription shows SSLCommerz"
  ((pass++))
else
  echo "FAIL subscription missing SSLCommerz"
  ((fail++))
fi

# API bus location
api_code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 "$BASE/api/bus/get-location/1" 2>/dev/null || echo ERR)
check "GET /api/bus/get-location/1" "200" "$api_code"

# SSE opens (not 404)
sse_head=$(curl -sS -m 2 -N "$BASE/api/bus/stream/1" 2>/dev/null | head -1 || true)
if [[ "$sse_head" == *"keepalive"* ]] || [[ -n "$sse_head" ]]; then
  echo "PASS SSE /api/bus/stream/1 opens"
  ((pass++))
else
  echo "FAIL SSE /api/bus/stream/1"
  ((fail++))
fi

# Driver login flow with CSRF
csrf=$(echo "$driver_html" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)
if [[ -n "$csrf" ]]; then
  driver_code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 \
    -c "$COOKIE" -b "$COOKIE" \
    -X POST "$BASE/driver/login" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    --data-urlencode "_token=$csrf" \
    --data-urlencode "bus_id=1" \
    --data-urlencode "password=driver123" 2>/dev/null || echo ERR)
  check "POST driver login" "302" "$driver_code"

  dash_code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 -b "$COOKIE" "$BASE/driver/dashboard" 2>/dev/null || echo ERR)
  check "GET driver dashboard (logged in)" "200" "$dash_code"
else
  echo "FAIL could not extract driver CSRF token"
  ((fail+=2))
fi

# Route suggestion query
suggest_code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 25 "$BASE/route-suggestion?from=Uttara&to=DSC" 2>/dev/null || echo ERR)
if [[ "$suggest_code" == "200" || "$suggest_code" == "302" ]]; then
  echo "PASS route-suggestion ($suggest_code)"
  ((pass++))
else
  echo "FAIL route-suggestion ($suggest_code)"
  ((fail++))
fi

echo ""
echo "=== Summary: $pass passed, $fail failed, $warn warnings ==="
exit $([[ $fail -eq 0 ]] && echo 0 || echo 1)
