<?php
require_once('../private/initialize.php');
$page_title = 'About';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="about-page">
    <section class="about-hero">
      <h1 class="about-kicker">About anotherrecipe.site</h1>
      <h2>Simple recipe sharing without the fluff.</h2>
      <p class="about-intro">anotherrecipe.site is a community recipe-sharing platform built for people who want practical, searchable recipes without the clutter. Browse recipes, save ideas, and share your own cooking in a clean, straightforward space.</p>
    </section>

    <section class="about-section">
      <h2>What this site is</h2>
      <p>anotherrecipe.site is designed to make recipe browsing and sharing feel simple again. Whether you are looking for a quick weeknight meal, a meal-prep staple, or something worth making for guests, the goal is to help you find useful recipes quickly and share your own just as easily.</p>
      <p>The site is built around a practical recipe structure with ingredients, directions, ratings, and searchable tags like meal type, cuisine, and dietary style.</p>
    </section>

    <section class="about-section">
      <h2>What makes it different</h2>
      <ul class="about-list">
        <li>Clean browsing focused on recipes instead of clutter.</li>
        <li>Simple recipe pages with clear ingredients, directions, and ratings.</li>
        <li>Search and filters that help you find recipes by real categories.</li>
        <li>No endless life story required to get to the actual food.</li>
        <li>Built to make sharing your own recipes approachable and straightforward.</li>
      </ul>
    </section>

    <section class="about-section">
      <h2>How it works</h2>
      <div class="about-steps">
        <div class="about-step">
          <h3>1. Browse</h3>
          <p>Explore recipes by keyword, meal type, cuisine, dietary style, or rating.</p>
        </div>

        <div class="about-step">
          <h3>2. Create an account</h3>
          <p>Sign up for a free account to rate recipes and start sharing your own.</p>
        </div>

        <div class="about-step">
          <h3>3. Share</h3>
          <p>Add your own recipes, upload photos, and choose whether they are public, unlisted, or private.</p>
        </div>
      </div>
    </section>

    <section class="about-section">
      <h2>Frequently asked questions</h2>

      <div class="about-faq">
        <div class="about-faq-item">
          <h3>Do I need an account to browse recipes?</h3>
          <p>No. Public recipes can be browsed without an account.</p>
        </div>

        <div class="about-faq-item">
          <h3>Do I need an account to add recipes?</h3>
          <p>Yes. You need an account to create and manage your own recipes.</p>
        </div>

        <div class="about-faq-item">
          <h3>Can I keep a recipe private?</h3>
          <p>Yes. Recipes can be shared publicly, kept unlisted (visible with direct link), or marked private.</p>
        </div>

        <div class="about-faq-item">
          <h3>Who is responsible for submitted content?</h3>
          <p>Users are responsible for the recipes, photos, and other content they choose to post.</p>
        </div>
      </div>
    </section>

    <section class="about-section">
      <h2>Contact</h2>
      <p>Questions, feedback, or support requests can be sent to <a href="mailto:&#x69;&#x6e;&#x66;&#x6f;&#x40;&#x61;&#x6e;&#x6f;&#x74;&#x68;&#x65;&#x72;&#x72;&#x65;&#x63;&#x69;&#x70;&#x65;&#x2e;&#x73;&#x69;&#x74;&#x65;">&#x69;&#x6e;&#x66;&#x6f;&#x40;&#x61;&#x6e;&#x6f;&#x74;&#x68;&#x65;&#x72;&#x72;&#x65;&#x63;&#x69;&#x70;&#x65;&#x2e;&#x73;&#x69;&#x74;&#x65;</a>.
      </p>
    </section>

    <section class="about-section">
      <h2>Privacy and Terms</h2>
      <p>anotherrecipe.site uses account and recipe data to provide recipe-sharing features, user accounts, ratings, and site functionality. We aim to keep things straightforward and respectful of user trust.</p>
      <p>By using the site, users are expected to submit content they have the right to share and to use the platform responsibly.</p>
      <p class="about-policy-links">
        <a href="<?php echo url_for('/privacy.php'); ?>">Privacy Policy</a>
        <span aria-hidden="true">•</span>
        <a href="<?php echo url_for('/terms.php'); ?>">Terms of Service</a>
      </p>
    </section>

    <section class="about-section about-creator">
      <h2>Built with intention</h2>
      <p>anotherrecipe.site was created to offer a cleaner, more useful recipe-sharing experience for home cooks who just want good food, clear instructions, and less nonsense.</p>
    </section>

  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
