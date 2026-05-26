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

## Routes

| Path                                | Method | Page / Action                        |
| ----------------------------------- | ------ | ------------------------------------ |
| `/`                                 | GET    | Home                                 |
| `/switches`                         | GET    | Switch list (search + filter + sort) |
| `/switches/{slug}`                  | GET    | Switch detail                        |
| `/switches/{slug}/submit-audio`     | GET    | Audio submission form                |
| `/switches/{slug}/submit-audio`     | POST   | Store audio submission               |
| `/blog`                             | GET    | Blog list                            |
| `/blog/{slug}`                      | GET    | Blog post detail                     |
| `/submit`                           | GET    | Submission form                      |
| `/submit`                           | POST   | Store submission                     |
| `/my-submissions`                   | GET    | User's submission history            |
| `/register`                         | GET/POST | Registration                       |
| `/login`                            | GET/POST | Login                              |
| `/logout`                           | POST   | Logout                               |
| `/admin`                            | GET    | Admin dashboard                      |
| `/admin/switches`                   | GET    | Admin switch list                    |
| `/admin/switches/add`               | GET    | Add switch form                      |
| `/admin/switches`                   | POST   | Store new switch                     |
| `/admin/switches/{id}/edit`         | GET    | Edit switch form                     |
| `/admin/switches/{id}`              | POST   | Update switch                        |
| `/admin/switches/{id}/delete`       | POST   | Delete switch                        |
| `/admin/designers`                  | GET    | Admin designer list                  |
| `/admin/designers/add`              | GET    | Add designer form                    |
| `/admin/designers`                  | POST   | Store new designer                   |
| `/admin/designers/{id}/edit`        | GET    | Edit designer form                   |
| `/admin/designers/{id}`             | POST   | Update designer                      |
| `/admin/designers/{id}/delete`      | POST   | Delete designer                      |
| `/admin/blog`                       | GET    | Admin blog list                      |
| `/admin/blog/add`                   | GET    | Add blog post form                   |
| `/admin/blog`                       | POST   | Store new blog post                  |
| `/admin/blog/{id}/edit`             | GET    | Edit blog post form                  |
| `/admin/blog/{id}`                  | POST   | Update blog post                     |
| `/admin/blog/{id}/delete`           | POST   | Delete blog post                     |
| `/admin/submissions`                | GET    | Submission review queue              |
| `/admin/submissions/{id}`           | GET    | Review single submission             |
| `/admin/submissions/{id}/update`    | POST   | Save submission edits                |
| `/admin/submissions/{id}/approve`   | POST   | Approve → publish as Switch          |
| `/admin/submissions/{id}/reject`    | POST   | Reject submission                    |
| `/admin/audio-submissions`          | GET    | Audio review queue                   |
| `/admin/audio-submissions/{id}`     | GET    | Review single audio submission       |
| `/admin/audio-submissions/{id}/approve` | POST | Approve recording → publish          |
| `/admin/audio-submissions/{id}/reject`  | POST | Reject recording                     |
| `/admin/tags`                       | GET    | Tag reference (read-only)            |
| `/admin/users`                      | GET    | User list                            |
| `/admin/users/{id}/role`            | POST   | Change user role                     |

Any unmatched path renders a 404 page.
