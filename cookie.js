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
        $('body').prepend('<div id="cookie-header" class="cookie-header" style="display: none; opacity: 0"><div class="content-width"><table><tr><td>This website uses cookies to ensure you get the best experience on my website. <a href="https://en.wikipedia.org/wiki/HTTP_cookie" target="_blank">Learn more.</a></td><td><input id="cookie-got-it-button" type="button" class="width-150 no-box-shadow" value="Got it!"/></td></tr></table></div></div>');
        
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