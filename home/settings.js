// menu
$('#settings-menu tr').on('click', function() {
  $('#settings-menu tr').removeClass('active');
  $(this).addClass('active');
  $('#settings-content > div').addClass('display-none');
  $('#settings-content > div[data-page=' + $(this).data('page') + ']').removeClass('display-none');
});

$('#settings-menu tr').first().trigger('click');


// change name
$('#settings-name').on('submit', function(e) {
  e.preventDefault();
  
  $('#settings-firstname, #settings-lastname').prop('disabled', true);
  $('#settings-submit-button').prop('disabled', true).attr('value', 'Changing name...');
  $('#settings-name-response').html('').addClass('display-none');
  
  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'set-name',
      firstname: $('#settings-firstname').val(),
      lastname: $('#settings-lastname').val()
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    $('#settings-firstname, #settings-lastname').prop('disabled', false);
    $('#settings-submit-button').prop('disabled', false).attr('value', 'Change name');
    
    var message = '';
    switch (data) {
      case '0': 
        message = 'The given name is not valid.';
        break;
      case '1':
        message = 'Your name has been update successfully.';  
        break;
      default:
        message = 'An unknown error occured.';
        break;
    }
    $('#settings-name-response').html(message).removeClass('display-none');
  });
});



// change password
$('#settings-password').on('submit', function(e) {
  e.preventDefault();
  
  $('#settings-password-old, #settings-password-new, #settings-password-new-confirm').prop('disabled', true);
  $('#settings-password-button').prop('disabled', true).attr('value', 'Changing password...');
  $('#settings-password-response').html('').addClass('display-none');
  
  // send request
  jQuery.ajax('server.php?action=set-password', {
    data: {
      password_old: $('#settings-password-old').val(),
      password_new: $('#settings-password-new').val(),
      password_new_confirm: $('#settings-password-new-confirm').val()
    },
    type: 'POST',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    $('#settings-password-old, #settings-password-new, #settings-password-new-confirm').prop('disabled', false).attr('value', '');
    $('#settings-password-button').prop('disabled', false).attr('value', 'Change password');
    
    var message = '';
    switch (data) {
      case '1':
        message = 'Your password has been updated successfully.';  
        break;
      case '2':
        message = 'The two new passwords are not equal.';  
        break;
      case '3':
        message = 'Your old password is not correct.';  
        break;
      case '5':
        message = 'The new password is not valid.';  
        break;
        
      default:
        message = 'An unknown error occured.';
        break;
    }
    $('#settings-password-response').html(message).removeClass('display-none');
  });
});

// change name
$('#settings-delete-account-form').on('submit', function(e) {
  e.preventDefault();
  
  $('#settings-delete-account-password').prop('disabled', true);
  $('#settings-delete-account-button').prop('disabled', true).attr('value', 'Deleting account...');
  $('#settings-delete-account-response').html('').addClass('display-none');
  
  // send request
  jQuery.ajax('server.php?action=delete-account', {
    data: {
      password: $('#settings-delete-account-password').val()
    },
    type: 'POST',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
    
    if (data === '1') {
      window.location.replace('server.php?action=logout');
    } 
    else {
      $('#settings-delete-account-password').prop('disabled', false).val('');
      $('#settings-delete-account-button').prop('disabled', false).attr('value', 'Delete account');
      
      $('#settings-delete-account-response').html((data === '0') ? 'The password is not correct.' : 'An unknown error occured.').removeClass('display-none');
    }
  });
});
