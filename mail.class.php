<?php
require_once('dbconnect.inc.php');
require_once('database.class.php');

class Mail {

  const DEFAULT_SENDER_EMAIL = "abfrage3@simsso.de";

  public $to;
  public $subject;
  public $body;
  public $header;

  protected $from;
  protected $reply_to;

  // Mail class constructor
  public function __construct($to, $from, $reply_to, $subject, $body) {
    $this->from = $from;
    $this->reply_to = $reply_to;

    // set to default value if no $from has been passed
    if (is_null($this->from)) {
      $this->from = self::DEFAULT_SENDER_EMAIL;
    }

    // set $reply_to to $from if no reply address has been passed
    if (is_null($this->reply_to)) {
      $this->reply_to = $this->from;
    }

    // set attributes
    $this->to = $to;
    $this->subject = $subject;
    $this->body = $body;

    // default header
    $this->update_header();
  }

  protected function update_header() {
    $this->header = "From: " . $this->from . "\r\nReply-To: " . $this->reply_to . "\r\n";
  }


  // send mail
  //
  // @return unsigned int: id of the insert in the "sent_mail" data base table
  function send() {
    mail($this->to, $this->subject, $this->body, $this->header);

    // log that an email has been sent
    global $con;
    $id = Database::email2id($this->to); // can be NULL
    $sql = "INSERT INTO `sent_email` (`user`, `email`, `ip`, `time`, `subject`) VALUES (".(($id === NULL) ? "NULL" : $id).", '".$this->to."', '".$_SERVER['REMOTE_ADDR']."', ".time().", '".$this->subject."');";
    $query = mysqli_query($con, $sql);
    echo Database::email2id($this->to);
    return mysqli_insert_id($con);
  }


  static function get_email_confirmation_mail($name, $email, $key) {
    $text = 'You have created an Abfrage3 account. Confirm your email address by clicking the following link:<br>
		<a href="http://abfrage3.simsso.de/?email=' . $email . '&hash=' . $key . '">http://abfrage3.simsso.de/?email=' . $email . '&hash=' . $key . '</a></p>
		<p>If you have not created an account simply ignore this email.';
    return new Default_Client_HTML_Mail($email, "Abfrage3 email confirmation", $name, $text);
  }


  // send newsletter
  //
  // @param string subject: subject of the newsletter
  // @param string text: content of the newsletterf
  //
  // @return int: number of sent emails
  static function send_newsletter($subject, $text) {
    global $con;
    $sql = "
      SELECT `user`.`id`, `user`.`email`, `user`.`hash`, `user`.`firstname`
      FROM `user`, `user_settings`
      WHERE `user`.`id` = `user_settings`.`user` AND
        `user`.`email_confirmed` = 1 AND `user`.`active` = 1 AND
        `user_settings`.`newsletter_enabled` = 1 AND `user`.`id` = 1;";

    $query = mysqli_query($con, $sql);
    $count = 0;
    while ($row = mysqli_fetch_assoc($query)) {
      $unsubscribe_link = 'http://abfrage3.simsso.de/?basic-page=&action=unsubscribe&medium=newsletter&id='.$row['id'].'&hash=' . $row['hash'];
      $mail = new Default_Client_HTML_Mail($row['email'], $subject, $row['firstname'], $text, $unsubscribe_link);
      $mail->send();
      $count++;
    }

    return $count;
  }
}

// HTML mail
class HTML_Mail extends Mail {
  public function __construct($to, $from, $reply_to, $subject, $body) {
    // call default constructor
    parent::__construct($to, $from, $reply_to, $subject, $body);

    // set different header with HTML information
    $this->update_header();
  }

  protected function update_header() {
    $this->header = "From: " . $this->from . "\r\nReply-To: " . $this->reply_to . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
  }
}

// default HTML mail for users
class Default_Client_HTML_Mail extends HTML_Mail {
  public function __construct($to, $subject, $name, $text, $unsubscribe = NULL) {
    $body ='
		<html>
		<body style="font-family: arial; background-color: #ECEFF1; margin: 0; padding: 0;">
		<div style="width: 100%;
		padding: 20px;
		background-color: #8892BF;
		border-style: solid;
		border-width: 0 0 6px 0;
		border-color: #4F5B93;">
		<div><a href="http://abfrage3.simsso.de"><img src="http://abfrage3.simsso.de/img/logo-46.png" alt="Abfrage3" style="margin: 0 30px;"></a></div>
		</div>
		<div style="padding: 20px 50px;">
		<h4>' . $subject . '</h4>
		<h3>Hey ' . $name . '!</h3>
		<p>' . $text . '</p>
		<p>Best regards, <br>Your Abfrage3-Team</p>
		<hr style="margin-top: 30px; background-color: #777777; height: 1px; border: 0; "/>
    ' . (($unsubscribe !== NULL) ? 
      '<p style="font-size: 80%; text-align: center"><a href="' . $unsubscribe . '" style="text-decoration: none; ">Unsubscribe</a></p>' : 
      ''
    ). '
		<p style="font-size: 80%; text-align: center">
    <a href="http://abfrage3.simsso.de" style="text-decoration: none; ">Website</a> &middot;
    <a href="http://abfrage3.simsso.de/#/about" style="text-decoration: none; ">About</a> &middot;
		<a href="http://abfrage3.simsso.de/#/contact" style="text-decoration: none; ">Contact</a> &middot;
    <a href="http://abfrage3.simsso.de/#/legal-info" style="text-decoration: none; ">Legal info</a>
		</p>
		</div>
		</body>
		</html>
		';
    parent::__construct($to, self::DEFAULT_SENDER_EMAIL, null, $subject, $body);
  }
}

?>
