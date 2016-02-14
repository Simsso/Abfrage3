"use strict";

var WordLists = {};

WordLists.Template = {
  // const strings
  noList: Handlebars.compile($(page['word-lists']).find('#word-lists-no-list-template').html()),
  noListSearch: Handlebars.compile($(page['word-lists']).find('#word-lists-no-list-search-template').html()),
  noWords: Handlebars.compile($(page['word-lists']).find('#word-lists-no-words-template').html()),
  noWordsNoEditingPermissions: Handlebars.compile($(page['word-lists']).find('#word-lists-no-words-no-editing-permissions-template').html()),
  noLabels: Handlebars.compile($(page['word-lists']).find('#word-lists-no-labels-template').html()),

  // list of word lists
  listOfWordLists: Handlebars.compile($(page['word-lists']).find('#word-lists-list-of-word-lists-template').html()),
  wordListTable: Handlebars.compile($(page['word-lists']).find('#word-lists-words-table-template').html()),
  wordListTableRow: Handlebars.compile($(page['word-lists']).find('#word-lists-words-table-row-template').html()),
  wordListsFolderView: Handlebars.compile($(page['word-lists']).find('#word-lists-folder-view-template').html()),

  listName: Handlebars.compile($(page['word-lists']).find('#word-lists-list-title-name-template').html()),
  // single list general information (owner, start test link, creation time, etc.)
  singleListGeneralInformation: Handlebars.compile($(page['word-lists']).find('#word-lists-single-list-general-information').html()),

  // list of words
  listOfWordsTable: Handlebars.compile($(page['word-lists']).find('#word-lists-words-table-template').html()),
  listOfWordsRow: Handlebars.compile($(page['word-lists']).find('#word-lists-words-table-row-template').html()),
  listOfWordsEditInput: Handlebars.compile($(page['word-lists']).find('#word-list-edit-word-input-template').html()),

  // labels
  labelTable: Handlebars.compile($(page['word-lists']).find('#word-lists-label-table-template').html()),
  labelList: Handlebars.compile($(page['word-lists']).find('#word-lists-label-list-template').html()),
  labelSingleListElement: Handlebars.compile($(page['word-lists']).find('#word-lists-label-single-list-element-template').html()),
  labelRenameInput: Handlebars.compile($(page['word-lists']).find('#word-lists-label-rename-input-template').html()),


  // share
  shareTable: Handlebars.compile($(page['word-lists']).find('#word-lists-share-table-template').html())
};

// single page application allows urls like
// ...#/word-lists/4 (<-- id)
$(window).on('page-word-lists', function(event, pageName, subPageName) {
  // sub page word-lists called

  var hashListId = parseInt(subPageName); // subPageName (last part of the url #/xxx/this) defines the id of the loaded list
  if (hashListId === WordLists.shownId) return; // no reason to touch the DOM because the requested list hasn't changed
  
  
  if (isNaN(hashListId)) { // no list id given via hash

    switch(subPageName.split('/')[0]) {
      case 'l': // hash determines label to display
        // show no list selected screen so that the things appear at all
        WordLists.showNoListSelectedScreen(false); // don't update the hash (parameter: false)

        var subPageParts = subPageName.split('/');
        var labelId = parseInt((subPageParts.length > 1) ? subPageParts[subPageParts.length - 1] : 0);
        if (isNaN(labelId)) labelId = 0; // no label ("root" label)

        var labelPath = subPageParts.slice(1); // hash part without the "l/" at the beginning
        // label path contains only ids; replace them with label objects
        for (var i = labelPath.length - 1; i >= 0; i--) {
          labelPath[i] = Database.getLabelById(labelPath[i]);
        }

        $(page['word-lists']).find('#list-of-word-lists').html(WordLists.Template.wordListsFolderView({
          list: WordLists.getListsOfLabel(labelId),
          label: WordLists.getDirectSubLabelsOfLabel(labelId),
          subPageName: subPageName,
          labelPath: labelPath // array of the label path objects (e.g. labels with ids 5/31/50) 
        }));

        // update link to word lists
        $('.link-to-show-current-word-list').attr('href', '#/word-lists/' + subPageName);
        break;

      case 's': // search
        // show no list selected screen so that the things appear at all
        // parameters: don't update the hash, don't update the list of word lists and don't select the search input fields content
        WordLists.showNoListSelectedScreen(false, false, true); 
        
        var subPageParts = subPageName.split('/');
        var searchString = "";
        if (subPageParts.length > 1) {
          searchString = unescape(subPageParts[1]);
          WordLists.search(searchString);
        }
        break;

      default: 
        WordLists.showNoListSelectedScreen(true);
        break;
    }
  }
  else {
    // a valid list id has been passed via url
    WordLists.show(parseInt(subPageName));
  }
}).on('page-word-lists-loaded', function(event, pageName, subPageName) {
  // finished loading event

  // to focus input fields there is no code like
  // $(page['word-lists']).find('#input...')
  // because an input can only be focused when it is in the DOM and not in the RAM only
  // and therefore above line doesn't make sense

  // focus input fields
  if (Database.lists.length) {
    // search list input, add new word input
    $('#word-lists-search, #words-add-language1').select();
  } else {
    $('#word-list-add-name').select();
  }
});


WordLists.shownId = -1; // stores the id of the word list which is shown at the moment (-1 if none)
WordLists.shown = null; // stores the data (words, creation date, etc.) of the word list which is shown at the moment (null if none)

WordLists.expandedLabelsIds = []; // stores which labels were expanded to expand them after refreshing the label HTML element



// word lists search
//
// @param string searchString: string to search word lists
WordLists.search = function(searchString) {
  // update search field value in case it isn't
  var searchField = $(page['word-lists']).find('#word-lists-search')
  if (searchField.val() != searchString) {
    searchField.val(searchString);
  }


  searchString = searchString.toLowerCase().trim();
  var data = Database.lists.sort(List.compareListsByName).slice();

  for (var i = 0; i < data.length; i++) {
    var name = data[i].name.toLowerCase().trim();
    if (name.length == name.replace(searchString, "").length) {
      // list name doesn't contain search string
      data.splice(i, 1);
      i--;
    }
  }

  var output;
  if (data.length === 0) {
    // no lists
    output = WordLists.Template.noListSearch();
  }
  else {
    // multiple lists: get HTML
    output = WordLists.Template.listOfWordLists({ list: data });
  }

  $(page['word-lists']).find('#list-of-word-lists').html(output); // update DOM with list of word lists

  WordLists.listOfWordListsAddEventListeners();
};


// adds a new word list to the data base
//
// @param string name: the name of the new word list
// @param function|undefined callback: callback which will be called after finishing the Ajax-request
WordLists.addNew = function(name, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'add-word-list',
      name: name
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // update local data base object
    var list = new List(
      data.id, 
      data.name, 
      data.creator, 
      data.comment, 
      data.language1,
      data.language2, 
      data.creation_time, 
      data.words);

    // since the user created the list it's sure that the permissions to edit and share exist
    list.allowEdit = true;
    list.allowSharing = true;
    list.labels = data.labels;
    Database.lists.push(list);

    callback(data);
  });
};


// event listener for form which adds new word lists to the data base
$(page['word-lists']).find('#word-list-add-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable text box to prevent resubmission
  $(page['word-lists']).find('#word-list-add-name').prop('disabled', true);

  // call the server contacting function
  WordLists.addNew($(page['word-lists']).find('#word-list-add-name').val(), function(data) {
    // finished callback
    // re-enable and the text box
    $(page['word-lists']).find('#word-list-add-name').prop('disabled', false).val('');

    // load the word list which has just been added
    WordLists.show(data.id);
  });
});



// download list of word lists
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param function|undefined callback: callback which will be called after finishing the list refresh
// @param bool|undefined firstCall: if set to false (which is also the default value if undefined is passed) the WordLists.showNoListSelectedScreen function will update the hash to "#/word-lists"
function refreshListOfWordLists(l) { WordLists.downloadListOfWordLists(l); }
WordLists.downloadListOfWordLists = function(showLoadingInformation, callback, firstCall) {
  if (firstCall === undefined) firstCall = false;

  // loading information
  if (showLoadingInformation)
    $(page['word-lists']).find('#list-of-word-lists').html(loading);

  // reset all table row highlights and hidden buttons indicating which list is selected
  WordLists.showNoListSelectedScreen(!firstCall, false);

  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.loadListOfWordLists.add(
    jQuery.ajax('server.php', {
      data: {
        action: 'get-query-lists-of-user'
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {    
      data = handleAjaxResponse(data);
      Database.lists = getListArrayByServerData(data);
      WordLists.updateListOfWordLists();
    })
  );
};


// word lists update list of word lists
// 
// takes the local data base object and adds the corresponding HTML-elements to the DOM
WordLists.updateListOfWordLists = function() {
  var data = Database.lists.sort(List.compareListsByName);

  var output;
  if (data.length === 0) {
    // no lists
    output = WordLists.Template.noList();
  }
  else {
    // multiple lists: get HTML
    output = WordLists.Template.listOfWordLists({ list: data });
  }

  $(page['word-lists']).find('#list-of-word-lists').html(output); // update DOM with list of word lists

  WordLists.listOfWordListsAddEventListeners();
};

// add event listeners to list of word lists
WordLists.listOfWordListsAddEventListeners = function() {

  // add event listeners for rows which have just been added
  $(page['word-lists']).find('#list-of-word-lists tr:not(:first-child)').on('click', function() {
    window.location.hash = '#/word-lists/' + $(this).data('list-id');
  });
};

// word lists search input field change event listener
$(page['word-lists']).find('#word-lists-search').on('change keyup paste', function(e) {
  // update hash
  var searchValue = $(this).val();

  if (searchValue) {
    window.location.hash = '#/word-lists/s/' + escape(searchValue);
  } 
  else {
    window.location.hash = '#/word-lists';
  }

  // enter
  if (e.keyCode === 13) {
    // open first list
    $(page['word-lists']).find('#list-of-word-lists tr').eq(1).trigger('click');
    $(page['word-lists']).find('#word-lists-search').val('');
  }
});


// show the information that no list is selected and update the vars
//
// @param bool updateHash: if set to true the hash will be updated to "#/word-lists"
// @param bool | undefined updateListOfWordLists: by default set to true; defines whether the function will be called
// @param bool | undefined dontFocusSearchField: defines whether the word lists search field will be focused or not (default false)
WordLists.showNoListSelectedScreen = function(updateHash, updateListOfWordLists, dontFocusSearchField) {
  if (typeof updateListOfWordLists === 'undefined') updateListOfWordLists = true;
  if (typeof dontFocusSearchField === 'undefined') dontFocusSearchField = false;

  WordLists.shownId = -1;
  WordLists.shown = null;

  if (updateListOfWordLists)
    WordLists.updateListOfWordLists();

  $('.link-to-show-current-word-list').attr('href', '#/word-lists');
  
  if (updateHash === true) {
    window.location.hash = '#/word-lists';
  }

  $(page['word-lists']).find('#word-list-info, #word-list-info-words, #word-list-title, #word-list-sharing, #word-list-label, #word-list-loading').hide();
  Scrolling.toTop();
  $(page['word-lists']).find('#list-of-word-lists-wrapper').show();
        
  // focus search field
  if (!dontFocusSearchField)
    $(page['word-lists']).find('#word-lists-search').select();
};



// load word list
//
// loads the word list from the data base and updates the local variables WordLists.shownId and WordLists.shown
// fills the divs Words, Sharing, Labels and General Information with HTML-content
// adds events to the new HTML-content of the divs mentioned above
//
// @param int id: the id of the requested word list
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param bool|undefined showWordListPage: defines whether the page "Word lists" should be shown
// @param function callback: callback when the data has been downloaded
WordLists.download = function(id, showLoadingInformation, showWordListPage, callback) {
  // show loading information
  if (showLoadingInformation) {

    // hide all divs which will later show things like words, sharings, labels and the list name while the list loads
    $(page['word-lists']).find('#word-list-info, #word-list-info-words, #word-list-sharing, #word-list-label, #word-list-title, #list-of-word-lists-wrapper').hide();

    // show loading div
    $(page['word-lists']).find('#word-list-loading').show();
  }
  
  // a call of this method can force to show the page "word lists" with the parameter showWordListPage
  if (showWordListPage === true) {
    if (window.location.hash !== '#/word-lists') {
      window.location.hash = '#/word-lists';
    }
  }

  WordLists.shownId = id;

  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.loadWordList.add(
    jQuery.ajax('server.php', {
      data: {
        action: 'get-word-list',
        word_list_id: id
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {    
      data = handleAjaxResponse(data);

      // the requested list is not available
      if (data === null) {
        callback(data);
        return;
      }

      // link loaded word list
      var listObject = getListObjectByServerData(data), linked = false;
      for (var i = Database.lists.length - 1; i >= 0; i--) {
        if (Database.lists[i].id === listObject.id) {
          Database.lists[i] = listObject;
          linked = true;
        }
      };
      if (!linked) Database.lists.push(listObject);

      callback(data);
    })
  );
};



// show word list
//
// shows the word list and loads it from the Database object
// so there is no Ajax-request
//
// to show a word list and load it from the server call WordLists.download method
// 
// @param unsigned int id: id of the word list
// @param bool | undefined addUsage: add a list usage entry to the data base (default true)
WordLists.show = function(id, addUsage) {
  if (typeof addUsage === 'undefined') addUsage = true;

  var data = null;
  for (var i = 0; i < Database.lists.length; i++) {
    if (Database.lists[i].id === id) {
      data = Database.lists[i];
    }
  }

  if (data === null) {
    // the list is not available offline
    WordLists.download(id, true, false, function(ajaxResponseData) {
      if (ajaxResponseData === null) {
        // the list is not available online
        WordLists.showNoListSelectedScreen(true); // update hash (true)
      }
      else {
        // the list has been downloaded
        // show it
        WordLists.show(id, addUsage);
      }
    });
    return;
  }

  Home.RecentlyUsed.addWordListUsage(id);

  // update the list data variable to the downloaded data
  WordLists.shown = data;
  WordLists.shownId = WordLists.shown.id;

  // update links and hash (url)
  $('.link-to-show-current-word-list').attr('href', '#/word-lists/' + WordLists.shownId);
  window.location.hash = '#/word-lists/' + WordLists.shownId;
  
  // list doesn't exist or no permissions or deleted
  if (WordLists.shown === null) {
    WordLists.showNoListSelectedScreen(true); // update hash to /#/word-lists because the list is not available
    return;

  }
  
  var allowEdit = WordLists.shown.allowEdit;
  var allowSharing = WordLists.shown.allowSharing;

  // because the default value of language1 and language2 in the data base is an empty string ("" not null) set it to "First language" and "Second language"
  // those vars are title of the bottom table, placeholder in the change language form and placeholder in the add new words form
  if (!WordLists.shown.language1) WordLists.shown.language1 = constString['First_language'];
  if (!WordLists.shown.language2) WordLists.shown.language2 = constString['Second_language'];

  // update list name box
  $(page['word-lists']).find('#word-list-title').html(WordLists.Template.listName({ name: WordLists.shown.name, allowSharing: allowSharing }));

  // info box body
  // add content depending on the users permissions (sharing and editing)
  var wordListInfoBoxBody = WordLists.Template.singleListGeneralInformation({
    allowSharing: allowSharing,
    list: WordLists.shown,
    allowEdit: allowEdit,
    creationTime: (new Date(parseInt(WordLists.shown.creationTime) * 1000)).toDefaultString()
  });

  $(page['word-lists']).find('#word-list-info .box-body').html(wordListInfoBoxBody); // update DOM


  $(page['word-lists']).find('#words-add-language1').attr('placeholder', WordLists.shown.language1);
  $(page['word-lists']).find('#words-add-language2').attr('placeholder', WordLists.shown.language2);

  // sharing box
  if (allowSharing) {
    // refresh sharing box with loading information
    WordLists.updateDomListSharings(true, WordLists.shown.id);
    $(page['word-lists']).find('#word-list-sharing').show();
  }
  else {
    $(page['word-lists']).find('#word-list-sharing').hide();
  }

  // list of words
  $(page['word-lists']).find('#shown-word-list-words-count').html(WordLists.shown.words.length); // update word count
  
  if (WordLists.shown.words.length === 0) { // no words added yet
    $(page['word-lists']).find('#words-in-list').html((allowEdit)?WordLists.Template.noWords():WordLists.Template.noWordsNoEditingPermissions());
  }
  else {
    // add words of the list to the DOM
    var wordListHTML = "";
    for (var i = WordLists.shown.words.length - 1; i >= 0; i--) {
      wordListHTML += WordLists.getTableRowOfWord(WordLists.shown.words[i].id, WordLists.shown.words[i].language1, WordLists.shown.words[i].language2, WordLists.shown.words[i].comment, allowEdit);
    }
    wordListHTML = WordLists.getTableOfWordList(wordListHTML, allowEdit, WordLists.shown.language1, WordLists.shown.language2);
    $(page['word-lists']).find('#words-in-list').html(wordListHTML);
  }

  // events
  // delete word list
  $(page['word-lists']).find('#delete-shown-word-list').on('click', function() {
    // show message box
    var messageBox = new MessageBox();
    messageBox.setTitle('Delete word list');
    messageBox.setContent('Do you want to delete the word list <span class="italic">' + WordLists.shown.name + '</span>?');
    messageBox.setButtons(MessageBox.ButtonType.YesNoCancel);
    messageBox.setFocusedButton('No');
    messageBox.setCallback(function(button) {
      if (button === 'Yes') {
        Button.setPending($(this));

        // call delete word list function and pass id of the list which will be deleted
        WordLists.deleteWordList(WordLists.shownId, function() {
          WordLists.showNoListSelectedScreen(true); // show the message that no list is shown at the moment
        });
      }
    });
    messageBox.show();
  });

  // hide word list (stop sharing)
  $(page['word-lists']).find('#hide-shown-word-list').on('click', function() {
    // show message box
    var messageBox = new MessageBox();
    messageBox.setTitle('Hide word list');
    messageBox.setContent('Do you want to hide the word list <span class="italic">' + WordLists.shown.name + '</span>?');
    messageBox.setButtons(MessageBox.ButtonType.YesNoCancel);
    messageBox.setFocusedButton('No');
    messageBox.setCallback(function(button) {
      if (button === 'Yes') {
        Button.setPending($(this)); // disable button
        var sharingId = $(page['word-lists']).find('tr[data-list-id=' + WordLists.shownId + ']').data('sharing-id');
        // send server request to hide the shared list
        WordLists.setSharingPermissionsBySharingId(sharingId, 0, function() {
          // because the shown list has just been removed update the screen to show the appropriate message
          WordLists.showNoListSelectedScreen(true);
        });
      }
    });
    messageBox.show();
  });

  // rename form
  $(page['word-lists']).find('#word-list-title-name').on('keydown', function(e) {
    if (e.keyCode !== 13) return; // listen for enter key

    e.preventDefault();

    var newListName = $(this).text().trim();
    // send information to the server
    WordLists.renameList(WordLists.shownId, newListName, function(data) {
      // update local list object
      WordLists.shown.name = newListName;

      // update the information where the list name was shown
      $(page['word-lists']).find('#list-of-word-lists-row-' + WordLists.shownId).children().first().html(newListName); // inside the list of word lists
    });

    // focus new word input
    $(page['word-lists']).find('#words-add-language1').val('').focus();
  });

  // change language form event listener
  $(page['word-lists']).find('#change-language-form').on('submit', function(e) {
    e.preventDefault();

    // disable inputs and button
    var lang1Input = $(page['word-lists']).find('#word-list-language1'), lang2Input = $(page['word-lists']).find('#word-list-language2'), submitButton = $(page['word-lists']).find('#word-list-languages-button');
    lang1Input.prop('disabled', true);
    lang2Input.prop('disabled', true);
    Button.setPending(submitButton);

    // read string values
    var lang1 = lang1Input.val(), lang2 = lang2Input.val();

    // send information to the server
    WordLists.setWordListLanguages(WordLists.shownId, lang1, lang2, function() {

      // re-enable inputs and buttons
      lang1Input.prop('disabled', false);
      lang2Input.prop('disabled', false);
      Button.setDefault(submitButton);

      // update local list object
      WordLists.shown.language1 = lang1;
      WordLists.shown.language2 = lang2;

      // update the information where the list languages were shown
      // placeholder of word add form
      $(page['word-lists']).find('#words-add-language1').attr('placeholder', lang1);
      $(page['word-lists']).find('#words-add-language2').attr('placeholder', lang2);
      // word list table head
      $(page['word-lists']).find('#word-list-table').find('td').eq(0).html(lang1);
      $(page['word-lists']).find('#word-list-table').find('td').eq(1).html(lang2);
    });

    // focus new word input
    $(page['word-lists']).find('#words-add-language1').val('').focus();
  });

  
  // set input fields not to comfortable mode
  $(page['word-lists']).find('#words-add-form').removeClass('comfortable');
  $(page['word-lists']).find('#words-add-form input[type=text]').val(''); // remove old content from add new word input field
  
  // hide divs
  $(page['word-lists']).find('#list-of-word-lists-wrapper, #word-list-loading').hide();

  Scrolling.toTop();
  // show divs which have been updated above
  $(page['word-lists']).find('#word-list-info, #word-list-title, #word-list-info-words').show();

  if (allowEdit)
    $(page['word-lists']).find('#words-add').show();
  else
    $(page['word-lists']).find('#words-add').hide();

  // update label list
  WordLists.updateDomLabelList();
  $(page['word-lists']).find('#word-list-label').show();


  // focus new word input field
  focusNewWordInput();
};


// focus new word input
// focuses the first input field to allow swift adding of new words
function focusNewWordInput() {
  $(page['word-lists']).find('#words-add-language1').focus();
}


// get table row of word
//
// @param unsigned int | undefined id: id of the word; no id="xxx" attribute will be assigned when passing undefined
// @param string lang1: first language
// @param string lang2: second language
// @param string comment: comment to the word
// @param bool allowEdit: determines whether to show edit and remove icons (true) or not (false)
// @param bool | undefined: whether the word is currently being uploaded
//
// @return string: the HTML of a single word row
WordLists.getTableRowOfWord = function(id, lang1, lang2, comment, allowEdit, pending) {
  if (typeof pending === 'undefined') pending = false;
  var showComment = (typeof comment !== 'undefined');
  return WordLists.Template.listOfWordsRow({ 
    id: id, 
    lang1: lang1, lang2: lang2, 
    comment: comment, 
    allowEdit: allowEdit, 
    pending: pending,
    showComment: showComment
  });
};


// get table of word list
//
// @return string: the HTML table around a given content of the word list
WordLists.getTableOfWordList = function(content, allowEdit, lang1, lang2) {
  return WordLists.Template.listOfWordsTable({ 
    content: new Handlebars.SafeString(content), 
    allowEdit: allowEdit, 
    lang1: lang1, lang2: lang2 
  });
};


// get direct sub labels of label
//
// @param unsigned int labelId: label id (can be zero: no label; root label)
//
// @return Label[]: array of labels which are sub label of the passed label
WordLists.getDirectSubLabelsOfLabel = function(labelId) {
  var labels = [];
  for (var i = Database.labels.length - 1; i >= 0; i--) {
    if (Database.labels[i].parent_label === labelId) {
      labels.push(Database.labels[i]);
    }
  }
  return labels;
};


// get lists of label
//
// @param unsigned int labelId: label id (can be zero: no label; root label)
//
// @return List[]: array of lists which are have been assigned to the passed label
WordLists.getListsOfLabel = function(labelId) {
  var lists = [];
  for (var i = Database.label_list_attachments.length - 1; i >= 0; i--) {
    if (Database.label_list_attachments[i].label === labelId) {
      var list = Database.getListById(Database.label_list_attachments[i].list);
      if (list) { // list could be undefined 
        lists.push(list);
      }
    }
  }

  // add lists without any label when the root label is requested
  if (labelId === 0) {
    lists.pushElements(WordLists.getListsWithoutAnyLabelAttachment());
  }
  return lists;
};


// get lists without any label attachment
//
// @return List[]: array of lists which are not connected to any label
WordLists.getListsWithoutAnyLabelAttachment = function() {
  var lists = [];
  for (var i = Database.lists.length - 1; i >= 0; i--) {
    var listHasLabel = false;
    for (var j = Database.label_list_attachments.length - 1; j >= 0; j--) {
      if (Database.label_list_attachments[j].list === Database.lists[i].id) {
        listHasLabel = true;
      }
    }

    if (!listHasLabel) {
      lists.push(Database.lists[i]);
    }
  }
  return lists;
};


// export word list
//
// under development
//
// @param List list: list object
WordLists.exportList = function(list) {
  if (list === undefined)
    list = WordLists.shown;

  var output = "";

  // convert the word list into a string
  for (var i = 0; i < list.words.length; i++) {
    // use "|" as separator between the two languages
    output += list.words[i].language1 + " | " + list.words[i].language2 + "\n";
  }

  // save the text
  // saveTextAsFile(output, list.name + '.txt');
  // TODO
};


// edit save word button click event
//
// in the word list the user has the possibility to edit single words inline
// editing and saving the word can be done with the same button
// the WordLists.editOrSaveWordEvent function is the event listener of the related form
// the information whether the line is currently in edit or save mode is stored in a data attribute of the button (data('action'))
// it can have the values 'edit' which means that the user wants to edit the word by clicking the button
// the value 'save' means that the user has added the word and wants to save the changes by clicking
//
// @param object event: event data
// @param unsigned int: id of the word to edit or save
WordLists.editOrSaveWordEvent = function(event, id) {
  event.preventDefault(); // stop form submission

  // jQuery vars of the important elements
  var row = $(page['word-lists']).find('#word-row-' + id); // the HTML row (<tr>)
  var editSaveButton = row.find('input[type=submit]'); // the button (<input type="button"/>)
  var cell1 = row.children().eq(0), cell2 = row.children().eq(1), cell3 = row.children().eq(2); // the first cell in the words table row (<td>)

  // edit button
  if (editSaveButton.data('action') == 'edit') { // edit mode
    row.addClass('show-icons');

    // update the buttons value
    editSaveButton.data('action', 'save').removeClass('pencil').addClass('check');

    // replace the words meanings with text boxes containing the meanings as value="" to allow editing by the user
    cell1.html(WordLists.Template.listOfWordsEditInput({ id: id, value: cell1.html(), name: 'language1' }));
    cell2.html(WordLists.Template.listOfWordsEditInput({ id: id, value: cell2.html(), name: 'language2' }));
    cell3.html(WordLists.Template.listOfWordsEditInput({ id: id, value: cell3.html(), name: 'comment' }));
  }

  // save button
  else {
    // disable the form elements
    var lang1Input = $(page['word-lists']).find('#word-edit-input-language1-' + id), lang2Input = $(page['word-lists']).find('#word-edit-input-language2-' + id), commentInput = $(page['word-lists']).find('#word-edit-input-comment-' + id);
    lang1Input.prop('disabled', true);
    lang2Input.prop('disabled', true);
    commentInput.prop('disabled', true);
    editSaveButton.prop('disabled', true).removeClass('check').addClass('upload');

    // send updated word information to the server
    WordLists.updateWord(id, lang1Input.val(), lang2Input.val(), commentInput.val(), function() {
      row.removeClass('show-icons');
      // reset the table row (hide the input fields and re-enable the edit button)
      editSaveButton.prop('disabled', false).data('action', 'edit').removeClass('upload').addClass('pencil');
      cell1.html(lang1Input.val());
      cell2.html(lang2Input.val());
      cell3.html(commentInput.val());
    });


    // focus new word input
    $(page['word-lists']).find('#words-add-language1').val('').focus();
  }
};


// update word
//
// saves changed made to a word into the database online and the local Database object
//
// @param int id: id of the word
// @param string lang1: first language
// @param string lang2: second language
// @param string comment: comment to the word
// @param function callback: called with the server response data after sending the Ajax-request
WordLists.updateWord = function(id, lang1, lang2, comment, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'update-word',
      word_id: id,
      lang1: lang1,
      lang2: lang2,
      comment: comment
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
      data = handleAjaxResponse(data);
      
      for (var i = 0; i < Database.lists.length; i++) {
        for (var j = Database.lists[i].words.length - 1; j >= 0; j--) {
          if (Database.lists[i].words[j].id === id) {
            Database.lists[i].words[j].language1 = lang1;
            Database.lists[i].words[j].language2 = lang2;
            Database.lists[i].words[j].comment = comment;
          }
        };
      }

      callback(data);
  });
};


// set word list languages
//
// the user has the ability to define the language of the words stored in a word list
// this function saves the language in the data base
//
// @param int id: id of the word list
// @param string lang1: the first language of the list
// @param string lang2: the second language of the list
// @param function callback: called with the server response data after sending the Ajax-request
WordLists.setWordListLanguages = function(id, lang1, lang2, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'set-word-list-languages',
      word_list_id: id,
      lang1: lang1,
      lang2: lang2
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    data = handleAjaxResponse(data);

    // update local object
    var list = Database.getListById(id);
    list.language1 = lang1;
    list.language2 = lang2;

    callback(data);
  });
};


// remove word
// 
// removes a single word from a word list
//
// @param int id: id of the word to remove
WordLists.removeWord = function(id) {
  var listId = WordLists.shownId; // store the id of the list of the word which will be removed
  
  // update button
  // TODO: use class to disable row and reenable it when an error occurs
  var row = $(page['word-lists']).find('#word-row-' + id).css('opacity', '0.5');
  var removeButton = row.find('* input[type=button]');
  removeButton.prop('disabled', true);

  // send message to server
  jQuery.ajax('server.php', {
    data: {
      action: 'remove-word',
      word_id: id
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {  
    data = handleAjaxResponse(data);
    
    // update local object
    var list = Database.getListById(listId);
    for (var i = 0; i < list.words.length; i++) {
      if (list.words[i].id === id) {
        list.words.splice(i, 1);
        break;
      }
    }
    
    // the user has loaded another list while the word was removed
    // there is no need to update the DOM (remove the row of the words table)
    if (WordLists.shownId !== listId) return; 
    
    $(page['word-lists']).find('#shown-word-list-words-count').html(WordLists.shown.words.length); // update word count


    // remove the row of the removed word from the DOM
    row.remove();

    // show special message if no word is left
    if ($(page['word-lists']).find('#word-list-table tr').length == 1) {
      $(page['word-lists']).find('#word-list-table').html(WordLists.Template.noWords());
    }
  });
};


// delete word list
//
// deletes a word list from the data base
// 
// @param int id: id of the word list to remove
// @param function callback: called with the server response data after sending the Ajax-request
WordLists.deleteWordList = function(id, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'delete-word-list',
      word_list_id: id
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    data = handleAjaxResponse(data);

    // update local object
    for (var i = Database.lists.length - 1; i >= 0; i--) {
      if (Database.lists[i].id === id) {

        // recently used
        Database.recentlyUsed.remove(Database.lists[i]); // delete from recently used array
        Home.RecentlyUsed.updateDom(); // update dom

        // remove list object from data base array
        Database.lists.splice(i, 1);
        break;
      }
    };


    // remove the word list row from the DOM
    $(page['word-lists']).find('#list-of-word-lists-row-' + id).remove();

    // no list table row anymore (except from the th)
    if ($(page['word-lists']).find('#list-of-word-lists tr').length == 1) {
      $(page['word-lists']).find('#list-of-word-lists').html(WordLists.Template.noList());
    }


    callback(data);
  });
};


// add word input change event listener
// goes into comfortable display mode (bigger input fields) if the user wants to add long words
$(page['word-lists']).find('#words-add-language1, #words-add-language2').on('keydown', function() {
  if (this.scrollWidth > $(this).innerWidth()) {
    // input field has a scrollbar
    $(page['word-lists']).find('#words-add-form').addClass('comfortable');
  }
});


// add new word form submit event listener
$(page['word-lists']).find('#words-add-form').on('submit', function(e) {
  e.preventDefault();

  // read input fields
  var lang1 = $(page['word-lists']).find('#words-add-language1').val(), lang2 = $(page['word-lists']).find('#words-add-language2').val(), comment = $(page['word-lists']).find('#words-add-comment').val();

  if (lang1.length === 0 && lang2.length === 0 && comment.length === 0) {
    // empty word (no lang1, lang2 and comment)

    // inform the user
    var mb = new MessageBox();
    mb.setTitle('Empty word');
    mb.setContent('You can\'t add empty words');
    mb.setFocusedButton('Ok');
    mb.show();

    // abort adding the word
    return;
  }

  // clear input fields and focus the first one to allow the user to enter the next word immediately
  $(page['word-lists']).find('#words-add-language1').val('').focus();
  $(page['word-lists']).find('#words-add-language2').val('');
  $(page['word-lists']).find('#words-add-comment').val('');

  // send word to the server
  WordLists.addWordToShownList(lang1, lang2, comment, true);
});


// add word
// 
// adds a new word the the shown list
//
// @param string lang1: first language
// @param string lang2: second language
// @param string comment: additional comment to the word
// @param bool allowEdit: information whether the user is allowed to edit (necessary to add the word <tr> element with or without Edit and Delete button)
WordLists.addWordToShownList = function(lang1, lang2, comment, allowEdit) {
  var listId = WordLists.shownId;
  var list = Database.getListById(listId);

  // check for a word with the same meaning in lang1 or lang2
  var messageBox = null, word = null;
  for (var i = 0; i < list.words.length; i++) {
    // make sure that empty meanings don't trigger a message box
    if (list.words[i].language1 == lang1 && lang1 || list.words[i].language2 == lang2 && lang2) {
      messageBox = new MessageBox();
      word = list.words[i];
    }
  }

  var sendServerRequest = function() {

    // update the word list table
    if ($(page['word-lists']).find('#word-list-table').length === 0) { 
      // no words added yet
      var wordListHTML = WordLists.getTableOfWordList("", allowEdit, WordLists.shown.language1, WordLists.shown.language2);
      $(page['word-lists']).find('#words-in-list').html(wordListHTML);
    }

    // add word row to the list of words
    var tmpId = getLocalId();
    $(page['word-lists']).find('#word-list-table tr:nth-child(1)').after(WordLists.getTableRowOfWord('pending-' + tmpId, lang1, lang2, comment, allowEdit, true));

    jQuery.ajax('server.php', {
      data: {
        action: 'add-word',
        word_list_id: listId,
        lang1: lang1,
        lang2: lang2,
        comment: comment
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {
      data = handleAjaxResponse(data);

      // mark word row as uploaded and change ids
      $(page['word-lists']).find('#word-row-pending-' + tmpId).attr('id', 'word-row-' + data).removeClass('pending');
      $(page['word-lists']).find('#word-row-' + data).find('input.rubbish').attr('onclick', 'WordLists.removeWord(' + data + ')');
      $(page['word-lists']).find('#word-row-' + data).find('input[type="submit"]').attr('form', 'word-row-' + data + '-form');
      $(page['word-lists']).find('#word-row-pending-' + tmpId + '-form').attr('id', 'word-row-' + data + '-form').attr('onsubmit', 'WordLists.editOrSaveWordEvent(event, ' + data + ')');
      
      // update local object of the edited list
      Database.getListById(listId).words.push(new Word(data, listId, lang1, lang2, comment, []));


      if (WordLists.shownId !== listId) return; // the user has loaded another list while the word was added

      // update word count
      $(page['word-lists']).find('#shown-word-list-words-count').html(WordLists.shown.words.length); 
    });
  };


  if (messageBox === null) {
    // no similar word has been found
    sendServerRequest();
  }
  else {
    // inform the user that the list contains a similar word
    messageBox.setTitle('Similar word found');
    messageBox.setContent('This word list already contains the word:<br><br><i>' + word.language1 + ' - ' + word.language2 + '</i><br><br>Do you want to add the word (<i>' + lang1 + ' - ' + lang2 + '</i>) though?');
    messageBox.setButtons(MessageBox.ButtonType.YesNoCancel);
    messageBox.setFocusedButton('Yes');
    messageBox.setCallback(function(button) {
      switch(button) {
        case 'Yes':
          // user wants to add the word nevertheless
          sendServerRequest();
          break;
        default:
          break;
      }
    });
    messageBox.show();
  }
};



// refresh list sharings
//
// refreshes the list of people who can see or edit the list
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param int|undefined wordListId: id of the word list for which the information will be requested
function refreshListSharings(l) { WordLists.downloadListSharings(l); }
WordLists.downloadListSharings = function(showLoadingInformation, wordListId) {
  // set id parameter to the shown list id if undefined has been passed
  if (wordListId === undefined)
    wordListId = WordLists.shownId;

  // show loading information
  $(page['word-lists']).find('#word-list-sharing').show();
  if (showLoadingInformation) {
    $(page['word-lists']).find('#list-sharings').html(loading);
  }


  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.downloadListSharings.add(
    jQuery.ajax('server.php', {
      data: {
        'action': 'get-sharing-info-of-list',
        'word_list_id': wordListId
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {    
      data = handleAjaxResponse(data);

      Database.getListById(wordListId).sharings = data;
      WordLists.updateDomListSharings();
    })
  );
};


// update dom list sharings
WordLists.updateDomListSharings = function() {
  var data = Database.getListById(WordLists.shownId).sharings;
  
  $(page['word-lists']).find('#list-sharings').html(WordLists.Template.shareTable({ share: data })); // display the output string

  // event listeners for the buttons just added
  // stop sharing button
  $(page['word-lists']).find('#list-sharings input[type=button]').on('click', function() {

    var button = $(this);
    Button.setPending(button); // change button value and disable button

    // send message to server to stop sharing of the list
    WordLists.setSharingPermissionsBySharingId(button.data('sharing-id'), 0, function() {

      // remove the row from the table
      $(page['word-lists']).find('#list-shared-with-row-' + button.data('sharing-id')).remove();

      // still rows left?
      if ($(page['word-lists']).find('#list-sharings tr').length == 1) {
        $(page['word-lists']).find('#list-sharings').html(WordLists.Template.notShared());
      }
    });
  });
};


// share list with another user form submit event listener
$(page['word-lists']).find('#share-list-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable form elements
  $(page['word-lists']).find('#share-list-other-user-email').prop('disabled', true);
  $(page['word-lists']).find('#share-list-permissions').prop('disabled', true);
  Button.setPending($(page['word-lists']).find('#share-list-submit'));

  // send message to server
  var email = $(page['word-lists']).find('#share-list-other-user-email').val().trim();
  WordLists.setSharingPermissions(
    WordLists.shownId, // list id
    email, // email of the user to share the list with
    $(page['word-lists']).find('#share-list-permissions').val(), // permissions
    function(data) { // finished callback

      // re-enable the form elements
      $(page['word-lists']).find('#share-list-other-user-email').prop('disabled', false).val('');
      $(page['word-lists']).find('#share-list-permissions').prop('disabled', false);
      Button.setDefault($(page['word-lists']).find('#share-list-submit'));

      // refresh the list of sharings without loading information
      WordLists.downloadListSharings(false, WordLists.shownId);
      
      // user doesn't exist
      if (data.set_permissions === -1) {
        var messageBox = new MessageBox();
        messageBox.setTitle('Not shared');
        messageBox.setContent('Found no user with the given email-address (<span class="italic">' + email + '</span>).');
        messageBox.setFocusedButton('Ok');
        messageBox.show();
      }
      // user hasn't added you
      // not working yet
      // TODO
      /*else if (!data.user_has_added_you) {
        var messageBox = new MessageBox();
        messageBox.setTitle('Not shared yet');
        messageBox.setContent('The other user can\'t see the list until they adds you in the Users section.');
        messageBox.show();
      }*/
    }
  );
});


// set sharing permissions by sharing id
// 
// set the sharing permissions by sharing id
// e.g. to delte a list sharing with a user set the permissions to 0
//
// @param int sharingId: the id of the sharing (primary index in share data base table)
// @param byte permissions: new permissions (can edit or view)
// @param function callback: called with the server response data after sending the Ajax-request
WordLists.setSharingPermissionsBySharingId = function(sharingId, permissions, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'set-sharing-permissions-by-sharing-id',
      sharing_id: sharingId,
      permissions: permissions
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    data = handleAjaxResponse(data);

    // update the box which shows recently used word lists
    // it could be the case that the function call removed one list for the user and therefore a list entry of the recently used lists has to be removed as well
    refreshRecentlyUsed(false);

    // callback with server response data
    callback(data);
  });
};


// set sharing permissions
//
// set sharing permissions of a list by email of other user
//
// @param int listId: id of the list which will be shared
// @param string email: email address of the other user (who will see the list)
// @param byte permissions: permissions for the other user (nothing, view or edit)
// @param function callback: called with the server response data after sending the Ajax-request
WordLists.setSharingPermissions = function(listId, email, permissions, callback) {
  // parameters: list of the id which will be shared, other user eamil, permissions (can edit or view), callback function
  jQuery.ajax('server.php', {
    data: {
      action: 'set-sharing-permissions',
      word_list_id: listId,
      email: email,
      permissions: permissions
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    data = handleAjaxResponse(data);

    callback(data);
  });
};



// label functions
//
// get label list
//
// downloads the label list of the user and updates the DOM
// updating the DOM is complex because of the sub-label structure
// adds event listeners to added HTML-elements
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function getLabelList(l) { WordLists.downloadLabelList(l); }
WordLists.downloadLabelList = function(showLoadingInformation) {
  if (showLoadingInformation)
    $(page['word-lists']).find('#list-labels-list').html(loading);

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-labels-of-user'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    Database.labels = handleAjaxResponse(data);

    WordLists.updateDomLabelList();
  });
};


// update dom label list
WordLists.updateDomLabelList = function() {
  $(page['word-lists']).find('#list-labels-list').html(WordLists.getEditableHtmlTableOfLabels(Database.labels)); // update DOM
  
  
  // open small menu for single label event trigger
  $(page['word-lists']).find('#list-labels-list img.small-menu-open-image').on('click', function(e) {
    $(page['word-lists']).find('.small-menu').addClass('display-none');
    $(this).next().removeClass('display-none');
    e.stopPropagation(); // prevent triggering click event on body which listens for click to close the popup
  });
  $(page['word-lists']).find('#list-labels-list .small-menu input').on('click', function(e) {
    $(this).parents('.small-menu').addClass('display-none');
  });
  $(page['word-lists']).find('#list-labels-list .small-menu').on('click', function(e) { e.stopPropagation(); }); // prevent triggering click event on body which listens for click to close the popup

  // just added checkboxes event listener
  // the checkboxes allow the user to attach the list to a label by checking the checkbox
  $(page['word-lists']).find('#list-labels-list input[type=checkbox]').click( function(){
    // read label id from checkbox data tag
    var labelId = $(this).data('label-id');

    // checkbox has been checked
    if($(this).is(':checked')) { // add list to label
      WordLists.attachListToLabel(labelId, WordLists.shownId, function() {
        // update list object by adding the label
        WordLists.shown.labels.push(Database.labels[WordLists.getLabelIndexByLabelId(Database.labels, labelId)]);
      });
    }
    // checkbox has been unchecked
    else { // detach list from label
      WordLists.detachListFromLabel(labelId, WordLists.shownId, function() {
        // update list object by removing the label
        WordLists.shown.labels.splice(WordLists.getLabelIndexByLabelId(WordLists.shown.labels, labelId), 1);
      });
    }
  });


  // add new label form event listener
  $(page['word-lists']).find('.label-add-form').on('submit', function(e) {
    e.preventDefault();

    // disable form elements
    var button = $(this).children('.label-add-button');
    Button.setPending(button);
    var nameInput = $(this).children('.label-add-name').prop('disabled', true);
    var parentSelect = $(this).children('.label-add-parent').prop('disabled', true);

    WordLists.expandedLabelsIds.push(parseInt(parentSelect.val())); // expand parent label of newly added label
    
    // send message to the server
    WordLists.addNewLabel(nameInput.val(), parseInt(parentSelect.val()), function(data) {

      // re-enable form elements
      Button.setDefault(button);
      nameInput.prop('disabled', false).val('');
      parentSelect.prop('disabled', false).val(null);
    });
  });

  // remove label form submit event listener
  $(page['word-lists']).find('.label-remove-form').on('submit', function(e) {
    e.preventDefault();

    // update form children
    $(this).children('.label-remove-select').prop('disabled', true);
    Button.setPending($(this).children('.label-remove-button'));

    var labelId = parseInt($(this).children('.label-remove-select').val()); // read label id

    // remove label server request
    WordLists.removeLabel(labelId, function() {
      // re-enable form children
      $(this).children('.label-remove-select').prop('disabled', false);
      Button.setDefault($(this).children('.label-remove-button'));

      // update local list object
      WordLists.shown.labels.splice(WordLists.getLabelIndexByLabelId(WordLists.shown.labels, labelId), 1);
    });
  });

  // add sub label event listener
  $(page['word-lists']).find('.label-add-sub-label').on('click', function() {
    // show the "add sub label form" which is hidden in the following <tr>
    $(this).hide().parent().parent().parent().next().show().children().find('input[type=text]').first().focus();
  });

  // label rename form event listener
  $(page['word-lists']).find('.label-rename-form').on('submit', function(e) {
    e.preventDefault();

    // get label id from data tag of the form
    var labelId = $(this).data('label-id');
    var button = $(page['word-lists']).find('#label-rename-button-' + labelId);
    var firstCell = $(page['word-lists']).find('#label-rename-table-cell-' + labelId);

    // edit name
    if (button.data('action') == 'rename-edit') {
      var labelName = Database.labels[WordLists.getLabelIndexByLabelId(Database.labels, labelId)].name;
      firstCell.find('label span').html('');
      firstCell.append(WordLists.Template.labelRenameInput({ labelName: labelName, labelId: labelId }));
      button.data('action', 'rename-save');
    }

    // submit edits
    else {
      var input = firstCell.children('input').first();
      var newName = input.val();

      Button.setPending(button);
      input.prop('disabled', true);

      // send new name to the server
      WordLists.renameLabel(labelId, newName, function() {
        Button.setDefault(button.data('action', 'rename-edit'));
        firstCell.children('input').remove();
        firstCell.find('label span').html('&nbsp;' + newName);

        // update local label object
        Database.labels[WordLists.getLabelIndexByLabelId(Database.labels, labelId)].name = newName;
      });
    }
  });


  // expand single labels
  $(page['word-lists']).find('#list-labels-list .small-exp-col-icon').on('click', function() {
    var expandIcon = $(this);
    var expand = (expandIcon.data('state') == 'collapsed');

    var i = 0;
    var row = expandIcon.parent().parent();
    var allFollowing = row.nextAll();
    var selfIndenting = row.data('indenting');
    // show all following rows which have a higher indenting (are sub-labels) or don't have an indenting (are "add sub-label" formular rows)
    while (allFollowing.eq(i).length > 0 && (allFollowing.eq(i).data('indenting') > selfIndenting || allFollowing.eq(i).data('indenting') === undefined)) {
      if (allFollowing.eq(i).data('indenting') == selfIndenting + 1 || !expand) {
        if (expand) // expand
          allFollowing.eq(i).show();

        else { // collapse
          allFollowing.eq(i).hide();
          allFollowing.eq(i).find('.small-exp-col-icon').attr('src', 'img/expand.svg').data('state', 'collapsed');

          // refresh array of expanded labels
          WordLists.expandedLabelsIds.removeAll(parseInt(allFollowing.eq(i).data('label-id')));
        }
      }
      i++;
    }

    if (expand) {
      expandIcon.data('state', 'expanded').attr('src', 'img/collapse.svg'); // flip image
      WordLists.expandedLabelsIds.push(parseInt(row.data('label-id'))); // refresh array of expanded labels
    }
    else {
      expandIcon.data('state', 'collapsed').attr('src', 'img/expand.svg'); // flip image
      WordLists.expandedLabelsIds.removeAll(parseInt(row.data('label-id'))); // refresh array of expanded labels
    }
  });
};


// close small menu for single label event trigger
$('body').on('click', function() {
  $(page['word-lists']).find('.small-menu').addClass('display-none');
}); 


// get editable HTML table of labels
//
// @param Label[] labels: array of label information received from the server
//
// @return string: HTML of the table
WordLists.getEditableHtmlTableOfLabels = function(labels) {
  // method returns the HTML code of the label list
  var html = WordLists.getHtmlListOfLabelId(labels, 0, 0);

  if (html.length > 0) {
    html = WordLists.Template.labelTable({ content: new Handlebars.SafeString(html) });
  }
  else {
    // if there was no code returned there are no labels to show
    html = WordLists.Template.noLabels();
  }
  return html;
};


// get HTML list of label id
//
// @param Label[] labels: array of label information received from the server
// @param int id: id of the label
// @param int indenting: the indenting of the current label (0..n)
//
// @return string: the HTML list showing a label and its sub-labels
WordLists.getHtmlListOfLabelId = function(labels, id, indenting) {
  var output = WordLists.Template.labelList({ 
    indentingPxl: (15 * indenting + 15 + ((indenting === 0) ? 0 : 16)), 
    id: id, 
    indenting: indenting, 
    show: (indenting === 0) 
  });

  var labelIds = WordLists.getLabelIdsWithIndenting(labels, indenting);
  for (var i = 0; i < labelIds.length; i++) {
    var currentLabel = labels[WordLists.getLabelIndexByLabelId(labels, labelIds[i])];
    if (currentLabel.parent_label == id) {
      output += WordLists.getSingleListElementOfLabelList(currentLabel, indenting);
      output += WordLists.getHtmlListOfLabelId(labels, labelIds[i], indenting + 1);
    }
  }
  return output;
};


// get single list element of label list
// 
// @param Label label: label object of the label for which the HTML is requested
// @param int indenting: indenting of the label for which the HTML is requested (0..n)
//
// @return string: HTML table row (<tr>) of a single label
WordLists.getSingleListElementOfLabelList = function(label, indenting) {
  var subLabelsCount = WordLists.getNumberOfSubLabels(Database.labels, label.id);
  var expanded = WordLists.expandedLabelsIds.contains(label.id), parentExpanded = WordLists.expandedLabelsIds.contains(label.parent_label); // label is expanded?


  return WordLists.Template.labelSingleListElement({
    label: label,
    indenting: indenting,
    displayNone: !(indenting === 0 || parentExpanded),
    paddingLeft: (15 * indenting + 15 + ((subLabelsCount === 0) ? 16 : 0)),
    hasSubLabels: (subLabelsCount > 0),
    isAttachedToList: WordLists.labelAttachedToList(WordLists.shown, label.id),
    expanded: expanded
  });
};


// get label index by label id
//
// @param Label[] labels: array of labels in which to search for the labelId
// @param int labelId: id of the label
//
// @return int: index of a label id in the passed labels array or -1 if the label doesn't occur in the array
WordLists.getLabelIndexByLabelId = function(labels, labelId) {
  for (var i = 0; i < labels.length; i++) {
    if (labelId == labels[i].id) {
      return i;
    }
  }
  return -1;
};


// number of sub labels
//
// @param Label[] labels: array of labels in which to work
// @param int labelId: id of the label for which to determine the number of sub labels
//
// @return int: number of sub-labels the label with id labelId has
WordLists.getNumberOfSubLabels = function(labels, labelId) {
  var count = 0;
  var indenting = WordLists.getLabelIndenting(labels, WordLists.getLabelIndexByLabelId(labels, labelId));
  var oneIndentingMore = WordLists.getLabelIdsWithIndenting(labels, indenting + 1);
  for (var i = 0; i < oneIndentingMore.length; i++) {
    if (labels[WordLists.getLabelIndexByLabelId(labels, oneIndentingMore[i])].parent_label == labelId) {
      count++;
    }
  }
  return count;
};


// label attached to list
//
// @param List list: list object
// @param int labelId: id of the label
//
// @return bool: true if the label is attached to the given list object
WordLists.labelAttachedToList = function(list, labelId) {
  for (var i = 0; i < list.labels.length; i++) {
    if (labelId == list.labels[i].id) {
      return true;
    }
  }
  return false;
};


// get label ids with indenting
// 
// @param Label[] labels: array of labels in which to work
// @param int indenting: indenting
//
// @return int[]: all label ids with the specified indenting
WordLists.getLabelIdsWithIndenting = function(labels, indenting) {
  var selectedLabels = [];
  for (var i = 0; i < labels.length; i++) {
    if (WordLists.getLabelIndenting(labels, i) === indenting) {
      selectedLabels.push(labels[i].id);
    }
  }
  return selectedLabels;
};


// get label indenting
// 
// @param Label[] labels: array of labels in which to work
// @param int index: index of the label in the passed labels array
//
// @return int: the indenting of the given label
WordLists.getLabelIndenting = function(labels, index) {
  if (labels[index] === undefined) return undefined;
  if (labels[index].parent_label === 0)
    return 0;

  return WordLists.getLabelIndenting(labels, WordLists.getLabelIndexByLabelId(labels, labels[index].parent_label)) + 1;
};


// add label
// 
// add a new label
//
// @param string name: name of the new label
// @param int parentId: id of the parent label
// @param function callback: callback function with Ajax-response as first parameter
WordLists.addNewLabel = function(name, parentId, callback) {
  // send Ajax request
  jQuery.ajax('server.php', {
    data: {
      action: 'add-label',
      label_name: name,
      parent_label_id: parentId
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // update local data base object
    Database.labels.push({
      active: 1,
      id: data,
      name: name,
      parent_label: parentId,
      user: Database.userId
    });

    // after adding successfully refresh the label list without loading information
    WordLists.updateDomLabelList();

    callback(data);
  });
};


// attach list to label
//
// attaches the given list the given label
//
// @param int labelId: id of the label to attach the list to
// @param int listId: id of the list to attach the label
// @param function callback: callback function with Ajax-response as first parameter
WordLists.attachListToLabel = function(labelId, listId, callback) {
  WordLists.setLabelListAttachment(labelId, listId, 1, callback);
};


// detach list from label
//
// detaches the given list from the given label
//
// @param int labelId: id of the label to detach the list from
// @param int listId: id of the list to detatch the label
// @param function callback: callback function with Ajax-response as first parameter
WordLists.detachListFromLabel = function(labelId, listId, callback) {
  WordLists.setLabelListAttachment(labelId, listId, 0, callback);
};


// set label list attachment
// 
// attaches or detaches a list from a label
// 
// @param int labelId: id of the label
// @param int listId: id of the list
// @param byte attachment: 0 = detached; 1 = attached
// @param function callback: callback function with Ajax-response as first parameter
WordLists.setLabelListAttachment = function(labelId, listId, attachment, callback) {
  if (listId === undefined) {
    listId = WordLists.shownId;
  }

  // send Ajax-request
  jQuery.ajax('server.php', {
    data: {
      action: 'set-label-list-attachment',
      label_id: labelId,
      list_id: listId,
      attachment: attachment
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // update local database object
    if (attachment === 0) {
      for (var i = Database.label_list_attachments.length - 1; i >= 0; i--) {
        if (Database.label_list_attachments[i].label === labelId && Database.label_list_attachments[i].list === listId) {
          Database.label_list_attachments.splice(i, 1);
          break;
        }
      }
    }
    else {
      Database.label_list_attachments.push({
        active: 1,
        label: labelId,
        list: listId,
        id: undefined
      });
    }
    callback(data);
  });
};


// remove label
//
// removes a label (not from a list but generally from the user)
//
// @param int labelId: id of the label
// @param function callback: callback function with Ajax-response as first parameter
WordLists.removeLabel = function(labelId, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'remove-label',
      label_id: labelId
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // update local data base object
    for (var i = 0; i < Database.labels.length; i++) {
      if (Database.labels[i].id === labelId) {
        Database.labels.splice(i, 1); // remove deleted label
        break;
      }
    }

    // update label list
    WordLists.updateDomLabelList();

    callback(data);
  });
};


// rename label
// 
// renames a label
// 
// @param int labelId: id of the label
// @param string labelName: new name of the label
// @param function callback: callback function with Ajax-response as first parameter
WordLists.renameLabel = function(labelId, labelName, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'rename-label',
      label_id: labelId,
      label_name: labelName
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
    callback(data);
  });
};


// rename list
//
// renames a word list
// @param int listId: id of the list to rename
// @param string listName: new name for the list
// @param function callback: callback function with Ajax-response as first parameter
WordLists.renameList = function(listId, listName, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'rename-word-list',
      word_list_id: listId,
      word_list_name: listName
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // update local list object
    Database.getListById(listId).name = listName;

    Home.RecentlyUsed.updateDom(); // the list might have been recently used and therefore the DOM has to bee updated to the new name

    callback(data);
  });
};



// special chars box
// TODO: add comments
$(page['word-lists']).find('#word-lists-show-special-chars').on('click', function() {
  $(page['word-lists']).find('.special-chars').toggleClass('display-none');
  if (lastFocusedInput !== null) {
    setCursorPosition(lastFocusedInput, parseInt($(lastFocusedInput).data('last-cursor-position')));
  }
});

$(page['word-lists']).find('#word-lists-special-chars select').on('change', function() {
  $(page['word-lists']).find('.special-chars > div').hide();
  $(page['word-lists']).find('.special-chars-' + $(this).val()).show();
});

$(page['word-lists']).find('#words-add-language1, #words-add-language2, #words-add-comment').on('keydown keyup click', function(event) {
  lastFocusedInput = this;
  var cursor = getCursorPosition(this);
  $(this).data('last-cursor-position', cursor);
});

var lastFocusedInput = null;
$(page['word-lists']).find('#word-lists-special-chars > div > div').on('click', function(e) {
  if (lastFocusedInput !== null) {
    var cursorPos = parseInt($(lastFocusedInput).data('last-cursor-position'));
    setCursorPosition(lastFocusedInput, cursorPos);
    insertAtCursor(lastFocusedInput, $(this).html());
    cursorPos++;
    setCursorPosition(lastFocusedInput, cursorPos);
    $(lastFocusedInput).data('last-cursor-position', cursorPos);
  }
});




// import namespace
WordLists.Import = {};


// load word array from string
//
// parses word array from string to allow importing word lists
//
// @param string string: raw data of the words like "meaning 1 (languageSeparator) meaning 2 (wordSeparator) next word (languageSeparator) blabla (wordSeparator)"
// @param string wordSeparator: string separating single words e.g. "\n"
// @param string languageSeparator: string separating the two languages of a word 
// @param int|undefined listId: can be the list id of the word objects which will be returned
//
// @return object: attributes store the actual data
// @return array object.word: array of type Word containig the parsed words
// @return array object.error: array of all rows which have not been parsed because of syntax errors
WordLists.Import.loadWordArrayFromString = function(string, wordSeparator, languageSeparator, listId) {
  var notImported = [], word = [];

  var line = string.split(wordSeparator);
  for (var i = 0; i < line.length; i++) {
    var meaning = line[i].split(languageSeparator);
    if (meaning.length !== 2) { // no valid word found error
      notImported.push(line[i]);
      continue; // next iteration
    }   
    else {
      meaning[0] = meaning[0].trim();
      meaning[1] = meaning[1].trim();

      if (meaning[0].length === 0 && meaning[1].length === 0) {
        // both words don't have a content
        notImported.push(line[i]);
        continue;
      }
      else {
        // TODO work with comments
        word.push(new Word(undefined, listId, meaning[0], meaning[1], '', []));
      }
    }
  }

  return {
    word: word,
    error: notImported
  };
};



// show import dialog
WordLists.Import.showDialog = function() {
  Scrolling.disable(); // disable scrolling

  $(document).on('keyup', WordLists.Import.escClose); // allow ESC keypress to close popup

  $('#background-black-overlay').html($('#word-import-box').html()); // fill background overlay with word import box content

  $('#background-black-overlay').removeClass('display-none'); // show background overlay


  // import dialog event listeners
  // separator between languages select change event 
  $('#word-import-separator-1-select').on('change', function() {
    // show textbox if the user wants to use a custom separator
    if ($(this).val() === 'custom') {
      $('#word-import-separator-1-text').removeClass('display-none');
    }
    else {
      $('#word-import-separator-1-text').val($(this).val()).addClass('display-none');
    }
  });

  // separator between words
  $('#word-import-separator-2-select').on('change', function() {
    // show textbox if the user wants to use a custom separator
    if ($(this).val() === 'custom') {
      $('#word-import-separator-2-text').removeClass('display-none');
    }
    else {
      $('#word-import-separator-2-text').val($(this).val()).addClass('display-none');
    }
  });

  // close icon click event
  $('#word-import-close-dialog').on('click', WordLists.Import.hideDialog);
};


// word lists import preview
//
// updates the import preview (list of detected words with the chosen separators)
WordLists.Import.preview = function() {
  var wordSeparator = '', languagesSeparator = '';
  switch ($('#word-import-separator-1-select').val()) {
    case 'tab': 
      languagesSeparator = '\t';
      break;
    case 'custom':
      languagesSeparator = $('#word-import-separator-1-text').val();
      break;
    default:
      break;
  }
  switch ($('#word-import-separator-2-select').val()) {
    case 'return': 
      wordSeparator = '\n';
      break;
    case 'custom':
      wordSeparator = $('#word-import-separator-2-text').val();
      break;
    default:
      break;
  }
  WordLists.Import.loadedWords = WordLists.Import.loadWordArrayFromString($('#word-import-input').val(), wordSeparator, languagesSeparator, WordLists.shownId);


  var tableContentHtml = '';
  for (var i = 0; i < WordLists.Import.loadedWords.word.length; i++) {
    tableContentHtml += WordLists.getTableRowOfWord(
      undefined, // id
      WordLists.Import.loadedWords.word[i].language1, // language 1
      WordLists.Import.loadedWords.word[i].language2, // language 2
      undefined, // comment
      false); // allow edit
  }
  $('#word-import-preview').html(tableContentHtml);
};


// hide import dialog
WordLists.Import.hideDialog = function() {
  $(document).unbind('keyup', WordLists.Import.escClose); // remove esc function event listener

  $('#background-black-overlay').addClass('display-none').html(''); // hide background overlay and remove children

  Scrolling.enable(); // enable scrolling
};


// word lists import ESC close
// 
// function will be fired when the user presses the ESC key
// hides the import dialog
//
// @param EventArgs e: event arguments
WordLists.Import.escClose = function(e) {
  if (e.keyCode == 27) { // detect ESC key press
    WordLists.Import.hideDialog();
  }
};




// initial load functions
WordLists.updateListOfWordLists();