var SPA = {}; // namespace

// single page app
SPA.shownPageName = '';
SPA.shownHashName = '';
SPA.shownSubPageName = '';
SPA.pageTitle = {
  'home': constString['Home'],
  'query': constString['Test'],
  'word-lists': constString['Word_lists'],
  'user': constString['Users'],
  'settings': constString['Settings'],
  'about': constString['About'],
  'contact': constString['Contact'],
  'legal-info': constString['Legal_info]'],
  'tour': constString['Tour'],
  'login': constString['Login']
};

var page = {}, // stores all dom elements from not rendered sites
  pageElementsParent = document.getElementById('main');


// siaf
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
SPA.updatePageContent = function (firstCall) {
  if (typeof firstCall === 'undefined') firstCall = false;

  // read page name from URL
  var hash = location.hash.slice(2);
  var firstPart = hash.substring(0, (hash.indexOf("/") === -1) ? hash.length : hash.indexOf("/"));
  var subPageName = hash.substring(firstPart.length + 1, hash.length);
  var pageName = (firstPart.length === 0) ? "home" : firstPart;

  SPA.shownSubPageName = subPageName;

  if (pageName === '' || typeof page[pageName] === 'undefined') { // given page doesn't exist
    // forward to home page
    pageName = "home";
  }
  
  
  // update document title
  document.title = SPA.pageTitle[pageName] + ' - Abfrage3 (dev)';

  // if the hash hasn't changed at all do nothing
  if (!firstCall && SPA.shownHashName === hash) return;
  SPA.shownHashName = hash; // the hash has changed

  // allow sub pages to process the hash change
  $(window).trigger('page-' + pageName, [pageName, subPageName]);
  
  // don't touch the DOM if the page (first part of the has) is already shown
  if (SPA.shownPageName === pageName) return;
  
  if (SPA.shownPageName !== undefined && SPA.shownPageName !== '') {
    pageElementsParent.removeChild(page[SPA.shownPageName]);
  }
  
  SPA.shownPageName = pageName;
  $('li').removeClass('visited'); // unmark the navigation element
  
  // mark nav element as visisted (class active)
  $('.nav_' + pageName).addClass('visited');
  pageElementsParent.insertBefore(page[pageName], pageElementsParent.firstChild); // show the div containing the requested page
  window.scrollTo(0, 0); // scroll to the top
  
  $('#footer-wrapper, #cookie-header').show();

  // tell sub pages that the DOM has been loaded
  $(window).trigger('page-' + pageName + '-loaded', [pageName, subPageName]);
};

$(document).ready(function() {
  // hashchange event listener
  $(window).on('hashchange', SPA.updatePageContent);
  SPA.updatePageContent(true); // update when loading the page
});
