// single page app
var shownPageName = undefined, 
    shownHashName = undefined, 
    pageTitle = {
      'home': 'Home',
      'query': 'Test',
      'word-lists': 'Word lists',
      'user': 'User',
      'settings': 'Settings',
      'about': 'About',
      'contact': 'Contact',
      'legal-info': 'Legal info'
    };

var updatePageContent = function () {

  // read page name from URL
  var hash = location.hash.slice(2);
  var firstPart = hash.substring(0, (hash.indexOf("/") === -1) ? hash.length : hash.indexOf("/"));
  var subPageName = hash.substring(firstPart.length + 1, hash.length);
  var pageName = (firstPart.length === 0) ? "home" : firstPart;

  if ($('#content-' + pageName).length === 0) { // given page doesn't exist
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
  
  shownPageName = pageName;
  
  
  $('#main').children('div').hide(); // hide all pages
  $('li').removeClass('visited'); // unmark the navigation element
  
  // mark nav element as visisted (class active)
  $('.nav_' + pageName).addClass('visited');
  $('#content-' + pageName).show(); // show the div containing the requested page
  window.scrollTo(0, 0); // scroll to the top
  
  $('.advertisement-bottom, #footer-wrapper').show();
};

// hashchange event listener
$(window).on('hashchange', updatePageContent);

updatePageContent(); // update when loading the page
