<?php
session_start();
require('database.class.php');

if (isset($_GET['action'])) {
  switch($_GET['action']) {

    // outer functions (contact, login, logout, signup)

    case 'contact':
      require('mail.class.php');

      $name = Validation::format_text($_POST['name']);
      $mail = Validation::format_text($_POST['email']);
      $subject = Validation::format_text($_POST['subject']);
      $message = Validation::format_text($_POST['message']);

      $mail = new Mail(Mail::DEFAULT_SENDER_EMAIL, $mail, null, $subject, $message);
      $mail->send();

      echo "<p>Thanks for your approach!</p><p>Your message has been sent.</p>";

      break;

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
        
        // stay logged in
        if ($_POST['stay-logged-in'] == 1) {
          Database::stay_logged_in($_SESSION['id']);
        }
        
        Database::add_login($id);
        header("Location: /#home");
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
        header("Location: /?signup_success=" . (($signup_success)?"true":"false") . "&email=" . $email . "&firstname=" . $firstname);
        exit();
      } else {
        header("Location: /?signup_success=" . (($signup_success)?"true":"false") . "&firstname=" . $firstname . "&lastname=" . $lastname . "&email=" . $email . "&signup_message=" . $message);
        exit();
      }
      break;


    //users

    case 'add-user':
      echo Database::add_user($_SESSION['id'], Validation::format_text($_GET['email']));
      break;

    case 'list-of-added-users':
      echo json_encode(Database::get_list_of_added_users_of_user($_SESSION['id']));
      break;

    case 'remove-user':
      echo Database::remove_user($_SESSION['id'], Validation::format_text($_GET['id']));
      break;

    case 'list-of-users-who-have-added-you':
      echo json_encode(Database::get_list_of_users_who_have_added_user($_SESSION['id']));
      break;


    // word lists

    case 'add-word-list':
      echo json_encode(Database::add_word_list($_SESSION['id'], Validation::format_text($_GET['name'])));
      break;

    case 'list-of-word-lists':
      echo json_encode(Database::get_word_lists_of_user($_SESSION['id']));
      break;

    case 'list-of-shared-word-lists-with-user':
      echo json_encode(Database::get_list_of_shared_word_lists_with_user($_SESSION['id']));
      break;

    case 'get-word-list':
      echo json_encode(Database::get_word_list($_SESSION['id'], Validation::format_text($_GET['word_list_id'])));
      break;

    case 'rename-word-list':
      echo Database::rename_word_list($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['word_list_name']));
      break;

    case 'delete-word-list':
      echo Database::delete_word_list($_SESSION['id'], Validation::format_text($_GET['word_list_id']));
      break;

    case 'set-word-list-languages':
      echo Database::set_word_list_languages($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['lang1']), Validation::format_text($_GET['lang2']));
      break;


    // single word in word list

    case 'add-word':
      echo Database::add_word($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['lang1']), Validation::format_text($_GET['lang2']));
      break;

    case 'update-word':
      echo Database::update_word($_SESSION['id'], Validation::format_text($_GET['word_id']), Validation::format_text($_GET['lang1']), Validation::format_text($_GET['lang2']));
      break;

    case 'remove-word':
      echo Database::remove_word($_SESSION['id'], Validation::format_text($_GET['word_id']));
      break;


    // word list sharing

    case 'share-list':
      echo Database::share_list($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['email']));
      break;

    case 'set-sharing-permissions':
      echo Database::set_sharing_permissions($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['email']), Validation::format_text($_GET['permissions']));
      break;

    case 'set-sharing-permissions-by-sharing-id':
      echo Database::set_sharing_permissions_by_sharing_id($_SESSION['id'], Validation::format_text($_GET['sharing_id']), Validation::format_text($_GET['permissions']));
      break;

    case 'get-sharing-perimssions-of-list-with_user':
      echo json_encode(Database::get_sharing_perimssions_of_list_with_user($_SESSION['id'], Validation::format_text($_GET['word_list_id']), Validation::format_text($_GET['email'])));
      break;

    case 'get-sharing-info-of-list':
      echo json_encode(Database::get_sharing_info_of_list($_SESSION['id'], Validation::format_text($_GET['word_list_id'])));
      break;


    // word list labels

    case 'add-label':
      echo Database::add_label($_SESSION['id'], Validation::format_text($_GET['label_name']), Validation::format_text($_GET['parent_label_id']));
      break;

    case 'remove-label':
      echo Database::set_label_status($_SESSION['id'], Validation::format_text($_GET['label_id']), "0");
      break;

    case 'set-label-list-attachment':
      echo Database::set_label_list_attachment($_SESSION['id'], Validation::format_text($_GET['label_id']), Validation::format_text($_GET['list_id']), Validation::format_text($_GET['attachment']));
      break;

    case 'get-labels-of-user':
      echo json_encode(Database::get_labels_of_user($_SESSION['id']));
      break;

    case 'rename-label':
      echo Database::rename_label($_SESSION['id'], Validation::format_text($_GET['label_id']), Validation::format_text($_GET['label_name']));
      break;
    
    
    // query
    
    case 'get-query-data':
      $result->labels = Database::get_labels_of_user($_SESSION['id']);
      $result->label_list_attachments = Database::get_label_list_attachments_of_user($_SESSION['id']);
      $result->lists = Database::get_query_lists_of_user($_SESSION['id']);
      echo json_encode($result);
      break;
    case 'upload-query-results':
      
      echo "{'response':'" . addslashes(Database::add_query_results($_SESSION['id'], json_decode(stripslashes($_POST['answers']), true))) . "'}";
      break;
    
  }
} else {
  echo "Abfrage3 server is running.";
}
exit();
?>
