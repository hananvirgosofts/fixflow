# FixFlow

Campus maintenance and complaint management for Thal University Bhakkar.

Web Technologies project by Abdul Hanan (COMPF24BSR08), Department of CS & IT. Submitted to Umer Khatab.

FixFlow is a responsive PHP application. Students and staff file complaints, an administrator assigns a maintenance worker, the worker updates the job, and the person who filed it can leave feedback after it is resolved.

## Stack

HTML, CSS, JavaScript, Bootstrap 5, PHP, and MySQL. It runs on XAMPP. No Node.js build is required.

## Run it on XAMPP

1. Copy this folder into the XAMPP web root and name it `fixflow`.
   - Windows: `C:\xampp\htdocs\fixflow`
   - Mac: `/Applications/XAMPP/htdocs/fixflow`
2. Start Apache and MySQL from the XAMPP control panel.
3. If the MySQL root account has a password, set it in `includes/config.php`.
4. Open `http://localhost/fixflow/install.php` and choose **Install FixFlow**.
5. Open `http://localhost/fixflow/` and sign in.

You can also start PHP from this folder with `php -S localhost:8000` while MySQL is running, then open `http://localhost:8000/install.php`.

## Deploy on Vercel

Vercel can run this app as a PHP container. It does not include MySQL, so create an empty MySQL 8 database first (Aiven, Railway, TiDB Cloud, or another host). Then:

1. Import `https://github.com/hananvirgosofts/fixflow` in Vercel. The repository already contains `Dockerfile`, `Caddyfile`, and `vercel.json`.
2. In the Vercel project, add these environment variables and redeploy:

| Variable | Example |
| --- | --- |
| `DB_HOST` | the database host |
| `DB_PORT` | `3306` |
| `DB_NAME` | `fixflow` |
| `DB_USER` | the database user |
| `DB_PASS` | the database password |
| `DB_SSL` | `1` if the host requires TLS, otherwise leave it unset |

3. Open `https://your-project.vercel.app/install.php` once and choose **Install FixFlow**.
4. Sign in with the demo password `Fixflow@123`.

Sign-in is stored in the MySQL `sessions` table, so it survives a new Vercel instance. Uploaded photographs are kept on the instance disk and can disappear when Vercel replaces that instance. The complaint text stays in MySQL.

On a local XAMPP install, leave these variables unset. The app then uses `localhost`, user `root`, and an empty password.

Times are stored in Pakistan Standard Time (UTC+5).

## Demo accounts

Every demo account uses the password `Fixflow@123`.

| Account | Email |
| --- | --- |
| Administrator | admin@tub.test |
| Student, Abdul Hanan | hanan@student.tub.test |
| Staff | staff@tub.test |
| Electrical worker | electrical@tub.test |
| Plumbing worker | plumbing@tub.test |
| Furniture worker | furniture@tub.test |
| Laboratory worker | laboratory@tub.test |
| Building worker | building@tub.test |
| Housekeeping worker | housekeeping@tub.test |

Students and staff can also register their own accounts. Worker accounts are created by the administrator.

## What each role can do

- **Student or staff:** register, file a complaint with an optional photograph, follow status, and rate a resolved complaint.
- **Administrator:** see every complaint, assign or reject it, manage workers, and read reports. Reports can be printed or downloaded as CSV.
- **Maintenance worker:** see assigned jobs, mark them in progress, add notes, and mark them resolved.

Statuses are Submitted, Assigned, In Progress, Resolved, and Rejected. Complaints are kept on record. A worker is deactivated rather than deleted, so older jobs still show who did the work.

## Reset the practice data

1. In phpMyAdmin, drop the `fixflow` database.
2. Delete `storage/installed.lock`.
3. Open `install.php` again.

## Database

`database/schema.sql` holds the tables: `users`, `categories`, `complaints`, `complaint_updates`, and `feedback`. `install.php` creates the database, loads that file, and inserts the demo records.

Passwords are stored with `password_hash`. Pages use PDO prepared statements, session login, and a per-session token on forms.
