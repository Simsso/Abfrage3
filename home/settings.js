"use strict";

var Settings = {}; // settings namespace

// settings templates
Settings.Template = {
  changeName: {
    serverResponse: {
      invalid: Handlebars.compile($(page['settings']).find('#settings-name-server-invalid-template').html()),
      success: Handlebars.compile($(page['settings']).find('#settings-name-server-success-template').html()),
      unknownError: Handlebars.compile($(page['settings']).find('#settings-name-server-unknown-error-template').html())
    }
  },
  changePassword: {
    serverResponse: {
      success: Handlebars.compile($(page['settings']).find('#settings-password-server-success-template').html()),
      notEqual: Handlebars.compile($(page['settings']).find('#settings-password-server-not-equal-template').html()),
      wrongOld: Handlebars.compile($(page['settings']).find('#settings-password-server-wrong-old-template').html()),
      invalid: Handlebars.compile($(page['settings']).find('#settings-password-server-invalid-template').html()),
      unknownError: Handlebars.compile($(page['settings']).find('#settings-password-server-unknown-template').html())
    }
  }
};

// single page application allow url like
// ...#settings/profile
Settings.shownSubPageName;
$(window).on('page-settings', function(event, pageName, subPageName) {
  // sub page settings called
  
  if (subPageName === Settings.shownSubPageName) return; // nothing has changed - no reason to touch the DOM

  if (!subPageName) {
    if (Settings.shownSubPageName) {
      subPageName = Settings.shownSubPageName;
    }
    else {
      subPageName = 'profile';
    }
  }
  
  Settings.showPage(subPageName);
  location.hash = '#/settings/' + subPageName;
  
  // load sub page (if given by url) like /#/settings/profile
  if (subPageName) {
    Settings.showPage(subPageName);
  }
});


// show settings page
//
// the page "Settings" has a sub-structure of multiple settings-pages (e.g. "Account", "Profile", ...)
// the method shows the passed page
// 
// @param string name: name of the settings-sub-page to show
Settings.showPage = function(name) {
  Settings.shownSubPageName = name;
  $(page['settings']).find('#settings-menu tr').removeClass('active');
  $(page['settings']).find('#settings-menu tr[data-page=' + name + ']').addClass('active');
  $(page['settings']).find('#settings-content > div').addClass('display-none');
  $(page['settings']).find('#settings-content > div[data-page=' + name + ']').removeClass('display-none');
};

// settings sub-pages menu event listener
$(page['settings']).find('#settings-menu tr').on('click', function() {
  location.hash = '#/settings/' + $(this).data('page');
});

// change name form event listener
$(page['settings']).find('#settings-name').on('submit', function(e) {
  e.preventDefault();
  
  $(page['settings']).find('#settings-firstname, #settings-lastname').prop('disabled', true);
  Button.setPending($(page['settings']).find('#settings-submit-button'));
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
    Button.setDefault($(page['settings']).find('#settings-submit-button'));
    
    var message;
    switch (data) {
      case 0: 
        message = Settings.Template.changeName.serverResponse.invalid;
        break;
      case 1:
        message = Settings.Template.changeName.serverResponse.success;  
        break;

      default:
        message = Settings.Template.changeName.serverResponse.unknownError;
        break;
    }
    var mb = new MessageBox();
    mb.setTitle('Change name');
    mb.setContent(message);
    mb.setFocusedButton('Ok');
    mb.show();
  });
});



// change password form event listener
$(page['settings']).find('#settings-password').on('submit', function(e) {
  e.preventDefault();
  
  $(page['settings']).find('#settings-password-old, #settings-password-new, #settings-password-new-confirm').prop('disabled', true);
  Button.setPending($(page['settings']).find('#settings-password-button'));
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
    Button.setDefault($(page['settings']).find('#settings-password-button'));
    
    var message;
    switch (data) {
      case 1:
        message = Settings.Template.changeName.serverResponse.success();
        break;
      case 2:
        message = Settings.Template.changeName.serverResponse.notEqual();
        break;
      case 3:
        message = Settings.Template.changeName.serverResponse.wrongOld();
        break;
      case 5:
        message = Settings.Template.changeName.serverResponse.invalid();
        break;
        
      default:
        message = Settings.Template.changeName.serverResponse.unknown();
        break;
    }
    var mb = new MessageBox();
    mb.setTitle('Change password');
    mb.setContent(message);
    mb.setFocusedButton('Ok');
    mb.show();

    $(page['settings']).find('#settings-password-old, #settings-password-new, #settings-password-new-confirm').val('');
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
  messageBox.setFocusedButton('No');
  messageBox.setCallback(function(button) {
    if (button === 'Yes') {
      $(page['settings']).find('#settings-delete-account-password').prop('disabled', true);
      Button.setPending($(page['settings']).find('#settings-delete-account-button'));
      
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
          Button.setDefault($(page['settings']).find('#settings-delete-account-button'));

          var mb = new MessageBox();
          mb.setTitle('Delete account');
          mb.setContent(((data === 0) ? 'The password is not correct.' : 'An unknown error occured.'));
          mb.setFocusedButton('Ok');
          mb.show();
          
        }
      });
    }
  });
  messageBox.show();
});



// set ads enabled
//
// @param bool adsEnabled: ads enabled or not
Settings.setAdsEnabled = function(adsEnabled) {
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
};


// checkbox event listener for changing ads enabled settings
$(page['settings']).find('#enable-ads-checkbox').on('change', function() {
  Settings.setAdsEnabled(this.checked);
});



// set newsletter enabled
//
// @param bool newsletterEnabled: newsletter enabled or not
Settings.setNewsletterEnabled = function(newsletterEnabled) {
  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'set-newsletter-enabled',
      newsletter_enabled: newsletterEnabled
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
  });
};


// checkbox event listener for changing ads enabled settings
$(page['settings']).find('#enable-newsletter-checkbox').on('change', function() {
  Settings.setNewsletterEnabled(this.checked);
});