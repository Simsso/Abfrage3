// single page app
var updatePageContent = function() {
    $('#main').children('div').hide();
    $('li').removeClass('visited');
    var pageName = (location.hash.slice(1).length == 0)?"home":location.hash.slice(1);
    $('#nav_' + pageName).addClass('visited');
    $('#content-' + pageName).show();
}

$(window).on('hashchange',function() {
    updatePageContent();
}); 

updatePageContent();