"use strict";

// single page application allow url like
// ...#settings/profile
var shownSettingsSubPageName;
$(window).on('page-settings', function(event, pageName, subPageName) {
  if (subPageName === shownSettingsSubPageName) return; // nothing has changed - no reason to touch the DOM

  if (!subPageName) {
    if (shownSettingsSubPageName) {
      subPageName = shownSettingsSubPageName;
    }
    else {
      subPageName = 'profile';
    }
  }
  
  showSettingsPage(subPageName);
  location.hash = '#/settings/' + subPageName;
  
  // load sub page (if given by url) like /#/settings/profile
  if (subPageName) {
    showSettingsPage(subPageName);
  }
});


// show settings page
//
// the page "Settings" has a sub-structure of multiple settings-pages (e.g. "Account", "Profile", ...)
// the method shows the passed page
// 
// @param string name: name of the settings-sub-page to show
function showSettingsPage(name) {
  shownSettingsSubPageName = name;
  $(page['settings']).find('#settings-menu tr').removeClass('active');
  $(page['settings']).find('#settings-menu tr[data-page=' + name + ']').addClass('active');
  $(page['settings']).find('#settings-content > div').addClass('display-none');
  $(page['settings']).find('#settings-content > div[data-page=' + name + ']').removeClass('display-none');
}

// settings sub-pages menu event listener
$(page['settings']).find('#settings-menu tr').on('click', function() {
  location.hash = '#/settings/' + $(this).data('page');
});

// change name form event listener
$(page['settings']).find('#settings-name').on('submit', function(e) {
  e.preventDefault();
  
  $(page['settings']).find('#settings-firstname, #settings-lastname').prop('disabled', true);
  $(page['settings']).find('#settings-submit-button').prop('disabled', true).attr('value', 'Changing name...');
  $(page['settings']).find('#settings-name-response').html('').addClass('display-none');
  
  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'set-name',
      firstname: $(page['settings']).find('#settings-firstname').val(),
      lastname: $(page['settings']).find('#settings-lastname').val()
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    $(page['settings']).find('#settings-firstname, #settings-lastname').prop('disabled', false);
    $(page['settings']).find('#settings-submit-button').prop('disabled', false).attr('value', 'Change name');
    
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
    $(page['settings']).find('#settings-name-response').html(message).removeClass('display-none');
  });
});



// change password form event listener
$(page['settings']).find('#settings-password').on('submit', function(e) {
  e.preventDefault();
  
  $(page['settings']).find('#settings-password-old, #settings-password-new, #settings-password-new-confirm').prop('disabled', true);
  $(page['settings']).find('#settings-password-button').prop('disabled', true).attr('value', 'Changing password...');
  $(page['settings']).find('#settings-password-response').html('').addClass('display-none');
  
  // send request
  jQuery.ajax('server.php?action=set-password', {
    data: {
      password_old: $(page['settings']).find('#settings-password-old').val(),
      password_new: $(page['settings']).find('#settings-password-new').val(),
      password_new_confirm: $(page['settings']).find('#settings-password-new-confirm').val()
    },
    type: 'POST',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    $(page['settings']).find('#settings-password-old, #settings-password-new, #settings-password-new-confirm').prop('disabled', false).attr('value', '');
    $(page['settings']).find('#settings-password-button').prop('disabled', false).attr('value', 'Change password');
    
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
    $(page['settings']).find('#settings-password-response').html(message).removeClass('display-none');
  });
});



// delete account form event listener
$(page['settings']).find('#settings-delete-account-form').on('submit', function(e) {
  e.preventDefault();
  
  // message box to make sure that no one accidentally deletes an account
  var messageBox = new MessageBox();
  messageBox.setTitle('Delete account');
  messageBox.setContent('Are you sure you want to delete your account?');
  messageBox.setButtons(MessageBox.ButtonType.YesNoCancel);
  messageBox.setCallback(function(button) {
    if (button === 'Yes') {
      $(page['settings']).find('#settings-delete-account-password').prop('disabled', true);
      $(page['settings']).find('#settings-delete-account-button').prop('disabled', true).attr('value', 'Deleting account...');
      $(page['settings']).find('#settings-delete-account-response').html('').addClass('display-none');
      
      // send request
      jQuery.ajax('server.php?action=delete-account', {
        data: {
          password: $(page['settings']).find('#settings-delete-account-password').val()
        },
        type: 'POST',
        error: function(jqXHR, textStatus, errorThrown) {

        }
      }).done(function(data) {
        data = handleAjaxResponse(data);

        if (data === 1) {
          window.location.replace('server.php?action=logout');
        } 
        else {
          $(page['settings']).find('#settings-delete-account-password').prop('disabled', false).val('');
          $(page['settings']).find('#settings-delete-account-button').prop('disabled', false).attr('value', 'Delete account');

          $(page['settings']).find('#settings-delete-account-response').html((data === 0) ? 'The password is not correct.' : 'An unknown error occured.').removeClass('display-none');
        }
      });
    }
  });
  messageBox.show();
});



// set ads enabled
//
// @param bool adsEnabled: ads enabled or not
function setAdsEnabled(adsEnabled) {
  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'set-ads-enabled',
      ads_enabled: adsEnabled
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // hide or show ads depending on the settings
    if (adsEnabled) {
      showAds();
    } 
    else {
      hideAds();
    }
  });
}

// checkbox event listener for changing ads enabled settings
$(page['settings']).find('#enable-ads-checkbox').on('change', function() {
  setAdsEnabled(this.checked);
});