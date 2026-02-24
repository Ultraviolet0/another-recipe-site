<!DOCTYPE html>
<html lang="en">

  <head>
    <title><?php if(isset($page_title)) { echo h($page_title) . ' - Another Recipe Site'; } ?></title>
    <meta charset="utf-8">
    <link href="<?php echo url_for('/css/styles.css'); ?>" rel="stylesheet">
  </head>

  <body>
    <a href="#main-content" id="skip-link">Skip to main content</a>
    <header role="banner">
      <div class="wrapper">
        <h1><a href="<?php echo url_for('/'); ?>"><span>AnotherRecipe.Site</span></a></h1>
        <nav role="navigation">
          <ul>
            <li><a href="<?php echo url_for('/recipes.php'); ?>"><span>Recipes</span></a></li>
          </ul>
          <form role="search" action="#" method="get" id="search-form">
            <label for="s" class="visually-hidden">Search</label>
            <input type="search" name="s" id="s" placeholder="Find a recipe" required>
            <button type="submit"><span class="visually-hidden">Search</span></button>
          </form>
          <ul>
            <li><a href="<?php echo url_for('/login.php'); ?>"><span>Login</span></a></li>
          </ul>
        </nav>
      </div>
    </header>

    <?php echo display_session_message(); ?>
