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
    return mysqli_insert_id($con);
  }


  static function get_email_confirmation_mail($name, $email, $key) {
    $text = 'You have created an Abfrage3 account. Confirm your email address by clicking the following link:<br>
		<a href="https://abfrage3.timodenk.com/?email=' . $email . '&hash=' . $key . '">https://abfrage3.timodenk.com/?email=' . $email . '&hash=' . $key . '</a></p>
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
      $unsubscribe_link = 'https://abfrage3.timodenk.com/?basic-page=&action=unsubscribe&medium=newsletter&id='.$row['id'].'&hash=' . $row['hash'];
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
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body style="font-family: Helvetica, Arial, sans-serif; outline: none; word-wrap: break-word; margin: 0; padding: 0; ">
        <div style="height: 56px; width: 100%; padding: 0; background-color: #8892BF; border-style: solid; border-width: 0; border-color: #4F5B93; box-shadow: 0 1px 8px rgba(0,0,0,.3); ">
            <div style="padding: 0 25px; ">
                <a href="/">
                    <img style="height: 40px; margin: 8px 30px 5px 0; " src="https://abfrage3.timodenk.com/img/logo-40.png" alt="Abfrage3">
                </a>
            </div>
        </div>
        <div style="position: relative; margin: 25px 25px 0; text-align: left; color: #263238; ">
            <div style="line-height: 18px; font-weight: bold; padding: 15px 0; background-color: #FFFFFF; border-bottom: 1px solid #ECEFF1; ">' . $subject . '</div>
            <div style="padding: 15px 0; background-color: #FFFFFF; ">
                <p style="margin-bottom: 10px; ">Hey ' . $name . '!</p>
                <p style="margin-bottom: 10px; ">' . str_replace('\n', '<br>', $text) . '</p><br>
                <p>Best regards, <br><br>Your Abfrage3-Team</p>
            </div></div>


        <div style="position: relative; margin: 25px 25px 0; text-align: left; color: #263238; ">
            <div style="padding: 15px 0; background-color: #FFFFFF; ">
                ' . (($unsubscribe !== NULL) ? 
                '<p style="font-size: 80%; text-align: center; margin-bottom: 10px; "><a href="' . $unsubscribe . '" style="text-decoration: none; ">Unsubscribe</a></p>' : 
                ''
                ). '
                <p style="font-size: 80%; text-align: center">
                    <a href="https://abfrage3.timodenk.com" style="text-decoration: none; ">Website</a> &middot;
                    <a href="https://abfrage3.timodenk.com/#/about" style="text-decoration: none; ">About</a> &middot;
                    <a href="https://abfrage3.timodenk.com/#/tour" style="text-decoration: none; ">Tour</a> &middot;
                    <a href="https://abfrage3.timodenk.com/#/contact" style="text-decoration: none; ">Contact</a> &middot;
                    <a href="https://abfrage3.timodenk.com/#/legal-info" style="text-decoration: none; ">Legal info</a>
                </p>
            </div>
        </div>
    </body>
</html>
		';
    parent::__construct($to, self::DEFAULT_SENDER_EMAIL, null, $subject, $body);
  }
}

?>
