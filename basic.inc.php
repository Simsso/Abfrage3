<?php
require_once('database.class.php');
require_once('lang/lang.inc.php');

$message = $l['Nothing_to_display_here__'];

switch ($_GET['action']) {

  // user wants to unsubscribe
  case 'unsubscribe':
  switch ($_GET['medium']) {

    // unsubscribe from the newsletter
    case 'newsletter':
    $id = $_GET['id'];
    $hash = $_GET['hash'];

    global $con;
    $sql = "
      UPDATE `user_settings`, `user` 
      SET `user_settings`.`newsletter_enabled` = 0 
      WHERE `user`.`id` = `user_settings`.`user` AND `user_settings`.`user` = ".$id." AND
        `user`.`hash` LIKE BINARY '".$hash."';";
    $query = mysqli_query($con, $sql);
    // update the newsletter_enabled field to 0 (unsubscribe)

    $message = $l['Successfully_unsubscribed_the_email_from_newsletter__'] . Database::id2email($id);
    break;
  }
  break;
}
?>

<!DOCTYPE html>
<html>
  <? require('html-include/head.php'); ?>
  <body>

    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="/">
          <img class="logo" src="img/logo.svg" alt="Abfrage3" />
        </a>
      </div>
    </nav>

    <div id="main-wrapper">


      <div class="main content-width" id="main">
        <div class="sk-three-bounce">
          <div class="sk-child sk-bounce1"></div>
          <div class="sk-child sk-bounce2"></div>
          <div class="sk-child sk-bounce3"></div>
        </div>

        <!-- Home -->
        <div id="content-home">
          <div class="box">
            <div class="box-body">
              <?php
                echo $message;
              ?>
            </div>
          </div>
        </div>


        <?php
          // include legal info, about, contact, tour and advertisement HTML code
          include('html-include/legal-info.php');
          include('html-include/about.php');
          include('html-include/contact.php');
          include('html-include/tour.php');
        ?>
        <br class="clear-both">

      </div>

      <?php
        $show_footer_nav = false;
        $show_footer_tour_link = false;
        include('html-include/footer.php');
      ?>
    </div>

    <script type="text/javascript">
      // constant strings
      var constString = JSON.parse('<? echo str_replace("'", "\\'", str_replace('"', '\\"', json_encode($l))); ?>');
    </script>

    <!-- add scripts to the DOM -->
    <script type="text/javascript">
      document.write('\x3Cscript src="jquery-1.11.3.min.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="handlebars-v4.0.4.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="messagebox.js" type="text/javascript">\x3C/script>');

      document.write('\x3Cscript src="extensions.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="scripts.js" type="text/javascript">\x3C/script>');


      // single page application script
      document.write('\x3Cscript src="single-page-application.js" type="text/javascript">\x3C/script>');
    </script>
  </body>
</html>