var SinglePageApplication = {}; // namespace

// single page app
var shownPageName, shownHashName, shownSubPageName,  
    pageTitle = {
      'home': 'Home',
      'query': 'Test',
      'word-lists': 'Word lists',
      'user': 'User',
      'settings': 'Settings',
      'about': 'About',
      'contact': 'Contact',
      'legal-info': 'Legal info',
      'tour': 'Tour',
      'login': 'Login'
    };

var page = {}, pageElementsParent = document.getElementById('main');
(function() {
  var jQueryPageElement = $('#main').children('div');
  for (var i = 0;  i < jQueryPageElement.length; i++) {
    var id = jQueryPageElement.eq(i).attr('id');
    if (id !== undefined && id.substring(0, 8) === 'content-') {
      page[id.substring(8)] = jQueryPageElement[i];
    }
  }

  for (var singlePage in page) {
    pageElementsParent.removeChild(page[singlePage]);
  }
})();


// update page content
//
// reads the current hash (the last part of the url behind the "#")
// checks if the hash is related to an existing page (e.g. "word-lists")
// shows the page if it isn't already
// since some page have sub-pages (like the "settings" page) it is necessary for those to trigger 
SinglePageApplication.updatePageContent = function () {

  // read page name from URL
  var hash = location.hash.slice(2);
  var firstPart = hash.substring(0, (hash.indexOf("/") === -1) ? hash.length : hash.indexOf("/"));
  var subPageName = hash.substring(firstPart.length + 1, hash.length);
  var pageName = (firstPart.length === 0) ? "home" : firstPart;

  shownSubPageName = subPageName;

  if (page[pageName] === undefined) { // given page doesn't exist
    // forward to home page
    pageName = "home";
  }
  
  
  // update document title
  document.title = pageTitle[pageName] + ' - Abfrage3';

  // if the hash hasn't changed at all do nothing
  if (shownHashName === hash) return;
  shownHashName = hash; // the hash has changed

  // allow sub pages to process the hash change
  $(window).trigger('page-' + pageName, [pageName, subPageName]);
  
  // don't touch the DOM if the page (first part of the has) is already shown
  if (shownPageName === pageName) return;
  
  if (shownPageName !== undefined) {
    pageElementsParent.removeChild(page[shownPageName]);
  }
  
  shownPageName = pageName;
  $('li').removeClass('visited'); // unmark the navigation element
  
  // mark nav element as visisted (class active)
  $('.nav_' + pageName).addClass('visited');
  pageElementsParent.insertBefore(page[pageName], pageElementsParent.firstChild); // show the div containing the requested page
  window.scrollTo(0, 0); // scroll to the top
  
  $('#footer-wrapper, #cookie-header').show();
};

$(document).ready(function() {
  // hashchange event listener
  $(window).on('hashchange', SinglePageApplication.updatePageContent);
  SinglePageApplication.updatePageContent(); // update when loading the page
});
