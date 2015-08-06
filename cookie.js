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
    if (true || readCookie('accepted_cookies') != 'true') {
        $('body').prepend('<div id="cookie-header" class="cookie-header"><div class="float-left">This website uses cookies to ensure you get the best experience on my website. <a href="https://en.wikipedia.org/wiki/HTTP_cookie" target="_blank">Learn more.</a></div><div><input id="cookie-got-it-button" type="button" class="inline float-right width-150 no-box-shadow" value="Got it!"/></div><br class="clear-both"></div>');
        
        $('#cookie-got-it-button').on('click', function() {
            $(this).prop('disabled', true)
            setCookie('accepted_cookies', 'true', 10000);
            $('#cookie-header').delay(1000).remove();
        });
    }
}, 1000);