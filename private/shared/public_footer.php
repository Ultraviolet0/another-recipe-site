</main>

<footer>
  <div class="wrapper">
    <div class="site-footer-grid">

      <section class="site-footer-section" aria-labelledby="footer-brand-heading">
        <h2 id="footer-brand-heading" class="site-footer-heading">anotherrecipe.site</h2>
        <p class="site-footer-text">A simple, no-nonsense place to discover recipes, save favorites, and share your own cooking.</p>
      </section>

      <nav class="site-footer-section" aria-labelledby="footer-explore-heading">
        <h2 id="footer-explore-heading" class="site-footer-heading">Explore</h2>
        <ul class="site-footer-list">
          <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
          <li><a href="<?php echo url_for('/recipes'); ?>">Browse Recipes</a></li>
          <?php if ($session->is_logged_in()) { ?>
            <li><a href="<?php echo url_for('/recipes/new.php'); ?>">Add a Recipe</a></li>
          <?php } ?>
          <li><a href="<?php echo url_for('/about.php'); ?>"><span>About</span></a></li>
        </ul>
      </nav>

      <nav class="site-footer-section" aria-labelledby="footer-account-heading">
        <h2 id="footer-account-heading" class="site-footer-heading">Account</h2>
        <ul class="site-footer-list">
          <?php if ($session->is_logged_in()) { ?>
            <li><a href="<?php echo url_for('/dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo url_for('/dashboard/recipes.php'); ?>">My Recipes</a></li>
            <li><a href="<?php echo url_for('/profile.php?id=' . u($session->get_user_id())); ?>">View Profile</a></li>
            <li><a href="<?php echo url_for('/dashboard/profile.php'); ?>">Edit Profile</a></li>
            <li><a href="<?php echo url_for('/logout.php'); ?>">Logout</a></li>
          <?php } else { ?>
            <li><a href="<?php echo url_for('/login.php'); ?>">Log In</a></li>
            <li><a href="<?php echo url_for('/signup.php'); ?>">Create Account</a></li>
          <?php } ?>
        </ul>
      </nav>

      <section class="site-footer-section" aria-labelledby="footer-credits-heading">
        <h2 id="footer-credits-heading" class="site-footer-heading">Credits</h2>
        <ul class="site-footer-list">
          <li><a href="https://icons8.com" target="_blank" rel="noopener noreferrer">Icons8</a></li>
        </ul>
      </section>

    </div>

    <div class="site-footer-bottom">
      <p>Copyright &copy; 2026 <a href="<?php echo url_for('/'); ?>">anotherrecipe.site</a>. All rights reserved.</p>
    </div>
  </div>
</footer>

</body>

</html>

<?php db_disconnect($database); ?>
