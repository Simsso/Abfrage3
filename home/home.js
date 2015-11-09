"use strict";

var Home = {};

$(window).on('page-user', function(event, pageName, subPageName) {
  // sub page user called
  //
});

// feed
Home.Feed = {};

// templates
Home.Feed.Template = {
  table: Handlebars.compile($(page['home']).find('#feed-table-template').html()),
  noContent: Handlebars.compile($(page['home']).find('#feed-no-content-template').html()),
  userAdded: Handlebars.compile($(page['home']).find('#feed-user-added-element-template').html()),
  listShared: Handlebars.compile($(page['home']).find('#feed-list-shared-element-template').html()),
  wordAdded: Handlebars.compile($(page['home']).find('#feed-word-added-element-template').html())
};

// feed HTML element
Home.Feed.domElement = $(page['home']).find('#feed'), 
    Home.Feed.since = -1; // show the feed since unix time (-1 = since last login)


// refresh feed
//
// downloads the feed data and calls the refresh DOM function
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param function|undefined callback: callback which will be called after finishing the Ajax-request and (!) updating the DOM
function refreshFeed(l) { Home.Feed.download(l); }
Home.Feed.download = function(showLoadingInformation, callback) {
  if (showLoadingInformation) {
    Home.Feed.domElement.html(loading);
  }

  // send request to get the feed
  jQuery.ajax('server.php', {
    data: {
      action: 'get-feed',
      since: Home.Feed.since
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // update local data base object with the feed information
    Database.feed = data;

    Home.Feed.updateDom();

    if (callback)
      callback(data);
  });
};


// update feed dom
//
// takes the local data base object and update the feed DOM elements to the respective values
Home.Feed.updateDom = function() {
  var data = Database.feed;

  // sort feed events by time
  data.events.sort(function(a, b) { 
    if (a.time < b.time) return -1; 
    if (a.time > b.time) return 1; 
    return 0;
  });
  
  
  var feedHtml = '';
  
  // go through the array the other way around to display newest first
  for (var i = data.events.length - 1; i >= 0; i--) { 
    var feedItem = data.events[i], info = data.events[i].info;
    feedItem.timeString = (new Date(feedItem.time * 1000)).toDefaultString();
    
    // depending on the type of the feed item show a different text and a different image
    switch (feedItem.type) {
      case 0: // user added
        feedHtml += Home.Feed.Template.userAdded({ feedItem: feedItem, info: info });
        break;
      case 1: // shared list
        info.editingPermissions = (info.permissions == 1) ? true : false;
        feedHtml += Home.Feed.Template.listShared({ feedItem: feedItem, info: info });
        break;
      case 2: // added word
        // info.amountString = info.amount.toEnglishString();
        info.amountString = info.amount;
        info.exactlyOneWord = (info.amount === 1);
        info.yourList = (info.list.creator === data.user);
        info.userAddedToTheirOwnList = (info.user.id === info.list.creator);
        feedHtml += Home.Feed.Template.wordAdded({ feedItem: feedItem, info: info });
        break;
    }
  }
  
  // nothing in the feed
  if (feedHtml.length === 0) 
    feedHtml = Home.Feed.Template.noContent();
  else {
    feedHtml = Home.Feed.Template.table({ 
      // use Handlebars.SafeString because feedHtml contains HTML-tags and SafeString makes sure that they will not be escaped
      tableBody: new Handlebars.SafeString(feedHtml) 
    });;
  }
  
  Home.Feed.domElement.html(feedHtml);
};


// button load whole feed event listener
$(page['home']).find('#feed-load-all').on('click', function() {
  Button.setPending($(page['home']).find('#feed-load-all'));
  Home.Feed.since = 0;
  Home.Feed.download(false, function() {
    $(page['home']).find('#feed-load-all').hide();
  });
});



// recently used
Home.RecentlyUsed = {};

// templates
Home.RecentlyUsed.Template = {
  noContent: Handlebars.compile($(page['home']).find('#recently-used-no-content-template').html()),
  table: Handlebars.compile($(page['home']).find('#recently-used-table-template').html())
};

Home.RecentlyUsed.domElement = $(page['home']).find('#recently-used');


// download recently used
//
// downloads the recently used lists and calls the DOM-update function
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param function|undefined callback: callback which will be called after finishing the Ajax-request and (!) updating the DOM
function refreshRecentlyUsed(l) { Home.RecentlyUsed.download(l); }
Home.RecentlyUsed.download = function(showLoadingInformation, callback) {
  if (showLoadingInformation) 
    Home.RecentlyUsed.domElement.html(loading);
  
  // send request to get the Feedd
  jQuery.ajax('server.php', {
    data: {
      action: 'get-last-used-n-lists',
      limit: 8
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    Database.recentlyUsed = data;

    Home.RecentlyUsed.updateDom();

    if (callback)
      callback(data);
  });
};


// update recently used list DOM element
Home.RecentlyUsed.updateDom = function() {
  var data = Database.recentlyUsed;

  // no recently used lists
  if (data.length === 0) {
    Home.RecentlyUsed.domElement.html(Home.RecentlyUsed.Template.noContent());
  }
  else {
    // update the DOM
    Home.RecentlyUsed.domElement.html(Home.RecentlyUsed.Template.table({ list: data }));

    // add event listener to make lists clickable (link to #/word-lists/xxx)
    Home.RecentlyUsed.domElement.find('tr').on('click', function() {
      window.location.href = '#/word-lists/' + $(this).data('list-id');
    });
  }
};


// add word list usage
Home.RecentlyUsed.addWordListUsage = function(listId) {
  // check if the recently used lists array contains the list
  for (var i = Database.recentlyUsed.length - 1; i >= 0; i--) {
    if (Database.recentlyUsed[i].id === listId) {
      Database.recentlyUsed.splice(i, 1);
      break;
    }
  }
  Database.recentlyUsed.unshift(Database.getListById(listId)); // push at the beginning of the array
  Home.RecentlyUsed.updateDom();
  
  jQuery.ajax('server.php', {
    data: {
      action: 'add-word-list-usage',
      word_list_id: listId
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
  });
};

// initial loading
Home.Feed.updateDom();
Home.RecentlyUsed.updateDom();