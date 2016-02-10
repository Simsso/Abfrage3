<footer id="footer-wrapper" class="content-width display-none">
  <div class="box" style="margin-top: 0; ">
    <div class="box-body footer">
      <?php
        if ($show_footer_nav) {
          echo '<a href="#/about">' . $l['About'] . '</a> &middot; 
          ' . ($show_footer_tour_link ? '<a href="#/tour">' . $l['Tour'] . '</a> &middot; ' : '') . 
          '<a href="#/contact">' . $l['Contact'] . '</a> &middot; 
          <a href="#/legal-info">' . $l['Legal_info'] . '</a>
          <br><br>';
        }
      ?>

      <a class="switch-lang-button<? if ($lang === 'en') echo ' bold'; ?>" data-lang="en" href="?lang=en">
      	<img src="img/flag-of-the-united-states.svg" class="language-flag" alt="" />
      	English
      </a> &middot; 
      <a class="switch-lang-button<? if ($lang === 'de') echo ' bold'; ?>" data-lang="de" href="?lang=de">
      	<img src="img/flag-of-germany.svg" class="language-flag" alt="" />
      	Deutsch
      </a>
      <hr>&copy; <a href="http://timodenk.com" target="_blank">Timo Denk</a>
    </div>
  </div>
</footer>
