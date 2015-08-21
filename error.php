<?php
$status = $_SERVER['REDIRECT_STATUS'];
$codes = array(
  400 => array('400 Bad Request', 'The server cannot or will not process the request due to something that is perceived to be a client error.'),
  401 => array('401 Unauthorized', 'An authentication is required and has failed or has not yet been provided.'),
  403 => array('403 Forbidden', 'The server has refused to fulfill your request.'),
  404 => array('404 Not Found', 'The document/file requested was not found on this server.'),
  405 => array('405 Method Not Allowed', 'The method specified in the Request-Line is not allowed for the specified resource.'),
  408 => array('408 Request Timeout', 'Your browser failed to send a request in the time allowed by the server.'),
  500 => array('500 Internal Server Error', 'The request was unsuccessful due to an unexpected condition encountered by the server.'),
  502 => array('502 Bad Gateway', 'The server received an invalid response from the upstream server while trying to fulfill the request.'),
  504 => array('504 Gateway Timeout', 'The upstream server failed to send a request in the time allowed by the server.'),
);
$error_code = $codes[$status][0];
$error_message = $codes[$status][1];
?>

<!DOCTYPE html>
<html>
  <head>
    <title><? echo $error_code; ?> - Abfrage3</title>

    <meta charset="utf-8">
    <meta name="author" content="Timo Denk" />
    <meta name="description" content="Abfrage3 is a online vocabulary trainer." />
    <meta name="keywords" content="Timo, Denk, Abfrage3" />
    <!-- disallow scaling to prevent auto-zoom when focusing input fields on mobile devices -->
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, user-scalable=0"/>

    <link rel="stylesheet" type="text/css" href="/css/basic.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/css/other.css" media="all" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico" />

    <style type="text/css">
      body > div {
        height: 90%;
        width: 100%;
        padding-top: 10%;
      }

      body > div > div {
        margin-left: calc(50% - 150px);
        width: 300px;
      }

      #error-code {
        font-weight: bold;
      }

      #error-message {
        font-style: italic;
      }
    </style>
  </head>
  <body>
    <div>
      <div>
        <p><a href="/"><img src="/img/logo-56.png"/></a></p>
        <p><span id="error-code"><? echo $error_code; ?>.&nbsp;</span>That's an error.</p>
        <p id="error-message"><? echo $error_message; ?></p>
      </div>
    </div>
  </body>
</html>