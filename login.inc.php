<!DOCTYPE html>
<html>
<? require('html-include/head.html'); ?>
    <body>
    	<nav id="head-nav" class="navbar" id="nav">
    		<div class="navbar-inner content-width">
    			<a href="#home">
    				<img class="logo" src="img/logo-46.png" />
    			</a>
    			<ul class="nav left">
    				<li id="nav_home"><a href="#home">Home</a></li>
    			</ul>
    				
    			<ul class="nav right">
    				<li id="nav_about"><a href="#about">About</a></li>
    				<li id="nav_contact"><a href="#contact">Contact</a></li>
    				<li id="nav_imprint"><a href="#imprint">Imprint</a></li>
    			</ul>
    		</div>
    	</nav>
    	<div class="main content-width" id="main">
    		<div id="content-home">
				<div class="left-column">
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
		        
		        <div class="right-column">
		        	<div class="box">
			        	<div class="box-head">Login</div>
			        	<div class="box-body">
			        		<form method="post" action="login.php">
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
			        					<td>Email-address</td>
			        					<td><input type="email" name="email" placeholder="" required="required" value="<? if($_GET['signup_success'] == "false") echo $_GET['email']; ?>"/></td>
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
	        
	        
    		<div id="content-imprint">
    			<div class="box">
    				<div class="box-head">Imprint</div>
    				<div class="box-body">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like). 
Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.
The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from "de Finibus Bonorum et Malorum" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.
There are many variatios of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even
    				</div>
    			</div>
    		</div>
	        
	        
    		<div id="content-about">
    			<div class="box">
    				<div class="box-head">About</div>
    				<div class="box-body">coming soon...
    				</div>
    			</div>
    		</div>
	        
	        
    		<div id="content-contact">
				<div class="left-column">
					<div class="box">
	    				<div class="box-head">Contact</div>
	    				<div class="box-body" id="contact-body">
	    					<p>Feel free to send me your ideas, feedback, questions and critique!</p>
	    					<form id="contact-form">
		    					<table>
		    						<tr>
		    							<td>Name</td>
		    							<td><input type="text" id="contact-name" required="required"/></td>
		    						</tr>
		    						<tr>
		    							<td>Email-address</td>
		    							<td><input type="email" id="contact-email" required="required"/></td>
		    						</tr>
		    						<tr>
		    							<td>Subject</td>
		    							<td><input type="text" id="contact-subject" required="required"/></td>
		    						</tr>
		    						<tr>
		    							<td>Message</td>
		    							<td><textarea id="contact-message" required="required"></textarea></td>
		    						</tr>
		    						<tr>
		    							<td>Bot protection</td>
		    							<td><span id="contact-bot-question"><? echo rand(0, 10) . " + " . rand(0, 1) . "</span> = "; ?><input type="number" id="contact-bot-protection" style="width: 100px; " required="required"/></td>
		    						</tr>
		    						<tr>
		    							<td><input type="submit" value="Senden" id="contact-submit"/></td>
		    							<td></td>
		    						</tr>
		    					</table>
	    					</form>
	    				</div>
	    			</div>
				</div>
				
				<div class="right-column">
					<div class="box">
						<div class="box-head">About me</div>
						<div class="box-body">
							<p>
								<img src="img/timo-denk.jpg"/>
								My name is Timo Denk, I am 18 years old and a German student. I am currently studying at Technisches Gymnasium in Waiblingen, Germany. I will get my degree in 2016.
							</p>
						</div>
					</div>
				</div>
    		</div>
        </div>
        
        
        <!-- jquery -->
        <script src="jquery-1.11.3.min.js" type="text/javascript"></script>
        <script type="text/javascript">
        	
        	var updatePageContent = function() {
        		$('#main').children().hide();
        		$('li').removeClass('visited');
        		var pageName = location.hash.slice(1);
        		$('#nav_' + ((pageName.length == 0)?"home":pageName)).addClass('visited');
        		switch(pageName) {
        			case "imprint":
        				$('#content-imprint').show();
        				break;
        			case "about":
        				$('#content-about').show();
        				break;
        			case "contact":
        				$('#content-contact').show();
        				break;
        			default:
        				$('#content-home').show();
        				break;
        		}
        	}
        	
            $(window).on('hashchange',function() {
            	updatePageContent();
            }); 
            
			updatePageContent(); 
			
			
			// contact
			$('#contact-form').on('submit', function(e) {
				// dont visit action="..." page
				e.preventDefault();
				
				var botQuestion = $('#contact-bot-question').html().split(' + ');
				if (parseInt(botQuestion[0]) + parseInt(botQuestion[1]) != $('#contact-bot-protection').val())
				{
					alert("You haven't answered the bot question correctly.");
					return;
				}
				
				// prevent multiple submissions
				$('#contact-submit').prop('disabled', true);
				$('#contact-submit').attr('value', 'Sending...');
				
				$.post('contact.php', { 
					name: $('#contact-name').val(), 
					email: $('#contact-email').val(),
					subject: $('#contact-subject').val(),
					message: $('#contact-message').val()
				}).done(function(data) { $('#contact-body').html(data); });
			});       
        </script>
        
        <?php 
        	require('html-include/scripts.html');
		?>
    </body>
</html>
