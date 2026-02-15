<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php if(isset($page_title)) { echo h($page_title) . ' - Another Recipe Site'; } ?></title>
    <meta charset="utf-8">
    <link href="<?php echo url_for('/css/styles.css'); ?>" rel="stylesheet">
  </head>

  <body>
    <a href="#main-content" id="skip-link">Skip to main content</a>
    <header class="wrapper">
        <h1><a href="<?php echo url_for('/'); ?>">anotherrecipe.site</a></h1>
        <nav>
          <ul>
            <a href="#"><li>Recipes</li></a>
            <a href="#"><li>Meals</li></a>
            <a href="#"><li>Cuisines</li></a>
            <a href="#"><li>Health</li></a>
          </ul>
          <form role="search" action="#" method="get" id="search-form">
            <label for="s" class="visually-hidden">Search</label>
            <input type="search" name="s" id="s" placeholder="Find a recipe" required>
            <button type="submit">Search</button>
          </form>
          <ul>
            <a href="#"><li>Login</li></a>
          </ul>
        </nav>
      </header>

    <?php echo display_session_message(); ?>
