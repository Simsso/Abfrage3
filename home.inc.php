<?php
	require('database.class.php');
	require('mail.class.php');
	$user = Database::get_user_by_id($_SESSION['id']);
	$next_to_last_login = Database::get_next_to_last_login_of_user($_SESSION['id']);
?>

<!DOCTYPE html>
<html>
<? require('html-include/head.html'); ?>
    <body>
    	<nav id="head-nav" class="navbar">
    		<div class="navbar-inner content-width">
    			<a href="#home">
    				<img class="logo" src="img/logo-46.png" />
    			</a><br class="clear-both smaller-800">
    			<ul class="nav left">
    				<li id="nav_home" class="nav-img-li">
                        <a href="#home"><img src="img/home.png" class="nav-image" alt="Home" title="Home"/></a>
                    </li>
    				<li id="nav_query"><a href="#query">Query</a></li>
    				<li id="nav_word-lists"><a href="#word-lists">Word lists</a></li>
    				<li id="nav_user"><a href="#user">User</a></li>
    			</ul>
    			<ul class="nav right">
    				<li id="nav_settings" class="nav-img-li">
                        <a href="#settings"><img src="img/settings.png" class="nav-image" alt="Settings" title="Settings"/></a>
                    </li>
    				<li id="nav_logout" class="nav-img-li">
                        <a href="/./server.php?action=logout"><img src="img/logout.png" class="nav-image" alt="Logout" title="Logout"/></a>
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
            
    		<div id="content-home">
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
    						Recently used
    					</div>
    					<div class="box-body">
    						coming soon...
    					</div>
    				</div>
    				
    			</div>
    		</div>
    		
    		
    		<div id="content-query">
    			
    			<div class="left-column">
                    
                </div>
    			<div class="right-column">
                    
                </div>
    		</div>
    		
    		
    		<div id="content-word-lists">
    			<div class="left-column">
                    <div class="box" id="word-list-info">
                        <div class="box-head">
                            <img src="img/info.png" />
                            <div class="inline"></div>
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
                        </div>
                        <div class="box-body" data-start-state="expanded">
                        </div>
                    </div>
                    
                    <div class="box" id="word-list-sharing">
                        <div class="box-head">
                            <img src="img/share.png" />
                            Share
                            <img src="img/refresh.png" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListSharings" />
                            <img src="img/expand.png" class="box-head-right-icon" data-action="expand" />
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
                    
                    <div class="box" id="word-list-label">
                        <div class="box-head">
                            <img src="img/tags.png" />
                            Labels
                            <img src="img/refresh.png" class="box-head-right-icon" data-action="refresh" data-function-name="getLabelList" />
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
                        </div>
                        <div class="box-body" data-start-state="expanded">
                            <!--<div id="label-add">
                                
                                
                                
                                <hr class="spacer-top-15 spacer-bottom-15">
                            </div>-->
                            <div id="list-labels-list">
                            </div>
                        </div>
                    </div>
                    
                    <div class="box" id="word-list-info-words">
                        <div class="box-head">
                            <img src="img/grid.png" />
                            Words
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
                        </div>
                        <div class="box-body" data-start-state="expanded">
                            <div id="words-add">
                                <div id="words-add-message"></div>
                                <form id="words-add-form">
                                    <input id="words-add-language1" type="text" placeholder="Language 1" required="true"/>
                                    <input id="words-add-language2" type="text" placeholder="Language 2" required="true"/>
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
                            <img src="img/server.png" />
                            Your word lists
                            <img src="img/refresh.png" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfWordLists" />
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
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
                            <img src="img/share.png" />
                            Shared with you
                            <img src="img/refresh.png" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfSharedWordLists" />
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
                        </div>
                        <div class="box-body" data-start-state="expanded">
                            <div id="list-of-shared-word-lists">
                            </div>
                        </div>
                    </div>
                </div>
    		</div>
    		
    		
    		<div id="content-share">
    			<div class="left-column">
                    
                </div>
    			<div class="right-column">
                    
                </div>
    		</div>
    		
    		
    		<div id="content-user">
    			<div class="left-column width-50">
    				<div class="box">
    					<div class="box-head">
                            <img src="img/users.png" />
    						People you've added
                            <img src="img/refresh.png" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfAddedUsers" />
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
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
                            <img src="img/users.png" />
    						People who have added you
                            <img src="img/refresh.png" class="box-head-right-icon" data-action="refresh" data-function-name="refreshListOfUsersWhoHaveAddedYou" />
                            <img src="img/collapse.png" class="box-head-right-icon" data-action="collapse" />
                        </div>
                        <div class="box-body" data-start-state="expanded">
                            <div id="people-who-have-added-you">
                            </div>
    					</div>
    				</div>
    			</div>
    		</div>
    		
    		
    		<div id="content-settings">
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
        </div>
        
        <?php
        	//require('html-include/footer.html');
        ?>
        
        <script src="jquery-1.11.3.min.js" type="text/javascript"></script>
        <script src="extensions.js" type="text/javascript"></script>
        <script src="scripts.js" type="text/javascript"></script>
        
        <script src="home/query.js" type="text/javascript"></script>
        <script src="home/word-lists.js" type="text/javascript"></script>
        <script src="home/user.js" type="text/javascript"></script>
        <script src="home/settings.js" type="text/javascript"></script>
        
        <script  src="single-page-application.js" type="text/javascript"></script>
    </body>
</html>