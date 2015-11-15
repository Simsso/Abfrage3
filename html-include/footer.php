<footer id="footer-wrapper" class="content-width display-none">
  <div class="box" style="margin-top: 0; ">
    <div class="box-body footer">
      <a href="#/about"><? echo $l['About']; ?></a> &middot; 
      <a href="#/tour"><? echo $l['Tour']; ?></a> &middot; 
      <a href="#/contact"><? echo $l['Contact']; ?></a> &middot; 
      <a href="#/legal-info"><? echo $l['Legal_info']; ?></a>
      <br><br>
      <a href="?lang=en"<? if ($lang === 'en') echo ' class="bold"'; ?>>
      	<img src="img/flag-of-the-united-states.svg" class="language-flag" alt="" />
      	English
      </a> &middot; 
      <a href="?lang=de"<? if ($lang === 'de') echo ' class="bold"'; ?>>
      	<img src="img/flag-of-germany.svg" class="language-flag" alt="" />
      	Deutsch
      </a>
      <hr>&copy; Timo Denk
    </div>
  </div>
</footer>
