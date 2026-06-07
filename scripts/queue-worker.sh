#!/bin/bash
# ============================================================
#  AnsarEats — Laravel Queue Worker (cPanel Safe)
#  Trick: worker runs for ~50s then gracefully stops, sleeps
#  a few seconds, and re-launches itself — so the cPanel
#  cron (runs every minute) never sees a zombie process AND
#  the server cannot force-kill a "hung" long-running daemon.
# ============================================================

# ── Paths (adjust if your cPanel home differs) ───────────────
PHP_BIN="/usr/local/bin/php"
APP_DIR="/home/tojjwrtk/ansar-eats"
LOG_FILE="$APP_DIR/storage/logs/queue-worker.log"
PID_FILE="/tmp/ansareats_queue.pid"

# ── Safety: only one instance at a time ─────────────────────
if [ -f "$PID_FILE" ]; then
    OLD_PID=$(cat "$PID_FILE")
    if kill -0 "$OLD_PID" 2>/dev/null; then
        # Another instance is already running — exit silently
        exit 0
    fi
fi

# Record our own PID
echo $$ > "$PID_FILE"

# ── Rotate log if it grows beyond 5 MB ──────────────────────
if [ -f "$LOG_FILE" ] && [ $(stat -c%s "$LOG_FILE" 2>/dev/null || echo 0) -gt 5242880 ]; then
    mv "$LOG_FILE" "${LOG_FILE}.1"
fi

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Queue worker starting (PID $$)" >> "$LOG_FILE"

# ── Run the queue worker with --stop-when-empty=false ────────
#    --max-time 50   → Gracefully stop after 50 seconds (before
#                       cPanel's 60-second cron window closes)
#    --sleep 3       → Wait 3 s between empty-queue polls
#    --tries 3       → Retry failed jobs 3 times before marking failed
#    --timeout 45    → Kill a single job that runs longer than 45 s
#    --memory 128    → Restart if memory exceeds 128 MB
# ─────────────────────────────────────────────────────────────
nohup "$PHP_BIN" "$APP_DIR/artisan" queue:work \
    --queue=default,high,low \
    --max-time=50 \
    --sleep=3 \
    --tries=3 \
    --timeout=45 \
    --memory=128 \
    >> "$LOG_FILE" 2>&1

EXIT_CODE=$?
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Worker exited (code $EXIT_CODE). Sleeping 5 s before next cycle..." >> "$LOG_FILE"

# ── The pause trick: sleep a few seconds so the cron's next  ─
#    minute trigger sees a fresh start, not an overlap.        ─
sleep 5

# ── Clean up PID file ────────────────────────────────────────
rm -f "$PID_FILE"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Ready for next cron trigger." >> "$LOG_FILE"

exit 0
