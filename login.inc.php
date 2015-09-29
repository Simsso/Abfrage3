<!DOCTYPE html>
<html>
  <? require('html-include/head.html'); ?>
  <body>

    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="#/home">
          <img class="logo" src="img/logo-46.png" alt="Abfrage3" />
        </a>
        <ul class="nav left">
          <a href="#/home">
            <li class="nav_home nav-img-li" data-text="Home">
              <img src="img/home.svg" class="nav-image" alt="Home" title="Home"/>
            </li>
          </a>
          <a href="#/login" class="show-mobile"><li class="nav_login" data-text"Login">Login</li></a>
        </ul>

        <ul class="nav right">
          <a href="#/about"><li class="nav_about" data-text="About">About</li></a>
          <a href="#/contact"><li class="nav_contact" data-text="Contact">Contact</li></a>
          <a href="#/legal-info"><li class="nav_legal-info" data-text="Legal info">Legal info</li></a>
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
if($_GET['signup_success'] == "true") {
  $infobox_header = "Successfully signed up";
  $infobox_green_red = "green";
  $infobox_body = '<p>Hello ' . $_GET['firstname'] . '!</p><p>Your account has been created. Check your emails and click on the link to confirm your email address (' . $_GET['email'] . ') and activate your account.</p>';
} else if ($_GET['signup_success'] == "false") {
  $infobox_header = "Could not sign up";
  $infobox_green_red = "red";
  $infobox_body = '<p>An error occured while creating your account.</p><p>' . $_GET['signup_message'] . '</p>';
}


// login
if($_GET['login_message']) {
  $infobox_header = "Could not login";
  $infobox_green_red = "red";
  $infobox_body = '<p>An error occured while logging in.</p><p>' . $_GET['login_message'] . '</p>';
}


// email confirmation
if($_GET['email_confirmation_key'] && $_GET['email']) {
  if (Database::confirm_email($_GET['email'], $_GET['email_confirmation_key'])) {
    $infobox_header = "Email address confirmed";
    $infobox_green_red = "green";
    $infobox_body = '<p>The email address ' . $_GET['email'] . ' is now confirmed and can be used to login.</p>';
  } else {

    $infobox_header = "Email address not confirmed";
    $infobox_green_red = "red";
    $infobox_body = '<p>The email address ' . $_GET['email'] . ' is not confirmed.</p>';
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
              include('html-include/tour.html'); 
            ?>
          </div>

          <div class="right-column">
          <?php
            include('html-include/login.html');
          ?>
            <div class="box right">
              <div class="box-head">Sign up</div>
              <div class="box-body">
                <form method="post" name="signup" action="server.php?action=signup" data-submit-loading="true">
                  <!-- prevents auto fill of sign up form -->
                  <input type="text" class="display-none"/>
                  <input type="password" class="display-none"/>
                  <table>
                    <tr>
                      <td>First name</td>
                      <td><input type="text" name="firstname" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['firstname']; ?>"/></td>
                    </tr>
                    <tr>
                      <td>Last name</td>
                      <td><input type="text" name="lastname" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['lastname']; ?>"/></td>
                    </tr>
                    <tr>
                      <td>Email-address</td>
                      <td><input type="email" name="signup-email" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['email']; ?>"/></td>
                    </tr>
                    <tr>
                      <td>Password</td>
                      <td><input type="password" name="password" placeholder="" required="required"/></td>
                    </tr>
                    <tr>
                      <td>Confirm password</td>
                      <td><input type="password" name="confirmpassword" placeholder="" required="required"/></td>
                    </tr>
                    <tr>
                      <td><input type="submit" value="Sign up"/></td>
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
            include('html-include/login.html');
          ?>
        </div>


        <?php
// include legal info, about and contact html code
include('html-include/legal-info.html');
include('html-include/about.html');
include('html-include/contact.html');
        ?>

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
