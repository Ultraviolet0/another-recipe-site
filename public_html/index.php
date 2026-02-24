<?php
require_once('../private/initialize.php');
$page_title = 'Home';
include(SHARED_PATH . '/public_header.php');
?>

<main role="main" tabindex="-1" id="main-content">
  <section class="wrapper">
    <h2>Welcome!</h2>
    <p>This is a simple, no-nonsense website to find and share recipes without the fluff.</p>
  </section>

  <section id="new-recipes-section">
    <div class="wrapper">
      <h2>Newest Recipes</h2>

      <div class="recipe-grid">
        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Quinoa Salad Bowl</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>5.0</span>
                <span>(10)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/quinoa-salad-bowl-270.png" width="270" height="270" alt="A colorful quinoa salad bowl." loading="lazy">
            <span class="recipe-card-badge">Whole foods</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Scrambled Eggs & Potatoes</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>4.5</span>
                <span>(12)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">45 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/breakfast-eggs-and-potatoes-270.png" width="270" height="270" alt="A plate of eggs and potatoes." loading="lazy">
            <span class="recipe-card-badge">Feel good</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Avocado Toast</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>4.2</span>
                <span>(8)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">10 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/avocado-toast-270.png" width="270" height="270" alt="A plate of avocado toast." loading="lazy">
            <span class="recipe-card-badge">Quick & easy</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Steak & Root Veggies Test Long Title Test Long Title Test Long Title</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>4.7</span>
                <span>(11)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/steak-and-veggies-270.png" width="270" height="270" alt="A plate of steak and vegetables." loading="lazy">
            <span class="recipe-card-badge">Hearty meal</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Garlic Aioli</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>5.0</span>
                <span>(6)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">15 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/garlic-aioli-270.png" width="270" height="270" alt="A jar of homemade garlic aioli." loading="lazy">
            <span class="recipe-card-badge">Cost saving</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Salmon & Green Veggies</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>4.6</span>
                <span>(12)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/salmon-and-veggies-270.png" width="270" height="270" alt="A plate of salmon and vegetables." loading="lazy">
            <span class="recipe-card-badge">Healthy</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Burrito Bowl</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>4.4</span>
                <span>(11)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">40 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/burrito-bowl-270.png" width="270" height="270" alt="A tex-mex burrito bowl." loading="lazy">
            <span class="recipe-card-badge">Latin influence</span>
          </div>
        </a>

        <a href="#" class="recipe-card">
          <div class="recipe-card-info">
            <h3>Breakfast Burrito</h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span>4.5</span>
                <span>(15)</span>
              </div>
              <time datetime="PT40M" class="recipe-card-time">25 mins</time>
            </div>
          </div>
          <div class="recipe-card-media">
            <img src="images/breakfast-burrito-270.png" width="270" height="270" alt="A breakfast burrito." loading="lazy">
            <span class="recipe-card-badge">On the go</span>
          </div>
        </a>
      </div>
    </div>
  </section>
</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
