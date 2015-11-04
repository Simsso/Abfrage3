"use strict";

var Query = {};

// templates
Query.Template = {
  querySelection: Handlebars.compile($(page['query']).find('#query-selection-template').html()),
  listSelectionTable: Handlebars.compile($(page['query']).find('#query-list-selection-table-template').html()),
  listSelectionRow: Handlebars.compile($(page['query']).find('#query-list-selection-row-template').html()),
  labelSelectionTable: Handlebars.compile($(page['query']).find('#query-label-selection-table-template').html()),
  labelSelectionRow: Handlebars.compile($(page['query']).find('#query-label-selection-row-template').html())
};

$(window).on('page-query', function(event, pageName, subPageName) {
  // sub page query called
  Query.updateDom(); // update the dom (e.g. a new list might have been added)
});

// default value of how man answers to consider when determining how well a word is known
Query.CONSIDERNANSWERS = 5;



// query algorithms
Query.Algorithm = {};

// query algorithm - in order
//
// saves the given array of words shuffled
// asks one word after another and restarts at the beginning after a whole iteration
//
// @param Word[] words: array of words to work with
//
// @return Query.Algorithm.InOrder: query algorithm object
Query.Algorithm.InOrder = function(words) {
  this.index = -1;
  this.words = words;
  // if it has been known 3 time in 4 queries the return value will be 0.75
  this.iterations = 0;
};

// get next word
// 
// @return Word: returns the next word considering the query algorithm which this function belongs to
Query.Algorithm.InOrder.prototype.getNextWord = function() {
  if (this.words.length === 0) return undefined;
  
  if (this.words.length === this.index + 1) {
    this.index = -1;
    this.iterations++;
  } 
  this.index++;
  return this.words[this.index];
};


// query algorithm - group words
//
// This algorithm takes a word array and saves a shuffled version. From the shuffled array it takes a few (groupSize) words and aks them.
// If a word in the selected group has been always known over the last n answers (parameter) it will be replaced by another word.
//
// @param Word[] words: array of words to work with
// @param int groupSize: size of the group which will be asked at the same time
// @param int careAboutLastNAnswers: number of answers to consider when defining whether a word is known or not
//
// @return Query.Algorithm.GroupWords: query algorithm object
Query.Algorithm.GroupWords = function(words, groupSize, careAboutLastNAnswers) {
  if (groupSize === undefined) groupSize = 8;
  if (careAboutLastNAnswers === undefined) careAboutLastNAnswers = Query.CONSIDERNANSWERS;

  this.groupSize = groupSize;
  this.careAboutLastNAnswers = careAboutLastNAnswers;
  this.words = words.slice().shuffle(); // shuffle a copy of the words array
  this.index = (groupSize < this.words.length) ? groupSize : 0;
  this.lastReturnedWord = null;
  
  this.currentGroup = [];
  
  if (this.words.length > groupSize) {
    this.currentGroup = this.words.slice(0, groupSize); // TODO: check slice parameters
  }
  else { // there are not enough words given to perform a senseful GroupWords Query.Algorithm
    this.currentGroup = words;
  }
};
  
  
// get next word
// 
// @return Word: returns the next word considering the query algorithm which this function belongs to
Query.Algorithm.GroupWords.prototype.getNextWord = function() {
  // go through all words in the current group and check of words which are known
  for (var i = 0; i < this.currentGroup.length; i++) {
    if (
      this.currentGroup[i].getKnownAverageOverLastNAnswers(this.careAboutLastNAnswers) === 1 && // average 
      this.currentGroup[i].answers.length >= 5) { // at least five answers
      // a word is known
      this.index++; // increment the pointer which indicates where the this.currentGroup ends in the this.words array
      if (this.index >= this.words.length) { // gone through all words 
        this.index = 0; // reset index and restart at the beginning
      }
      this.currentGroup[i] = this.words[this.index];
    }
  }
  
  // select a random word but don't return the last asked word
  if (this.currentGroup.length === 1) return this.currentGroup[0];
  var nextWord = this.currentGroup.slice().remove(this.lastReturnedWord).getRandomElement();
  this.lastReturnedWord = nextWord;
  return nextWord;
};


// enumerations
Query.AlgorithmEnum = Object.freeze({
  Random: 0, 
  UnderAverage: 1, 
  GroupWords: 2,
  InOrder: 3
});

Query.DirectionEnum = Object.freeze({
  Both: -1, 
  Ltr: 0, 
  Rtl: 1
});

Query.TypeEnum = Object.freeze({
  TextBox: 0, 
  Buttons: 1
});

Query.AnswerStateEnum = Object.freeze({
  Start: 0, 
  NotSureClicked: 1,
  Known: 2,
  NotKnown: 3,
  WaitToContinue: 4,
  NotKnownClicked: 5
});


 Query.labels = null;
 Query.labelListAttachments = null;
 Query.lists = null;
 Query.selectedLabels = [];
 Query.selectedLists = [];

// refresh query label list
//
// Before starting a query the user can selct lists which he wants to learn. Multiple lists can be selected at the same time by selecting a whole label in the label list.
// This label list is downloaded and added to the DOM by the following function.
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function refreshQueryLabelList(showLoadingInformation) { Query.downloadQueryData(showLoadingInformation); } // transceiver function
Query.downloadQueryData = function(showLoadingInformation) {
  Query.stop();
  if (showLoadingInformation) {
    $(page['query']).find('#query-selection').html(loading);
  }

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-query-data'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    var data = handleAjaxResponse(data);
    Database = data;
    Query.lists = Database.lists; // link 
    Query.updateDom();
  });
};


Query.updateDom = function() {
  // labels
  Query.labels = Database.labels;

  // label list attachments
  // information which list is attached to which label
  Query.labelListAttachments = Database.label_list_attachments;

  // word lists
  Query.lists = Database.lists;


  // fill query all answers array
  for (var i = 0; i < Query.lists.length; i++) {
    for (var j = 0; j < Query.lists[i].words.length; j++) {
      Query.allAnswers.pushElements(Query.lists[i].words[j].answers);
    }
  }

  Query.allAnswers.sort(QueryAnswer.sortByTime);

  $(page['query']).find('#query-selection').html(Query.Template.querySelection);
  $(page['query']).find('#query-start-button').attr('value', $(page['query']).find('#query-start-button').data('value-start')); // set value to "Start test"

  // provide label selection
  $(page['query']).find('#query-label-selection').html(Query.getHtmlTableOfLabels(Query.labels));

  // provide list selection
  Query.refreshListSelection();

  // when updating the dom while a query is running it is necessary to mark the selected labels and lists of the running query
  if (Query.running) {
    Query.checkStartButtonEnable();
    Query.updateLabelSelections();
    Query.updateListSelections();
    $(page['query']).find('#query-start-button').attr('value', $(page['query']).find('#query-start-button').data('value-stop'));
  }


  // start query button click event
  $(page['query']).find('#query-start-button').on('click', function() {
    if (Query.running) {
      Query.stop();
    }
    else {
      Query.start();
    }
  });

  // checkbox click event
  $(page['query']).find('#query-label-selection tr').on('click', function(){
    // read label id from checkbox data tag
    var labelId = $(this).data('query-label-id');
    // checkbox has been unchecked
    if($(this).data('checked') === true) {
      Query.removeLabel(labelId);
    }
    // checkbox has been checked
    else if ($(this).data('checked') === false) { 
      Query.addLabel(labelId);
    }
  });

  // expand functionallity
  // expand single labels
  $(page['query']).find('#query-label-selection .small-exp-col-icon').on('click', function(e) {
    e.stopPropagation();
    var $this = $(this);
    var expand = ($this.data('state') == 'collapsed');

    var i = 0;
    var $row = $this.parent().parent();
    var allFollowing = $row.nextAll();
    var selfIndenting = $row.data('indenting');
    // show all following rows which have a higher indenting (are sub-labels) or don't have an indenting (are "add sub-label" formular rows)
    while (allFollowing.eq(i).length > 0 && (allFollowing.eq(i).data('indenting') > selfIndenting || allFollowing.eq(i).data('indenting') === undefined)) {
      if (allFollowing.eq(i).data('indenting') == selfIndenting + 1 || !expand) {
        if (expand) // expand
          allFollowing.eq(i).show();

        else { // collapse
          allFollowing.eq(i).hide();
          allFollowing.eq(i).find('.small-exp-col-icon').attr('src', 'img/expand.svg').data('state', 'collapsed');
        }
      }
      i++;
    }

    if (expand) {
      $this.data('state', 'expanded').attr('src', 'img/collapse.svg'); // flip image
    }
    else {
      $this.data('state', 'collapsed').attr('src', 'img/expand.svg'); // flip image
    }
  });
};



// case sensitive query
Query.caseSensitivity = $(page['query']).find('#query-case-sensitivity').is(':checked');

// checkbox case sensitivity query change event listener
$(page['query']).find('#query-case-sensitivity').on('change', function() {
  Query.caseSensitivity = $(this).is(':checked');
});



// refresh query list selection
//
// Refreshes the list of word lists where the user can select the lists for the test.
//
Query.refreshListSelection = function() {
  var html = '';

  Query.lists.sort(List.compareListsByName);

  for (var i = 0; i < Query.lists.length; i++) {
    var selected = false;
    if (Query.selectedLists.contains(Query.lists[i].id)) 
      selected = true;

    html += Query.getListRow(Query.lists[i], selected);
  }


  $(page['query']).find('#query-list-selection').html(Query.Template.listSelectionTable({ content: new Handlebars.SafeString(html) }));

  // checkbox click event
  $(page['query']).find('#query-list-selection tr').on('click', function() {
    // read list id from checkbox data tag
    var listId = $(this).data('query-list-id');
    
    // checkbox has been unchecked
    if($(this).data('checked') === true) {
      Query.removeList(listId);
    }
    // checkbox has been checked
    else if ($(this).data('checked') === false) { 
      Query.addList(listId);
    }
  });
};


// add label to query
//
// When the user has clicked on a label the related lists (which are connected to the label) have to be marked and added to the test
//
// @param int labelId: the id of the label
Query.addLabel = function(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < Query.labelListAttachments.length; i++) {
    if (Query.labelListAttachments[i].label == labelId) {
      Query.addList(Query.labelListAttachments[i].list);
    }
  }

  Query.selectedLabels.push(labelId);
  Query.updateLabelSelections();
};


// remove label from query
// 
// opposite of add label to query
//
// @param int labelId: id of the label to remove
Query.removeLabel = function(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < Query.labelListAttachments.length; i++) {
    if (Query.labelListAttachments[i].label == labelId) {
      Query.removeList(Query.labelListAttachments[i].list);
    }
  }
  Query.selectedLabels.removeAll(labelId); // remove all removes all occurences of the passed object
  Query.updateLabelSelections();
};


// update label selections
//
// marks labels as selected which are in the selectedLabels array
Query.updateLabelSelections = function() {
  for (var i = Query.selectedLabels.length - 1; i >= 0; i--) {
      $(page['query']).find('#query-label-selection tr[data-query-label-id=' + Query.selectedLabels[i] + ']').addClass('active').data('checked', true);
  }
};


// add list to query
//
// adds a list with its words to the test
//
// @param int listId: list id 
Query.addList = function(listId) {
  Query.stop();
  Query.selectedLists.push(Query.getListById(listId));
  Query.checkStartButtonEnable();
  Query.updateListSelections();

  // update information about the language of the selected words
  Query.updateQueryWordsLanguageInformation(Query.getLanguagesOfWordLists(Query.selectedLists));
};


// remove list from query
// 
// opposite of add list from query
//
// @param int listId: list id
Query.removeList = function(listId) {
  Query.selectedLists.removeAll(Query.getListById(listId));
  Query.checkStartButtonEnable();
  Query.updateListSelections();

  // update information about the language of the selected words
  Query.updateQueryWordsLanguageInformation(Query.getLanguagesOfWordLists(Query.selectedLists));
};


// update list selections
//
// marks lists as selected which are in the selectedLists array
Query.updateListSelections = function() {
  $(page['query']).find('#query-list-selection tr').data('checked', false).removeClass('active');
  for (var i = Query.selectedLists.length - 1; i >= 0; i--) {
    $(page['query']).find('#query-list-selection tr[data-query-list-id=' + Query.selectedLists[i].id + ']').data('checked', true).addClass('active');
  }
};


// get list row
// 
// creates a formatted <tr> HTML-element for the table of word lists
// 
// @param List list: list for which the row will be created
// @param bool selected: true if the list is selected for the next test
// 
// @return <tr> HTML-element for the table of word lists
Query.getListRow = function(list, selected) {
  return Query.Template.listSelectionRow({ list: list, selected: selected });
};

// check start query button enable
//
// enables the start query button if words have been selected due clicking on lists or labels
Query.checkStartButtonEnable = function() {
  var wordSum = 0;
  for (var i = Query.selectedLists.length - 1; i >= 0; i--) {
      wordSum += Query.selectedLists[i].words.length;
  };  
  $(page['query']).find('#query-start-button').prop('disabled', wordSum === 0 && !Query.running);
};


// get html table of labels
// 
// creates a <table> HTML-element containig the passed query labels
//
// @param Label[] labels: array of labels
// 
// @return string: <table> HTML-element or paragraph that no labels exist
Query.getHtmlTableOfLabels = function(labels) {
  // method returns the HTML code of the label list
  var html = Query.getHtmlListOfLabelId(labels, 0, 0);

  return Query.Template.labelSelectionTable({ content: new Handlebars.SafeString(html) });
};


// get HTML list of label id
// 
// recursively creates a list of labels and sub-labels, etc.
//
// @param Label[] labels: array of labels
// @param int id: id of the current label
// @param unsigned int indenting: indenting of the current label (increases with every recursion by one)
//
// @return string: HTML-ist showing a label and its sub-labels
Query.getHtmlListOfLabelId = function(labels, id, indenting) {
  var output = '';
  var labelIds = WordLists.getLabelIdsWithIndenting(labels, indenting);
  for (var i = 0; i < labelIds.length; i++) {
    var currentLabel = labels[WordLists.getLabelIndexByLabelId(labels, labelIds[i])];
    if (currentLabel.parent_label == id) {
      output += Query.getSingleListElementOfLabelList(currentLabel, indenting);
      output += Query.getHtmlListOfLabelId(labels, labelIds[i], indenting + 1);
    }
  }
  return output;
};


// get single list element of label list
//
// @param Label label: label object
// @param unsigned int indenting: indenting of the label
//
// @return string: HTML-row of a single label
Query.getSingleListElementOfLabelList = function(label, indenting) {
  var subLabelsCount = WordLists.getNumberOfSubLabels(Query.labels, label.id);
  var expanded = false; // show all labels collapsed

  return Query.Template.labelSelectionRow({ 
    label: label,
    indenting: indenting,
    indentingPxl: (15 * indenting + 15 + ((subLabelsCount === 0) ? 16 : 0)),
    subLabelsCount: subLabelsCount,
    expanded: expanded
  });
};


// get list by id
// 
// searches through the Query.lists array
// 
// @param int id: id of the list
//
// @return List: object of the list which has the passed id
Query.getListById = function(id) {
  for (var i = 0; i < Query.lists.length; i++) {
    if (Query.lists[i].id === id) {
      return Query.lists[i];
    }
  }
  return undefined;
};




Query.words = []; // array of all words which the user selected for the query
Query.chosenAlgorithm = Query.AlgorithmEnum.GroupWords; // the algorithm the user has chosen
Query.chosenDirection = Query.DirectionEnum.Both; // the query direction the user has chosen
Query.chosenType = Query.TypeEnum.TextBox; // type (text box or buttons to answer the question)
Query.running = false; // true if a query is running
Query.currentWord = null; // reference to the Word object which is currently asked
Query.currentDirection = null; // the query direction (0 or 1)
Query.correctAnswer = null; // the string value containing the currect answer for the current word
Query.answers = []; // array of answers the user already gave
Query.allAnswers = []; // array of all answers (also those given in the past - those which have been downloaded)
Query.selectedWordsAllAnswers = []; // array of all answers given to the selected words
Query.nextIndexToUpload = 0; // first index of answers which has not been uploaded already (if Query.answers[] contains 4 words and 3 of them have been uploaded the var will hav the value 3)
Query.currentAnswerState = Query.AnswerStateEnum.Start; // query answer state
Query.inOrderAlgorithm; 
Query.groupWordsAlgorithm;
    

// start query
Query.start = function() {
  // produce one array containing all query words
  Query.words = [];
  for (var i = 0; i < Query.selectedLists.length; i++) {
    Query.words = Query.words.concat(Query.selectedLists[i].words);
  }

  if (Query.words.length === 0) {
    var messageBox = new MessageBox();
    messageBox.setTitle('Can\'t start test');
    messageBox.setContent('You haven\'t selected enough words to start a test. Select at least one word.');
    messageBox.setButtons(MessageBox.ButtonType.Ok);
    messageBox.setFocusedButton('Ok');
    messageBox.show();
    return;
  }


  Query.running = true;

  // update start query button and test fields
  $(page['query']).find('#query-start-button').attr('value', $(page['query']).find('#query-start-button').data('value-stop'));
  $(page['query']).find('#query-not-started-info').addClass('display-none');
  $(page['query']).find('#query-content-table').removeClass('display-none');
  

  // refresh array of selected words all answers
  Query.selectedWordsAllAnswers = [];
  Query.Drawing.storedDataOfQueryAnswers = [];
  for (var i = 0; i < Query.words.length; i++) {
    Query.selectedWordsAllAnswers.pushElements(Query.words[i].answers);
  }
  Query.selectedWordsAllAnswers.sort(QueryAnswer.sortByTime);

  // update information about the language of the selected words
  Query.updateQueryWordsLanguageInformation(Query.getLanguagesOfWordLists(Query.getListsOfWords(Query.words)));

  // array of ids of words selecte for the query
  var wordIds = [];
  for (var j = 0; j < Query.words.length; j++) {
    wordIds.push(Query.words[j].id);
  }
  
  Query.inOrderAlgorithm = new Query.Algorithm.InOrder(Query.words);
  Query.groupWordsAlgorithm = new Query.Algorithm.GroupWords(Query.words);

  Query.nextWord(); // actually start the query

  //$(page['query']).find('#query-select-box img[data-action="collapse"]').trigger('collapse');
  $(page['query']).find('#query-box img[data-action="expand"]').trigger('expand'); // expand query container

};


// stop query
Query.stop = function() {
  if (!Query.running) return;

  Query.running = false;

  $(page['query']).find('#query-start-button').attr('value', $(page['query']).find('#query-start-button').data('value-start'));
  $(page['query']).find('#query-not-started-info').removeClass('display-none');
  $(page['query']).find('#query-content-table').addClass('display-none');

  Query.selectedLists = [];
  Query.selectedLabels = [];
  Query.refreshListSelection();
  $(page['query']).find('#query-label-selection tr').data('checked', false).removeClass('active');

  Query.checkStartButtonEnable();
  
  Query.updateQueryWordsLanguageInformation(Query.getLanguagesOfWordLists([]));
};


// next word
//
// gets the next word for the test
// updates the DOM (shows the word)
Query.nextWord = function() {
  Query.currentAnswerState = Query.AnswerStateEnum.Start;
  
  $(page['query']).find('#query-answer-not-known').prop('disabled', false);
  $(page['query']).find('#query-answer-known').attr('value', 'I know!');
  $(page['query']).find('#query-answer-not-known').attr('value', 'No idea.');
  $(page['query']).find('#query-answer-buttons').hide();
  $(page['query']).find('#correct-answer').hide();
  $(page['query']).find('#query-answer-not-sure').prop('disabled', false);

  
  Query.currentWord = Query.getNextWord();
  var listOfTheWord = Query.getListById(Query.currentWord.list);

  if (Query.chosenDirection == Query.DirectionEnum.Both) { // both directions
    Query.currentDirection = Math.round(Math.random()); // get random direction
  }
  else {
    Query.currentDirection = Query.chosenDirection;
  }

  // fill the question fields
  if (Query.currentDirection === Query.DirectionEnum.Ltr) {
    $(page['query']).find('#query-lang1').html(listOfTheWord.language1);
    $(page['query']).find('#query-lang2').html(listOfTheWord.language2);
    $(page['query']).find('#query-question').html(Query.currentWord.language1);
    Query.correctAnswer = Query.currentWord.language2;
  }
  else if (Query.currentDirection === Query.DirectionEnum.Rtl) {
    $(page['query']).find('#query-lang1').html(listOfTheWord.language2);
    $(page['query']).find('#query-lang2').html(listOfTheWord.language1);
    $(page['query']).find('#query-question').html(Query.currentWord.language2);
    Query.correctAnswer = Query.currentWord.language1;
  }

  // show word's comment
  $(page['query']).find('#query-comment').html(Query.currentWord.comment);
  
  $(page['query']).find('#query-answer').val('').focus();

  Query.Stats.updateWordInformation(Query.currentWord);
  Query.Stats.updateSelectedWordsInformation();
};


// get next word
//
// determines the next word depending on the selected query algorithm
//
// @return Word: word object
Query.getNextWord = function() {
  switch (Query.chosenAlgorithm) {
    case Query.AlgorithmEnum.Random:
      return Query.words.getRandomElement();
    case Query.AlgorithmEnum.UnderAverage:
      var avg = Word.getKnownAverageOfArray(Query.words);
      return Word.getWordKnownBelow(Query.words, avg, Query.CONSIDERNANSWERS);
    case Query.AlgorithmEnum.InOrder:
      return Query.inOrderAlgorithm.getNextWord();
    case Query.AlgorithmEnum.GroupWords:
      return Query.groupWordsAlgorithm.getNextWord();
  }
};


// allow enter pressing to check the user's answer
$(page['query']).find('#query-answer').on('keypress', function(e) {
  if (e.which == 13) {
    if (Query.checkAnswer($(this).val(), Query.correctAnswer)) { // correct answer  
      if (Query.currentAnswerState == Query.AnswerStateEnum.NotKnown || Query.currentAnswerState == Query.AnswerStateEnum.NotSureClicked || Query.currentAnswerState == Query.AnswerStateEnum.WaitToContinue) {
        Query.nextWord();
      }
      else {
        Query.currentAnswerState = Query.AnswerStateEnum.Known;
        Query.processCurrentAnswerState();
      }
    }
    else { // wrong answer
      if (Query.currentAnswerState == Query.AnswerStateEnum.NotKnown) { // answer already shown and already saved that the user didn't know the word
        return;
      }
      
      Query.currentAnswerState = Query.AnswerStateEnum.NotKnown;
      Query.processCurrentAnswerState();

    }
  }
});


// add query to answer
//
// push into arrays whether the user has answered correctly
//
// @param Word word: word which has been answered
// @param byte correct: right or wrong answer (0 = wrong; 1 = right)
Query.addAnswer = function(word, correct) {
  var answer = new QueryAnswer(word.id, correct, Query.chosenType, Query.currentDirection); 
  Query.answers.push(answer);
  Query.allAnswers.push(answer);
  if (Query.words.contains(word)) Query.selectedWordsAllAnswers.push(answer);
  Query.refreshResultsUploadCounter();
  word.answers.push(answer);
};


// query synonyms
//
// object storing arrays
// the index e.g. 'something' has the array of synonyms as its value (e.g. ['sth.', ...])
Query.Synonyms = {
  'something': ['sth.', 's.th.'],
  'someone': ['so.', 's.o.']
};


// check answer
//
// compares two string for "equality"
// 
// eqality means that e.g. spaces at the end don't matter
//
// @param string user: the user's string
// @param string correct: the correct string
Query.checkAnswer = function(user, correct) {
  var answerSeparator = ',';
  var userArray = user.split(answerSeparator), correctArray = correct.split(answerSeparator);
  if (userArray.length !== correctArray.length) return false;

  // iterate through every correct answer (split by the separator)
  for (var i = correctArray.length - 1; i >= 0; i--) {
    // remove spaces, returns, etc. at beginning and end of the strings
    userArray[i] = userArray[i].trim();
    correctArray[i] = correctArray[i].trim();

    // no case sensitivity
    if (!Query.caseSensitivity) {
      userArray[i] = userArray[i].toLowerCase();
      correctArray[i] = correctArray[i].toLowerCase();
    }

    // replace synonyms (like sth. --> something)
    for (var synonym in Query.Synonyms) {
      for (var j = Query.Synonyms[synonym].length - 1; j >= 0; j--) {
        userArray[i] = userArray[i].replace(Query.Synonyms[synonym][j], synonym);
        correctArray[i] = correctArray[i].replace(Query.Synonyms[synonym][j], synonym);
      }
    }

    // check if the user array has the currently iterated correct answer in it
    if (!userArray.contains(correctArray[i])) {
      return false;
    }
  }
  return true;
};


// refresh query results upload button
//
// enables or disables the "Upload query results" button depending on the amount of answers which have not been uploaded yet
Query.refreshResultsUploadButton = function() {
  var notUploadedAnswersCount = Query.answers.length - Query.nextIndexToUpload;
  $(page['query']).find('#query-results-upload-button').prop('disabled', !(notUploadedAnswersCount > 0)).attr('value', 'Upload ' + ((notUploadedAnswersCount > 0)? notUploadedAnswersCount + ' ' : '') + 'answer' + ((notUploadedAnswersCount == 1) ? '' : 's'));
};


// refresh query results upload counter
//
// The upload counter is an information like "Uploaded 0/0 test answers.". 
// The functions updates the values.
Query.refreshResultsUploadCounter = function() {
  $(page['query']).find('#query-results-upload-counter').html('Uploaded ' + Query.nextIndexToUpload + '/' + Query.answers.length + ' test answers.');
};


// upload query results
//
// uploads the answers which have not been uploaded yet
// Query.answers[] stores all answers
// Query.nextIndexToUpload points to the first element in Query.answers which has not been uploaded yet
Query.uploadResults = function() {
  var startedUploadIndex = Query.nextIndexToUpload; 
  var answersToUpload = Query.answers.slice(Query.nextIndexToUpload);
  Query.nextIndexToUpload = Query.answers.length;

  Query.refreshResultsUploadButton();

  $.ajax({
    type: 'POST',
    url: 'server.php?action=upload-query-results',
    data: { 'answers': JSON.stringify(answersToUpload)},
    error: function(jqXHR, textStatus, errorThrown) {
      // remove the (because of the errror) not uploaded answers and append them to the array again to ensure they will be re-uploaded later
      Query.answers.splice(startedUploadIndex, answersToUpload.length);
      Query.nextIndexToUpload -= answersToUpload.length;
      Query.answers.pushElements(answersToUpload);
      Query.refreshResultsUploadButton();
    }
  })
  .done( function( data ) {
    data = handleAjaxResponse(data);
    Query.refreshResultsUploadCounter();
  });
};



// upload query results button click event listener
$(page['query']).find('#query-results-upload-button').on('click', Query.uploadResults);


// query answer buttons events (know, not sure, don't know)
// known
$(page['query']).find('#query-answer-known').on('click', function() {
  // known button click event
  if (Query.currentAnswerState == Query.AnswerStateEnum.WaitToContinue || Query.currentAnswerState == Query.AnswerStateEnum.NotKnown) {
    Query.nextWord();
  }
  else {
    Query.currentAnswerState = Query.AnswerStateEnum.Known;
    Query.processCurrentAnswerState();
  }
});
// not sure
$(page['query']).find('#query-answer-not-sure').on('click', function() {
  // not sure button click event
  Query.currentAnswerState = Query.AnswerStateEnum.NotSureClicked;
  Query.processCurrentAnswerState();
});
// not known
$(page['query']).find('#query-answer-not-known').on('click', function() {
  // not known button click event
  Query.currentAnswerState = Query.AnswerStateEnum.NotKnownClicked;
  Query.processCurrentAnswerState();
});


// process query current answer state
Query.processCurrentAnswerState = function() {
  switch (Query.currentAnswerState) {
    case Query.currentAnswerState.Start:
      return;
    case Query.AnswerStateEnum.Known:
      $(page['query']).find('#query-box').trigger('shadow-blink-green');
      Query.addAnswer(Query.currentWord, 1);
      Query.tryAutoUpload();
      Query.nextWord();
      return;
    case Query.AnswerStateEnum.NotSureClicked:
      $(page['query']).find('#query-answer-not-sure').prop('disabled', true);
      $(page['query']).find('#query-answer-known').attr('value', 'I knew that!');
      $(page['query']).find('#query-answer-not-known').attr('value', 'I didn\'t know that.');
      Query.showSolution();
      return;
    case Query.AnswerStateEnum.NotKnownClicked:
      Query.currentAnswerState = Query.AnswerStateEnum.WaitToContinue;
      // no break here
    case Query.AnswerStateEnum.NotKnown:
      $(page['query']).find('#query-answer-not-known').prop('disabled', true);
      $(page['query']).find('#query-answer-not-sure').prop('disabled', true);
      $(page['query']).find('#query-answer-known').attr('value', 'Continue.');

      Query.showSolution();
      Query.addAnswer(Query.currentWord, 0);
      Query.tryAutoUpload();

      Query.Stats.updateWordInformation(Query.currentWord);
      Query.Stats.updateSelectedWordsInformation();
      return;
  }
};


// show query solution
//
// shows the query solution to the user
Query.showSolution = function() {
  $(page['query']).find('#query-answer-buttons').show().html(Query.correctAnswer);
  $(page['query']).find('#correct-answer').show().html(Query.correctAnswer);
  $(page['query']).find('#query-answer').select();
};




// settings (algorithm, direction and type)

// query algorithm
$(page['query']).find('#query-algorithm tr').on('click', function() {
  $(page['query']).find('#query-algorithm tr').removeClass('active');
  $(this).addClass('active');
  Query.chosenAlgorithm = parseInt($(this).data('algorithm'));
});

// query direction
$(page['query']).find('#query-direction tr').on('click', function() {
  $(page['query']).find('#query-direction tr').removeClass('active');
  $(this).addClass('active');
  Query.chosenDirection = parseInt($(this).data('direction'));
});

// query type
$(page['query']).find('#query-type tr').on('click', function() {
  $(page['query']).find('#query-type tr').removeClass('active');
  $(this).addClass('active');
  Query.setType(parseInt($(this).data('type')));
});


// set query type
//
// @param Query.TypeEnum queryType: query type (buttons or text box)
Query.setType = function(queryType) {
  if (Query.chosenType != queryType) {
    Query.chosenType = queryType;
    
    if (queryType == Query.TypeEnum.Buttons) {
      $(page['query']).find('#query-answer-table-cell-text-box, .query-special-chars-wrapper').hide();
      $(page['query']).find('#query-answer-table-cell-buttons').show();
      
    }
    else if (queryType == Query.TypeEnum.TextBox) {
      $(page['query']).find('#query-answer-table-cell-buttons').hide();
      $(page['query']).find('#query-answer-table-cell-text-box, .query-special-chars-wrapper').show();
      $(page['query']).find('#query-answer').focus();
    }
  }
};



// try auto upload
//
// query results auto upload
Query.tryAutoUpload = function() {
  if (Query.autoUploadEnabled()) 
    Query.uploadResults();
  else
    Query.refreshResultsUploadButton();
};

// auto upload enabled
//
// @return bool: returns if the user has enabled auto upload of their query answers
Query.autoUploadEnabled = function() {
  return $(page['query']).find('#query-results-auto-upload').is(':checked');
}

// query auto upload checkbox event listener
$(page['query']).find('#query-results-auto-upload').on('click', function() {
  if (Query.autoUploadEnabled()) 
    Query.uploadResults();
});



// update query list language information
//
// On the right side of the "Test" page there is a box where the user has the ability to choose the query direciton. It looks like so:
//  - First language to Second language
//  - Second language to First language
//  - Both directions
// If the selected lists all have the same first and second language this text is updated to e.g "German to English".
// The method updates the DOM.
//
// @param string[2] languages: both langauges
Query.updateQueryWordsLanguageInformation = function(languages) {
  if (languages[0] === undefined || languages[1] === undefined) {
    languages = ["First language", "Second language"];
  }

  $(page['query']).find('span[data-value="first-language-information"]').html(languages[0]);
  $(page['query']).find('span[data-value="second-language-information"]').html(languages[1]);
};



// get lists of words
//
// gets the lists which the words belong to
// e.g. a word contained in the passed array has a list with an id "5" the return array will have contain the list object
// this happens with all passed words
//
// @param Word[] word: array of words
//
// @return List[]: array of lists which belongs to the words
Query.getListsOfWords = function(word) {

  var list = [];

  for (var i = 0; i < word.length; i++) {
    var listOfCurrentWord = Query.getListById(word[i].list);
    if (!list.contains(listOfCurrentWord)) {
      list.push(listOfCurrentWord);
    }
  }

  return list;
};


// detect languages of word lists
// 
// @param List[] list: lists to detect langues
//
// @return string[2]: an array with two elements containing the two languages of the given word lists
// @return string[2]: if the lists have different language the function will return [undefined, undefined]
Query.getLanguagesOfWordLists = function(list) {

  // no lists given
  if (list.length === 0) 
    return [undefined, undefined];

  // set languages to values of the first list
  var language = [list[0].language1, list[0].language2];

  // iterate through all lists to check if they fit the languages of the first list in the array
  for (var i = 0; i < list.length; i++) {
    if (list[i].language1 != language[0] || list[i].language2 != language[1])
      return [undefined, undefined];
  }

  return language;
};


// starts a test with the passed list id
//
// @param int listId: the list id
Query.startTestWithList = function(listId, showQueryPage) {
  if (showQueryPage) {
    window.location.hash = '#/query';
  }
  Query.stop();
  Query.addList(listId);
  Query.start();
};


// Query.Drawing
//
// namespace for functions related to the drawing of the information how the user has answered a word or list in the past
Query.Drawing = {};


// query drawing get svg of word
//
// @param Word word: the word for which the svg will be generated
//
// @return string: the <svg> element
Query.Drawing.getSvgOfWord = function(word) {
  var svg = '<svg class="word-known-graph">';

  if (word.answers.length > 0) {
    // words answer array not empty

    // add a single horziontal line if there is only one word
    if (word.answers.length === 1) {
      svg += '<line ';
      svg += 'x1="0%" ';
      svg += 'x2="100%" ';
      svg += 'y1="' +  Math.map(word.answers[0].correct, 0, 1, 100, 0) + '%" ';
      svg += 'y2="' +  Math.map(word.answers[0].correct, 0, 1, 100, 0) + '%" ';
      svg += 'class="crisp" />';
      svg += '<circle cx="50%" cy="' +  Math.map(word.answers[0].correct, 0, 1, 100, 0) + '%" r="4" />';
    }
    else {
      for (var i = 0; i < word.answers.length; i++) {
        // add a cicle for every answer
        svg += '<circle ';
        svg += 'cx="' + Math.map(i, 0, word.answers.length - 1, 0, 100) + '%" ';
        svg += 'cy="' +  Math.map(word.answers[i].correct, 0, 1, 100, 0) + '%" ';
        svg += ' r="4" />';

        // connect all answer circles with lines
        if (i > 0) {
          var x1 = Math.map(i - 1, 0, word.answers.length - 1, 0, 100), x2 = Math.map(i, 0, word.answers.length - 1, 0, 100);
          var y1 = Math.map(word.answers[i - 1].correct, 0, 1, 100, 0), y2 = Math.map(word.answers[i].correct, 0, 1, 100, 0);
          svg += '<line ';
          svg += 'x1="' +  x1 + '%" ';
          svg += 'x2="' +  x2 + '%" ';
          svg += 'y1="' +  y1 + '%" ';
          svg += 'y2="' +  y2 + '%" ';
          svg += ((y1 === y2) ? 'class="crisp" ': '') + '/>';
        }
      };
    }
  }
  else {
    // no answers
    svg += '<text y="75%" text-anchor="right">No answers yet.</text>';
  }

  // return HTML-element
  return svg + '</svg>'
};


// stored statistic data of query answers
//
// the diagram showing the knowledge of the word doesn't need to be redrawed every time
// old values are stored in the array
//
// []
Query.Drawing.storedDataOfQueryAnswers = [];


// query drawing get svg of selected words (Query.words) array
//
// @return string: the <svg> element
Query.Drawing.getSvgOfQueryAnswers = function() {
  var svg = '<svg class="selected-words-avg-graph">', numberOfPoints = 100;
  var maxX = (Query.selectedWordsAllAnswers.length > numberOfPoints) ? numberOfPoints : Query.selectedWordsAllAnswers.length;
  var lastX, lastY;
  for (var i = maxX - 1, j = 0; i >= 0 && maxX > 1; i--, j++) {
    var x, y;

    x = Math.map(i, 0, maxX - 1, 100, 0);

    var numberOfIgnoredAnswers = Math.round(Math.map(i, 0, maxX - 1, 0, Query.selectedWordsAllAnswers.length - 1));
    var numberOfConsideredAnswers = Query.selectedWordsAllAnswers.length - numberOfIgnoredAnswers;
    // check if the values have already been calculated
    if (typeof Query.Drawing.storedDataOfQueryAnswers[numberOfConsideredAnswers] !== 'undefined') {
      // the values have already been calculated
      // load them from the array
      y = Query.Drawing.storedDataOfQueryAnswers[numberOfConsideredAnswers];
    }
    else {
      // calculate the values
      var ignoredAnswers = Query.selectedWordsAllAnswers.slice(Query.selectedWordsAllAnswers.length - numberOfIgnoredAnswers);
      var average = Word.getKnownAverageOfArrayOverLastNAnswers(Query.words, Query.CONSIDERNANSWERS, ignoredAnswers);
      y = Math.map(average, 0, 1, 100, 0);
      Query.Drawing.storedDataOfQueryAnswers[numberOfConsideredAnswers] = y;
    }

    svg += '<circle cx="' + x + '%" cy="' + y + '%" r="1" />';
    if (j > 0) {
      // line can only be drawn between points (from the second iteration of the loop on)
      svg += '<line x1="' + lastX + '%" x2="' + x + '%" y1="' + lastY + '%" y2="' + y + '%" />';
    }
    lastX = x;
    lastY = y;
  }

  if (Query.selectedWordsAllAnswers.length === 0) {
    svg += '<line x1="0%" x2="100%" y1="100%" y2="100%" />';
  }
  else if (Query.selectedWordsAllAnswers.length === 1) {
    var y = Math.map(Query.selectedWordsAllAnswers[0].correct, 0, 1, 100, 0);
    svg += '<line x1="0%" x2="100%" y1="' + y + '%" y2="' + y + '%" />';
  }
  svg += '<text y="100%" fill-opacity="0.4" x="50%" text-anchor="middle" font-size="160px">' + 
    Math.round(Math.map(Word.getKnownAverageOfArrayOverLastNAnswers(Query.words, Query.CONSIDERNANSWERS), 0, 1, 0, 100)) + '%</text>';
  return svg + '</svg>';
};


// query stats
//
// namespace for all funtions related to the statistics about words and lists shown during the query
Query.Stats = {};


// query stats update word information
//
// @param Word word: the word for which to show the stats
Query.Stats.updateWordInformation = function(word) {
  $(page['query']).find('#query-word-stats').html(Query.Drawing.getSvgOfWord(word));
};


// query stats update selected words informatino
//
// @param Word[] words: array of words for which to show the stats
Query.Stats.updateSelectedWordsInformation = function() {
  setTimeout(function() {
    // setTimeout to make sure it donesn't block the UI
    $(page['query']).find('#query-selected-words-stats').html(Query.Drawing.getSvgOfQueryAnswers());
  }, 5 );
};




// special chars box
$(page['query']).find('#query-show-special-chars').on('click', function() {
  $(page['query']).find('.special-chars').toggleClass('display-none');
  if (queryInput !== null) {
    setCursorPosition(queryInput, parseInt($(queryInput).data('last-cursor-position')));
  }
});

$(page['query']).find('#query-special-chars select').on('change', function() {
  $(page['query']).find('.special-chars > div').hide();
  $(page['query']).find('.special-chars-' + $(this).val()).show();
});

$(page['query']).find('#query-answer').on('keydown keyup click', function(event) {
  queryInput = this;
  var cursor = getCursorPosition(this);
  $(this).data('last-cursor-position', cursor);
});

var queryInput = $(page['query']).find('#query-answer')[0];
$(page['query']).find('#query-special-chars > div > div').on('click', function(e) {
  if (queryInput !== null) {
    var cursorPos = parseInt($(queryInput).data('last-cursor-position'));
    setCursorPosition(lastFocusedInput, cursorPos);
    insertAtCursor(queryInput, $(this).html());
    cursorPos++;
    setCursorPosition(queryInput, cursorPos);
    $(queryInput).data('last-cursor-position', cursorPos);
  }
});


// initial loading
Query.updateDom();