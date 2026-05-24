# SwitchesLib

A beginner-friendly mechanical keyboard switch database. Plain PHP + MySQL, no framework.

## Tech stack

- PHP (plain, no framework — see ADR-0001)
- MySQL / MariaDB
- HTML, CSS, vanilla JavaScript

## Project structure

```
SwitchesLib/
├── app/
│   ├── core/           # Database (PDO singleton), Router
│   ├── controllers/    # request handlers
│   ├── views/          # page templates + partials
│   ├── helpers.php     # view(), e(), url()
│   └── routes.php      # route table
├── config/
│   └── config.php      # DB connection settings
├── database/
│   ├── schema.sql      # all tables
│   └── seed.sql        # tags, designers, 15 switches, 3 blog posts
├── public/             # web root
│   ├── index.php       # front controller
│   ├── router.php      # entry point for `php -S` dev server
│   ├── .htaccess       # Apache rewrite to index.php
│   └── uploads/        # admin-uploaded images
└── bootstrap.php       # paths, autoloader, helpers
```

## Setup (XAMPP)

1. **Start Apache and MySQL** from the XAMPP control panel.

2. **Import the database.** In phpMyAdmin (http://localhost/phpmyadmin) run `database/schema.sql` then `database/seed.sql`. Or from the terminal:

   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < database/schema.sql
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < database/seed.sql
   ```

3. **Check DB credentials** in `config/config.php`. The defaults match a stock XAMPP install (host `127.0.0.1`, user `root`, empty password). Edit if yours differ.

4. **Serve the app.** Point Apache at `public/`. The simplest way is to symlink the project into htdocs:

   ```bash
   ln -s "$(pwd)" /Applications/XAMPP/xamppfiles/htdocs/SwitchesLib
   ```

   Then open **http://localhost/SwitchesLib/public/**. The router auto-detects the subdirectory, so links work without extra config.

## Setup (PHP built-in server — no Apache)

With a MySQL server running and the database imported:

```bash
php -S localhost:8000 -t public public/router.php
```

Then open **http://localhost:8000/**.

## Routes (so far)

| Path                | Page                                  |
| ------------------- | ------------------------------------- |
| `/`                 | Home (scaffold stub)                  |
| `/switches`         | Switch list (built in Slice 2)        |
| `/switches/{slug}`  | Switch detail (built in Slice 5)      |

Any unmatched path renders a 404 page.
