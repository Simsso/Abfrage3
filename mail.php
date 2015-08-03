class Mail {
    const public DEFAULT_SENDER_EMAIL = "abfrage3@simsso.de";

    public $to;
    public $sender;
    public $subject;
    public $body;
    public $header;

    public function __construct($to, $from, $reply_to, $sender, $subject, $body) {
        this->$to = $to;
        this->$sender = $sender;
        this->$subject = $subject;
        this->$body = $body;

        this->$header = "From: " . $from . "\r\nReply-To: " . $reply_to . "\r\n";
    }
    

    public function send() {
        mail($to, $subject, $body, $header);
    }
}

class HTML_Mail extends Mail {
    public function __construct($to, $from, $reply_to, $sender, $subject, $body) {
        parent::__construct($to, $from, $reply_to, $sender, $subject, $body);
        this->$header = "From: " . $from . "\r\nReply-To: " . $reply_to . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
    }
}