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
          <img class="logo" src="img/logo.svg" />
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
                <div class="text-align-center spacer-top-15"><input type="button" value="Load all" data-pending-value="Loading all" id="feed-load-all" /></div>
              </div>
            </div>


            <!-- feed templates -->

            <script id="feed-table-template" type="text/x-handlebars-template">
              <table class="feed-table box-table no-flex">{{tableBody}}</table>
            </script>

            <script id="feed-no-content-template" type="text/x-handlebars-template">
              <p>Nothing new since last login.</p>
            </script>

            <script id="feed-user-added-element-template" type="text/x-handlebars-template">
              <tr>
                <td>
                  <img src="img/users.svg">
                </td>
                <td>
                  {{info.firstname}} {{info.lastname}} has added you.
                  &nbsp;<span class="feed-time">{{feedItem.timeString}}</span>
                </td>
              </tr>
            </script>

            <script id="feed-list-shared-element-template" type="text/x-handlebars-template">
              <tr>
                <td>
                  <img src="img/share.svg">
                </td>
                <td>
                  {{info.user.firstname}} {{info.user.lastname}} gave you permissions to 
                  {{#if info.editingPermissions}}
                    edit
                  {{else}}
                    view
                  {{/if}} their list <a href="#/word-lists/{{info.list.id}}">{{info.list.name}}</a>.
                  &nbsp;<span class="feed-time">{{feedItem.timeString}}</span>
                </td>
              </tr>
            </script>

            <script id="feed-word-added-element-template" type="text/x-handlebars-template">
              <tr>
                <td>
                  <img src="img/add.svg">
                </td>
                <td>
                  {{info.user.firstname}} {{info.user.lastname}} has added {{info.amountString}} word{{#unless info.exactlyOneWord}}s{{/unless}} 
                  to 
                  {{#if info.yourList}}
                    your
                  {{else}}
                    {{#if info.userAddedToTheirOwnList}}
                      their
                    {{else}}
                      {{info.list_creator.firstname}}&#39;s
                    {{/if}}
                  {{/if}} 
                  list <a href="#/word-lists/{{info.list.id}}">{{info.list.name}}</a>.
                  &nbsp;<span class="feed-time">{{feedItem.timeString}}</span>
                </td>
              </tr>
            </script>

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

            <script id="recently-used-no-content-template" type="text/x-handlebars-template">
              <p>No recently used lists found.</p>
            </script>

            <script id="recently-used-table-template" type="text/x-handlebars-template">
              <table class="box-table cursor-pointer">
                {{#each list}}
                  <tr data-list-id="{{id}}">
                    <td>
                      {{name}}
                    </td>
                  </tr>
                {{/each}}
              </table>
            </script>

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
                      <td><span class="language" id="query-lang1">First language</span>:&nbsp;</td>
                      <td class="width-100" id="query-question">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><span class="language" id="query-lang2">Second language</span>:&nbsp;</td>
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
                    <tr>
                      <td>Comment:&nbsp;</td>
                      <td id="query-comment"></td>
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
                  <p>
                    <label>
                      <input type="checkbox" id="query-case-sensitivity" checked="true"/>
                      &nbsp;
                      Case sensitive test

                      <span class="tooltip">Accept e.g. "answer" for "Answer".</span>
                    </label>
                  </p>

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
                  <tr data-algorithm="0">
                    <td>
                      Random
                      <span class="tooltip">
                        The "Random" algorithm asks a randomly chosen word.
                      </span>
                  </td>
                  </tr>
                  <tr data-algorithm="1">
                    <td>
                      Words below average
                      <span class="tooltip">
                        The "Words below average" algorithm asks words you haven't known often compared to the others.
                      </span>
                  </td>
                  </tr>
                  <tr data-algorithm="3">
                    <td>
                      In order
                      <span class="tooltip">
                        The "In order" algorithm iterates through all words one after the other.
                      </span>
                  </td>
                  </tr>
                  <tr data-algorithm="2" class="active">
                    <td>
                      Group words
                      <span class="tooltip">
                        The group words algorithm picks some words and asks them nearly randomly. If you know one word it will be replaced by a new one.
                      </span>
                  </td>
                  </tr>
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
                <input id="word-list-add-button" type="submit" value="Create list" data-pending-value="Creating list"/>
              </form>
              <div id="list-of-word-lists">
              </div>
            </div>

            <script id="word-lists-no-list-template" type="text/x-handlebars-template"><p class="spacer-top-15">You haven&#39;t created any wordlists yet.</p></script>

            <script id="word-lists-list-of-word-lists-template" type="text/x-handlebars-template">
              <table class="box-table cursor-pointer">
                <tr class="cursor-default">
                  <th>Name</th>
                  <th class="hide-mobile">Content</th>
                  <th class="hide-mobile">Entries</th>
                  <th class="hide-mobile">Creator</th>
                </tr>
                {{#each list}}
                  <tr data-action="edit" data-list-id="{{id}}" id="list-of-word-lists-row-{{id}}">
                    <td>{{name}}</td>
                    <td class="hide-mobile">{{language1}} - {{language2}}</td>
                    <td class="hide-mobile">{{words.length}}</td>
                    <td class="hide-mobile">{{creator.firstname}} {{creator.lastname}}</td>
                  </tr>
                {{/each}}
              </table>
            </script>
          </div>


          <div id="word-list-loading" class="box">
            <div id="word-list-loading-head" class="box-head">
              Loading word list
            </div>
            <div class="box-body">
              <div class="sk-three-bounce">
                <div class="sk-child sk-bounce1"></div>
                <div class="sk-child sk-bounce2"></div>
                <div class="sk-child sk-bounce3"></div>
              </div>

              <a href="/#/word-lists">Show all word lists.</a>
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

              <script id="word-lists-no-labels-template" type="text/x-handlebars-template"><p>You don&#39;t have any labels.</p></script>

              <script id="word-lists-label-table-template" type="text/x-handlebars-template">
                <table class="box-table button-right-column no-flex">{{content}}</table
              </script>

              <script id="word-lists-label-list-template" type="text/x-handlebars-template">
                <tr
                {{#unless show}} style="display: none; "{{/unless}} class="cursor-default">
                  <td colspan="2" style="padding-left: {{indentingPxl}}px; text-align: left; ">
                    <form class="label-add-form inline">
                      <input type="hidden" class="label-add-parent" value="{{id}}"/>
                      <input class="label-add-name inline" style="margin-left: -8px; " type="text" placeholder="Label name" required="true"/>&nbsp;
                      <input class="label-add-button inline" type="submit" value="Add label" data-pending-value="Adding label"/>
                    </form>
                  </td>
                </tr>
              </script>

              <script id="word-lists-label-single-list-element-template" type="text/x-handlebars-template">
                <tr data-label-id="{{label.id}}" data-indenting="{{indenting}}" {{#if displayNone}} style="display: none; "{{/if}} id="label-list-row-id-{{label.id}}">
                  <form class="label-rename-form" id="label-rename-form-{{label.id}}" data-label-id="{{label.id}}"></form>
                  <td class="label-list-first-cell" style="padding-left: {{paddingLeft}}px; " id="label-rename-table-cell-{{label.id}}">
                    {{#if hasSubLabels}}
                      <img 
                        src="img/{{#if expanded}}collapse{{else}}expand{{/if}}.svg" 
                        data-state="{{#if expanded}}expanded{{else}}collapsed{{/if}}" 
                        class="small-exp-col-icon" />
                    {{/if}}
                    &nbsp;
                    <label class="checkbox-wrapper">
                      <input type="checkbox" data-label-id="{{label.id}}" {{#if isAttachedToList}}checked="true"{{/if}}/>
                      <span>&nbsp;{{label.name}}</span>
                    </label>
                  </td>
                  <td>
                    <img class="small-menu-open-image" src="img/menu-small.svg" />
                    <div class="small-menu display-none">
                      <input type="submit" class="width-100" form="label-rename-form-{{label.id}}" id="label-rename-button-{{label.id}}" data-action="rename-edit" value="Rename" /><br>
                      <input type="button" class="label-add-sub-label width-100" value="Add sub-label"/><br>
                      <form class="label-remove-form inline">
                        <input type="hidden" class="label-remove-select" value="{{label.id}}"/>
                        <input class="label-remove-button width-100" type="submit" value="Remove" />
                      </form>
                    </div>
                  </td>
                </tr>
              </script>
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
                  <input id="share-list-submit" type="submit" value="Share" data-pending-value="Sharing"/>
                </form>
                <div id="list-sharings">

                </div>
              </div>

              <script id="word-lists-share-table-template" type="text/x-handlebars-template">
                {{#if share}}
                  <table class="box-table button-right-column">
                    <tr class="bold cursor-default">
                      <td>Name</td>
                      <td></td>
                      <td></td>
                    </tr>
                    {{#each share}}
                      <tr id="list-shared-with-row-{{id}}">
                        <td>{{user.firstname}} {{user.lastname}}</td>
                        <td>
                          {{#if permissions}}
                            Can edit
                          {{else}}
                            Can view
                          {{/if}}
                        </td>
                        <td><input type="button" class="inline" value="Stop sharing" data-pending-value="Stopping sharing" data-action="delete-sharing" data-sharing-id="{{id}}"/></td>
                      </tr>
                    {{/each}}
                {{else}}
                  <p class="spacer-top-15">The selected list isn&#39;t shared with anyone. Only you can see it.</p>
                {{/if}}
              </script>

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
                    <input id="words-add-language1" type="text" placeholder="First language"/>
                    <input id="words-add-language2" type="text" placeholder="Second language"/>
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

            <script id="word-lists-no-words-template" type="text/x-handlebars-template"><p class="spacer-top-15">The selected list doesn&#39;t contain any words yet.</p></script>
            <script id="word-lists-no-words-no-editing-permissions-template" type="text/x-handlebars-template"><p class="spacer-top-15">The selected list doesn&#39;t contain any words yet. You don&#39;t have permissions to add new words.</p></script>

            <script id="word-lists-words-table-template" type="text/x-handlebars-template">
              <table id="word-list-table" class="box-table{{#if allowEdit}} button-right-column{{/if}}">
                <tr class="bold cursor-default">
                  <td>{{lang1}}</td>
                  <td>{{lang2}}</td>
                  <td>Comment</td>
                 {{#if allowEdit}}<td></td>{{/if}}
                </tr>
                {{content}}
              </table>
            </script>

            <script id="word-lists-words-table-row-template" type="text/x-handlebars-template">
              <tr
                {{#if id}}
                  &nbsp;
                  id="word-row-{{id}}"
                {{/if}}
                {{#if pending}}
                  &nbsp;
                  class="pending" 
                {{/if}}>

                <td>{{lang1}}</td>
                <td>{{lang2}}</td>

                {{#if showComment}}
                  <td>{{comment}}</td>
                {{/if}}

                {{#if allowEdit}}
                  <td>
                    <input type="submit" class="icon pencil table-icon" value="" data-action="edit" form="word-row-{{id}}-form"/>
                    &nbsp;
                    <input type="button" value="" onclick="WordLists.removeWord({{id}})" class="icon rubbish table-icon"/>
                    <form id="word-row-{{id}}-form" onsubmit="WordLists.editOrSaveWordEvent(event, {{id}})"></form>
                  </td>
                {{/if}}
              </tr>
            </script>
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
                  <input id="user-add-button" type="submit" value="Add user" data-pending-value="Adding user"/>
                </form>
                <div id="people-you-have-added">
                </div>
              </div>
            </div>

            <!-- user people you've added templates -->
            <script id="user-none-added-template" type="text/x-handlebars-template">
              <p class="spacer-top-15">You haven&#39;t added other users yet.</p>
            </script>

            <script id="user-add-server-response-wrong-email-template" type="text/x-handlebars-template">Email-address does not exist.</script>
            <script id="user-add-server-response-success-template" type="text/x-handlebars-template">User has been added.</script>
            <script id="user-add-server-response-cant-add-yourself-template" type="text/x-handlebars-template">You can not add yourself.</script>
            <script id="user-add-server-response-unknown-error-template" type="text/x-handlebars-template">An unknown error occured.</script>

            <script id="user-list-of-added-users-template" type="text/x-handlebars-template">
              <table class="box-table button-right-column">
                <tr class="bold cursor-default">
                  <td>Name</td>
                  <td>Email-address</td>
                  <td></td>
                </tr>
                {{#each user}}
                  <tr id="added-users-row-{{id}}">
                    <td>{{firstname}} {{lastname}}</td>
                    <td>{{email}}</td>
                    <td>
                      <input id="added-users-remove-{{id}}" type="button" class="inline" value="Remove" data-pending-value="Removing" onclick="User.remove({{id}})"/>
                    </td>
                  </tr>
                {{/each}}
              </table>
            </script>
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

            <!-- user who have added you templates -->
            <script id="user-none-have-added-you-template" type="text/x-handlebars-template">
              <p class="spacer-top-15">No users have added you yet.</p>
            </script>

            <script id="user-list-of-users-who-have-added-you-template" type="text/x-handlebars-template">
              <table class="box-table button-right-column">
                <tr class="bold cursor-default">
                  <td>Name</td>
                  <td>Email-address</td>
                  <td></td>
                </tr>
                {{#each user}}
                  <tr>
                    <td>{{firstname}} {{lastname}}</td>
                    <td>{{email}}</td>
                    <td>
                      {{#unless bidirectional}}
                        <input type="button" class="inline" value="Add user" data-pending-value="Adding user" data-email="{{email}}"/>
                      {{/unless}}
                    </td>
                  </tr>
                {{/each}}
              </table>
            </script>
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
                <form id="settings-name">
                  <input type="text" required="true" value="<? echo $user->firstname; ?>" placeholder="First name" id="settings-firstname" />&nbsp;
                  <input type="text" required="true" value="<? echo $user->lastname; ?>" placeholder="Last name" id="settings-lastname" />&nbsp;
                  <input type="submit" value="Change name" data-pending-value="Changing name" id="settings-submit-button"/>&nbsp;
                </form>
              </div>
            </div>

            <script id="settings-name-server-invalid-template" type="text/x-handlebars-template">The given name is not valid.</script>
            <script id="settings-name-server-success-template" type="text/x-handlebars-template">Your name has been updated successfully.</script>
            <script id="settings-name-server-unknown-error-template" type="text/x-handlebars-template">An unknown error occured.</script>


            <div class="box" data-page="profile">
              <div class="box-head">
                Change password
              </div>
              <div class="box-body">
                <form id="settings-password">
                  <table class="width-auto">
                    <tr><td>Old password</td><td><input id="settings-password-old" required="true" type="password"/></td></tr>
                    <tr><td>New password</td><td><input id="settings-password-new" required="true" type="password"/></td></tr>
                    <tr><td>Confirm new password</td><td><input id="settings-password-new-confirm" required="true" type="password"/></td></tr>
                    <tr><td><input id="settings-password-button" type="submit" value="Change password" data-pending-value="Changing password" class="width-auto"/></td><td></td></tr>
                  </table>
                </form>
              </div>
            </div>

            <script id="settings-password-server-success-template" type="text/x-handlebars-template">Your password has been updated successfully.</script>
            <script id="settings-password-server-not-equal-template" type="text/x-handlebars-template">The two new passwords are not equal.</script>
            <script id="settings-password-server-wrong-old-template" type="text/x-handlebars-template">Your old password is not correct.</script>
            <script id="settings-password-server-invalid-template" type="text/x-handlebars-template">The new password is not valid.</script>
            <script id="settings-password-server-unknown-template" type="text/x-handlebars-template">An unknown error occured.</script>

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
                <p>
                  <label>
                    <input type="checkbox" disabled checked/>
                    &nbsp;
                    The NSA is allowed to spy me.
                    <span class="tooltip">
                      This is just a little joke.
                    </span>
                  </label>                
                </p>
              </div>
            </div>


            <div class="box" data-page="email-notifications">
              <div class="box-head">
                Email notifications
              </div>
              <div class="box-body">
                <label><input type="checkbox" id="enable-newsletter-checkbox" <? echo ($user_settings->newsletter_enabled ? 'checked' : ''); ?>/>&nbsp;Newsletter</label>
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
                <form id="settings-delete-account-form">
                  <input type="password" required="true" placeholder="Password" id="settings-delete-account-password" />&nbsp;
                  <input type="button" value="Delete account" data-pending-id="Deleting account" id="settings-delete-account-button" />
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
        <div class="box margin-0">
          <div class="box-head">
            Import words
            <img src="img/close.svg" class="box-head-right-icon" id="word-import-close-dialog" />
          </div>
          <div class="box-body">
            Coming soon...
            <!--<div class="word-import-left-col">
              <table>
                <tr>
                  <td>Separator between both languages</td>
                  <td>
                    <select id="word-import-separator-1-select">
                      <option value="tab">Tab</option>
                      <option value="custom">Custom</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" id="word-import-separator-1-text" class="display-none" />
                  </td>
                </tr>
                <tr>
                  <td>Separator between words</td>
                  <td>
                    <select id="word-import-separator-2-select">
                      <option value="return">Return</option>
                      <option value="custom">Custom</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" id="word-import-separator-2-text" class="display-none" />
                  </td>
                </tr>
              </table>
              <textarea id="word-import-input" placeholder="Paste your words here." class="margin-0"></textarea>
            </div>
            <div class="word-import-right-col">
               <input type="button" value="Preview" onclick="WordLists.Import.preview()" />&nbsp;
               <input type="button" value="Import" />
               <hr class="spacer-15">
               <table id="word-import-preview" class="box-table">

               </table>
            </div>-->
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript" src="database.js"></script>

    <script type="text/javascript">
      // PHP-defined global variables
      var adsEnabled = <? echo $user_settings->ads_enabled ? 'true' : 'false'; ?>;
      
      // word lists, words, answers
      var Database = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_query_data($_SESSION['id']))); ?>');

      // users who have added you
      Database.listOfUsersWhoHaveAddedYou = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_list_of_users_who_have_added_user($_SESSION['id']))); ?>');

      // users you have added
      Database.listOfAddedUsers = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_list_of_added_users_of_user($_SESSION['id']))); ?>');

      // feed data
      Database.feed = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_feed($_SESSION['id'], -1))); ?>');

      // recently used lists
      Database.recentlyUsed = JSON.parse('<? echo str_replace("'", "\\'", json_encode(Database::get_last_used_n_lists_of_user($_SESSION['id'], 8))); ?>');
      

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
      document.write('\x3Cscript src="handlebars-v4.0.4.js" type="text/javascript">\x3C/script>');
      document.write('\x3Cscript src="messagebox.js" type="text/javascript">\x3C/script>');

      document.write('\x3Cscript src="extensions.js" type="text/javascript">\x3C/script>');
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
