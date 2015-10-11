<?php
require('mail.class.php'); // include email class
$user = Database::get_user_by_id($_SESSION['id']);
$user_settings = Database::get_user_settings($_SESSION['id']);
$next_to_last_login = Database::get_next_to_last_login_of_user($_SESSION['id']);
?>

<!DOCTYPE html>
<html>
  <? require('html-include/head.html'); ?>
  <body>
    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="#/home">
          <img class="logo" src="img/logo-46.png" />
        </a>
        <ul class="nav left">
          <a href="#/home">
            <li class="nav_home nav-img-li" data-text="Home">
              <img src="img/home.svg" class="nav-image" alt="Home" title="Home"/>
            </li>
          </a>
          <a href="#/query"><li class="nav_query" data-text="Test">Test</li></a>
          <a class="link-to-show-current-word-list" href="#/word-lists"><li class="nav_word-lists" data-text="Word lists">Word lists</li></a>
        </ul>
        <ul class="nav right">
          <a href="#/user">
            <li class="nav_user nav-img-li" data-text="User">
              <img src="img/multiple-user.svg" class="nav-image" alt="Users" title="Users"/>
            </li>
          </a>
          <a href="#/settings">
            <li class="nav_settings nav-img-li" data-text="Settings">
              <img src="img/settings-white.svg" class="nav-image" alt="Settings" title="Settings"/>
            </li>
          </a>
          <a href="server.php?action=logout">
            <li class="nav_logout nav-img-li" data-text="Logout">
              <img src="img/logout.svg" class="nav-image" alt="Logout" title="Logout"/>
            </li>
          </a>
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
        <div id="content-home" data-page="home">
          <div class="left-column">
            <div class="box">
              <div class="box-head">
                Hey <? echo $user->firstname; ?>!
              </div>
              <!--<div class="box-body">
                <?php
if (is_null($next_to_last_login)) {
  // first login
  echo '<p>Welcome to Abfrage3! <a href="#/word-lists">Start by creating a new word list.</a></p>';
} else {
  echo '<p>Last login at ' . $next_to_last_login->get_date_string() . ' from IP-address ' . $next_to_last_login->ip . '</p>';
}
                ?>

              </div>-->
            </div>
            
            <div class="box">
              <div class="box-head">
                <img src="img/feed.svg" />
                Feed
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshFeed" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="feed"></div>
                <div class="text-align-center spacer-top-15"><input type="button" value="Load all" id="feed-load-all" /></div>
              </div>
            </div>
          </div>
          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/history.svg"/>
                Recently used
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshRecentlyUsed" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded" id="recently-used">
              </div>
            </div>

          </div>
        </div>


        <!-- Test -->
        
        
        <div id="content-query" data-page="query">
          <div class="left-column">
            <div class="box" id="query-box">
              <div class="box-head">
                <img src="img/question.svg" />
                Test
                <img src="img/fullscreen.svg" class="box-head-right-icon" data-action="fullscreen" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="query-div">
                  <div id="query-not-started-info">
                    To start a test select labels and lists below and click the button "Start test".
                  </div>
                   
                  <table class="width-100 display-none" id="query-content-table">
                    <tr>
                      <td class="width-150px"><span class="language" id="query-lang1">First language</span>:&nbsp;</td>
                      <td id="query-question">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class="width-150px"><span class="language" id="query-lang2">Second language</span>:&nbsp;</td>
                      <td id="query-answer-table-cell-text-box">
                        <input type="text" id="query-answer" class="unremarkable width-100" data-last-cursor-position="0"/>
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
                    <tr class="query-special-chars-wrapper">
                      <td>
                      </td>
                      <td>
                        <div id="correct-answer" class="display-none unselectable" unselectable="on" style="display: inline; "></div><input type="button" value="&#35805;" class="show-special-chars" id="query-show-special-chars" />
                      </td>
                    </tr>
                    <tr class="query-special-chars-wrapper">
                      <td colspan="2">
                        <div id="query-special-chars" class="special-chars display-none box">
                          <?php include('html-include/special-chars.html'); ?>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td id="query-word-stats" colspan="2"></td>
                    </tr>
                    <tr>
                      <td id="query-selected-words-stats" colspan="2"></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
        
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


            <div class="box" id="query-advanced-settings-box">
              <div class="box-head">
                <img src="img/settings.svg" />
                Advanced settings
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div>
                  <h4>Case sensitivity</h4>
                  <p><label><input type="checkbox" id="query-case-sensitivity" checked="true"/>&nbsp;Case sensitive test</label></p>

                  <h4>Test answer upload</h4>
                  <p><label><input type="checkbox" id="query-results-auto-upload" checked/>&nbsp;Auto upload</label>&nbsp;<input type="button" value="Upload answers" id="query-results-upload-button" disabled="true"/></p>
                  <p id="query-results-upload-counter">Uploaded 0/0 test answers.</p>
                  <p>You can upload your answers (the information about whether you answered the word correctly or not) to the data base to make your next test better adjusted to your knowledge.</p>
                </div>
              </div>
            </div>
          </div>


          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/algorithm.svg" />
                Test algorithm
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <table class="box-table cursor-pointer" id="query-algorithm">
                  <tr data-algorithm="0"><td>Random</td></tr>
                  <tr data-algorithm="1"><td>Words below average</td></tr>
                  <tr data-algorithm="3"><td>In order</td></tr>
                  <tr data-algorithm="2" class="active"><td>Group words</td></tr>
                </table>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/settings.svg" />
                Test settings
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <table class="box-table cursor-pointer" id="query-type">
                  <tr class="active" data-type="0"><td>Text box</td></tr>
                  <tr data-type="1"><td>Buttons</td></tr>
                </table>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/swap.svg" />
                Test direction
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <table class="box-table cursor-pointer" id="query-direction">
                  <tr data-direction="0"><td><span data-value="first-language-information">First language</span> to <span data-value="second-language-information">Second language</span></td></tr>
                  <tr data-direction="1"><td><span data-value="second-language-information">Second language</span> to <span data-value="first-language-information">First language</span></td></tr>
                  <tr class="active" data-direction="-1"><td>Both directions</td></tr>
                </table>
              </div>
            </div>
          </div>
        </div>


        <!-- Word lists -->
        <div id="content-word-lists" data-page="word-lists">
          <div class="box" id="list-of-word-lists-wrapper">
            <div class="box-head">
              <img src="img/server.svg" />
              Word lists
              <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfWordLists" />
              <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
            </div>
            <div class="box-body" data-start-state="expanded">
              <form id="word-list-add-form">
                <input id="word-list-add-name" type="text" placeholder="Word list name" required="true"/>
                <input id="word-list-add-button" type="submit" value="Create list"/>
              </form>
              <div id="list-of-word-lists">
              </div>
            </div>
          </div>
          
          <div id="word-lists-left-column" class="left-column-small">
            <div class="box" id="word-list-title">
              <div class="box-head active"> 
                <a href="#/word-lists"><img src="img/menu-back.svg" id="word-list-menu-back"/></a>
                <div id="word-list-title-name"></div>
              </div>
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

            <div class="box" id="word-list-label" style="z-index: 105; ">
              <div class="box-head">
                <img src="img/tags.svg" />
                Labels
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="getLabelList" />
                <img src="img/expand.svg" class="box-head-right-icon" data-action="expand" />
              </div>
              <div class="box-body" data-start-state="collapsed">
                <div id="list-labels-list">
                </div>
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
                <div id="list-sharings">

                </div>
              </div>
            </div>
          </div>

          <div class="right-column-big">
            <div class="box" id="word-list-info-words">
              <div class="box-head">
                <img src="img/grid.svg" />
                Words (<span id="shown-word-list-words-count">0</span>)
                <img src="img/fullscreen.svg" class="box-head-right-icon" data-action="fullscreen" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="words-add">
                  <div id="words-add-message"></div>
                  <form id="words-add-form">
                    <input id="words-add-language1" type="text" placeholder="First language" required="true"/>
                    <input id="words-add-language2" type="text" placeholder="Second language" required="true"/>
                    <input id="words-add-comment" type="text" placeholder="Comment"/>
                    <input id="words-add-button" type="submit" value="Add word"/>
                    <input type="button" value="&#35805;" class="show-special-chars" id="word-lists-show-special-chars" />
                  </form>
                  <div id="word-lists-special-chars" class="special-chars display-none box">
                    <?php include('html-include/special-chars.html'); ?>
                  </div>
                </div>
                <div id="words-in-list">
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
              <div class="box-head">
                <img src="img/settings.svg" />
                Settings
              </div>
              <div class="box-body">
                <table class="box-table cursor-pointer" id="settings-menu">
                  <tr data-page="profile"><td>Profile</td></tr>
                  <tr data-page="email-notifications"><td>Email notifications</td></tr>
                  <tr data-page="account"><td>Account</td></tr>
                </table>
              </div>
            </div>
          </div>
          <div class="right-column width-70" id="settings-content">
            <div class="box" data-page="profile">
              <div class="box-head">
                Change name
              </div>
              <div class="box-body">
                <p id="settings-name-response" class="display-none"></p>
                <form id="settings-name">
                  <input type="text" required="true" value="<? echo $user->firstname; ?>" placeholder="First name" id="settings-firstname" />&nbsp;
                  <input type="text" required="true" value="<? echo $user->lastname; ?>" placeholder="Last name" id="settings-lastname" />&nbsp;
                  <input type="submit" value="Change name" id="settings-submit-button"/>&nbsp;
                </form>
              </div>
            </div>

            <div class="box" data-page="profile">
              <div class="box-head">
                Change password
              </div>
              <div class="box-body">
                <p id="settings-password-response" class="display-none"></p>
                <form id="settings-password">
                  <table class="width-auto">
                    <tr><td>Old password</td><td><input id="settings-password-old" required="true" type="password"/></td></tr>
                    <tr><td>New password</td><td><input id="settings-password-new" required="true" type="password"/></td></tr>
                    <tr><td>Confirm new password</td><td><input id="settings-password-new-confirm" required="true" type="password"/></td></tr>
                    <tr><td><input id="settings-password-button" type="submit" value="Change password" class="width-auto"/></td><td></td></tr>
                  </table>
                </form>
              </div>
            </div>

            <!--<div class="box" data-page="profile">
              <div class="box-head">
                Change email-address
              </div>
              <div class="box-body">
                <form id="settings-email">
                  <input type="text" class="display-none"/>
                  <input type="password" class="display-none"/>
                  
                  <input type="text" id="settings-email-new-email" value="<? echo $user->email; ?>" required="true"/>&nbsp;
                  <input type="password" id="settings-email-password" placeholder="Password" required="true" />&nbsp;
                  <input type="submit" id="settings-email-submit-button" value="Change email-address" />
                </form>
              </div>
            </div>-->
            
            <div class="box" data-page="profile">
              <div class="box-head">
                Secret service clause
              </div>
              <div class="box-body">
                <input type="checkbox" disabled checked/>&nbsp;The NSA is allowed to spy me.
              </div>
            </div>


            <div class="box" data-page="email-notifications">
              <div class="box-head">
                Email notifications
              </div>
              <div class="box-body">
                Not available yet.
              </div>
            </div>

            <div class="box" data-page="account">
              <div class="box-head">Advertisement</div>
              <div class="box-body">
                <label><input type="checkbox" id="enable-ads-checkbox" <? echo ($user_settings->ads_enabled ? 'checked' : ''); ?>/>&nbsp;Load and show advertisement</label>
              </div>
            </div>

            <div class="box" data-page="account">
              <div class="box-head">
                Delete account
              </div>
              <div class="box-body">
                <p>Be careful: This action can't be undone. Your shared lists will still be visible to other users. </p>
                <p id="settings-delete-account-response" class="display-none"></p>
                <form id="settings-delete-account-form">
                  <input type="password" required="true" placeholder="Password" id="settings-delete-account-password" />&nbsp;
                  <input type="button" value="Delete account" id="settings-delete-account-button" />
                </form>
              </div>
            </div>
          </div>
        </div>


        <?php
          // include legal info, about, contact, tour and advertisement HTML code
          include('html-include/legal-info.html');
          include('html-include/about.html');
          include('html-include/contact.html');
          include('html-include/tour.html');
        ?>
        <br class="clear-both">
        <?php
          include('html-include/advertisement.html');
        ?>
      </div>

      <?php
        include('html-include/footer.html');
      ?>
      <div id="scroll-top-button">
        <img src="img/menu-back.svg"/>
    </div>

    <div id="background-black-overlay" class="display-none"></div>

    <div id="word-import-box" class="display-none">
      <div class="word-import-box-inner-wrapper">
        <div class="box">
          <div class="box-head">Import words</div>
          <div class="box-body">coming soon...</div>
        </div>
      </div>
    </div>

    <script type="text/javascript" src="database.js"></script>

    <script type="text/javascript">
      // PHP-defined global variables
      var adsEnabled = <? echo $user_settings->ads_enabled ? 'true' : 'false'; ?>;
      
      var Database = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_query_data($_SESSION['id']))); ?>');
      Database.listOfUsersWhoHaveAddedYou = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_list_of_users_who_have_added_user($_SESSION['id']))); ?>');
      Database.listOfAddedUsers = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_list_of_added_users_of_user($_SESSION['id']))); ?>');


      function getListObjectByServerData(data) {
        var list = new List(
          data.id, 
          data.name, 
          data.creator, 
          data.comment, 
          data.language1,
          data.language2, 
          data.creation_time, 
          data.words, 
          data.sharings);
        list.allowEdit = data.allowEdit;
        list.allowSharing = data.allowSharing;
        list.labels = data.labels;
        return list;
      }

      function getListArrayByServerData(data) {
        var listObjectArray = [];
        for (var i = 0; i < data.length; i++) {
          listObjectArray.push(getListObjectByServerData(data[i]));
        }
        return listObjectArray;
      }

      (function() {
        Database.lists = getListArrayByServerData(Database.lists);
        Database.userId = <? echo $user->id; ?>;
        Database.getListById = function(id) {
          for (var i = Database.lists.length - 1; i >= 0; i--) {
            if (Database.lists[i].id === id) {
              return Database.lists[i];
            }
          };
        };
      })();
    </script>

    <!-- add scripts to the DOM -->
    <script type="text/javascript">
      document.write('\x3Cscript src="jquery-1.11.3.min.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="extensions.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="messagebox.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="scripts.js" type="text/javascript">\x3C/script>');
    
      // single page appcliation script
      document.write('\x3Cscript src="single-page-application.js" type="text/javascript">\x3C/script>');

      // include scripts for every single page
      document.write('\x3Cscript src="home/home.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="home/word-lists.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="home/query.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="home/user.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="home/settings.js" type="text/javascript">\x3C/script>');
    </script>
    
  </body>
</html>
