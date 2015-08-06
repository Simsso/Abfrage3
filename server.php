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
        }
    }
    exit();
?>