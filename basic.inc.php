<?php
require_once('database.class.php');

$message = "Nothing to display here...";

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

    $message = "Successfully unsubscribed ".Database::id2email($id)." from the newsletter.";
    break;
  }
  break;
}
?>

<!DOCTYPE html>
<html>
  <? require('html-include/head.html'); ?>
  <body>

    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="http://abfrage3.simsso.de">
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
// include legal info, about and contact html code
include('html-include/legal-info.html');
include('html-include/about.html');
include('html-include/contact.html');
        ?>
        <br class="clear-both">

      </div>

      <?php
        include('html-include/footer.html');
      ?>
    </div>

    <!-- add scripts to the DOM -->
    <script type="text/javascript">
      document.write('\x3Cscript src="jquery-1.11.3.min.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="extensions.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="messagebox.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="scripts.js" type="text/javascript">\x3C/script>');


      // single page application script
      document.write('\x3Cscript src="single-page-application.js" type="text/javascript">\x3C/script>');
    </script>
  </body>
</html>