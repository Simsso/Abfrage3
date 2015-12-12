"use strict";

// window load event listener
$(window).load(function() {
    window.loaded = true;
});


// box head right icons (like reload, expand and collapse)
$('.box .box-head img.box-head-right-icon').on('click', function(event) {
  switch($(this).data('action')) {
    case 'expand': // expand
      $(this).data('action', 'collapse').attr('src', 'img/collapse.svg').parent().next().show();
      BoxExpansion.set($(this).parent().parent().attr('id'), true);
      break;

    case 'collapse': // collapse
      $(this).data('action', 'expand').attr('src', 'img/expand.svg').parent().next().hide();
      BoxExpansion.set($(this).parent().parent().attr('id'), false);

      // stop fullscreen if it is in fullscreen
      var imgs = $(this).parent().find('img');
      for (var i = imgs.length - 1; i >= 0; i--) {
        if (imgs.eq(i).data('action') == 'stop-fullscreen') {
          imgs.eq(i).trigger('click');
        }
      };
      break;
      
    case 'refresh': // refresh
      window[$(this).data('function-name')](true);
      break;

    case 'fullscreen': // fullscreen
      var box = $(this).data('action', 'stop-fullscreen').attr('src', 'img/fullscreen-exit.svg').parent().parent();

      // expand if it isn't already
      var imgs = $(this).parent().find('img');
      for (var i = imgs.length - 1; i >= 0; i--) {
        if (imgs.eq(i).data('action') == 'expand') {
          imgs.eq(i).trigger('click');
        }
      };

      Scrolling.disable();
      box.addClass('fullscreen');
      var escFunction = function(e) {
        if (e.keyCode == 27) { // detect ESC key press
          box.find('img[data-action="fullscreen"]').trigger('click');
          $(document).unbind('keyup', escFunction); // remove esc function event listener
        }
      };
      $(document).on('keyup', escFunction);
      break;

    case 'stop-fullscreen':
      var box = $(this).data('action', 'fullscreen').attr('src', 'img/fullscreen.svg').parent().parent();
      Scrolling.enable();
      box.removeClass('fullscreen');
      break;
  }
  
  event.stopPropagation();
}).show();


// allow expanding by clicking on the box head
$('.box .box-head').on('click', function(e) {
  $(this).find('img.box-head-right-icon').last().trigger('expand');
});

// expand can be triggered (custom jQuery event)
$('.box .box-head img.box-head-right-icon').on('expand', function() {
  $(this).data('action', 'expand').trigger('click');
});

// collapse can be triggered (custom jQuery event)
$('.box .box-head img.box-head-right-icon').on('collapse', function() {
  $(this).data('action', 'collapse').trigger('click');
});


// box expansion
// manages the storage of div.box expansion
var BoxExpansion = {
  BOX_EXPANSION_KEY: "BoxExpansion",

  removeAll: function() {
    window.localStorage.removeItem(this.BOX_EXPANSION_KEY);
  },

  set: function(id, expanded) {
    if (typeof id === 'undefined') return;

    var expansions = this.getAll();
    expansions[id] = expanded;
    window.localStorage.setItem(this.BOX_EXPANSION_KEY, JSON.stringify(expansions));
  },

  getAll: function() {
    var storage = JSON.parse(window.localStorage.getItem(this.BOX_EXPANSION_KEY));

    if (storage === null) {
      storage = {};
    }

    return storage;
  },

  refreshDom: function() {
    var expansions = this.getAll();
    for(var key in expansions) {
      console.log(key + ' ' + expansions[key]);
      console.log($('#' + key).find('.box-head img.box-head-right-icon').last().trigger((expansions[key]) ? 'expand' : 'collapse'));
    }
  }
};
BoxExpansion.refreshDom();


// box shadow blinking
// used for example when a word has been aswered correctly
$('.box').on('shadow-blink-green', function() {
  var $this = $(this).find('.box-body');
  $this.addClass('green-shadow');
  setTimeout(function() {
    $this.removeClass('green-shadow');
  }, 200);
});



// mobile menu
var menuShown = false;

// add mobile menu html to body element
$('body').prepend('<nav id="mobile-nav"><div></div></nav>');
var htmlString = '<ul class="nav">';
for (var i = 0; i < $('.navbar-inner.content-width > ul').length; i++) {
  htmlString += $('.navbar-inner.content-width > ul').eq(i).html();
}
htmlString += '</ul>';
$('#mobile-nav > div').html(htmlString).find('*').show();

var menuIcons = $('#mobile-nav > div .nav-img-li');
for (var i = 0; i < menuIcons.length; i++) {
  var currentMenuIcon = menuIcons.eq(i);
  var text = currentMenuIcon.data('text');
  currentMenuIcon.append('&nbsp;' + text);
}


// toggle mobile menu
function toggleMenu() {
  if (menuShown)
    hideMenu();
  else
    showMenu();
}


// show mobile menu
function showMenu() {
  if (menuShown) return;

  menuShown = true;

  Scrolling.disable();

  $('body').addClass('mobile-menu-shown');
  $('.menu-button').attr('src', 'img/menu-back.svg');

  $('#main-wrapper').on('touchstart', function(e) { 
    e.preventDefault(); 
    $('#main-wrapper').trigger('click'); 
  });
  $('#main-wrapper').on('click', function(e) {
    e.preventDefault;
    hideMenu();
  });
}


// hide mobile menu
function hideMenu() {
  if (!menuShown) return;

  menuShown = false;

  Scrolling.enable();

  $('#main-wrapper').unbind('click');
  $('#main-wrapper').unbind('touchstart');

  $('body').removeClass('mobile-menu-shown');
  $('.menu-button').attr('src', 'img/menu.svg');
}

$('#head-nav').append('<img src="img/menu.svg" class="menu-button nav-image" onclick="toggleMenu()" />');

$('#mobile-nav > div li').on('click', hideMenu);
$(window).on('resize', function() {
  if (menuShown && $(window).width() > 700) {
    hideMenu();
  }
});

$('.navbar .logo').on('click', hideMenu); // hide mobile menu when clicking on the logo


// scrolling functions 
var Scrolling = {

  // disable scrolling
  disable: function() {
    var scrollPosition = [
      self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
      self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
    ];
    var html = jQuery('html'); // it would make more sense to apply this to body, but IE7 won't have that
    html.data('scroll-position', scrollPosition);
    html.data('previous-overflow', html.css('overflow'));
    html.css('overflow', 'hidden');
    window.scrollTo(scrollPosition[0], scrollPosition[1]);
  },


  // enable scrolling
  enable: function () {
    // un-lock scroll position
    var html = jQuery('html');
    var scrollPosition = html.data('scroll-position');
    html.css('overflow', html.data('previous-overflow'));
    window.scrollTo(scrollPosition[0], scrollPosition[1]);
  },


  // scroll to the top
  toTop: function() {
    window.scrollTo(0, 0);
  }
};


// loading animation html
var loading = '<div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>';
var loadingFullscreen = '<div class="sk-three-bounce fullscreen"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>';
function getLoadingFullscreenWithMessage(message) {
  return '<div class="sk-three-bounce fullscreen">' + message + '<br><br><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>';
}



// toast
$('#main-wrapper').after('<div id="toast" style="transition: opacity 0s; position: fixed; bottom: 57px; left: calc(50% - 200px); width: 400px; color: white; padding: 10px; border: 1px solid #4F5B93; box-shadow: 1px 1px 3px #4F5B93; background-color: #8892BF; overflow: hidden; text-align: center; display: none; "></div>');

// Toast
// 
// calling
// @code new Toast("message"); 
// will make a toast appear on the bottom of the scrren including the passed string
//
// @param string text: the text to show
// @param int|undefined ms: milliseconds to show the message (default 5000) 
function Toast(text, ms) {
  this.ms = ms;
  this.text = text;

  if (ms === undefined) {
    ms = 5000;
  }
  $('#toast').promise().done(function() {
    $(this).html(text).fadeIn().delay(ms).fadeOut();
  });
}



// Ajax request manager
// manages Ajax requests of the same type and can make sure that there is only one running at the same time
//
// @param bool onlyOne: defines wheter there can be only one request of the type running at the same time
function AjaxRequestsManager(onlyOne) {
  this.onlyOne = onlyOne;
  this.list = [];
  this.add = function (ajaxRequest) {
    if (this.onlyOne) {
      while (this.list.length > 0) {
        this.list.shift().abort(); // remove first item and abort it
      }
    }
    this.list.push(ajaxRequest);
  };
}

var ajaxRequests = {
  loadWordList: new AjaxRequestsManager(true),
  loadListOfWordLists: new AjaxRequestsManager(true),
  loadListOfSharedWordLists: new AjaxRequestsManager(true),
  downloadListSharings: new AjaxRequestsManager(true)
};


// handle ajax response data
//
// This function is called every time an Ajax-request has been done. This is to make sure every Ajax request is logged into the console and parsing is not decentral.
// The Abfrage3 server always (except from PHP errors) responds with an JSON string created like so:
//
// @code  // response object
//        $obj = new stdClass();
//        $obj->status = $status; // status: "success" or "error"
//        $obj->data = $data; // data: the actual data
//        $obj->action = $_GET['action']; // the action which has been done (like "get-word-list")
//        $obj->execution_time_ms = (microtime(true) - $start_time) * 1000; // measured execution time (debugging purposes)
//
//        // JSON encode response object
//        // echo it by passing it to the exit() function
//        // stop script execution by calling exit()
//        exit(json_encode($obj)); 
//
// The information of this object is being parsed to an JavaScript object and will be logged. 
// If the sent status is "success" the function will return the parsed data object.
// If something went wrong e.g. the server responds with an PHP error the try {} will fail and the whole response string will be logged inside the catch block.
// In latter case the returned value will be undefined.
//
// @param string|undefined data: server response string (JSON) or undefined in case the passed data is not valid
// 
// @return object: parsed server response (JSON) as a JavaScript object
function handleAjaxResponse(data) {
  try { // try because parsing JSON could cause an exception
    var obj = JSON.parse(data);
    obj.rawSize = data.length;
    console.log(obj);

    if (obj.status === "success") {
      return obj.data;
    }
    else if (obj.status === "error") {
      if (obj.data === "no session") {
        $('#main-wrapper').after(getLoadingFullscreenWithMessage("Your session has expired.")); // TODO: move into lang file
        $('.sk-three-bounce.fullscreen').css('opacity', '1');

        setTimeout(function () {
          window.location.replace('server.php?action=logout');
        }, 2000);
      }
    }
  }
  catch (e) {
    // will be cought if server response is e.g. a PHP error
    // log it for debugging purpose
    console.error(data);
  }
}


// screenshot popups
$('#main-wrapper').after('<table id="screenshot-popup-wrapper" style="z-index: 1000; height: 100%; width: 100%; position: fixed; top: 0px; left: 0px; display: none; background-color: rgba(0, 0, 0, 0.8); "><tbody><tr><td style="cursor: pointer; vertical-align: middle; text-align: center; "><img id="screenshot-popup-image" style="cursor: default; max-height: 80%; max-width: 80%; border: 1px solid black; box-shadow: 0 0 5px black; "><br><br><span style="color: white;" id="screenshot-description">Description</span></td></tr></tbody></table>');
$('.screenshot').on('click', function() {
  $('#screenshot-popup-image').attr('src', $(this).attr('src').replace('.min', ''));
  $('#screenshot-description').html($(this).data('description'));
  $('#screenshot-popup-wrapper').show();
});
$('#screenshot-popup-wrapper').on('click', function() {
  $(this).hide();
});
$(document).keyup(function(e) {
  if (e.keyCode == 27) { // escape key maps to keycode '27'
    $('#screenshot-popup-wrapper').fadeOut();
  }
});



// cookie functions
var Cookie = {

  // set cookie
  //
  // @param string cname: name
  // @param string cvalue: cooke value
  // @param int exdays: expiration days
  set: function(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
  },


  // read cookie
  //
  // @param string key: name of the cookie
  //
  // @return string: cookie value
  read: function(key)
  {
    var result;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? (result[1]) : null;
  }

}


if (Cookie.read('accepted_cookies') != 'true') {
  // show cookie message
  $('#main').append('<footer id="cookie-header" class="cookie-header box display-none"><div class="content-width"><table><tr><td>This website uses cookies to ensure you get the best experience. <a href="https://en.wikipedia.org/wiki/HTTP_cookie" target="_blank">Learn more.</a></td><td><input id="cookie-got-it-button" type="button" class="width-110 no-box-shadow" value="Got it!"/></td></tr></table></div></footer>');

  $('#cookie-got-it-button').on('click', function() {
    $(this).prop('disabled', true);
    Cookie.set('accepted_cookies', 'true', 10000);
    $('#cookie-header').css('opacity', 0);
    setTimeout(function() { $('#cookie-header').remove(); }, 500);
  });
}

// contact form submit event lsitener
$('#contact-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // check if the bot question was answered correctly
  var botQuestion = $('#contact-bot-question').html().split(' + ');
  if (parseInt(botQuestion[0]) + parseInt(botQuestion[1]) != $('#contact-bot-protection').val())
  {
    var messageBox = new MessageBox();
    messageBox.setTitle('Bot question');
    messageBox.setContent('You haven\'t answered the bot question correctly.');
    messageBox.show();
    return; // abort sending
  }

  // prevent multiple submissions
  $('#contact-submit').prop('disabled', true);
  $('#contact-submit').attr('value', 'Sending...');

  // send data to the server
  $.post('server.php?action=contact', {
    name: $('#contact-name').val(),
    email: $('#contact-email').val(),
    subject: $('#contact-subject').val(),
    message: $('#contact-message').val()
  }).done(function(data) {
    data = handleAjaxResponse(data);
    $('#contact-body').html(data); // after sending replace the form with the server response
  });
});

// submit forms loading screen
// when submitting the login or sign up form show a nice loading screen
$('form[data-submit-loading=true]').on('submit', function(e) {
  $('#main-wrapper').after(loadingFullscreen);
  $('.sk-three-bounce.fullscreen').css('opacity', '1');

  // delay form submit
  var form = this;
  e.preventDefault();
  setTimeout(function () {
    form.submit();
  }, 500);
});



// advertisement

var adsLoaded = false;

// show ads
function showAds() {
  adsEnabled = true;
  if (window.loaded) {
    $('.advertisement-bottom').show();
    loadAds();
  }
  else {
    $(window).on('load', function() {
      showAds();
    });
  }
}

// hide ads
function hideAds() {
  adsEnabled = false;
  $('.advertisement-bottom').hide();
}

// load ads
function loadAds() {
  if (!adsLoaded) {
    (adsbygoogle = window.adsbygoogle || []).push({});
  }
  adsLoaded = true;
}

if (typeof adsEnabled !== 'undefined' && adsEnabled === true) {
  showAds();
}



// scroll to top button
$(window).scroll(function(){
    var fromTopPx = 200; // distance to trigger
    var scrolledFromtop = jQuery(window).scrollTop();
    if(scrolledFromtop > fromTopPx){
        $('#scroll-top-button').addClass('scrolled');
    }else{
        $('#scroll-top-button').removeClass('scrolled');
    }
});
$('#scroll-top-button').on('click',function(){
    jQuery("html, body").animate({ scrollTop: 0 }, 600);
    return false;
});


// insert at cursor
//
// @param object field: dom element
// @param string value: string value to insert
function insertAtCursor(field, value) {
  //IE support
  if (document.selection) {
    field.focus();
    sel = document.selection.createRange();
    sel.text = value;
  }
  //MOZILLA and others
  else if (field.selectionStart || field.selectionStart == '0') {
    var startPos = field.selectionStart;
    var endPos = field.selectionEnd;
    field.value = field.value.substring(0, startPos)
      + value
      + field.value.substring(endPos, field.value.length);
  } 
  else {
    field.value += value;
  }
}


// get cursor position
// 
// @param object element: dom element (of type input, textarea or so)
//
// @return int: the cursor position in the passed input field
function getCursorPosition(element) {
  var el = $(element).get(0);
  var pos = 0;
  if ('selectionStart' in el) {
    pos = el.selectionStart;
  } 
  else if ('selection' in document) {
    el.focus();
    var Sel = document.selection.createRange();
    var SelLength = document.selection.createRange().text.length;
    Sel.moveStart('character', -el.value.length);
    pos = Sel.text.length - SelLength;
  }
  return pos;
}


// set cursor position
//
// @param object element: dom element (of type input, textarea or so)
// @param int position: new position of the cursor
function setCursorPosition(elem, position) {

  if(elem != null) {
    if(elem.createTextRange) {
      var range = elem.createTextRange();
      range.move('character', position);
      range.select();
    }
    else {
      if(elem.selectionStart) {
        elem.focus();
        elem.setSelectionRange(position, position);
      }
      else
        elem.focus();
    }
  }
}


$(window).on('load', function() {
  $(window).on('scroll resize', function() {
    // word list shown
    if (false && $(window).width() > 700) {
      // TODO: intelligent scrolling
      if (SPA.shownPageName === 'word-lists' && SPA.shownSubPageName !== '') {
        if ($('#word-list-table').height() + $('#word-list-table').offset().top - $(window).scrollTop() - $(window).height() > 0) {
          var leftColumnElement = $('#word-lists-left-column'), titleElement = $('#word-list-title');

          var scrollTop = $(window).scrollTop(), elementOffsetTop = leftColumnElement.offset().top;
          var headNavHeight = $('#head-nav').height();


          var actualOffset = scrollTop - elementOffsetTop + headNavHeight;

          if (actualOffset >= 0) {
            if (actualOffset > parseInt(leftColumnElement.css('padding-top').replace('px', ''))) {
              // scrolling downwards
              /// ----->>>
              if (leftColumnElement.height() + leftColumnElement.offset().top <= scrollTop + $(window).height())
                leftColumnElement.css('padding-top', actualOffset + 'px');
            }
            else {
              // scrolling upwards
              leftColumnElement.css('padding-top', actualOffset + 'px');
            }
          }
          else {
            leftColumnElement.css('padding-top', '0px');
          }

          console.log(leftColumnElement.height());
          console.log($(window).height());
        }
      }
    }
    else {
      // mobile device
      $('#word-lists-left-column').css('padding-top', '0px');
    }

  });
});


// functions for buttons
var Button = {
  

  // set pending
  //
  // disables the button and sets the value to the in data-pending-value="" defined value
  // 
  // @param object btn: jQuery element object of a button
  setPending: function(btn) {
    btn.prop('disabled', true);
    if (btn.data('pending-value')) {
      btn.data('default-value', btn.attr('value'))
        .attr('value', btn.data('pending-value') + '...');
    }
  },


  // set default
  //
  // enables the button and sets the value to the in data-default-value="" defined value
  // 
  // @param object btn: jQuery element object of a button
  setDefault: function(btn) {
    btn.prop('disabled', false);

    if (btn.data('default-value')) {
      btn.attr('value', btn.data('default-value'));
    }
  }
};


// switch language link click event
$('.switch-lang-button').on('click', function(e) {
  e.preventDefault(); // don't do link action
  Cookie.set('lang', $(this).data('lang'), 365); // set language cookie
  location.reload(); // reload the page to update the language strings
});


// save text as file
//
// @param string text: file content
// @param string fileName: file name
function saveTextAsFile(text, fileName) {
  var textFileAsBlob = new Blob([text], {type:'text/plain'});
  var tmpDownloadLink = document.createElement("a");
  tmpDownloadLink.download = fileName;
  tmpDownloadLink.href = window.URL.createObjectURL(textFileAsBlob);
  tmpDownloadLink.click();
}


// get local id
//
// @return unsigned int: a incremented number (incremented once every function call)
var localId = 0;
function getLocalId() {
  return localId++; // return and increment
}


// Google analytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments);},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m);})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-37082212-1', 'auto');
ga('send', 'pageview');