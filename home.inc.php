<?php
require('mail.class.php');
$user = Database::get_user_by_id($_SESSION['id']);
$next_to_last_login = Database::get_next_to_last_login_of_user($_SESSION['id']);
?>

<!DOCTYPE html>
<html>
  <? require('html-include/head.html'); ?>
  <body>
    <div id="main-wrapper">

      <!-- navigation -->
      <nav id="head-nav" class="navbar">
        <div class="navbar-inner content-width">
          <a href="#home">
            <img class="logo" src="img/logo-46.png" />
          </a><br class="clear-both smaller-800">
          <ul class="nav left">
            <li class="nav_home nav-img-li" data-text="Home">
              <a href="#home"><img src="img/home.svg" class="nav-image" alt="Home" title="Home"/></a>
            </li>
            <li class="nav_query" data-text="Test"><a href="#query">Test</a></li>
            <li class="nav_word-lists" data-text="Word lists"><a href="#word-lists">Word lists</a></li>
          </ul>
          <ul class="nav right">
            <li class="nav_user nav-img-li" data-text="User">
              <a href="#user"><img src="img/multiple-user.svg" class="nav-image" alt="Users" title="Users"/></a>
            </li>
            <li class="nav_settings nav-img-li" data-text="Settings">
              <a href="#settings"><img src="img/settings-white.svg" class="nav-image" alt="Settings" title="Settings"/></a>
            </li>
            <li class="nav_logout nav-img-li" data-text="Logout">
              <a href="/./server.php?action=logout"><img src="img/logout.svg" class="nav-image" alt="Logout" title="Logout"/></a>
            </li>
          </ul><br class="clear-both">
        </div>
      </nav>


      <div class="main content-width" id="main">
        <div class="sk-three-bounce">
          <div class="sk-child sk-bounce1"></div>
          <div class="sk-child sk-bounce2"></div>
          <div class="sk-child sk-bounce3"></div>
        </div>

        <!-- Home -->
        <div id="content-home" data-page="home">
          <div class="left-column">
            <div class="box">
              <div class="box-head">
                Hey <? echo $user->firstname; ?>!
              </div>
              <div class="box-body">
                <?php
if (is_null($next_to_last_login)) {
  // first login
  echo '<p>Welcome to Abfrage3! <a href="#word-lists">Start by creating a new word list.</a></p>';
} else {
  echo '<p>Last login at ' . $next_to_last_login->get_date_string() . ' from IP-address ' . $next_to_last_login->ip . '</p>';
}
                ?>

              </div>
            </div>
          </div>
          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/history.svg"/>
                Recently used
              </div>
              <div class="box-body">
                coming soon...
              </div>
            </div>

          </div>
        </div>


        <!-- Test -->
        <div id="content-query" data-page="query">
          <div class="left-column">
            <div class="box" id="query-select-box">
              <div class="box-head">
                <img src="img/tags.svg" />
                Select labels and word lists
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshQueryLabelList" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="query-selection"></div>
              </div>
            </div>

            <div class="box" id="query-box">
              <div class="box-head">
                <img src="img/question.svg" />
                Test
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="query">
                  <div id="query-not-started-info">
                    To start a test select labels and lists above and click the button "Start test".
                  </div>
                   
                  <table class="width-100 display-none" id="query-content-table">
                    <tr>
                      <td class="width-150px"><span class="language" id="query-lang1">First language</span>:&nbsp;</td>
                      <td id="query-question">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class="width-150px"><span class="language" id="query-lang2">Second language</span>:&nbsp;</td>
                      <td id="query-answer-table-cell-text-box">
                        <input type="text" id="query-answer" class="unremarkable width-100"/>
                        <div id="correct-answer" class="display-none unselectable" unselectable="on"></div>
                      </td>
                      <td id="query-answer-table-cell-buttons" class="display-none">
                        <table class="width-100">
                          <tr>
                            <td class="width-33"><input id="query-answer-known" type="button" value="I know!" class="height-50px width-100"/></td>
                            <td class="width-33"><input id="query-answer-not-sure" type="button" value="Not sure..." class="height-50px width-100"/></td>
                            <td class="width-33"><input id="query-answer-not-known" type="button" value="No idea." class="height-50px width-100"/></td>
                          </tr>
                        </table>
                        <div id="query-answer-buttons"></div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div id="query-word-mark"></div>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>


            <div class="box" id="query-results-upload-box">
              <div class="box-head">
                <img src="img/upload.svg" />
                Upload test results
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="query-results-upload">
                  <p><label><input type="checkbox" id="query-results-auto-upload" checked/>&nbsp;Auto upload</label>&nbsp;<input type="button" value="Upload answers" id="query-results-upload-button" disabled="true"/></p>
                  <p>You can upload your answers (the information about whether you answered the word correctly or not) to the cloud to make your next test better adjusted to your knowledge. No one else will be able to see your answers.</p>
                </div>
              </div>
            </div>
          </div>


          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/algorithm.svg" />
                Test algorithm
              </div>
              <div class="box-body">
                <table class="box-table cursor-pointer" id="query-algorithm">
                  <tr class="active" data-algorithm="0"><td>Random</td></tr>
                  <tr data-algorithm="1"><td>Words under average</td></tr>
                  <tr data-algorithm="2"><td>Group words</td></tr>
                </table>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/settings.svg" />
                Test settings
              </div>
              <div class="box-body">
                <table class="box-table cursor-pointer" id="query-type">
                  <tr class="active" data-type="0"><td>Text box</td></tr>
                  <tr><td data-type="1">Buttons</td></tr>
                </table>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/swap.svg" />
                Test direction
              </div>
              <div class="box-body">
                <table class="box-table cursor-pointer" id="query-direction">
                  <tr><td data-direction="0">First language to second language</td></tr>
                  <tr><td data-direction="1">Second language to first language</td></tr>
                  <tr class="active" data-direction="-1"><td>Both directions</td></tr>
                </table>
              </div>
            </div>
          </div>
        </div>


        <!-- Word lists -->
        <div id="content-word-lists" data-page="word-lists">
          <div class="left-column">
            <div class="box" id="word-list-title">
              <div class="box-head active"></div>
            </div>

            <div class="box" id="word-list-info">
              <div class="box-head">
                <img src="img/info.svg" />
                <div class="inline"></div>
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
              </div>
            </div>

            <div class="box" id="word-list-sharing">
              <div class="box-head">
                <img src="img/share.svg" />
                Share
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListSharings" />
                <img src="img/expand.svg" class="box-head-right-icon" data-action="expand" />
              </div>
              <div class="box-body" data-start-state="collapsed">
                <form id="share-list-form">
                  <input id="share-list-other-user-email" type="text" placeholder="Email-address" required="true"/>
                  <select id="share-list-permissions" required="true">
                    <option value="2">Can view</option>
                    <option value="1">Can edit</option>
                  </select>
                  <input id="share-list-submit" type="submit" value="Share"/>
                </form>
                <hr class="spacer-top-15 spacer-bottom-5">
                <div id="list-sharings">

                </div>
              </div>
            </div>

            <div class="box" id="word-list-label" style="z-index: 105; ">
              <div class="box-head">
                <img src="img/tags.svg" />
                Labels
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="getLabelList" />
                <img src="img/expand.svg" class="box-head-right-icon" data-action="expand" />
              </div>
              <div class="box-body" data-start-state="collapsed">
                <!--<div id="label-add">



<hr class="spacer-top-15 spacer-bottom-15">
</div>-->
                <div id="list-labels-list">
                </div>
              </div>
            </div>

            <div class="box" id="word-list-info-words">
              <div class="box-head">
                <img src="img/grid.svg" />
                Words
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="words-add">
                  <div id="words-add-message"></div>
                  <form id="words-add-form">
                    <input id="words-add-language1" type="text" placeholder="First language" required="true"/>
                    <input id="words-add-language2" type="text" placeholder="Second language" required="true"/>
                    <input id="words-add-button" type="submit" value="Add word"/>
                  </form>
                  <hr class="spacer-top-15 spacer-bottom-5">
                </div>
                <div id="words-in-list">
                </div>
              </div>
            </div>
          </div>
          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/server.svg" />
                Your word lists
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfWordLists" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <form id="word-list-add-form">
                  <input id="word-list-add-name" type="text" placeholder="Word list name" required="true"/>
                  <input id="word-list-add-button" type="submit" value="Create list"/>
                </form>
                <hr class="spacer-top-15 spacer-bottom-5">
                <div id="list-of-word-lists">
                </div>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/share.svg" />
                Shared with you
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfSharedWordLists" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="list-of-shared-word-lists">
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Users -->
        <div id="content-user" data-page="users">
          <div class="left-column width-50">
            <div class="box">
              <div class="box-head">
                <img src="img/users.svg" />
                People you've added
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfAddedUsers" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="user-add-message"></div>
                <form id="user-add-form">
                  <input id="user-add-email" type="email" placeholder="Email-address" required="true"/>
                  <input id="user-add-button" type="submit" value="Add user"/>
                </form>
                <hr class="spacer-top-15 spacer-bottom-5">
                <div id="people-you-have-added">
                </div>
              </div>
            </div>
          </div>

          <div class="right-column width-50">
            <div class="box">
              <div class="box-head">
                <img src="img/users.svg" />
                People who have added you
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfUsersWhoHaveAddedYou" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="people-who-have-added-you">
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Settings -->
        <div id="content-settings" data-page="settings">
          <div class="left-column width-30">
            <div class="box">
              <div class="box-head">Settings</div>
              <div class="box-body">
                <table class="box-table">
                  <tr><td>Profile</td></tr>
                  <tr><td>Email notifications</td></tr>
                  <tr><td>Account</td></tr>
                </table>
              </div>
            </div>
          </div>
          <div class="right-column width-70">
            <div class="box">
              <div class="box-head">
                Change name
              </div>
              <div class="box-body">
                coming soon...
              </div>
            </div>

            <div class="box">
              <div class="box-head">
                Change password
              </div>
              <div class="box-body">
                coming soon...
              </div>
            </div>

            <div class="box">
              <div class="box-head">
                Change email-address
              </div>
              <div class="box-body">
                coming soon...
              </div>
            </div>


            <div class="box">
              <div class="box-head">
                Email notifications
              </div>
              <div class="box-body">
                coming soon...
              </div>
            </div>

            <div class="box">
              <div class="box-head">
                Delete account
              </div>
              <div class="box-body">
                coming soon...
              </div>
            </div>
          </div>
        </div>

        <br class="clear-both hide-below-700">

        <footer class="advertisment-bottom box">
          <div class="box-body">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- Abfrage3 Bottom -->
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-9727315436627573" data-ad-slot="4992943646" data-ad-format="auto"></ins>
            <script>
              (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
          </div>
        </footer>
      </div>

      <?php
include('html-include/footer.html');
      ?>
    </div>

    <!-- basic scripts -->
    <script src="jquery-1.11.3.min.js" type="text/javascript"></script>
    <script src="extensions.js" type="text/javascript"></script>
    <script src="scripts.js" type="text/javascript"></script>


    <!-- include scripts for every single page -->
    <script src="home/word-lists.js" type="text/javascript"></script>
    <script src="home/query.js" type="text/javascript"></script>
    <script src="home/user.js" type="text/javascript"></script>
    <script src="home/settings.js" type="text/javascript"></script>

    <!-- single page application script -->
    <script src="single-page-application.js" type="text/javascript"></script>
  </body>
</html>
