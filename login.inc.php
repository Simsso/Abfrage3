<!DOCTYPE html>
<html>
    <head>
        <title>Abfrage3</title>
        
        <meta charset="utf-8">
        <meta name="author" content="Timo Denk" />
        <meta name="description" content="Abfrage3 is a online vocabulary trainer." />
        <meta name="keywords" content="Timo, Denk, Abfrage3" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <link rel="stylesheet" type="text/css" href="css/basic.css" media="all" />
        <link rel="stylesheet" type="text/css" href="css/other.css" media="all" />
        <link rel="icon" type="image/x-icon" href="img/favicon.ico" />
    </head>
    <body>
    	<nav id="head-nav" class="navbar">
    		<div class="navbar-inner content-width">
    			<a href="/./">
    				<img src="img/logo-56.png" />
    			</a>
    			<ul class="nav">
    				<li><a href="#">About</a></li>
    				<li><a href="#">Contact</a></li>
    				<li><a href="#">Imprint</a></li>
    			</ul>
    		</div>
    	</nav>
    	<div class="main content-width">
    	
    		<div class="left">
		    	<?php
		    	
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
						require('database.php');
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
		        	<div class="box-head">What is Abfrage3?</div>
		        	<div class="box-body">
		        		<p>Abfrage3 is a web tool allowing users to enter vocabulary, share word lists and learn another language.</p>
		        		<p>The website is still under heavy development and therefore not fully functional.</p>
		        	</div>
		        </div>
	        </div>
	        
	        <div class="right">
	        	<div class="box">
		        	<div class="box-head">Login</div>
		        	<div class="box-body">
		        		<form method="post" action="login.php">
		        			<table>
		        				<tr>
		        					<td>Email-Address</td>
		        					<td><input type="text" name="email" placeholder="" required="required" value="<? if(!$_GET['signup_success'] == "false") echo($_GET['email']); ?>"/></td>
		        				</tr>
		        				<tr>
		        					<td>Password</td>
		        					<td><input type="password" name="password" placeholder="" required="required"/></td>
		        				</tr>
		        				<tr>
		        					<td><input type="submit" value="Login"/></td>
		        					<td></td>
		        				</tr>
		        				<tr>
		        					<td colspan="2"><a href="#"><small>Forgot your password?</small></a></td>
		        				</tr>
		        			</table>
		        		</form>
					</div>
		        </div>
		        <div class="box right">
		        	<div class="box-head">Sign up</div>
		        	<div class="box-body">
		        		<form method="post" action="signup.php">
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
		        					<td>Email-Address</td>
		        					<td><input type="text" name="email" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['email']; ?>"/></td>
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
        
        
        <!-- jquery -->
        <script src="jquery-1.11.3.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            (function() {
                
            })();
        </script>
        
        <!--<script type="text/javascript" src="analytics.js"></script>-->
        
        <script type="text/javascript" src="cookieconsent-options.js"></script>
        <script type="text/javascript" src="//s3.amazonaws.com/cc.silktide.com/cookieconsent.latest.min.js"></script>
    </body>
