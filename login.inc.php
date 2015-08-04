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
    				<img src="img/logo.png" />
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
		        <div class="box">
		        	<div class="box-head">What is Abfrage3?</div>
		        	<div class="box-body">Abfrage3 is a web tool allowing users to enter vocabulary, share word lists and learn another language.</div>
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
		        					<td><input type="text" name="email" placeholder="" required="required"/></td>
		        				</tr>
		        				<tr>
		        					<td>Password</td>
		        					<td><input type="password" name="password" placeholder="" required="required"/></td>
		        				</tr>
		        				<tr>
		        					<td><input type="submit" value="Login"/></td>
		        					<td></td>
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
		        					<td><input type="text" name="firstname" placeholder="" required="required"/></td>
		        				</tr>
		        				<tr>
		        					<td>Last name</td>
		        					<td><input type="text" name="lastname" placeholder="" required="required"/></td>
		        				</tr>
		        				<tr>
		        					<td>Email-Address</td>
		        					<td><input type="text" name="email" placeholder="" required="required"/></td>
		        				</tr>
		        				<tr>
		        					<td>Password</td>
		        					<td><input type="password" name="password" placeholder="" required="required"/></td>
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
        
        
        <!-- analytics -->
        <!--<script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
            
            ga('create', 'UA-37082212-1', 'auto');
            ga('send', 'pageview');
        </script>-->
        
        <!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
        <script type="text/javascript">
            window.cookieconsent_options = {
                "message": "This website uses cookies to ensure you get the best experience on our website. ",
                "dismiss": "Got it!",
                "learnMore": "Learn more.",
                "link": "https://en.wikipedia.org/wiki/HTTP_cookie",
                "theme": "dark-bottom"
            };
        </script>
        <script type="text/javascript" src="//s3.amazonaws.com/cc.silktide.com/cookieconsent.latest.min.js"></script>
        <!-- End Cookie Consent plugin -->
    </body>
