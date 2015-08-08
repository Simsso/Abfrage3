<?php
  	session_start();
	if (isset($_SESSION['id'])) {
        require('database.php');
        switch($_GET['action']) {
            case 'add-user':
                echo Database::add_user($_SESSION['id'], $_GET['email']);
                break;
            case 'list-of-added-users':
                echo json_encode(Database::get_list_of_added_users_of_user($_SESSION['id']));
                break;
            case 'remove-user':
                echo Database::remove_user($_SESSION['id'], $_GET['id']);
                break;
            case 'list-of-users-who-have-added-you':
                echo json_encode(Database::get_list_of_users_who_have_added_user($_SESSION['id']));
                break;
            case 'add-word-list':
                echo json_encode(Database::add_word_list($_SESSION['id'], $_GET['name']));
                break;
            case 'list-of-word-lists':
                echo json_encode(Database::get_word_lists_of_user($_SESSION['id']));
                break;
            case 'list-of-shared-word-lists-with-user':
                echo json_encode(Database::get_list_of_shared_word_lists_with_user($_SESSION['id']));
                break;
            case 'get-word-list':
                echo json_encode(Database::get_word_list($_SESSION['id'], $_GET['word_list_id']));
                break;
            case 'delete-word-list':
                echo Database::delete_word_list($_SESSION['id'], $_GET['word_list_id']);
                break;
            case 'add-word':
                echo Database::add_word($_SESSION['id'], $_GET['word_list_id'], $_GET['lang1'], $_GET['lang2']);
                break;
            case 'update-word':
                echo Database::update_word($_SESSION['id'], $_GET['word_id'], $_GET['lang1'], $_GET['lang2']);
                break;
            case 'remove-word':
                echo Database::remove_word($_SESSION['id'], $_GET['word_id']);
                break;
            case 'share-list':
                echo Database::share_list($_SESSION['id'], $_GET['word_list_id'], $_GET['email']);
                break;
            case 'set-sharing-permissions':
                echo Database::set_sharing_permissions($_SESSION['id'], $_GET['word_list_id'], $_GET['email'], $_GET['permissions']);
                break;
            case 'set-sharing-permissions-by-sharing-id':
                echo Database::set_sharing_permissions_by_sharing_id($_SESSION['id'], $_GET['sharing_id'], $_GET['permissions']);
                break;
            case 'get-sharing-perimssions-of-list-with_user':
                echo json_encode(Database::get_sharing_perimssions_of_list_with_user($_SESSION['id'], $_GET['word_list_id'], $_GET['email']));
                break;
            case 'get-sharing-info-of-list':
                echo json_encode(Database::get_sharing_info_of_list($_SESSION['id'], $_GET['word_list_id']));
                break;
        }
    }
    exit();
?>