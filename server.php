<?php
$start_time = microtime(true); // measure execution time

// session required
function session_required() {
  // if the session has expired set the cookie again
  Database::refresh_session_if_staying_logged_in();
  // cancels script execution when the session cookie is not set
  // returns an "no session" error
  if (!isset($_SESSION['id'])) {
    Response::send("no session", "error");
  }
}

// response class
class Response {
  // default status is "success"
  static function send($data, $status = "success") {
    global $start_time; // measure execution time variable
    
    // response object
    $obj = new stdClass();
    $obj->status = $status; // status: "success" or "error"
    $obj->data = $data; // data: the actual data
    $obj->action = $_GET['action']; // the action which has been done (like "get-word-list")
    $obj->execution_time_ms = (microtime(true) - $start_time) * 1000; // measured execution time (debugging purposes)
    
    // JSON encode response object
    // echo it by passing it to the exit() function
    // stop script execution by calling exit()
    exit(json_encode($obj)); 
  }
}


session_start(); // start session
require('database.class.php'); // include database class



if (isset($_GET['action'])) { // check whether the user request type was passed
  
  // log server requests
  if (isset($_SESSION['id'])) {
    Database::log_server_request($_SESSION['id'], $_GET['action']);
  }
  
  switch($_GET['action']) {
    // outer functions (contact, login, logout, signup)

    // session valid
    case 'session-valid':
    session_required();
    Response::send(1);
    break;
    
    // contact
    case 'contact':
    require('mail.class.php');

    $name = Validation::format_text($_POST['name']);
    $mail = Validation::format_text($_POST['email']);
    $subject = Validation::format_text($_POST['subject']);
    $message = Validation::format_text($_POST['message']);

    $mail = new Mail(Mail::DEFAULT_SENDER_EMAIL, $mail, null, $subject, $message);
    $mail->send();

    Response::send("<p>Thanks for your approach!</p><p>Your message has been sent.</p>");

    break;
    
    // login
    case 'login':
    require('mail.class.php');

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = Database::check_login_data($email, $password);

    $id = Database::email2id($email);

    // correct combination
    if ($result == 1) {
      session_start();
      $_SESSION['id'] = $id;

      Database::add_login($id, $_POST['stay-logged-in'] == 1);
      header("Location: /#/home");
      exit();
    } else if ($result == 2) { // correct combination but email not comfirmed yet
      $user = Database::get_user_by_id($id);
      $mail = Mail::get_email_confirmation_mail($user->firstname, $user->email, $user->email_confirmation_key);
      $mail->send();
      header("Location: /?login_message=The email address has not been confirmed yet. A new email has been sent.&email=" . $user->email);
      exit();
    } else {
      header("Location: /?login_message=The given email-password-combination does not exist.&email=" . $email);
      exit();
    }
    break;

    // logout
    case 'logout':
    session_destroy();

    // delete stay logged in cookies
    if (isset($_COOKIE['stay_logged_in_hash'])) {
      unset($_COOKIE['stay_logged_in_hash']);
      setcookie('stay_logged_in_hash', null, -1, '/');
    }
    if (isset($_COOKIE['stay_logged_in_id'])) {
      unset($_COOKIE['stay_logged_in_id']);
      setcookie('stay_logged_in_id', null, -1, '/');
    }

    header("Location: /");
    exit();
    break;

    case 'signup':
    require('mail.class.php');

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['signup-email'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['password'];

    $message = NULL;
    $signup_success = FALSE;
    try {
      if (Database::register_user($firstname, $lastname, $email, $password, $confirmpassword)) {
        $signup_success = TRUE;
      }
    } catch (Exception $e) {
      $signup_success = FALSE;
      $message = $e->getMessage();
    }

    if ($signup_success) {
      // forward and pass information as URL parameters
      header("Location: /?signup_success=" . (($signup_success)?"true":"false") . "&email=" . $email . "&firstname=" . $firstname);
      exit();
    } else {
      // forward and pass information as URL parameters
      header("Location: /?signup_success=" . (($signup_success)?"true":"false") . "&firstname=" . $firstname . "&lastname=" . $lastname . "&email=" . $email . "&signup_message=" . $message);
      exit();
    }
    break;


    //users

    // add user
    case 'add-user':
    session_required();
    Response::send(Database::add_user($_SESSION['id'], Validation::format_text($_GET['email'])));
    break;

    // list of added users
    case 'list-of-added-users':
    session_required();
    Response::send(Database::get_list_of_added_users_of_user($_SESSION['id']));
    break;

    // remove user
    case 'remove-user':
    session_required();
    Response::send(Database::remove_user($_SESSION['id'], Validation::format_text($_GET['id'])));
    break;

    // list of users who have added you
    case 'list-of-users-who-have-added-you':
    session_required();
    Response::send(Database::get_list_of_users_who_have_added_user($_SESSION['id']));
    break;


    // word lists

    // add word list
    case 'add-word-list':
    session_required();
    Response::send(Database::add_word_list($_SESSION['id'], Validation::format_text($_GET['name'])));
    break;

    // list of word lists
    case 'list-of-word-lists':
    session_required();
    Response::send(Database::get_word_lists_of_user($_SESSION['id']));
    break;

    // list of shared word lists with user
    case 'list-of-shared-word-lists-with-user':
    session_required();
    Response::send(Database::get_list_of_shared_word_lists_with_user($_SESSION['id']));
    break;

    // get word list
    case 'get-word-list':
    session_required();
    $res = new stdClass();
    
    // actual list data
    $res->list = Database::get_word_list($_SESSION['id'], Validation::format_text($_GET['word_list_id']), true);
    if ($res->list !== NULL) { // list is null if it doesn't exist of has been deleted
      // stores whether the requesting unser is the list creator
      $res->allowSharing = ($_SESSION['id'] == $res->list->creator->id);

      // stores whether the requesting user is allowed to edit the list
      $res->allowEdit = ($res->allowSharing) ? TRUE : $res->list->get_editing_permissions_for_user($_SESSION['id']);
    }
    
    Response::send($res);
    break;

    // rename word list
    case 'rename-word-list':
    session_required();
    Response::send(Database::rename_word_list($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['word_list_name'])));
    break;

    // delete word list
    case 'delete-word-list':
    session_required();
    Response::send(Database::delete_word_list($_SESSION['id'], Validation::format_text($_GET['word_list_id'])));
    break;

    // set word list language
    case 'set-word-list-languages':
    session_required();
    Response::send(Database::set_word_list_languages($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['lang1']), Validation::format_text($_GET['lang2'])));
    break;


    // single word in word list

    // add word
    case 'add-word':
    session_required();
    Response::send(Database::add_word($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['lang1']), Validation::format_text($_GET['lang2'])));
    break;

    // update word
    case 'update-word':
    session_required();
    Response::send(Database::update_word($_SESSION['id'], Validation::format_text($_GET['word_id']), Validation::format_text($_GET['lang1']), Validation::format_text($_GET['lang2'])));
    break;

    // remove word
    case 'remove-word':
    session_required();
    Response::send(Database::remove_word($_SESSION['id'], Validation::format_text($_GET['word_id'])));
    break;


    // word list sharing

    // share list
    case 'share-list':
    session_required();
    Response::send(Database::share_list($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['email'])));
    break;

    // set sharing permissions
    case 'set-sharing-permissions':
    session_required();
    $res = new stdClass();
    $email = Validation::format_text($_GET['email']);
    $id = Database::email2id($email);
    if ($id == NULL) {
      $res->set_permissions = -1;
      //$res->user_has_added_you = 0;
    } else {
      $res->set_permissions = Database::set_sharing_permissions($_SESSION['id'], Validation::format_text($_GET['word_list_id']), $email, Validation::format_text($_GET['permissions']));
      //$res->user_has_added_you = Database::user_has_added_user($email, $_SESSION['id']);
    }
    Response::send($res);
    break;

    // set sharing permissions by sharing id
    case 'set-sharing-permissions-by-sharing-id':
    session_required();
    Response::send(Database::set_sharing_permissions_by_sharing_id($_SESSION['id'], Validation::format_text($_GET['sharing_id']), Validation::format_text($_GET['permissions'])));
    break;

    // get sharing permissions of list with user
    case 'get-sharing-perimssions-of-list-with_user':
    session_required();
    Response::send(Database::get_sharing_perimssions_of_list_with_user($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['email'])));
    break;

    // get sharing info of list
    case 'get-sharing-info-of-list':
    session_required();
    Response::send(Database::get_sharing_info_of_list($_SESSION['id'], Validation::format_text($_GET['word_list_id'])));
    break;


    // word list labels

    // add label
    case 'add-label':
    session_required();
    Response::send(Database::add_label($_SESSION['id'], Validation::format_text($_GET['label_name']), Validation::format_text($_GET['parent_label_id'])));
    break;

    // remove label
    case 'remove-label':
    session_required();
    Response::send(Database::set_label_status($_SESSION['id'], Validation::format_text($_GET['label_id']), "0"));
    break;

    // set label list attachment
    case 'set-label-list-attachment':
    session_required();
    Response::send(Database::set_label_list_attachment($_SESSION['id'], Validation::format_text($_GET['label_id']), Validation::format_text($_GET['list_id']), Validation::format_text($_GET['attachment'])));
    break;

    // get labels of user
    case 'get-labels-of-user':
    session_required();
    Response::send(Database::get_labels_of_user($_SESSION['id']));
    break;

    // rename label
    case 'rename-label':
    session_required();
    Response::send(Database::rename_label($_SESSION['id'], Validation::format_text($_GET['label_id']), Validation::format_text($_GET['label_name'])));
    break;


    // query

    // get query data
    case 'get-query-data':
    session_required();
    $result->labels = Database::get_labels_of_user($_SESSION['id']);
    $result->label_list_attachments = Database::get_label_list_attachments_of_user($_SESSION['id']);
    $result->lists = Database::get_query_lists_of_user($_SESSION['id']);
    Response::send($result);
    break;

    // upload query results
    case 'upload-query-results':
    session_required();
    $rawJSON = stripslashes($_POST['answers']);
    $answers = json_decode($rawJSON, true);
    $count = Database::add_query_results($_SESSION['id'], $answers);
    $response = new stdClass();
    $response->count = $count;
    Response::send($response);
    break;
    
    
    // settings
    
    // set name
    case 'set-name':
    session_required();
    Response::send(Database::set_name($_SESSION['id'], Validation::format_text($_GET['firstname']), Validation::format_text($_GET['lastname'])));
    break;
    
    // set password
    case 'set-password':
    session_required();
    Response::send(Database::set_password($_SESSION['id'], Validation::format_text($_POST['password_old']), Validation::format_text($_POST['password_new']), Validation::format_text($_POST['password_new_confirm'])));
    break;
    
    // set email
    case 'set-email':
    session_required();
    Response::send(Database::set_email($_SESSION['id'], Validation::format_text($_POST['email']), Validation::format_text($_POST['password'])));
    break;
    
    // delete account
    case 'delete-account':
    session_required();
    Response::send(Database::delete_account($_SESSION['id'], Validation::format_text($_POST['password'])));
    break;
    
    
    // last used lists
    case 'get-last-used-n-lists':
    session_required();
    Response::send(Database::get_last_used_n_lists_of_user($_SESSION['id'], Validation::format_text($_GET['limit'])));
    
    
    // feed
    
    // get feed
    case 'get-feed':
    session_required();
    Response::send(Database::get_feed($_SESSION['id'], Validation::format_text($_GET['since'])));
    break;
  }
} else {
  // dummy response showing that the server is online
  echo "Abfrage3 server is running.";
}
exit();
?>
