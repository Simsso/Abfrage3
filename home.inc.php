<?php
	require('database.php');
	require('mail.php');
	
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
    				<li id="nav_share"><a href="#share">Share</a></li>
    				<li id="nav_user"><a href="#user">User</a></li>
    			</ul>
    			<ul class="nav right">
    				<li id="nav_settings" class="nav-img-li">
                        <a href="#settings"><img src="img/settings.png" class="nav-image" alt="Settings" title="Settings"/></a>
                    </li>
    				<li id="nav_logout" class="nav-img-li">
                        <a href="/./logout.php"><img src="img/logout.png" class="nav-image" alt="Logout" title="Logout"/></a>
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
    						<p>Last login at <? echo $next_to_last_login->get_date_string(); ?> from IP-address <? echo $next_to_last_login->ip; ?></p>
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
                            
                        </div>
                        <div class="box-body">
                        </div>
                    </div>
                    <div class="box" id="word-list-info-words">
                        <div class="box-head">
                            Words
                        </div>
    					<div class="box-body">
                            <div id="words-add-message"></div>
                            <form id="words-add-form">
                                <input id="words-add-language1" type="text" placeholder="Language 1" required="true"/>
                                <input id="words-add-language2" type="text" placeholder="Language 2" required="true"/>
                                <input id="words-add-button" type="submit" value="Add word"/>
                            </form>
                            <hr class="spacer-top-15 spacer-bottom-5">
                            <div id="words-in-list">
                            </div>
    					</div>
                    </div>
                </div>
    			<div class="right-column">
                    <div class="box">
                        <div class="box-head">
                            Your word lists
                        </div>
                        <div class="box-body">
                            <form id="word-list-add-form">
                                <input id="word-list-add-name" type="text" placeholder="Word list name" required="true"/>
                                <input id="word-list-add-button" type="submit" value="Create list"/>
                            </form>
                            <hr class="spacer-top-15 spacer-bottom-5">
                            <div id="list-of-word-lists">
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
    						People you've added
    					</div>
    					<div class="box-body">
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
    						People who have added you
    					</div>
    					<div class="box-body" id="people-who-have-added-you">
                            
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
        	require('html-include/footer.html');
        ?>
        
        
        <script src="jquery-1.11.3.min.js" type="text/javascript"></script><!-- jquery -->
        
        <script type="text/javascript">
            var loading = '<div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>'            
        </script>
        
        <script src="home/word-lists.js" type="text/javascript"></script>
        <script type="text/javascript">
            var noWordListOutput = '<p class="spacer-top-15">You haven\'t created any wordlists yet.</p>';
            var noWordsInList = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
            var shownListId = -1;
            
            $('#word-list-add-form').on('submit', function(e) {
                // dont visit action="..." page
                e.preventDefault();
                
                $('#word-list-add-name').prop('disabled', true);
                $('#word-list-add-button').prop('disabled', true).attr('value', 'Creating list...');
                addWordList($('#word-list-add-name').val(), function(data) {
                    // finished callback
                    $('#word-list-add-name').prop('disabled', false).val('');
                    $('#word-list-add-button').prop('disabled', false).attr('value', 'Create list');
                    
                    refreshListOfWordLists(false);
                    loadWordList(data.id, true, function() { });
                });
            });
            
            function addWordList(name, callback) {
                jQuery.ajax('server.php', {
                    data: {
                        action: 'add-word-list',
                        name: name
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                }).done(function(data) {
                    console.log(data);
                    data = jQuery.parseJSON(data);
                    if (data.status == 1) {
                        loadWordList(data.id, true, function() { });
                    }

                    callback(data);
                });
            }
            
            function refreshListOfWordLists(showLoadingInformation) {
                if (showLoadingInformation)
                    $('#list-of-word-lists').html(loading);
                
                jQuery.ajax('server.php', {
                    data: {
                        action: 'list-of-word-lists'
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                }).done(function(data) {
                    console.log(data);
                    data = jQuery.parseJSON(data);

                    var output = "";
                    for (var i = 0; i < data.length; i++) {
                        output += '<tr id="list-of-word-lists-row-' + data[i].id + '"><td>' + data[i].name + '</td><td><input type="button" class="inline" value="Edit" data-action="edit" data-list-id="' + data[i].id + '"/> <input type="button" class="inline" value="Delete" data-action="delete" data-list-id="' + data[i].id + '"/></td></tr>';
                    }
                    if (output.length == 0) {
                        output = noWordListOutput;
                    }
                    else {
                        output = '<table class="box-table button-right-column"><tr class="bold"><td>Name</td><td></td></tr>' + output + '</table>';
                    }
                    $('#list-of-word-lists').html(output);
                    $('#list-of-word-lists input[type=button]').on('click', function() {
                        $button = $(this);
                        
                        if ($button.data('action') == 'delete') { // delete list button click
                            $button.prop('disabled', true).attr('value', 'Deleting...');
                            deleteWordList($button.data('list-id'), true, function() { });
                            
                            if ($button.data('list-id') == shownListId) {
                                showNoListSelectedInfo();
                            }
                        }
                        else if ($button.data('action') == 'edit') { // edit / show list button click
                            $('#list-of-word-lists input[type=button]').prop('disabled', false);
                            $button.prop('disabled', true);
                            loadWordList($button.data('list-id'), true, function() { });
                        }
                    });
                });
            }
            
            function showNoListSelectedInfo() {
                $('#word-list-info .box-head').html("Word lists");
                $('#word-list-info .box-body').html('<p class="spacer-30">Create or select a word list to start editing.</p>');
                $('#word-list-info-words').hide();
            }
                    
            function loadWordList(id, showLoadingInformation, callback) {
                if (showLoadingInformation) {
                    $('#word-list-info .box-head').html("Loading...");
                    $('#word-list-info .box-body').html(loading);
                    $('#word-list-info-words').hide();
                }
                
                jQuery.ajax('server.php', {
                    data: {
                        action: 'get-word-list',
                        word_list_id: id
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                }).done(function(data) {
                    console.log(data);
                    data = jQuery.parseJSON(data);
                    
                    shownListId = id;
                    
                        
                    $('#word-list-info .box-head').html("Word list: " + data.name);
                        
                    $('#word-list-info .box-body').html("Information coming soon...");
                    
                    if (data.words.length == 0) { // no words added yet
                        $('#words-in-list').html(noWordsInList);
                    }
                    else {
                        var wordListHTML = "";
                        for (var i = 0; i < data.words.length; i++) {
                            wordListHTML += getTableRowOfWord(data.words[i].id, data.words[i].language1, data.words[i].language2);
                        }
                        wordListHTML = getTableOfWordList(wordListHTML);
                        $('#words-in-list').html(wordListHTML);
                    }
                    $('#word-list-info-words').show();
                });
            }
            
            function getTableRowOfWord(id, lang1, lang2) {
                return '<tr><td>' + lang1 + '</td><td>' + lang2 + '</td><td><input type="button" class="inline" value="Edit"/> <input type="button" class="inline" value="Remove"/></td></tr>';
            }
            function getTableOfWordList(content) {
                return '<table id="word-list-table" class="box-table button-right-column"><tr class="bold"><td>First language</td><td>Second language</td><td></td></tr>' + content + '</table>';
            } 
            
            function deleteWordList(id) {
                jQuery.ajax('server.php', {
                    data: {
                        action: 'delete-word-list',
                        word_list_id: id
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                }).done(function(data) {
                    // set text to 
                    if ($('#list-of-word-lists tr').length == 1) {
                        $('#list-of-word-lists').html(noWordListOutput);
                    }
                    
                    $('#list-of-word-lists-row-' + id).remove();
                });
            }
            
            function saveListEdits() {
                if (shownListId == -1) 
                    return;
                
            }
            
            $('#words-add-form').on('submit', function(e) {
                e.preventDefault();
                
                var lang1 = $('#words-add-language1').val(), lang2 = $('#words-add-language2').val();
                $('#words-add-language1').val('').focus();
                $('#words-add-language2').val('');
                
                addWord(lang1, lang2);
            });
            
            function addWord(lang1, lang2) {
                jQuery.ajax('server.php', {
                    data: {
                        action: 'add-word',
                        word_list_id: shownListId,
                        lang1: lang1,
                        lang2: lang2
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                }).done(function(data) {
                    if ($('#word-list-table').length == 0) { // no words added
                        var wordListHTML = getTableOfWordList("");
                        $('#words-in-list').html(wordListHTML);
                    }
                    $('#word-list-table').append(getTableRowOfWord(data, lang1, lang2));
                });
            }
            
            // refresh functions
            showNoListSelectedInfo();
            refreshListOfWordLists(true);
        </script>
        <script src="home/user.js" type="text/javascript"></script>
        
        <script src="single-page-app.js" type="text/javascript"></script>
        
        
        <?php 
        	require('html-include/scripts.html');
		?>
    </body>
</html>
