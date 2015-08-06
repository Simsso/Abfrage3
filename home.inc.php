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
                    
                </div>
    			<div class="right-column">
                    
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
        
        
        <!-- jquery -->
        <script src="jquery-1.11.3.min.js" type="text/javascript"></script>
        <script src="single-page-app.js" type="text/javascript"></script>
        <script type="text/javascript">
            var loading = '<div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>'
            
            
            // add user
            var noUsersAddedOutput = '<p>You have not added other users yet.</p>';
            var noUsersHaveAddedYouOutput = '<p>No users have added you yet.</p>';
            function addUser(email, callback) {
                jQuery.ajax('server.php', {
                    data: {
                        action: 'add-user',
                        email: email
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {
                        
                    }
                }).done(function(data) {
                    if (data == 1) {
                        refreshListOfAddedUsers(false);
                    }
                    
                    callback(data);
                });
            };
            
            $('#user-add-form').on('submit', function(e) {
				// dont visit action="..." page
				e.preventDefault();
                
                $('#user-add-email').prop('disabled', true);
                $('#user-add-button').prop('disabled', true).attr('value', 'Adding...');
                addUser($('#user-add-email').val(), function(data) {
                    $('#user-add-email').prop('disabled', false).val('');
                    $('#user-add-button').prop('disabled', false).attr('value', 'Add user');
                    var responseString; 
                    if (data == -1) responseString = "Email-address does not exist.";
                    else if (data == 0) responseString =  "You have already added this user.";
                    else if (data == 1) responseString =  "User has been added.";
                    else if (data == 2) responseString =  "You can not add yourself.";
                    else responseString = "An unknown error occured.";

                    $('#user-add-message').html(responseString);
                });
            });
            
            function removeUser(id) {
                $('#added-users-remove-' + id).prop('disabled', true).attr('value', 'Removing...');
                jQuery.ajax('server.php', {
                    data: {
                        action: 'remove-user',
                        id: id
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {
                        
                    }
                }).done(function(data) {
                    console.log(data);
                    $('#added-users-row-' + id).hide();
                    
                    // set text to 
                    if ($('#people-you-have-added tr').length == 1) {
                        $('#people-you-have-added').html(noUsersAddedOutput);
                    }
                    
                    refreshListOfUsersWhoHaveAddedYou(false);
                });
            }
            
            
            // list of added users
            function refreshListOfAddedUsers(hideLoadingInformation) {
                if (hideLoadingInformation)
                    $('#people-you-have-added').html(loading);
                
                jQuery.ajax('server.php', {
                    data: {
                        action: 'list-of-added-users'
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {
                        
                    }
                }).done(function(data) {
                    console.log(data);
                    data = jQuery.parseJSON(data);
                    console.log(data);
                    var output = "";
                    for (var i = 0; i < data.length; i++) {
                        output += '<tr id="added-users-row-' + data[i].id + '"><td>' + data[i].firstname + ' ' + data[i].lastname + '</td><td>' + data[i].email + '</td><td><input id="added-users-remove-' + data[i].id + '" type="button" class="inline" value="Remove" onclick="removeUser(' + data[i].id + ')"/></td></tr>';
                    }
                    if (output.length == 0) {
                        output = noUsersAddedOutput;
                    }
                    else {
                        output = '<table class="box-table"><tr class="bold"><td>Name</td><td>Email-Address</td><td></td></tr>' + output + '</table>';
                    }
                    console.log(output);
                    $('#people-you-have-added').html(output);
                });
            }
            
            function refreshListOfUsersWhoHaveAddedYou(showLoadingInformation) {
                if (showLoadingInformation)
                    $('#people-who-have-added-you').html(loading);
                
                jQuery.ajax('server.php', {
                    data: {
                        action: 'list-of-users-who-have-added-you'
                    },
                    type: 'GET',
                    error: function(jqXHR, textStatus, errorThrown) {
                        
                    }
                }).done(function(data) {
                    console.log(data);
                    data = jQuery.parseJSON(data);
                    var output = "";
                    for (var i = 0; i < data.length; i++) {
                        output += '<tr><td>' + data[i].firstname + ' ' + data[i].lastname + '</td><td>' + data[i].email + '</td><td>' + ((data[i].bidirectional)?'':'<input type="button" class="inline" value="Add user" data-email="' + data[i].email + '"/>') + '</td></tr>';
                    }
                    if (output.length == 0) {
                        output = noUsersHaveAddedYouOutput;
                    }
                    else {
                        output = '<table class="box-table"><tr class="bold"><td>Name</td><td>Email-Address</td><td></td></tr>' + output + '</table>';
                    }
                    console.log(output);
                    $('#people-who-have-added-you').html(output);
                    
                    $('#people-who-have-added-you input[type=button]').on('click', function() {
                        $button = $(this);
                        $button.prop('disabled', true).val('Adding...');
                        addUser($button.data('email'), function() {
                            $button.fadeOut();
                        });
                    });
                });
            }
            
            
            
            
            // load methods
            refreshListOfAddedUsers(true);
            refreshListOfUsersWhoHaveAddedYou(true);
            
        </script>
        
        <?php 
        	require('html-include/scripts.html');
		?>
    </body>
</html>
