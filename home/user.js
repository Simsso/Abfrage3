"use strict";

// user page namespace
var User = {};

// templates
User.Template = {
  noneAdded: Handlebars.compile($(page['user']).find('#user-none-added-template').html()),
  noneHaveAddedYou: Handlebars.compile($(page['user']).find('#user-none-have-added-you-template').html()),

  serverWrongEmail: Handlebars.compile($(page['user']).find('#user-add-server-response-wrong-email-template').html()),
  serverSuccess: Handlebars.compile($(page['user']).find('#user-add-server-response-success-template').html()),
  serverCantAddYourself: Handlebars.compile($(page['user']).find('#user-add-server-response-cant-add-yourself-template').html()),
  serverUnknownError: Handlebars.compile($(page['user']).find('#user-add-server-response-unknown-error-template').html()),

  listOfAddedUsers: Handlebars.compile($(page['user']).find('#user-list-of-added-users-template').html()),
  listOfUsersWhoHaveAddedYou: Handlebars.compile($(page['user']).find('#user-list-of-users-who-have-added-you-template').html())
};


$(window).on('page-user', function(event, pageName, subPageName) {
  // sub page user called
  //

  // load methods
  User.updateDomListOfAddedUsers();
  User.updateDomListOfUsersWhoHaveAddedYou();
});


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
  Button.setPending($(page['user']).find('#user-add-button'));

  // call actual add user function and pass required information (email address of user to add)
  User.add($(page['user']).find('#user-add-email').val(), function(data) {
    // re-enable button and input field to allow adding another user
    $(page['user']).find('#user-add-email').prop('disabled', false).val('');
    Button.setDefault($(page['user']).find('#user-add-button'));

    // handle response string
    var responseString;
    if (data === -1) responseString = User.Template.serverWrongEmail();
    else if (data === -2) responseString = User.Template.serverCantAddYourself();
    else if (data === 1 || data === 2) responseString = User.Template.serverSuccess();
    else responseString = User.Template.serverUnknownError(); // code 0 (data === 0)
    $(page['user']).find('#user-add-message').html(responseString);
  });
});


// remove user
//
// removes a user by their id
// 
// @param int id: user id
User.remove = function(id) {
  // disable button to avoid resubmission
  Button.setPending($(page['user']).find('#added-users-remove-' + id));

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
    if ($(page['user']).find('#people-you-have-added tr').length === 1) {
      $(page['user']).find('#people-you-have-added').html(User.Template.noneAdded());
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
  var output;
  // if no users have been added yet show the appropriate message
  if (Database.listOfAddedUsers.length === 0) {
    output = User.Template.noneAdded();
  }
  else {
    // get the html rows for the table
    output = User.Template.listOfAddedUsers({ user: Database.listOfAddedUsers });
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
  var output;
  // show appropriate message if there are no users who have added you
  if (Database.listOfUsersWhoHaveAddedYou.length === 0) {
    output = User.Template.noneHaveAddedYou();
  }
  else { // otherwise add table and table head to the string
    output = User.Template.listOfUsersWhoHaveAddedYou({ user: Database.listOfUsersWhoHaveAddedYou });
  }


  $(page['user']).find('#people-who-have-added-you').html(output); // update DOM

  // add event listener for newly added buttons
  $(page['user']).find('#people-who-have-added-you input[type=button]').on('click', function() {
    // buttons function is adding users
    var button = $(this);
    Button.setPending(button); // disable button and change value

    // call actual function to update the database
    User.add(button.data('email'), function() {
      button.fadeOut(); // hide button after the user has been added
    });
  });
};