<!DOCTYPE html>
<html>
<? require('html-include/head.html'); ?>
<body>
	<div id="main-wrapper">

		<!-- navigation -->
		<nav id="head-nav" class="navbar">
			<div class="navbar-inner content-width">
				<a href="#home">
					<img class="logo" src="img/logo-46.png" alt="Abfrage3" />
				</a><br class="clear-both smaller-800">
				<ul class="nav left">
					<li class="nav_home nav-img-li" data-text="Home">
						<a href="#home"><img src="img/home.svg" class="nav-image" alt="Home" title="Home"/></a>
					</li>
				</ul>

				<ul class="nav right">
					<li class="nav_about" data-text="About"><a href="#about">About</a></li>
					<li class="nav_contact" data-text="Contact"><a href="#contact">Contact</a></li>
					<li class="nav_legal-info" data-text="Legal info"><a href="#legal-info">Legal info</a></li>
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

					<div class="box">
						<div class="box-head">Stats</div>
						<div class="box-body">
							<p>Number of registered users: <? echo Database::get_number_of_registered_users(); ?></p>
							<p>Number of logins during the last 24 hours: <? echo Database::get_number_of_logins_during_last_time(24 * 60 * 60); ?></p>
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
                                        <td colspan="2"><label><input type="checkbox" name="stay-logged-in" value="1" class="initial-width initial-height" checked/>&nbsp;Stay logged in</label></td>  
                                    </tr>
									<tr>
										<td><input type="submit" value="Login"/></td>
										<td></td>
									</tr>
									<tr>
										<td colspan="2" style="padding-top: 5px; "><a href="#"><small>Forgot your password?</small></a></td>
									</tr>
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


			<!-- Legal info -->
			<div id="content-legal-info">
				<div class="box">
					<div class="box-head">Terms and conditions template for website usage</div>
					<div class="box-body">
						<p>Welcome to my website. If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use, which together with my privacy policy govern Timo Denk's relationship with you in relation to this website. If you disagree with any part of these terms and conditions, please do not use my website.</p>
						<p>The term 'Timo Denk' or 'me' or 'I' refers to the owner of the website. The term 'you' refers to the user or viewer of our website.</p>
						<p>The use of this website is subject to the following terms of use:</p>
						<ul class="normal">
							<li>The content of the pages of this website is for your general information and use only. It is subject to change without notice.</li>
							<li>This website uses cookies to monitor browsing preferences.</li>
							<li>Neither I nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose. You acknowledge that such information and materials may contain inaccuracies or errors and I expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.</li>
							<li>Your use of any information or materials on this website is entirely at your own risk, for which I shall not be liable. It shall be your own responsibility to ensure that any products, services or information available through this website meet your specific requirements.</li>
							<li>This website contains material which is owned by or licensed to me. This material includes, but is not limited to, the design, layout, look, appearance and graphics. Reproduction is prohibited other than in accordance with the copyright notice, which forms part of these terms and conditions.</li>
							<li>All trade marks reproduced in this website which are not the property of, or licensed to, the operator are acknowledged on the website.</li>
							<li>Unauthorised use of this website may give rise to a claim for damages and/or be a criminal offence.</li>
							<li>From time to time this website may also include links to other websites. These links are provided for your convenience to provide further information. They do not signify that I endorse the website(s). I have no responsibility for the content of the linked website(s).</li>
							<li>Your use of this website and any dispute arising out of such use of the website is subject to the laws of Germans.</li>
						</ul>
					</div>
				</div>

				<div class="box">
					<div class="box-head">Security</div>
					<div class="box-body">
						<p>Entering your data on my website is at your own risk. I can't guarantee for the security of the entered information. Your account's password is not being stored in plain text. Before it is being safed to the database the <a href="http://php.net/manual/de/function.sha1.php" target="_blank">SHA1-hash</a> is applied to it together with a randomly generated salt.</p>
					</div>
				</div>

				<div class="box">
					<div class="box-head">Disclaimer</div>
					<div class="box-body">
						<p>The information contained in this website is for general information purposes only. The information is provided by Timo Denk and while I endeavour to keep the information up to date and correct, I make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability or availability with respect to the website or the information, products, services, or related graphics contained on the website for any purpose. Any reliance you place on such information is therefore strictly at your own risk.</p>
						<p>In no event will I be liable for any loss or damage including without limitation, indirect or consequential loss or damage, or any loss or damage whatsoever arising from loss of data or profits arising out of, or in connection with, the use of this website.</p>
						<p>Through this website you are able to link to other websites which are not under the control of Timo Denk. I have no control over the nature, content and availability of those sites. The inclusion of any links does not necessarily imply a recommendation or endorse the views expressed within them.</p>
						<p>Every effort is made to keep the website up and running smoothly. However, Timo Denk takes no responsibility for, and will not be liable for, the website being temporarily unavailable due to technical issues beyond our control.</p>
					</div>
				</div>

				<div class="box">
					<div class="box-head">Copyright</div>
					<div class="box-body">
						<p>This website and its content is copyright of Timo Denk. All rights reserved.</p>
						<p>Any redistribution or reproduction of part or all of the contents in any form is prohibited other than the following:</p>
						<ul class="normal">
							<li>You may print or download to a local hard disk extracts for your personal and non-commercial use only.</li>
							<li>You may copy the content to individual third parties for their personal use, but only if you acknowledge the website as the source of the material.</li>
							<li>You may not, except with our express written permission, distribute or commercially exploit the content. Nor may you transmit it or store it in any other website or other form of electronic retrieval system.</li>
						</ul>
					</div>
				</div>

				<div class="box">
					<div class="box-head">Icon source</div>
					<div class="box-body">
						<div>
							Icons made by <a href="http://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a>, <a href="http://www.flaticon.com/authors/dave-gandy" title="Dave Gandy">Dave Gandy</a> and <a href="http://www.flaticon.com/authors/google" title="Google">Google</a> from <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0">CC BY 3.0</a>
						</div>
					</div>
				</div>
			</div>


			<!-- About -->
			<div id="content-about">
				<div class="left-column">
					<div class="box">
						<div class="box-head">About Abfrage3</div>
						<div class="box-body">
							<h4>Earlier versions</h4>
							<p>Abfrage3 has a long history: The first version, "Abfrage", was written in Java and looked very awful (Screenshot 1). It was developed in October 2013. Abfrage2 was not really successful though, it was just an attempt to create a nice WPF layout which totally failed. In Summer 2014 the third version Abfrage3 (Screenshot 2), a C# WPF application, became the first version in this series of the vocabulary learning software with advanced features like uploading and sharing lists, auto translation and a lot more. Unfortunately it was only executable on Windows which lead in August 2015 to the development of Abfrage3Web - the website you are currently visiting. </p>
							<h4>Naming</h4>
							<p>The name Abfrage3 is German and means something like <i>Test3</i>. For traditional reasons, the English version was also named in German.</p>
							<h4>Ambition</h4>
							<p>The big idea of Abfrage3Web was to create a fully functional vocabulary trainer which is fun to use. An important specification was the possibility to comfortably share word lists with other users. </p>
						</div>
					</div>
					<div class="box">
						<div class="box-head">Source code</div>
						<div class="box-body">
							<p>Abfrage3 is open source! The whole code can be found on GitHub.</p>
							<p><a href="https://github.com/Simsso/Abfrage3Web" target="_blank"><img src="img/github.png" alt="GitHub" /></a></p>
						</div>
					</div>
				</div>
				<div class="right-column">
					<div class="box">
						<div class="box-body">
							<div class="right-column-img-wrapper"><img src="img/abfrage-screenshot.min.jpg" class="screenshot right-column-img" data-description="The first version - Abfrage" alt="Abfrage screenshot"/></div>
						</div>
					</div>
					<div class="box">
						<div class="box-body">
							<div class="right-column-img-wrapper"><img src="img/abfrage3-screenshot.min.jpg" class="screenshot right-column-img" data-description="Abfrage3 - a C# WPF application" alt="Abfrage3 screenshot"/></div>
						</div>
					</div>
				</div>
			</div>


			<!-- Contact -->
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
										<td><input type="submit" value="Send" id="contact-submit"/></td>
										<td></td>
									</tr>
								</table>
							</form>
						</div>
					</div>

					<div class="box">
						<div class="box-head">Social</div>
						<div class="box-body">
							<ul class="social-icons">
								<li>
									<a href="https://plus.google.com/106647445778912368795/posts" target="_blank">
										<img src="img/google+.png" alt="Google+" class="width-100px">
									</a>
								</li>
								<li>
									<a href="http://www.youtube.com/user/Simssos" target="_blank">
										<img src="img/youtube.png" alt="YouTube" class="width-100px">
									</a>
								</li>
								<li>
									<a href="https://github.com/Simsso" target="_blank">
										<img src="img/github.png" alt="GitHub" class="width-100px">
									</a>
								</li>
							</ul>
							<br class="clear-both">
						</div>
					</div>
				</div>

				<div class="right-column">
					<div class="box">
						<div class="box-head">About me</div>
						<div class="box-body">
							<div class="right-column-img-wrapper">
								<img src="img/timo-denk.jpg" class="right-column-img" alt="Timo Denk"/>
							</div>
							<p>My name is Timo Denk, I am 18 years old and a German student. I am currently studying at Technisches Gymnasium in Waiblingen, Germany. I will get my abitur in 2016.</p>
							<p>In my leisure time I like to develop software and realize hardware projects. I also do a lot of sports.</p>
						</div>
					</div>
				</div>
			</div>

			<br class="clear-both hide-below-700">
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
	<script src="login/contact.js" type="text/javascript"></script>

	<!-- single page application script -->
	<script src="single-page-application.js" type="text/javascript"></script>
</body>
</html>
