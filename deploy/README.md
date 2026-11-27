# CRM deployment - office PC + Cloudflare Tunnel

The CRM runs on this PC and is published to a public `https://` hostname by a Cloudflare
tunnel. Nothing is uploaded to a hosting provider and the database never leaves this machine.

## How a request travels

```
staff browser -> https://crm.<domain>  (Cloudflare edge, their TLS cert)
              -> cloudflared on this PC (outbound connection only, no router rules)
              -> Apache 127.0.0.1:8081  (deploy/apache-crm.conf, mod_php 8.2)
              -> Laravel public/index.php -> MySQL student_crm
```

| Piece | Where | Started by |
| --- | --- | --- |
| Apache origin, port 8081 | `C:\xampp\apache` + `deploy/apache-crm.conf` | XAMPP Control Panel, or `deploy/start-apache.bat` |
| MySQL, port 3306 | `C:\xampp\mysql` | XAMPP Control Panel |
| Email queue worker | `queue-worker.bat` | own console window, or `CRM Email Queue` task |
| Python face API, port 8001 | `python-face/` | as before - the origin is on this PC, so it still reaches `127.0.0.1:8001` |
| Cloudflare tunnel | `%USERPROFILE%\.cloudflared\config.yml` | `deploy/start-tunnel.bat`, or `CRM Tunnel` task |
| Nightly backup | `D:\Company\I-TECH-MEDIA\CRM-BACKUPS\` | `CRM Backup` task at 22:30 |

## Finish the setup (two commands, both need your own login)

1. Put the domain on Cloudflare (free) with its nameservers changed, then create the tunnel.
   This opens a browser sign-in to your Cloudflare account - it cannot be done for you:

   ```
   powershell -ExecutionPolicy Bypass -File deploy\setup-tunnel.ps1 -Hostname crm.yourdomain.com
   ```

   The same command also writes the tunnel config and the DNS record. If `APP_URL` in `.env`
   does not already match that hostname, update it and run `php artisan config:clear`.

2. Register the autostart + backup tasks from an **elevated** PowerShell (right-click -> Run as
   administrator). Without elevation `schtasks` answers "Access is denied":

   ```
   powershell -ExecutionPolicy Bypass -File deploy\register-autostart.ps1
   ```

   Creates: `CRM Apache`, `CRM Email Queue`, `CRM Tunnel` (at logon) and `CRM Backup` (daily 22:30).
   Remove them again with the same file and `-Unregister`.

Start order after a reboot: MySQL, Apache (`deploy/start-apache.bat`), queue worker, tunnel.
Then open the hostname, log in, and check that the address bar stayed `https://` and that the
login form worked on the second try - that proves the secure cookie and the proxy header trust
are both wired.

## What was changed on this PC

| Change | Revert |
| --- | --- |
| `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://crm.itechmediakh.com` | copy `D:\Company\I-TECH-MEDIA\CRM-BACKUPS\env\.env.pre-production-20261126-141017` over `.env`, then `php artisan config:clear` |
| `C:\xampp\apache\conf\httpd.conf`: one `Include` line at the end | restore `C:\xampp\apache\conf\httpd.conf.bak-20261126-140634`, restart Apache |
| new file `deploy/apache-crm.conf` (vhost on 127.0.0.1:8081) | delete it with the Include line removed |
| `bootstrap/app.php`: `$middleware->trustProxies(at: '127.0.0.1')` | remove those two lines |
| cloudflared installed per-user via winget | `winget uninstall --id Cloudflare.cloudflared` |
| `D:\Company\I-TECH-MEDIA\CRM-BACKUPS\` (outside the repo, holds dumps + the .env backup) | delete the folder |

`php artisan serve` on 127.0.0.1:8000 still works for development; it now runs in production mode
too, because there is one `.env`. Set `APP_DEBUG=true` locally when you want stack traces back.

## Backups

`php deploy/backup-crm.php` writes a timestamped folder with `database.sql` (mysqldump,
`--single-transaction`), `storage/app/public` (uploaded documents), `env.txt` (contains `APP_KEY`,
required to decrypt a restore) and `manifest.json` (row counts, so an empty dump is visible at a
glance). Folders older than 14 days are deleted. `CRM-BACKUPS/latest-error.txt` appears only when
a run failed.

Restore:

```
php deploy/restore-from-backup.php backups/student_crm_2026-11-26.sql
```

That command dumps the current database to `CRM-BACKUPS/pre-restore-<timestamp>\` first, drops and
recreates `student_crm`, imports the file, runs the pending migrations (an old dump predates
columns like `students.campus_id`), clears `sessions`/`jobs`, and prints row counts before and
after. To restore by hand instead:

```
"C:\xampp\mysql\bin\mysql.exe" -u <dbuser> -p student_crm < database.sql
php artisan migrate --force
php artisan config:clear && php artisan queue:restart
```

A dump restored from before the campus feature leaves `students.campus_id` and
`student_courses.campus_id` empty, so campus-filtered pages show nothing until the students are
attached with Students -> All campuses -> tick -> Bulk Actions -> Set campus, and staff accounts
get their campus rows back under Settings -> Users.

## 2026-11-26 data incident

`student_crm` was rebuilt from migrations + seeders at 11:57 today, which discarded the employee,
invoice and payment records that existed at 09:31. `log_bin = OFF`, so the gap between those two
points cannot be replayed. The 09:31 file in `backups/` is the only copy of that data; treat it as
precious until the restore has been checked, and note that the Backup page's output is what saved
it - schedule that page's job or the nightly task so there is always a recent copy.

## Known limits of hosting on a PC

- The CRM is offline whenever this PC is off, asleep, or rebooted and nobody has logged in
  (scheduled tasks are per-user at logon; only a Windows service would start before login).
- Windows updates that reboot the machine take the CRM down silently until someone signs in.
- One MySQL server, no replica: the nightly dump is the only recovery point, and MySQL here has
  `log_bin = OFF`, so nothing between two dumps can be replayed.
- Anything written to `storage/` stays on this disk. Back it up before moving machines.
- Student personal data is served over the public internet. Keep Cloudflare's "Always Use HTTPS"
  on, and consider Zero Trust Access in front of the hostname if staff accounts should not be
  reachable at all from outside the office.
