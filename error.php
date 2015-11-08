<?php
// error page

// language file
include('lang/lang.inc.php')

// read error status code
$status = $_SERVER['REDIRECT_STATUS'];

// array which stores the description for some error codes in an array with two elements (short description, long description)
$codes = array(
  400 => array('400 Bad Request', $l['Error_message_400__']),
  401 => array('401 Unauthorized', $l['Error_message_401__']),
  403 => array('403 Forbidden', $l['Error_message_403__']),
  404 => array('404 Not Found', $l['Error_message_404__']),
  405 => array('405 Method Not Allowed', $l['Error_message_405__']),
  408 => array('408 Request Timeout', $l['Error_message_408__']),
  500 => array('500 Internal Server Error', $l['Error_message_500__']),
  502 => array('502 Bad Gateway', $l['Error_message_502__']),
  504 => array('504 Gateway Timeout', $l['Error_message_504__']),
);

// read the data from the array to use it later in HTML
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
      html, body {
        background-color: #FFFFFF;
      }
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

      .center-logo {
        height: 58px;
      }
    </style>
  </head>
  <body>
    <div>
      <div>
        <p><a href="/"><img class="center-logo" src="/img/logo-white.svg"/></a></p>
        <p><span id="error-code"><? echo $error_code; ?>.&nbsp;</span>That's an error.</p>
        <p id="error-message"><? echo $error_message; ?></p>
      </div>
    </div>
  </body>
</html>