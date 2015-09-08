<!DOCTYPE html>
<html>
  <? require('html-include/head.html'); ?>
  <body>

    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="#/home">
          <img class="logo" src="img/logo.svg" alt="Abfrage3" />
        </a><br class="clear-both smaller-800">
        <ul class="nav left">
          <a href="#/home">
            <li class="nav_home nav-img-li" data-text="Home">
              <img src="img/home.svg" class="nav-image" alt="Home" title="Home"/>
            </li>
          </a>
        </ul>

        <ul class="nav right">
          <a href="#/about"><li class="nav_about" data-text="About">About</li></a>
          <a href="#/contact"><li class="nav_contact" data-text="Contact">Contact</li></a>
          <a href="#/legal-info"><li class="nav_legal-info" data-text="Legal info">Legal info</li></a>
        </ul><br class="clear-both">
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
            <div class="box">
              <div class="box-body tour-container font-size-100">
                <div class="text-align-center">
                  <table class="width-100 headline spacer-15">
                    <tr>
                      <td><h2>Welcome to&nbsp;</h2></td>
                      <td><img src="img/logo-46.png"/></td>
                    </tr>
                  </table>
                  <p>
                    Abfrage3 is an online vokabulary trainer with functionality to create, share and organize word lists. 
                    Its purpose is to make learning vocabulary of a foreign language more efficient.
                    The website is still under heavy development so some features might not work as expected.
                  </p>
                  <p>
                    <img src="/img/mockup-image.jpg" class="full-width">
                  </p>
                  <p class="italic">Here is how it works:</p>
                </div>


                <div class="tour-element">
                  <hr class="spacer-30">
                  <h2>Create lists and add words</h2>
                  <div>
                    <div class="col-l">
                      <p>Define a name and create a new list for your words.</p>
                    </div>
                    <div class="col-r">
                      <div class="box">
                        <div class="box-head">
                          <img src="img/server.svg">
                          Your word lists
                        </div>
                        <div class="box-body">
                          <input type="text" placeholder="Word list name">
                          <input type="button" value="Create list">
                          <hr class="spacer-top-15">
                          <div><table class="box-table cursor-pointer"><tbody><tr><td>My first word list</td></tr><tr><td>Difficult new words</td></tr></tbody></table></div>
                        </div>
                      </div>
                    </div>
                    <br class="clear-both">

                    <div class="col-l">
                      <p>Add words to your lists to learn them later.</p>
                      <p>If you already have some words, e.g. in a file, you can simply import them.</p>
                    </div>
                    <div class="col-r">
                      <div class="box">
                        <div class="box-head">
                          <img src="img/grid.svg">
                          Words
                        </div>
                        <div class="box-body">
                          <div>
                            <input type="text" placeholder="German">
                            <input type="text" placeholder="English">
                            <input type="button" value="Add word">
                            <hr class="spacer-top-15">
                          </div>
                          <div>
                            <table class="box-table button-right-column">
                              <tbody>
                                <tr class="bold cursor-default"><td>German</td><td>English</td><td></td></tr>
                                <tr><td>abwechslungsreich</td><td>diversified</td><td><input type="button" class="inline" value="Edit">&nbsp;<input type="button" class="inline" value="Remove"></td></tr>
                                <tr><td>Vorschlag</td><td>proposal</td><td><input type="button" class="inline" value="Edit">&nbsp;<input type="button" class="inline" value="Remove"></td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    <br class="clear-both">
                  </div>
                </div>


                <div class="tour-element">
                  <hr class="spacer-30">
                  <h2>Learn words</h2>
                  <div>
                    <div class="col-l">
                      <p>Learn the added words using the <span class="italic">Test</span> feature. Your answers (correct or not) will be saved to personalize your tests.</p>
                      <p>You can select different algorithms e.g. <span class="italic">Random</span> or <span class="italic">Below average</span> to make the test even more efficient.</p>
                    </div>
                    <div class="col-r">
                      <div class="box" id="query-box">
                        <div class="box-head">
                          <img src="img/question.svg">
                          Test
                        </div>
                        <div class="box-body">
                          <div>
                            <table class="width-100">
                              <tbody>
                                <tr>
                                  <td class="width-150px"><span class="language">English</span>:&nbsp;</td>
                                  <td id="query-question">swift</td>
                                </tr>
                                <tr>
                                  <td class="width-150px"><span class="language">German</span>:&nbsp;</td>
                                  <td>
                                    <input type="text" id="query-answer" class="unremarkable width-100">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    <br class="clear-both">

                    <div class="col-l">
                      <p>To lazy to type? Use buttons to learn the words instead of typing the answer every time.</p>
                    </div>
                    <div class="col-r">
                      <div class="box" id="query-box">
                        <div class="box-body">
                          <div id="query-div">
                            <table class="width-100" id="query-content-table">
                              <tbody>
                                <tr>
                                  <td class="width-150px"><span class="language" id="query-lang1">English</span>:&nbsp;</td>
                                  <td id="query-question">leverage</td>
                                </tr>
                                <tr>
                                  <td class="width-150px"><span class="language" id="query-lang2">German</span>:&nbsp;</td>
                                  <td id="query-answer-table-cell-buttons" class="display-none" style="display: table-cell;">
                                    <table class="width-100">
                                      <tbody>
                                        <tr>
                                          <td class="width-33"><input id="query-answer-known" type="button" value="I know!" class="height-50px width-100"></td>
                                          <td class="width-33"><input id="query-answer-not-sure" type="button" value="Not sure..." class="height-50px width-100"></td>
                                          <td class="width-33"><input id="query-answer-not-known" type="button" value="No idea." class="height-50px width-100"></td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    <div id="query-answer-buttons" style="display: none;"></div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    <br class="clear-both">
                  </div>
                </div>


                <div class="tour-element">
                  <hr class="spacer-30">
                  <h2>Share your word lists</h2>
                  <div>
                    <div class="col-l">
                      <p>To share lists with other users add them by their email-address and define whether they have permissions to view or edit the list.</p>
                      <p>Keep in mind: To prevent spamming, other users can only see lists you've shared with them, if they have added you in the <span class="italic">Users</span> section.</p>

                    </div>
                    <div class="col-r">
                      <div class="box" id="word-list-sharing" style="display: block;">
                        <div class="box-head">
                          <img src="img/share.svg">
                          Share
                        </div>
                        <div class="box-body">
                          <input id="share-list-other-user-email" type="text" placeholder="Email-address" required="true">
                          <select id="share-list-permissions" required="true">
                            <option value="2">Can view</option>
                            <option value="1">Can edit</option>
                          </select>
                          <input id="share-list-submit" type="button" value="Share">
                          <hr class="spacer-top-15">
                          <div id="list-sharings">
                            <table class="box-table button-right-column">
                              <tbody>
                                <tr class="bold cursor-default"><td>Name</td><td>Permissions</td><td></td></tr>
                                <tr><td>My classmate</td><td>Can view</td><td><input type="button" class="inline" value="Stop sharing"></td></tr>
                                <tr><td>Another guy</td><td>Can edit</td><td><input type="button" class="inline" value="Stop sharing"></td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    <br class="clear-both">

                    <div class="col-l">
                      <p>Add other users to see lists they've shared with you.</p>
                    </div>
                    <div class="col-r">
                      <div class="box">
                        <div class="box-head">
                          <img src="img/users.svg">
                          People you've added
                        </div>
                        <div class="box-body" data-start-state="expanded">
                          <div id="user-add-message"></div>
                          <input id="user-add-email" type="email" placeholder="Email-address" required="true">
                          <input id="user-add-button" type="button" value="Add user">
                          <hr class="spacer-top-15">
                          <div id="people-you-have-added">
                            <table class="box-table button-right-column">
                              <tbody>
                                <tr class="bold cursor-default"><td>Name</td><td>Email-Address</td><td></td></tr>
                                <tr><td>My classmate</td><td>bla@gmail.com</td><td><input type="button" class="inline" value="Remove"></td></tr>
                                <tr><td>Another guy</td><td>email2@gmail.com</td><td><input type="button" class="inline" value="Remove"></td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    <br class="clear-both">
                  </div>
                </div>


                <!--<div class="tour-element">
<hr class="spacer-30">
<h2></h2>
<div>
<div class="col-l">
<p></p>
</div>
<div class="col-r">

</div>
<br class="clear-both">
</div>
</div>-->
              </div>
            </div>
          </div>

          <div class="right-column">
            <div class="box">
              <div class="box-head">Login</div>
              <div class="box-body">
                <form method="post" name="login" action="server.php?action=login" data-submit-loading="true">
                  <table>
                    <tr>
                      <td>Email-address</td>
                      <td><input type="email" name="email" placeholder="" required="required" value="<? if(!$_GET['signup_success'] == "false") echo($_GET['email']); ?>"/></td>
                    </tr>
                    <tr>
                      <td>Password</td>
                      <td><input type="password" name="password" placeholder="" required="required"/></td>
                    </tr>
                    <tr>
                      <td colspan="2" class="padding-v-6"><label><input type="checkbox" name="stay-logged-in" value="1" class="initial-width initial-height" checked/>&nbsp;Stay logged in</label></td>  
                    </tr>
                    <tr>
                      <td><input type="submit" value="Login"/></td>
                      <td></td>
                    </tr>
                    <!--<tr>
<td colspan="2" style="padding-top: 5px; "><a href="#"><small>Forgot your password?</small></a></td>
</tr>-->
                  </table>
                </form>
              </div>
            </div>
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


        <?php
// include legal info, about and contact html code
include('html-include/legal-info.html');
include('html-include/about.html');
include('html-include/contact.html');
        ?>


        <br class="clear-both hide-below-700">
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
