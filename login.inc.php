<!DOCTYPE html>
<html>
  <? require('html-include/head.php'); ?>
  <body>

    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="#/home">
          <img class="logo" src="img/logo.svg" alt="Abfrage3" />
        </a>
        <ul class="nav left">
          <a href="#/home">
            <li class="nav_home nav-img-li" data-text="<? echo $l['Home']; ?>">
              <img src="img/home.svg" class="nav-image" alt="<? echo $l['Home']; ?>" title="<? echo $l['Home']; ?>"/>
            </li>
          </a>
          <a href="#/login" class="show-mobile"><li class="nav_login" data-text"<? echo $l['Login']; ?>"><? echo $l['Login']; ?></li></a>
        </ul>

        <ul class="nav right">
          <a href="#/about"><li class="nav_about" data-text="<? echo $l['About']; ?>"><? echo $l['About']; ?></li></a>
          <a href="#/contact"><li class="nav_contact" data-text="<? echo $l['Contact']; ?>"><? echo $l['Contact']; ?></li></a>
          <a href="#/legal-info"><li class="nav_legal-info" data-text="<? echo $l['Legal_info']; ?>"><? echo $l['Legal_info']; ?></li></a>
        </ul>
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
          <div class="left-column">
            <?php

// general information about things like
//  - sign up success
//  - login success
//  - email confirmation success

$infobox_green_red = NULL;
$infobox_header = NULL;
$infobox_body = NULL;

// sign up
if($_GET['signup_success'] == 'true') {
  $infobox_header = $l['Successfully_signed_up']; ;
  $infobox_green_red = 'green';
  $infobox_body = "<p>" . $l['Hey'] . " " . $_GET['firstname'] . "!</p>" . $l['P_Your_account_has_been_created__'];
} else if ($_GET['signup_success'] == 'false') {
  $infobox_header = $l['Could_not_sign_up'];
  $infobox_green_red = 'red';
  $infobox_body = "<p>" . $l['An_error_occured_creating_account__'] . "</p><p>" . $_GET['signup_message'] . "</p>";
}


// login
if($_GET['login_message']) {
  $infobox_header = $l['Failed_to_log_in'];
  $infobox_green_red = 'red';
  $infobox_body = "<p>" . $l['An_error_occured_logging_in__'] . "</p><p>" . $_GET['login_message'] . "</p>";
}


// email confirmation
if($_GET['hash'] && $_GET['email']) {
  if (Database::confirm_email($_GET['email'], $_GET['hash'])) {
    $infobox_header = $l['Email_address_confirmed'];
    $infobox_green_red = 'green';
    $infobox_body = "<p>" . $l['Email_now_confirmed__'] . " " . $_GET['email'] . "</p>";
  } else {

    $infobox_header = $l['Email_address_not_confirmed'];
    $infobox_green_red = 'red';
    $infobox_body = "<p>" . $l['The_email_address_is_not_confirmed_'] . " " . $_GET['email'] . "</p>";
  }
}

if (!is_null($infobox_body) && !is_null($infobox_green_red) && !is_null($infobox_header)) {
  echo '
    <div class="box">
    <div class="box-head ' . $infobox_green_red . '">' . $infobox_header . '</div>
    <div class="box-body">' . $infobox_body . '</div>
    </div>';
}
?>            

            <?php 
              include('html-include/tour.php'); 
            ?>
          </div>

          <div class="right-column">
          <?php
            include('html-include/login.php');
          ?>
            <div class="box right">
              <div class="box-head"><? echo $l['Sign_up']; ?></div>
              <div class="box-body">
                <form method="post" name="signup" action="server.php?action=signup" data-submit-loading="true">
                  <!-- prevents auto fill of sign up form -->
                  <input type="text" class="display-none"/>
                  <input type="password" class="display-none"/>
                  <table>
                    <tr>
                      <td><? echo $l['First_name']; ?></td>
                      <td><input type="text" name="firstname" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['firstname']; ?>"/></td>
                    </tr>
                    <tr>
                      <td><? echo $l['Last_name']; ?></td>
                      <td><input type="text" name="lastname" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['lastname']; ?>"/></td>
                    </tr>
                    <tr>
                      <td><? echo $l['Email_address']; ?></td>
                      <td><input type="email" name="signup-email" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['email']; ?>"/></td>
                    </tr>
                    <tr>
                      <td><? echo $l['Password']; ?></td>
                      <td><input type="password" name="password" placeholder="" required="required"/></td>
                    </tr>
                    <tr>
                      <td><? echo $l['Confirm_password']; ?></td>
                      <td><input type="password" name="confirmpassword" placeholder="" required="required"/></td>
                    </tr>
                    <tr>
                      <td><input type="submit" value="<? echo $l['Sign_up']; ?>"/></td>
                      <td></td>
                    </tr>
                  </table>
                </form>
              </div>
            </div>
          </div>
        </div>
        

        <div id="content-login">
          <?php
            include('html-include/login.php');
          ?>
        </div>


        <?php
// include legal info, about and contact html code
include('html-include/legal-info.php');
include('html-include/about.php');
include('html-include/contact.php');
        ?>
        <br class="clear-both">

      </div>

      <?php
        include('html-include/footer.php');
      ?>
    </div>

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
