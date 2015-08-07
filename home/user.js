// add user
var noUsersAddedOutput = '<p class="spacer-top-15">You haven\'t added other users yet.</p>';
var noUsersHaveAddedYouOutput = '<p class="spacer-top-15">No users have added you yet.</p>';
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
        if (data.status == 1) {
            refreshListOfAddedUsers(false);
        }

        callback(data);
    });
};

$('#user-add-form').on('submit', function(e) {
    // dont visit action="..." page
    e.preventDefault();

    $('#user-add-email').prop('disabled', true);
    $('#user-add-button').prop('disabled', true).attr('value', 'Adding...');
    addUser($('#user-add-email').val(), function(data) {
        $('#user-add-email').prop('disabled', false).val('');
        $('#user-add-button').prop('disabled', false).attr('value', 'Add user');
        var responseString; 
        if (data == -1) responseString = "Email-address does not exist.";
        else if (data == 0) responseString =  "You have already added this user.";
        else if (data == 1) responseString =  "User has been added.";
        else if (data == 2) responseString =  "You can not add yourself.";
        else responseString = "An unknown error occured.";

        $('#user-add-message').html(responseString);
    });
});

function removeUser(id) {
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
        console.log(data);
        $('#added-users-row-' + id).hide();

        // set text to 
        if ($('#people-you-have-added tr').length == 1) {
            $('#people-you-have-added').html(noUsersAddedOutput);
        }

        refreshListOfUsersWhoHaveAddedYou(false);
    });
}


// list of added users
function refreshListOfAddedUsers(hideLoadingInformation) {
    if (hideLoadingInformation)
        $('#people-you-have-added').html(loading);

    jQuery.ajax('server.php', {
        data: {
            action: 'list-of-added-users'
        },
        type: 'GET',
        error: function(jqXHR, textStatus, errorThrown) {

        }
    }).done(function(data) {
        console.log(data);
        data = jQuery.parseJSON(data);
        console.log(data);
        var output = "";
        for (var i = 0; i < data.length; i++) {
            output += '<tr id="added-users-row-' + data[i].id + '"><td>' + data[i].firstname + ' ' + data[i].lastname + '</td><td>' + data[i].email + '</td><td><input id="added-users-remove-' + data[i].id + '" type="button" class="inline" value="Remove" onclick="removeUser(' + data[i].id + ')"/></td></tr>';
        }
        if (output.length == 0) {
            output = noUsersAddedOutput;
        }
        else {
            output = '<table class="box-table button-right-column "><tr class="bold"><td>Name</td><td>Email-Address</td><td></td></tr>' + output + '</table>';
        }
        console.log(output);
        $('#people-you-have-added').html(output);
    });
}

function refreshListOfUsersWhoHaveAddedYou(showLoadingInformation) {
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
        console.log(data);
        data = jQuery.parseJSON(data);
        var output = "";
        for (var i = 0; i < data.length; i++) {
            output += '<tr><td>' + data[i].firstname + ' ' + data[i].lastname + '</td><td>' + data[i].email + '</td><td>' + ((data[i].bidirectional)?'':'<input type="button" class="inline" value="Add user" data-email="' + data[i].email + '"/>') + '</td></tr>';
        }
        if (output.length == 0) {
            output = noUsersHaveAddedYouOutput;
        }
        else {
            output = '<table class="box-table"><tr class="bold"><td>Name</td><td>Email-Address</td><td></td></tr>' + output + '</table>';
        }
        console.log(output);
        $('#people-who-have-added-you').html(output);

        $('#people-who-have-added-you input[type=button]').on('click', function() {
            $button = $(this);
            $button.prop('disabled', true).val('Adding...');
            addUser($button.data('email'), function() {
                $button.fadeOut();
            });
        });
    });
}




// load methods
refreshListOfAddedUsers(true);
refreshListOfUsersWhoHaveAddedYou(true);