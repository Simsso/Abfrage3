// const strings
var noUsersAddedOutput = '<p class="spacer-top-15">You haven\'t added other users yet.</p>';
var noUsersHaveAddedYouOutput = '<p class="spacer-top-15">No users have added you yet.</p>';

// add a new user by email address
function addUser(email, callback) {
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
    refreshListOfAddedUsers(false);
    refreshListOfSharedWordLists(true);

    callback(data);
  });
}

// event listener for submit of form to add a new user
$('#user-add-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable button to avoid resubmission
  $('#user-add-email').prop('disabled', true);
  $('#user-add-button').prop('disabled', true).attr('value', 'Adding...');

  // call actual add user function and pass required information (email address of user to add)
  addUser($('#user-add-email').val(), function(data) {
    // re-enable button and input field to allow adding another user
    $('#user-add-email').prop('disabled', false).val('');
    $('#user-add-button').prop('disabled', false).attr('value', 'Add user');

    // handle response string
    var responseString;
    if (data === -1) responseString = "Email-address does not exist.";
    else if (data === 0) responseString =  "You have already added this user.";
    else if (data === 1) responseString =  "User has been added.";
    else if (data === 2) responseString =  "You can not add yourself.";
    else responseString = "An unknown error occured.";
    $('#user-add-message').html(responseString);
  });
});

// removes a user by his id
function removeUser(id) {
  // disable buton to avoid resubmission
  $('#added-users-remove-' + id).prop('disabled', true).attr('value', 'Removing...');

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
    $('#added-users-row-' + id).remove();

    // update div
    // if no added users are left show the appropriate message
    if ($('#people-you-have-added tr').length == 1) {
      $('#people-you-have-added').html(noUsersAddedOutput);
    }

    // refresh the other list without loading information
    refreshListOfUsersWhoHaveAddedYou(false);

    // refresh list of shared word lists because some items might be not there anymore
    refreshListOfSharedWordLists(true, true);
  });
}


// list of added users
function refreshListOfAddedUsers(showLoadingInformation) {
  // loading information
  if (showLoadingInformation)
    $('#people-you-have-added').html(loading);

  jQuery.ajax('server.php', {
    data: {
      action: 'list-of-added-users'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);


    var output = "";
    // get the html rows for the table
    for (var i = 0; i < data.length; i++) {
      output += '<tr id="added-users-row-' + data[i].id + '"><td>' + data[i].firstname + ' ' + data[i].lastname + '</td><td>' + data[i].email + '</td><td><input id="added-users-remove-' + data[i].id + '" type="button" class="inline" value="Remove" onclick="removeUser(' + data[i].id + ')"/></td></tr>';
    }

    // if no users have been added yet show the appropriate message
    if (output.length === 0) {
      output = noUsersAddedOutput;
    }
    else { // table head is only visible if users have been added
      output = '<table class="box-table button-right-column"><tr class="bold cursor-default"><td>Name</td><td>Email-Address</td><td></td></tr>' + output + '</table>';
    }

    $('#people-you-have-added').html(output); // update the html element with the list
  });
}

// list of users who have added you
function refreshListOfUsersWhoHaveAddedYou(showLoadingInformation) {
  // loading information
  if (showLoadingInformation)
    $('#people-who-have-added-you').html(loading);

  jQuery.ajax('server.php', {
    data: {
      action: 'list-of-users-who-have-added-you'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    data = handleAjaxResponse(data);


    var output = "";
    // create a string with all html rows of users who have added you
    for (var i = 0; i < data.length; i++) {
      output += '<tr><td>' + data[i].firstname + ' ' + data[i].lastname + '</td><td>' + data[i].email + '</td><td>' + ((data[i].bidirectional)?'':'<input type="button" class="inline" value="Add user" data-email="' + data[i].email + '"/>') + '</td></tr>';
    }

    // show appropriate message if there are no users who have added you
    if (output.length === 0) {
      output = noUsersHaveAddedYouOutput;
    }

    // otherwise add table and table head to the string
    else {
      output = '<table class="box-table button-right-column"><tr class="bold cursor-default"><td>Name</td><td>Email-Address</td><td></td></tr>' + output + '</table>';
    }

    $('#people-who-have-added-you').html(output); // update DOM

    // add event listener for newly added buttons
    $('#people-who-have-added-you input[type=button]').on('click', function() {
      // buttons function is adding users
      var $button = $(this);
      $button.prop('disabled', true).val('Adding...'); // disable button and change value

      // call actual function to update the database
      addUser($button.data('email'), function() {
        $button.fadeOut(); // hide button after the user has been added
      });
    });
  });
}




// load methods
refreshListOfAddedUsers(true);
refreshListOfUsersWhoHaveAddedYou(true);
