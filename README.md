# ala_cart_login

Simple PHP-based login/signup/password-reset demo application used for local testing and learning.

## What this repository contains
- PHP files for authentication and front-end pages in `auth/` and `includes/`.
- A small SQLite database in `config/app.db` (shipping DB file for convenience).
- Utility scripts in `tools/` and tests in `tests/`.

This repo appears to be a local demo project; it includes example credentials, temporary files, and logs used during development.

## Quick start
1. Install PHP and SQLite on your machine.
2. Configure web server root to this project or use the built-in PHP server for quick testing:

```powershell
php -S localhost:8000 -t .
```

3. Open http://localhost:8000/auth/signup.php to create a user, or inspect the `tests/` folder for integration flows.

## Security notes
- The repository currently contains a checked-in SQLite DB (`config/app.db`), temporary cookies/log files, and other files that may contain sensitive data. Do not publish this repository publicly if those files contain real secrets.
- Consider removing `config/app.db` and other runtime files from the repo and instead use environment-based configuration. See `.gitignore`.

## Files to remove or secure before sharing
- `config/app.db` (SQLite DB)
- `cookiejar.txt`, `tmp_cookies.txt`, `text/email_log.txt`
- Any `.env` or credential files if present

## License
This repository has no license file. Add a LICENSE if you want to set reuse terms.

---
Generated README by automation during user request.
