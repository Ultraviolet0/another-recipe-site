# anotherrecipe.site

A recipe-sharing web application built primarily with PHP, MySQL, and CSS, plus some JavaScript for progressive enhancement.

## Overview

anotherrecipe.site is a custom recipe website focused on clean server-rendered functionality, accessibility, and performance. Most core features work without JavaScript, with JS used primarily to enhance the experience rather than make the site functional in the first place.

Users can create accounts, publish recipes with ingredients, directions, images, tags, videos, ratings, profile pages, and more. Admins can manage users, categories, badges, and moderation-related features through the admin interface.

## Design Philosophy

### Progressive enhancement
This site is built so that core functionality works with little or no JavaScript whenever practical.

Examples:
- Recipe creation and editing work through standard server-rendered forms
- Filtering works without JavaScript through normal GET requests
- JavaScript enhances features like auto-submitting filters, gallery behavior, scaling controls, and AJAX rating updates

### Accessibility-minded
The site is built with accessibility in mind, including:
- semantic HTML structure
- keyboard-friendly interactions
- visible labels and helper text
- focus-aware navigation patterns
- support for screen readers where practical
- reduced reliance on JavaScript for critical features

### Performance-conscious
The site tries to keep things reasonably lightweight by:
- server-rendering most pages
- limiting unnecessary JavaScript
- using responsive image sizes
- using cached static assets with versioning
- optimizing uploaded recipe images into multiple sizes

## Tech Stack

- PHP
- MySQL / MariaDB
- Composer
- Dotenv (`vlucas/phpdotenv`)
- Apache
- JavaScript
- CSS

## Key Features

- User registration and login
- Recipe creation and editing
- Ingredient and direction management
- Recipe image uploads with resizing
- Recipe filtering and sorting
- Recipe ratings
- User profiles
- Admin dashboard
- Category management
- Badge support
- YouTube recipe video embedding
- Printable recipe view / PDF via browser print
- AI-powered recipe recommendations through OpenRouter

## Project Structure

This project uses a split public/private structure.

- `public_html/` contains public-facing entry points and static assets
- `private/` contains initialization, classes, shared includes, and helper functions
- `vendor/` contains composer dependencies
- `/` root contains `.env` and composer settings

Structure:

```text
public_html/
  admin/
    users/
      delete.php
      edit.php
      form_fields.php
      index.php
      new.php
    categories.php
    index.php
  assets/
  css/
    styles.css
  dashboard/
    index.php
    profile.php
    recipes.php
  fonts/
  images/
  js/
    scripts.js
  recipes/
    delete.php
    edit.php
    form_fields.php
    index.php
    new.php
    print.php
    show.php
  uploads/
    profile/
      28/
      110/
      140/
      270/
    recipes/
      270/
      400/
      540/
      800/
      1600/
  .htaccess
  404.php
  about.php
  favicon.ico
  index.php
  login.php
  logout.php
  privacy.php
  profile.php
  signup.php
  template.php
  terms.php


private/
  classes/
    badge.class.php
    cuisine.class.php
    databaseobject.class.php
    dietarystyle.class.php
    mealtype.class.php
    measurement.class.php
    recipe.class.php
    role.class.php
    session.class.php
    user.class.php
  config/
    db_credentials.php
  shared/
    404_content.php
    public_footer.php
    public_header.php
  database_functions.php
  functions.php
  image_upload_functions.php
  initialize.php
  openrouter_functions.php
  recipe_functions.php
  security_functions.php
  status_error_functions.php
  validation_functions.php

root directory:
.env
composer.json
composer.lock
```

## Requirements

Before setting up the project, make sure you have the following installed:

- PHP 8.x recommended
- Composer
- MySQL or MariaDB
- Apache with `.htaccess` support enabled
- A local environment such as XAMPP, WAMP, MAMP, Laragon, or a custom Apache/PHP/MySQL setup

## Setup Instructions

### 1. Clone the repository

```bash id="lbch3l"
git clone https://github.com/Ultraviolet0/another-recipe-site.git
cd another-recipe-site
```

### 2. Install Composer dependencies

If vendor/ is not already present, install dependencies with:

```bash 
composer install
```

If Composer is not installed yet, install it first and then run the command above.

### 3. Create the database

Database setup SQL exists at `assets/uhren-robert-db-dump.sql`. It's highly recommended to modify lines 10, 11, 14 to a database name of your choosing and lines 321-323 with secure credentials of your choosing.

Load the database into your server with your preferred method. I like phpMyAdmin.

### 4. Create the `.env` file

Use the included `env.example` file as a sample to create your own `.env` file with your specific credentials.

Notes:
- DB_SERVER is often localhost in local development
- DB_USER is often root in XAMPP unless you changed it
- DB_PASS may be blank in some local XAMPP setups
- Keep your real .env file out of version control

### 5. Verify environment loading

This project expects environment variables to be loaded through Dotenv.

Make sure:

- Composer dependencies are installed
- `vendor/autoload.php` exists
- your `.env` file is in the correct location
- `initialize.php` is loading Dotenv correctly

### 6. Point your local server to the public directory

Your web server should serve the site from `public_html/`.

### 7. Make sure upload directories exist

The site expects upload directories for recipe images and profile images.

For this version, you will need the following folders:

```
public_html/uploads/recipes/1600/
public_html/uploads/recipes/800/
public_html/uploads/recipes/540/
public_html/uploads/recipes/400/
public_html/uploads/recipes/270/
```

Future versions may also require folders such as:

```
public_html/uploads/profile/28
```

However for now, these are not necessary.

If these folders are not already present, create them manually.

Make sure Apache/PHP has permission to write to them.

### 8. Confirm asset paths

Make sure the following kinds of assets are present and accessible:

- CSS files
- JS files
- fonts
- site images/icons
- upload directories

If assets are missing, broken styling or missing icons may occur.

### 9. Test the site

Once the server is running, test the following:

- homepage loads
- registration works
- login works
- recipe index page loads
- recipe creation works
- image uploads work
- dashboard loads
- admin area loads for admin users
- `.env` values are being read correctly

### 10. Troubleshooting

If you have any issues setting up your own recipe site, the developer can be contacted at robert.uhren@gmail.com.
