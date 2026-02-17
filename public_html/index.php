<?php
require_once('../private/initialize.php');
$page_title = 'Home';
include(SHARED_PATH . '/public_header.php');
?>

<main id="main-content">
  <section class="wrapper">
    <h2>Welcome!</h2>
    <p>This is a simple, no-nonsense website to find and share recipes without the fluff.</p>
  </section>

  <section id="top-recipes-section">
    <div class="wrapper">
      <h2>Top-rated Recipes</h2>
      <div class="recipe-grid">
        <article class="recipe-card">
          <div>
            <h3><a href="#">Quinoa Salad Bowl</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★★</span>
              <span class="rating-number">5</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            <span class="recipe-card-badge">High protein, low carb</span>
          </div>
          <a href="#"><img src="images/quinoa-salad-bowl-270.png" width="270" height="270" alt="A colorful quinoa salad bowl." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Scrambled Eggs & Potatoes</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★☆</span>
              <span class="rating-number">4.5</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">45 mins</time>
            <span class="recipe-card-badge">Feel good</span>
          </div>
          <a href="#"><img src="images/breakfast-eggs-and-potatoes-270.png" width="270" height="270" alt="A plate of eggs and potatoes." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Avocado Toast</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★☆</span>
              <span class="rating-number">4.2</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">10 mins</time>
            <span class="recipe-card-badge">Quick and easy</span>
          </div>
          <a href="#"><img src="images/avocado-toast-270.png" width="270" height="270" alt="A plate of avocado toast." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Steak & Root Veggies</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★★</span>
              <span class="rating-number">4.7</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            <span class="recipe-card-badge">Hearty meal</span>
          </div>
          <a href="#"><img src="images/steak-and-veggies-270.png" width="270" height="270" alt="A plate of steak and vegetables." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Garlic Aioli</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★★</span>
              <span class="rating-number">5</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">15 mins</time>
            <span class="recipe-card-badge">Cost saving</span>
          </div>
          <a href="#"><img src="images/garlic-aioli-270.png" width="270" height="270" alt="A jar of homemade garlic aioli." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Salmon & Green Veggies</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★★</span>
              <span class="rating-number">4.6</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            <span class="recipe-card-badge">Healthy</span>
          </div>
          <a href="#"><img src="images/salmon-and-veggies-270.png" width="270" height="270" alt="A plate of salmon and vegetables." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Burrito Bowl</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★☆</span>
              <span class="rating-number">4.4</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            <span class="recipe-card-badge">Latin influence</span>
          </div>
          <a href="#"><img src="images/burrito-bowl-270.png" width="270" height="270" alt="A tex-mex burrito bowl." loading="lazy"></a>
        </article>

        <article class="recipe-card">
          <div>
            <h3><a href="#">Breakfast Burrito</a></h3>
            <div class="recipe-card-rating">
              <span aria-hidden="true">★★★★☆</span>
              <span class="rating-number">4.5</span>
            </div>
            <time datetime="PT40M" class="recipe-card-time">25 mins</time>
            <span class="recipe-card-badge">On the go</span>
          </div>
          <a href="#"><img src="images/breakfast-burrito-270.png" width="270" height="270" alt="A breakfast burrito." loading="lazy"></a>
        </article>
      </div>
    </div>
  </section>
</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
