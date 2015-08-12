// string prototype
String.prototype.repeat = function(n) {
    if (n == 0) 
        return '';
    n= n || 1;
    return Array(n + 1).join(this);
}

Array.prototype.contains = function(obj) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
}
Array.prototype.remove = function(obj) {
    var index = this.indexOf(obj) != -1
    if (index != -1) {
        array.splice(i, 1);
    }
}
Array.prototype.removeAll = function(obj) {
    for(var i = 0; i < this.length; i++) {
	   if (this[i] === obj) {
           this.splice(i, 1);
       }
    }
}


// time
Date.prototype.toDefaultString = function() {
    return this.getDate() + "." + (this.getMonth()+1) + "." + this.getFullYear() + " " + this.getHours() + ":" + this.getMinutes() + ":" + this.getSeconds();
}

// box head right icons
$('.box .box-head img.box-head-right-icon').on('click', function() {
    switch($(this).data('action')) {
        case 'expand':
            $(this).data('action', 'collapse').attr('src', 'img/collapse.png').parent().next().show();
            break;
        case 'collapse':
            $(this).data('action', 'expand').attr('src', 'img/expand.png').parent().next().hide();
            break;
        case 'refresh':
            window[$(this).data('function-name')](true);
            break;
    }
}).show();

// mobile menu
var menuShown = false;
function toggleMenu() {
    $('.smaller-800, ul.nav').css({ 'display': (menuShown)?'none':'block' });
    menuShown = !menuShown;
}

$('#head-nav').append('<img src="img/menu.png" class="menu-button nav-image" style="background: url(\'img/menu.png\'); " onclick="toggleMenu()" />');


// loading animation
var loading = '<div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>' 
var loadingFullscreen = '<div class="sk-three-bounce fullscreen"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>' 


// toast
$('body').append('<div id="toast" style="transition: opacity 0s; position: fixed; bottom: 57px; left: calc(50% - 200px); width: 400px; color: white; padding: 10px; border: 1px solid #4F5B93; box-shadow: 1px 1px 3px #4F5B93; background-color: #8892BF; overflow: hidden; text-align: center; display: none; z-index: 1; "></div>');

function Toast(text, ms) {
    this.ms = ms;
    this.text = text;
    
    if (!ms)
        var ms = 5000; 
    $('#toast').promise().done(function() {
        $(this).html(text).fadeIn().delay(ms).fadeOut();
    });
}


// ajax request manager
function AjaxRequestsManager(onlyOne) {
    this.onlyOne = onlyOne;
    this.list = new Array();
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
    refreshListSharings: new AjaxRequestsManager(true)
};

$(document).ajaxError(function() {
    new Toast('An Ajax request failed.');
});


// screenshots
$('body').append('<table id="screenshot-popup-wrapper" style="z-index: 1000; height: 100%; width: 100%; position: fixed; top: 0px; left: 0px; display: none; background-color: rgba(0, 0, 0, 0.8); "><tbody><tr><td style="cursor: pointer; vertical-align: middle; text-align: center; "><img id="screenshot-popup-image" style="cursor: default; max-height: 80%; max-width: 80%; border: 1px solid black; box-shadow: 0 0 5px black; "><br><br><span style="color: white;" id="screenshot-description">Description</span></td></tr></tbody></table>');
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


// cookies
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
function readCookie(key)
{
    var result;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? (result[1]) : null;
}

setTimeout(function() {
    if (readCookie('accepted_cookies') != 'true') {
        $('body').prepend('<div id="cookie-header" class="cookie-header" style="display: none; opacity: 0"><div class="content-width"><table><tr><td>This website uses cookies to ensure you get the best experience on my website. <a href="https://en.wikipedia.org/wiki/HTTP_cookie" target="_blank">Learn more.</a></td><td><input id="cookie-got-it-button" type="button" class="width-110 no-box-shadow" value="Got it!"/></td></tr></table></div></div>');
        
        setTimeout(function() {
            $('#cookie-header').css('display', 'block');
            setTimeout(function() {
                $('#cookie-header').css('opacity', '1');
            },  1);
        }, 1500);
        
        $('#cookie-got-it-button').on('click', function() {
            $(this).prop('disabled', true);
            setCookie('accepted_cookies', 'true', 10000);
            $('#cookie-header').css('opacity', 0);
            setTimeout(function() { $('#cookie-header').remove(); }, 200);
        });
    }
}, 1000);


// save text as file
function saveTextAsFile(text, fileName) {
    var textFileAsBlob = new Blob([text], {type:'text/plain'});
    var tmpDownloadLink = document.createElement("a");
    tmpDownloadLink.download = fileName;
    tmpDownloadLink.href = window.URL.createObjectURL(textFileAsBlob);
    tmpDownloadLink.click();
}


// analytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-37082212-1', 'auto');
ga('send', 'pageview');