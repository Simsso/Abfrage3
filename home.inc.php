<?php
require_once('mail.class.php'); // include email class
$user = Database::get_user_by_id($_SESSION['id']);
$user_settings = Database::get_user_settings($_SESSION['id']);
$next_to_last_login = Database::get_next_to_last_login_of_user($_SESSION['id']);
?>

<!DOCTYPE html>
<html>
  <? require('html-include/head.php'); ?>
  <body>
    <!-- navigation -->
    <nav id="head-nav" class="navbar">
      <div class="navbar-inner content-width">
        <a href="#/home">
          <img class="logo" src="<? echo $logo_path; ?>" />
        </a>
        <ul class="nav left">
          <a href="#/home">
            <li class="nav_home nav-img-li" data-text="<? echo $l['Home']; ?>">
              <img src="img/home.svg" class="nav-image" alt="<? echo $l['Home']; ?>" title="<? echo $l['Home']; ?>"/>
            </li>
          </a>
          <a href="#/query"><li class="nav_query" data-text="<? echo $l['Test']; ?>"><? echo $l['Test']; ?></li></a>
          <a class="link-to-show-current-word-list" href="#/word-lists"><li class="nav_word-lists" data-text="<? echo $l['Word_lists']; ?>"><? echo $l['Word_lists']; ?></li></a>
        </ul>
        <ul class="nav right">
          <a href="#/user">
            <li class="nav_user nav-img-li" data-text="<? echo $l['Users']; ?>">
              <img src="img/multiple-user.svg" class="nav-image" alt="<? echo $l['Users']; ?>" title="<? echo $l['Users']; ?>"/>
            </li>
          </a>
          <a href="#/settings">
            <li class="nav_settings nav-img-li" data-text="<? echo $l['Settings']; ?>">
              <img src="img/settings-white.svg" class="nav-image" alt="<? echo $l['Settings']; ?>" title="<? echo $l['Settings']; ?>"/>
            </li>
          </a>
          <a href="server.php?action=logout">
            <li class="nav_logout nav-img-li" data-text="<? echo $l['Logout']; ?>">
              <img src="img/logout.svg" class="nav-image" alt="<? echo $l['Logout']; ?>" title="<? echo $l['Logout']; ?>"/>
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
                <? echo $l['Hey']; ?> <? echo $user->firstname; ?>!
              </div>
            </div>
            
            <div class="box">
              <div class="box-head">
                <img src="img/feed.svg" />
                <? echo $l['Feed']; ?>
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshFeed" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="feed"></div>
                <div class="text-align-center spacer-top-15">
                  <input type="button" value="<? echo $l['Load_all']; ?>" data-pending-value="<? echo $l['Loading_all']; ?>" id="feed-load-all" />
                </div>
              </div>
            </div>


            <!-- feed templates -->

            <script id="feed-table-template" type="text/x-handlebars-template">
              <table class="feed-table box-table no-flex">{{tableBody}}</table>
            </script>

            <script id="feed-no-content-template" type="text/x-handlebars-template">
              <p><? echo $l['Nothing_new_since_last_login_']; ?></p>
            </script>

            <script id="feed-user-added-element-template" type="text/x-handlebars-template">
              <tr>
                <td>
                  <img src="img/users.svg">
                </td>
                <td>
                  <? echo $l['T_has_added_you__']; ?>
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
                  <? echo $l['T_gave_you_permissions_to__']; ?>
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
                  <? echo $l['T_user_has_added_words__']; ?>
                  &nbsp;<span class="feed-time">{{feedItem.timeString}}</span>
                </td>
              </tr>
            </script>

          </div>

          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/history.svg"/>
                <? echo $l['Recently_used']; ?>
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshRecentlyUsed" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded" id="recently-used">
              </div>
            </div>

            <script id="recently-used-no-content-template" type="text/x-handlebars-template">
              <p><? echo $l['No_recently_used_lists_found_']; ?></p>
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
                <? echo $l['Test']; ?>
                <img src="img/fullscreen.svg" class="box-head-right-icon" data-action="fullscreen" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="query-div">
                  <div id="query-not-started-info">
                    <? echo $l['To_start_a_test__']; ?>
                  </div>
                   
                  <table class="width-100 display-none" id="query-content-table">
                    <tr>
                      <td><span class="language" id="query-lang1"><? echo $l['First_language']; ?></span>:&nbsp;</td>
                      <td class="width-100" id="query-question">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><span class="language" id="query-lang2"><? echo $l['Second_language']; ?></span>:&nbsp;</td>
                      <td id="query-answer-table-cell-text-box">
                        <input type="text" id="query-answer" class="unremarkable width-100" data-last-cursor-position="0" spellcheck="false"/>
                      </td>
                      <td id="query-answer-table-cell-buttons" class="display-none">
                        <table class="width-100">
                          <tr>
                            <td class="width-33"><input id="query-answer-known" type="button" value="<? echo $l['I_know_']; ?>" class="height-50px width-100"/></td>
                            <td class="width-33"><input id="query-answer-not-sure" type="button" value="<? echo $l['Not_sure_']; ?>" class="height-50px width-100"/></td>
                            <td class="width-33"><input id="query-answer-not-known" type="button" value="<? echo $l['No_idea_']; ?>" class="height-50px width-100"/></td>
                          </tr>
                        </table>
                        <div id="query-answer-buttons"></div>
                      </td>
                    </tr>
                    <tr>
                      <td><? echo $l['Comment']; ?>:&nbsp;</td>
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
                          <?php include('html-include/special-chars.php'); ?>
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
                <? echo $l['Select_labels_and_word_lists']; ?>
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshQueryLabelList" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="query-selection"></div>
              </div>

              <script id="query-selection-template" type="text/x-handlebars-template">
                <p>
                  <input id="query-start-button" type="button" data-value-start="<? echo $l['Start_test']; ?>" data-value-stop="<? echo $l['Stop_test']; ?>" class="width-100 height-50px font-size-20px" disabled="true"/>
                </p>
                <div id="query-label-selection"></div>
                <div id="query-list-selection"></div>
                <br class="clear-both">
              </script>

              <script id="query-list-selection-table-template" type="text/x-handlebars-template">
                {{#if content}}
                  <table class="box-table cursor-pointer no-flex">
                    <tr class="cursor-default">
                      <th colspan="2"><? echo $l['Lists']; ?></th>
                    </tr>
                    {{content}}
                  </table>
                {{else}}
                  <p><? echo $l['You_havent_created_any_word_lists_yet_']; ?></p>
                {{/if}}
              </script>

              <script id="query-list-selection-row-template" type="text/x-handlebars-template">
                <tr {{#if selected}}class="active" {{/if}} data-query-list-id="{{list.id}}" data-checked="false">
                  <td>{{list.name}}</td>
                  <td>{{list.words.length}}</td>
                </tr>
              </script>


              <script id="query-label-selection-table-template" type="text/x-handlebars-template">
                {{#if content}}
                  <table class="box-table cursor-pointer">
                    <tr class="cursor-default">
                      <th><? echo $l['Labels']; ?></th>
                    </tr>
                    {{content}}
                  </table>
                {{else}}
                  <p><? echo $l['You_dont_have_any_labels__']; ?></p>
                {{/if}}
              </script>

              <script id="query-label-selection-row-template" type="text/x-handlebars-template">
                <tr data-checked="false" data-query-label-id="{{label.id}}" data-indenting="{{indenting}}"{{#if indenting}} style="display: none; "{{/if}}>
                  <td class="label-list-first-cell" style="padding-left: {{indentingPxl}}px; ">
                    {{#if subLabelsCount}}
                      <img src="img/{{#if expanded}}collapse{{else}}expand{{/if}}.svg" data-state="{{#if expanded}}expanded{{else}}collapsed{{/if}}" class="small-exp-col-icon" />
                    {{/if}}
                    &nbsp;{{label.name}}
                  </td>
                </tr>
              </script>
            </div>


            <div class="box" id="query-advanced-settings-box">
              <div class="box-head">
                <img src="img/settings.svg" />
                <? echo $l['Advanced_settings']; ?>
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div>
                  <h4><? echo $l['Case_sensitivity']; ?></h4>
                  <p>
                    <label>
                      <input type="checkbox" id="query-case-sensitivity" checked="true"/>
                      &nbsp;
                      <? echo $l['Case_sensitive_test']; ?>

                      <span class="tooltip"><? echo $l['Accept_eg__']; ?></span>
                    </label>
                  </p>

                  <h4><? echo $l['Test_answer_upload']; ?></h4>
                  <p><label><input type="checkbox" id="query-results-auto-upload" checked/>&nbsp;<? echo $l['Auto_upload']; ?></label>&nbsp;<input type="button" value="<? echo $l['Upload_answers']; ?>" id="query-results-upload-button" disabled="true"/></p>
                  <p id="query-results-upload-counter">Uploaded 0/0 test answers.</p>
                  <p><? echo $l['You_can_upload_answers_to_make__']; ?></p>
                </div>
              </div>

              <script id="query-results-upload-button-template" type="x-handlebars-template"><? echo $l['T_Upload_n_answers__']; ?></script>

              <script id="query-results-upload-counter-template" type="x-handlebars-template"><? echo $l['T_Uploaded_n_of_m_answers__']; ?></script>
            </div>
          </div>


          <div class="right-column">
            <div class="box">
              <div class="box-head">
                <img src="img/algorithm.svg" />
                <? echo $l['Test_algorithm']; ?>
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <table class="box-table cursor-pointer" id="query-algorithm">
                  <tr data-algorithm="0">
                    <td>
                      <? echo $l['Random']; ?>
                      <span class="tooltip">
                        <? echo $l['The_random_algorithm__']; ?>
                      </span>
                  </td>
                  </tr>
                  <tr data-algorithm="1">
                    <td>
                      <? echo $l['Words_below_average']; ?>
                      <span class="tooltip">
                        <? echo $l['The_words_below_average_algorithm__']; ?>
                      </span>
                  </td>
                  </tr>
                  <tr data-algorithm="3">
                    <td>
                      <? echo $l['In_order']; ?>
                      <span class="tooltip">
                        <? echo $l['The_in_order_algorithm__']; ?>
                      </span>
                  </td>
                  </tr>
                  <tr data-algorithm="2" class="active">
                    <td>
                      <? echo $l['Group_words']; ?>
                      <span class="tooltip">
                        <? echo $l['The_group_words_algorithm__']; ?>
                      </span>
                  </td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/settings.svg" />
                <? echo $l['Test_settings']; ?>
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <table class="box-table cursor-pointer" id="query-type">
                  <tr class="active" data-type="0"><td><? echo $l['Text_box']; ?></td></tr>
                  <tr data-type="1"><td><? echo $l['Buttons']; ?></td></tr>
                </table>
              </div>
            </div>
            <div class="box">
              <div class="box-head">
                <img src="img/swap.svg" />
                <? echo $l['Test_direction']; ?>
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <table class="box-table cursor-pointer" id="query-direction">
                  <tr data-direction="0"><td><span data-value="first-language-information"><? echo $l['First_language']; ?></span> <? echo $l['to']; ?> <span data-value="second-language-information"><? echo $l['Second_language']; ?></span></td></tr>
                  <tr data-direction="1"><td><span data-value="second-language-information"><? echo $l['Second_language']; ?></span> <? echo $l['to']; ?> <span data-value="first-language-information"><? echo $l['First_language']; ?></span></td></tr>
                  <tr class="active" data-direction="-1"><td><? echo $l['Both_directions']; ?></td></tr>
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
              <? echo $l['Word_lists']; ?>
              <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfWordLists" />
              <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
            </div>
            <div class="box-body" data-start-state="expanded">
              <form id="word-list-add-form">
                <input id="word-list-add-name" type="text" placeholder="<? echo $l['Word_list_name']; ?>" required="true"/>
                <input id="word-list-add-button" type="submit" value="<? echo $l['Create_list']; ?>" data-pending-value="<? echo $l['Creating_list']; ?>"/>
              </form>
              <div id="list-of-word-lists">
              </div>
            </div>

            <script id="word-lists-no-list-template" type="text/x-handlebars-template"><p class="spacer-top-15"><? echo $l['You_havent_created_any_word_lists_yet_']; ?></p></script>

            <script id="word-lists-list-of-word-lists-template" type="text/x-handlebars-template">
              <table class="box-table cursor-pointer">
                <tr class="cursor-default">
                  <th><? echo $l['Name']; ?></th>
                  <th class="hide-mobile"><? echo $l['Content']; ?></th>
                  <th class="hide-mobile"><? echo $l['Entries']; ?></th>
                  <th class="hide-mobile"><? echo $l['Creator']; ?></th>
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

            <script id="word-lists-folder-view-template" type="text/x-handlebars-template">
              <table class="box-table cursor-pointer word-lists-folder-view-table">
                {{#each label}}
                  <tr>
                    <td><img src="img/tags.svg" /></td>
                    <td colspan="4"><a href="#/word-lists/{{../subPageName}}/{{id}}">{{name}}</a></td>
                  </tr>
                {{/each}}

                {{#each list}}
                  <tr>
                    <td></td>
                    <td><a href="#/word-lists/{{id}}">{{name}}</a></td>
                    <td class="hide-mobile">{{language1}} - {{language2}}</td>
                    <td class="hide-mobile">{{words.length}}</td>
                    <td class="hide-mobile">{{creator.firstname}} {{creator.lastname}}</td>
                  </tr>
                {{/each}}
            </script>
          </div>


          <div id="word-list-loading" class="box">
            <div id="word-list-loading-head" class="box-head">
              <? echo $l['Loading_word_list']; ?>
            </div>
            <div class="box-body">
              <div class="sk-three-bounce">
                <div class="sk-child sk-bounce1"></div>
                <div class="sk-child sk-bounce2"></div>
                <div class="sk-child sk-bounce3"></div>
              </div>

              <a href="/#/word-lists"><? echo $l['Show_all_word_lists_']; ?></a>
            </div>
          </div>

          

          <div id="word-lists-left-column" class="left-column-small">
            <div class="box" id="word-list-title">
              <div class="box-head active"> 
                <a href="#/word-lists"><img src="img/menu-back.svg" id="word-list-menu-back"/></a>
                <div id="word-list-title-name"></div>
              </div>
            </div>

            <!-- General -->
            <div class="box" id="word-list-info">
              <div class="box-head display-none">
                <img src="img/info.svg" />
                <div class="inline">General</div>
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
              </div>

              <script id="word-lists-single-list-general-information" type="x-handlebars-template">
                {{! owner string}}
                {{#if allowSharing}}
                  <p><? echo $l['You_own_this_list_']; ?></p>
                {{else}}
                  <p>{{list.creator.firstname}} {{list.creator.lastname}} <? echo $l['shares_this_list_with_you_']; ?></p>
                {{/if}}

                {{! permissions string }}
                {{#unless allowSharing}}
                  <p><? echo $l['T_You_have_permissions_to__']; ?></p>
                {{/unless}}

                {{! start test string}}
                <p>
                  <a href="#/query" onclick="Query.startTestWithList({{list.id}}, true)">
                    <? echo $l['Start_test_with_this_list_']; ?>
                  </a>
                </p>

                {{! creation time string}}
                <p><? echo $l['Created']; ?>: {{creationTime}}</p>

                {{! rename list string}}
                {{#if allowSharing}}
                  <form id="rename-list-form">
                    <input type="text" id="rename-list-name" required="true" placeholder="<? echo $l['List_name']; ?>" value="{{list.name}}"/>&nbsp;<input type="submit" value="<? echo $l['Rename']; ?>" data-pending-value="<? echo $l['Renaming']; ?>" id="rename-list-button"/>
                  </form>
                  <p></p>
                {{/if}}

                {{! edit languages string}}
                {{#if allowEdit}}
                  <form id="change-language-form">
                    <input id="word-list-language1" required="true" type="text" placeholder="<? echo $l['First_language']; ?>" value="{{list.language1}}" class="width-60px" />&nbsp;<input id="word-list-language2" required="true" type="text" placeholder="<? echo $l['Second_language']; ?>" value="{{list.language2}}" class="width-60px" />&nbsp;<input type="submit" id="word-list-languages-button" value="<? echo $l['Edit_languages']; ?>" data-pending-value="<? echo $l['Editing_languages']; ?>" />
                  </form>
                {{/if}}

                <hr class="spacer-15">

                {{! import words string }}
                {{#if allowEdit}}
                  <input type="button" value="<? echo $l['Import_words']; ?>..." onclick="WordLists.Import.showDialog()" />
                  <hr class="spacer-15">
                {{/if}}


                {{! delete string}}
                {{#if allowSharing}}
                  <input type="button" value="<? echo $l['Delete_list']; ?>" data-pending-value="<? echo $l['Deleting_list']; ?>" id="delete-shown-word-list"/>
                {{else}}
                  <input type="button" value="<? echo $l['Hide_list']; ?>" data-pending-value="<? echo $l['Hiding_list']; ?>" id="hide-shown-word-list"/>
                {{/if}}
              </script>
            </div>

            <div class="box" id="word-list-label" style="z-index: 105; ">
              <div class="box-head">
                <img src="img/tags.svg" />
                <? echo $l['Labels']; ?>
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="getLabelList" />
                <img src="img/expand.svg" class="box-head-right-icon" data-action="expand" />
              </div>
              <div class="box-body" data-start-state="collapsed">
                <div id="list-labels-list">
                </div>
              </div>

              <script id="word-lists-no-labels-template" type="text/x-handlebars-template"><p><? echo $l['You_dont_have_any_labels__']; ?></p></script>

              <script id="word-lists-label-table-template" type="text/x-handlebars-template">
                <table class="box-table button-right-column no-flex">{{content}}</table
              </script>

              <script id="word-lists-label-list-template" type="text/x-handlebars-template">
                <tr
                {{#unless show}} style="display: none; "{{/unless}} class="cursor-default">
                  <td colspan="2" style="padding-left: {{indentingPxl}}px; text-align: left; ">
                    <form class="label-add-form inline">
                      <input type="hidden" class="label-add-parent" value="{{id}}"/>
                      <input class="label-add-name inline" style="margin-left: -8px; " type="text" placeholder="<? echo $l['Label_name']; ?>" required="true"/>&nbsp;
                      <input class="label-add-button inline" type="submit" value="<? echo $l['Add_label']; ?>" data-pending-value="<? echo $l['Adding_label']; ?>"/>
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
                      <input type="submit" class="width-100" form="label-rename-form-{{label.id}}" id="label-rename-button-{{label.id}}" data-action="rename-edit" value="<? echo $l['Rename']; ?>" /><br>
                      <input type="button" class="label-add-sub-label width-100" value="<? echo $l['Add_sub_label']; ?>"/><br>
                      <form class="label-remove-form inline">
                        <input type="hidden" class="label-remove-select" value="{{label.id}}"/>
                        <input class="label-remove-button width-100" type="submit" value="<? echo $l['Remove']; ?>" />
                      </form>
                    </div>
                  </td>
                </tr>
              </script>


              <script id="word-lists-label-rename-input-template" type="text/x-handlebars-template">
                &nbsp;
                <input type="text" form="label-rename-form-{{labelId}}" class="inline" value="{{labelName}}" required="true"/>
              </script>
            </div>


            <div class="box" id="word-list-sharing">
              <div class="box-head">
                <img src="img/share.svg" />
                <? echo $l['Share']; ?>
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListSharings" />
                <img src="img/expand.svg" class="box-head-right-icon" data-action="expand" />
              </div>
              <div class="box-body" data-start-state="collapsed">
                <form id="share-list-form">
                  <input id="share-list-other-user-email" type="text" placeholder="Email-address" required="true"/>
                  <select id="share-list-permissions" required="true">
                    <option value="2"><? echo $l['Can_view']; ?></option>
                    <option value="1"><? echo $l['Can_edit']; ?></option>
                  </select>
                  <input id="share-list-submit" type="submit" value="<? echo $l['Share']; ?>" data-pending-value="<? echo $l['Sharing']; ?>"/>
                </form>
                <div id="list-sharings">

                </div>
              </div>

              <script id="word-lists-share-table-template" type="text/x-handlebars-template">
                {{#if share}}
                  <table class="box-table button-right-column">
                    <tr class="bold cursor-default">
                      <td><? echo $l['Name']; ?></td>
                      <td></td>
                      <td></td>
                    </tr>
                    {{#each share}}
                      <tr id="list-shared-with-row-{{id}}">
                        <td>{{user.firstname}} {{user.lastname}}</td>
                        <td>
                          {{#if permissions}}
                            <? echo $l['Can_edit']; ?>
                          {{else}}
                            <? echo $l['Can_view']; ?>
                          {{/if}}
                        </td>
                        <td><input type="button" class="inline" value="<? echo $l['Stop_sharing']; ?>" data-pending-value="<? echo $l['Stopping_sharing']; ?>" data-action="delete-sharing" data-sharing-id="{{id}}"/></td>
                      </tr>
                    {{/each}}
                {{else}}
                  <p class="spacer-top-15"><? echo $l['The_selected_list_isnt_shared__']; ?></p>
                {{/if}}
              </script>

            </div>


          </div>

          <div class="right-column-big">
            <div class="box" id="word-list-info-words">
              <div class="box-head">
                <img src="img/grid.svg" />
                <? echo $l['Words']; ?> (<span id="shown-word-list-words-count">0</span>)
                <img src="img/fullscreen.svg" class="box-head-right-icon" data-action="fullscreen" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="words-add">
                  <div id="words-add-message"></div>
                  <form id="words-add-form">
                    <input id="words-add-language1" type="text" placeholder="<? echo $l['First_language']; ?>" spellcheck="false"/>
                    <input id="words-add-language2" type="text" placeholder="<? echo $l['Second_language']; ?>" spellcheck="false"/>
                    <input id="words-add-comment" type="text" placeholder="<? echo $l['Comment']; ?>" spellcheck="false"/>
                    <input id="words-add-button" type="submit" value="<? echo $l['Add_word']; ?>"/>
                    <input type="button" value="&#35805;" class="show-special-chars" id="word-lists-show-special-chars" />
                  </form>
                  <div id="word-lists-special-chars" class="special-chars display-none box">
                    <?php include('html-include/special-chars.php'); ?>
                  </div>
                </div>
                <div id="words-in-list">
                </div>
              </div>

              <script id="word-list-edit-word-input-template" type="text/x-handlebars-template">
                <input type="text" class="inline-both" form="word-row-{{id}}-form" id="word-edit-input-{{name}}-{{id}}" value="{{value}}" />
              </script>
            </div>

            <script id="word-lists-no-words-template" type="text/x-handlebars-template"><p class="spacer-top-15"><? echo $l['The_selected_list_doesnt_contain__']; ?></p></script>
            <script id="word-lists-no-words-no-editing-permissions-template" type="text/x-handlebars-template"><p class="spacer-top-15"><? echo $l['The_selected_list_doesnt_contain__']; ?> <? echo $l['You_dont_have_permissions_to_add_new_words_']; ?></p></script>

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
                <? echo $l['People_youve_added']; ?>
                <img src="img/refresh.svg" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfAddedUsers" />
                <img src="img/collapse.svg" class="box-head-right-icon" data-action="collapse" />
              </div>
              <div class="box-body" data-start-state="expanded">
                <div id="user-add-message"></div>
                <form id="user-add-form">
                  <input id="user-add-email" type="email" placeholder="<? echo $l['Email_address']; ?>" required="true"/>
                  <input id="user-add-button" type="submit" value="<? echo $l['Add_user']; ?>" data-pending-value="<? echo $l['Adding_user']; ?>"/>
                </form>
                <div id="people-you-have-added">
                </div>
              </div>
            </div>

            <!-- user people you've added templates -->
            <script id="user-none-added-template" type="text/x-handlebars-template">
              <p class="spacer-top-15"><? echo $l['You_havent_added_other_users_yet_']; ?></p>
            </script>

            <script id="user-add-server-response-wrong-email-template" type="text/x-handlebars-template"><? echo $l['Email_address_does_not_exist_']; ?></script>
            <script id="user-add-server-response-success-template" type="text/x-handlebars-template"><? echo $l['User_has_been_added']; ?></script>
            <script id="user-add-server-response-cant-add-yourself-template" type="text/x-handlebars-template"><? echo $l['You_can_not_add_yourself']; ?></script>
            <script id="user-add-server-response-unknown-error-template" type="text/x-handlebars-template"><? echo $l['An_unknown_error_occured']; ?></script>

            <script id="user-list-of-added-users-template" type="text/x-handlebars-template">
              <table class="box-table button-right-column">
                <tr class="bold cursor-default">
                  <td><? echo $l['Name']; ?></td>
                  <td><? echo $l['Email_address']; ?></td>
                  <td></td>
                </tr>
                {{#each user}}
                  <tr id="added-users-row-{{id}}">
                    <td>{{firstname}} {{lastname}}</td>
                    <td>{{email}}</td>
                    <td>
                      <input id="added-users-remove-{{id}}" type="button" class="inline" value="<? echo $l['Remove']; ?>" data-pending-value="<? echo $l['Removing']; ?>" onclick="User.remove({{id}})"/>
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
                <? echo $l['People_who_have_added_you']; ?>
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
              <p class="spacer-top-15"><? echo $l['No_users_have_added_you_yet']; ?></p>
            </script>

            <script id="user-list-of-users-who-have-added-you-template" type="text/x-handlebars-template">
              <table class="box-table button-right-column">
                <tr class="bold cursor-default">
                  <td><? echo $l['Name']; ?></td>
                  <td><? echo $l['Email_address']; ?></td>
                  <td></td>
                </tr>
                {{#each user}}
                  <tr>
                    <td>{{firstname}} {{lastname}}</td>
                    <td>{{email}}</td>
                    <td>
                      {{#unless bidirectional}}
                        <input type="button" class="inline" value="<? echo $l['Add_user']; ?>" data-pending-value="<? echo $l['Adding_user']; ?>" data-email="{{email}}"/>
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
                <? echo $l['Settings']; ?>
              </div>
              <div class="box-body">
                <table class="box-table cursor-pointer" id="settings-menu">
                  <tr data-page="profile"><td><? echo $l['Profile']; ?></td></tr>
                  <tr data-page="email-notifications"><td><? echo $l['Email_notifications']; ?></td></tr>
                  <tr data-page="account"><td><? echo $l['Account']; ?></td></tr>
                </table>
              </div>
            </div>
          </div>
          <div class="right-column width-70" id="settings-content">
            <div class="box" data-page="profile">
              <div class="box-head">
                <? echo $l['Change_name']; ?>
              </div>
              <div class="box-body">
                <form id="settings-name">
                  <input type="text" required="true" value="<? echo $user->firstname; ?>" placeholder="<? echo $l['First_name']; ?>" id="settings-firstname" />&nbsp;
                  <input type="text" required="true" value="<? echo $user->lastname; ?>" placeholder="<? echo $l['Last_name']; ?>" id="settings-lastname" />&nbsp;
                  <input type="submit" value="<? echo $l['Change_name']; ?>" data-pending-value="<? echo $l['Changing_name']; ?>" id="settings-submit-button"/>&nbsp;
                </form>
              </div>
            </div>

            <script id="settings-name-server-invalid-template" type="text/x-handlebars-template"><? echo $l['The_given_name_is_not_valid_']; ?></script>
            <script id="settings-name-server-success-template" type="text/x-handlebars-template"><? echo $l['Your_name_has_been_updated_successfully_']; ?></script>
            <script id="settings-name-server-unknown-error-template" type="text/x-handlebars-template"><? echo $l['An_unknown_error_occured_']; ?></script>


            <div class="box" data-page="profile">
              <div class="box-head">
                <? echo $l['Change_password']; ?>
              </div>
              <div class="box-body">
                <form id="settings-password">
                  <table class="width-auto">
                    <tr><td><? echo $l['Old_password']; ?></td><td><input id="settings-password-old" required="true" type="password"/></td></tr>
                    <tr><td><? echo $l['New_password']; ?></td><td><input id="settings-password-new" required="true" type="password"/></td></tr>
                    <tr><td><? echo $l['Confirm_new_password']; ?></td><td><input id="settings-password-new-confirm" required="true" type="password"/></td></tr>
                    <tr><td><input id="settings-password-button" type="submit" value="<? echo $l['Change_password']; ?>" data-pending-value="<? echo $l['Changing_password']; ?>" class="width-auto"/></td><td></td></tr>
                  </table>
                </form>
              </div>
            </div>

            <script id="settings-password-server-success-template" type="text/x-handlebars-template"><? echo $l['Your_password_has_been_updated_successfully_']; ?></script>
            <script id="settings-password-server-not-equal-template" type="text/x-handlebars-template"><? echo $l['The_two_new_passwords_are_not_equal_']; ?></script>
            <script id="settings-password-server-wrong-old-template" type="text/x-handlebars-template"><? echo $l['Your_old_password_is_not_correct_']; ?></script>
            <script id="settings-password-server-invalid-template" type="text/x-handlebars-template"><? echo $l['The_new_password_is_not_valid_']; ?></script>
            <script id="settings-password-server-unknown-template" type="text/x-handlebars-template"><? echo $l['An_unknown_error_occured_']; ?></script>

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
                <? echo $l['Secret_service_clause']; ?>
              </div>
              <div class="box-body">
                <p>
                  <label>
                    <input type="checkbox" disabled checked/>
                    &nbsp;
                    <? echo $l['The_NSA_is_allowed_to_spy_me_']; ?>
                    <span class="tooltip">
                      <? echo $l['This_is_just_a_little_joke_']; ?>
                    </span>
                  </label>                
                </p>
              </div>
            </div>


            <div class="box" data-page="email-notifications">
              <div class="box-head">
                <? echo $l['Email_notifications']; ?>
              </div>
              <div class="box-body">
                <label><input type="checkbox" id="enable-newsletter-checkbox" <? echo ($user_settings->newsletter_enabled ? 'checked' : ''); ?>/>&nbsp;<? echo $l['Newsletter']; ?></label>
              </div>
            </div>

            <div class="box" data-page="account">
              <div class="box-head"><? echo $l['Advertisement']; ?></div>
              <div class="box-body">
                <label><input type="checkbox" id="enable-ads-checkbox" <? echo ($user_settings->ads_enabled ? 'checked' : ''); ?>/>&nbsp;<? echo $l['Load_and_show_advertisement']; ?></label>
              </div>
            </div>

            <div class="box" data-page="account">
              <div class="box-head">
                <? echo $l['Delete_account']; ?>
              </div>
              <div class="box-body">
                <p><? echo $l['Be_careful_cant_be_undone_lists_still_visible__']; ?></p>
                <form id="settings-delete-account-form">
                  <input type="password" required="true" placeholder="<? echo $l['Password']; ?>" id="settings-delete-account-password" />&nbsp;
                  <input type="button" value="<? echo $l['Delete_account']; ?>" data-pending-id="<? echo $l['Deleting_account']; ?>" id="settings-delete-account-button" />
                </form>
              </div>
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
        <?php
          include('html-include/advertisement.php');
        ?>
      </div>

      <?php
        include('html-include/footer.php');
      ?>
      <div id="scroll-top-button">
        <img src="img/menu-back.svg"/>
    </div>

    <div id="background-black-overlay" class="display-none"></div>

    <div id="word-import-box" class="display-none">
      <div class="word-import-box-inner-wrapper">
        <div class="box margin-0">
          <div class="box-head">
            <? echo $l['Import_words']; ?>
            <img src="img/close.svg" class="box-head-right-icon" id="word-import-close-dialog" />
          </div>
          <div class="box-body">
            <? echo $l['Coming_soon']; ?>...
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
      var start = new Date().getTime();
      // PHP-defined global variables
      var adsEnabled = <? echo $user_settings->ads_enabled ? 'true' : 'false'; ?>;

      var constString = JSON.parse('<? echo str_replace("'", "\\'", str_replace('"', '\\"', json_encode($l))); ?>');
      
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
          }
        };

        Database.getLabelById = function(id) {
          for (var i = Database.labels.length - 1; i >= 0; i--) {
            if (Database.labels[i].id === id) {
              return Database.labels[i];
            }
          };
        };

        // link ids in recently used array to respective objects
        for (var i = Database.recentlyUsed.length - 1; i >= 0; i--) {
          Database.recentlyUsed[i] = Database.getListById(Database.recentlyUsed[i]);
        }
      })();

      var end = new Date().getTime();
      var time = end - start;
      console.info('Execution time for client-side data base init: ' + time + ' ms');
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
