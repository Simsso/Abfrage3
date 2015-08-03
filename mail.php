class Mail {
    const public DEFAULT_SENDER_EMAIL = "abfrage3@simsso.de";

    public $to;
    public $subject;
    public $body;
    public $header;

    public function __construct($to, $from, $reply_to, $subject, $body) {
    	// set to default value if no $from has been passed
    	if (is_null($from)) {
    		$from = self::DEFAULT_SENDER_EMAIL;
    	}
    	
    	// set $reply_to to $from if no reply address has been passed
    	if (is_null($reply_to)) {
    		$reply_to = $from;
    	}
    	
    	// set attributes
        this->$to = $to;
        this->$subject = $subject;
        this->$body = $body;

		// default header
        this->$header = "From: " . $from . "\r\nReply-To: " . $reply_to . "\r\n";
    }
    

    public function send() {
        mail($to, $subject, $body, $header);
    }
}

class HTML_Mail extends Mail {
    public function __construct($to, $from, $reply_to, $subject, $body) {
    	// call default constructor
        parent::__construct($to, $from, $reply_to, $subject, $body);
        
        // set different header with HTML information
        this->$header = "From: " . $from . "\r\nReply-To: " . $reply_to . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
    }
}

class Default_Client_HTML_Mail {
	public function __construct($to, $)
}
