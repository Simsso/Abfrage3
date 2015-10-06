"use strict";

var User = {};

$(window).on('page-user', function(event, pageName, subPageName) {
  // sub page user called
  //
});

// const strings
User.noUserAddedString = '<p class="spacer-top-15">You haven\'t added other users yet.</p>';
User.noUsersHaveAddedYouString = '<p class="spacer-top-15">No users have added you yet.</p>';

// add user
//
// add a new user by email address
// 
// @param string email: user to add
// @param function callback: callback with request response data passed
User.add = function(email, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'add-user',
      email: email
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // refresh both lists (added users and shared word lists) with and without loading information
    User.downloadListOfAddedUsers(false);

    callback(data);
  });
};

// event listener for submit of form to add a new user
$(page['user']).find('#user-add-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable button to avoid resubmission
  $(page['user']).find('#user-add-email').prop('disabled', true);
  $(page['user']).find('#user-add-button').prop('disabled', true).attr('value', 'Adding...');

  // call actual add user function and pass required information (email address of user to add)
  User.add($(page['user']).find('#user-add-email').val(), function(data) {
    // re-enable button and input field to allow adding another user
    $(page['user']).find('#user-add-email').prop('disabled', false).val('');
    $(page['user']).find('#user-add-button').prop('disabled', false).attr('value', 'Add user');

    // handle response string
    var responseString;
    if (data === -1) responseString = "Email-address does not exist.";
    else if (data === 0) responseString =  "You have already added this user.";
    else if (data === 1) responseString =  "User has been added.";
    else if (data === 2) responseString =  "You can not add yourself.";
    else responseString = "An unknown error occured.";
    $(page['user']).find('#user-add-message').html(responseString);
  });
});


// remove user
//
// removes a user by their id
// 
// @param int id: user id
User.remove = function(id) {
  // disable buton to avoid resubmission
  $(page['user']).find('#added-users-remove-' + id).prop('disabled', true).attr('value', 'Removing...');

  jQuery.ajax('server.php', {
    data: {
      action: 'remove-user',
      id: id
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // remove row of the user who has just been deleted from the database
    $(page['user']).find('#added-users-row-' + id).remove();

    // update div
    // if no added users are left show the appropriate message
    if ($(page['user']).find('#people-you-have-added tr').length == 1) {
      $(page['user']).find('#people-you-have-added').html(User.noUserAddedString);
    }

    // refresh the other list without loading information
    User.downloadListOfUsersWhoHaveAddedYou(false);
  });
};


// download list of added users
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function refreshListOfAddedUsers(l) { User.downloadListOfAddedUsers(l); }
User.downloadListOfAddedUsers = function(showLoadingInformation) {
  // loading information
  if (showLoadingInformation)
    $(page['user']).find('#people-you-have-added').html(loading);

  jQuery.ajax('server.php', {
    data: {
      action: 'list-of-added-users'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
    Database.listOfAddedUsers = data;
    User.updateDomListOfAddedUsers();
  });
};


// update dom list of added users
User.updateDomListOfAddedUsers = function() {
  var output = "";
  // get the html rows for the table
  for (var i = 0; i < Database.listOfAddedUsers.length; i++) {
    output += '<tr id="added-users-row-' + Database.listOfAddedUsers[i].id + '"><td>' + Database.listOfAddedUsers[i].firstname + ' ' + Database.listOfAddedUsers[i].lastname + '</td><td>' + Database.listOfAddedUsers[i].email + '</td><td><input id="added-users-remove-' + Database.listOfAddedUsers[i].id + '" type="button" class="inline" value="Remove" onclick="User.remove(' + Database.listOfAddedUsers[i].id + ')"/></td></tr>';
  }

  // if no users have been added yet show the appropriate message
  if (output.length === 0) {
    output = User.noUserAddedString;
  }
  else { // table head is only visible if users have been added
    output = '<table class="box-table button-right-column"><tr class="bold cursor-default"><td>Name</td><td>Email-address</td><td></td></tr>' + output + '</table>';
  }

  $(page['user']).find('#people-you-have-added').html(output); // update the html element with the list
};


// download list of users who have added you
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function refreshListOfUsersWhoHaveAddedYou(l) { User.downloadListOfUsersWhoHaveAddedYou(l); }
User.downloadListOfUsersWhoHaveAddedYou = function(showLoadingInformation) {
  // loading information
  if (showLoadingInformation)
    $(page['user']).find('#people-who-have-added-you').html(loading);

  jQuery.ajax('server.php', {
    data: {
      action: 'list-of-users-who-have-added-you'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    data = handleAjaxResponse(data);

    Database.listOfUsersWhoHaveAddedYou = data;

    User.updateDomListOfUsersWhoHaveAddedYou();
  });
};


// update dom list of users who have added you
User.updateDomListOfUsersWhoHaveAddedYou = function() {

  var output = "";
  // create a string with all html rows of users who have added you
  for (var i = 0; i < Database.listOfUsersWhoHaveAddedYou.length; i++) {
    output += '<tr><td>' + Database.listOfUsersWhoHaveAddedYou[i].firstname + ' ' + Database.listOfUsersWhoHaveAddedYou[i].lastname + '</td><td>' + Database.listOfUsersWhoHaveAddedYou[i].email + '</td><td>' + ((Database.listOfUsersWhoHaveAddedYou[i].bidirectional)?'':'<input type="button" class="inline" value="Add user" data-email="' + Database.listOfUsersWhoHaveAddedYou[i].email + '"/>') + '</td></tr>';
  }

  // show appropriate message if there are no users who have added you
  if (output.length === 0) {
    output = User.noUsersHaveAddedYouString;
  }

  // otherwise add table and table head to the string
  else {
    output = '<table class="box-table button-right-column"><tr class="bold cursor-default"><td>Name</td><td>Email-address</td><td></td></tr>' + output + '</table>';
  }

  $(page['user']).find('#people-who-have-added-you').html(output); // update DOM

  // add event listener for newly added buttons
  $(page['user']).find('#people-who-have-added-you input[type=button]').on('click', function() {
    // buttons function is adding users
    var $button = $(this);
    $button.prop('disabled', true).val('Adding...'); // disable button and change value

    // call actual function to update the database
    User.add($button.data('email'), function() {
      $button.fadeOut(); // hide button after the user has been added
    });
  });
};




// load methods
User.updateDomListOfAddedUsers();
User.updateDomListOfUsersWhoHaveAddedYou();
