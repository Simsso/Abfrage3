// single page app
var updatePageContent = function() {
    $('#main').children('div').hide(); // hide all pages
    $('li').removeClass('visited'); // unmark the navigation element
    
    // read page name from URL
    var pageName = (location.hash.slice(1).length == 0)?"home":location.hash.slice(1);
    
    if ($('#content-' + pageName).length == 0) { // given page doesn't exist
   		// forward to home page
   		pageName = "home";
    }
    
    // mark nav element as visisted (class active)
    $('.nav_' + pageName).addClass('visited');
    $('#content-' + pageName).show(); // show the div containing the requested page
}

// hashchange event listener
$(window).on('hashchange',function() {
    updatePageContent();
}); 

updatePageContent(); // update when loading the page