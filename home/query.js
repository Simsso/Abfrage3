"use strict";

// constructors

// List
//
// word list object factory method
//
// @param int id: id
// @param string name: name
// @param int creator: id of the list creator
// @param string comment: comment of the list (short info or so)
// @param string language1: first language of the list
// @param string language2: second language of the list
// @param int creationTime: unix timestamp of the creation time
// @param Word[] words: array of the lists words
//
// @return List: list object
function List(id, name, creator, comment, language1, language2, creationTime, words) {
  this.id = id;
  this.name = name;
  this.creator = creator;
  this.language1 = language1;
  this.language2 = language2;
  this.comment = comment;
  this.creationTime = creationTime;

  this.words = [];
  // convert parsed JSON data to "Word" objects
  for (var i = 0; i < words.length; i++) {
    this.words.push(new Word(words[i].id, words[i].list, words[i].language1, words[i].language2, words[i].answers)); 
  }


  // methods

  // get name
  //
  // @return string: name of the list
  this.getName = function() {
    return this.name;
  };


  // get known average
  //
  // goes through all words and looks in their answer history how often they have been answered correctly
  // calculates the known average of the list by calculating the average of all single words
  //
  // @return float: known average of the list
  this.getKnownAverage = function() {
    if (this.words.length === 0) return 0;

    var sum = 0.0;
    for (var i = 0; i < this.words.length; i++) {
      sum += this.words[i].getKnownAverage();
    }

    return sum / this.words.length;
  };
}

// Word
//
// word object factory method
//
// @param int id: id
// @param int list: id of the list which the word belongs to
// @param string language1: meaning of the word in the first language of the list
// @param string language2: meaning of the word in the second language of the list
// @param QueryAnswer[] answers: array of query answers
// 
// @return Word: word object
function Word(id, list, language1, language2, answers) {
  this.id = id;
  this.language1 = language1;
  this.language2 = language2;
  this.list = list;

  this.answers = [];
  // convert JSON-parsed answers into Answer objects
  for (var i = 0; i < answers.length; i++) {
    this.answers.push(new QueryAnswer(answers[i].word, answers[i].correct, answers[i].type, answers[i].direction, answers[i].id, answers[i].time));
  }


  // methods

  // get known average
  //
  // goes through all answers and determines how often the word has been known
  // if it has been known 3 time in 4 queries the return value will be 0.75
  //
  // @return float: known average of the word
  this.getKnownAverage = function() {
    return this.getKnownAverageOverLastNAnswers(this.answers.length);
  };
  

  // get known average over last n answers
  //
  // goes through the last n answers and determines how often the word has been known
  //
  // @param int n: number of answers to consider 
  //
  // @return float: known average of the word considering the last n answers
  this.getKnownAverageOverLastNAnswers = function(n) {
    if (this.answers.length === 0) return 0;
    if (n > this.answers.length) return Math.map(this.getKnownAverage(), 0, 1, 0, this.answers.length / n);

    var knownCount = 0.0;
    for (var i = (this.answers.length - n); i < this.answers.length; i++) {
      if (this.answers[i].correct === 1) {
        knownCount++;
      }
    }
    return knownCount / n;
  };
}

// static functions
// get word known below
//
// @param Word[] wordArray: array of words to consider
// @param float percentage: percentage known
// 
// @return Word: random word out of the passed array which has been known below the passed percentage
Word.getWordKnownBelow = function(wordArray, percentage) {
  var wordsBelow = [];
  // search for all words below given percentage
  for (var i = 0; i < wordArray.length; i++) {
    if (wordArray[i].getKnownAverage() < percentage) {
      wordsBelow.push(wordArray[i]);
    }
  }
  
  return wordsBelow.getRandomElement();
};

// get known average of array
//
// @param Word[] wordArray: array of words to consider
// 
// @return float: known average of the words in the given array
Word.getKnownAverageOfArray = function(wordArray) {
  if (wordArray.length === 0) return 0;

  var sum = 0.0;
  for (var i = 0; i < wordArray.length; i++) {
    sum += wordArray[i].getKnownAverage();
  }

  return sum / wordArray.length;
};


// query algorithms
function QueryAlgorithm() {}

// query algorithm - in order
//
// saves the given array of words shuffled
// asks one word after another and restarts at the beginning after a whole iteration
//
// @param Word[] words: array of words to work with
//
// @return QueryAlgorithm.InOrder: query algorithm object
QueryAlgorithm.InOrder = function(words) {
  this.index = -1;
  this.words = words;
  // if it has been known 3 time in 4 queries the return value will be 0.75
  this.iterations = 0;
  
  // get next word
  // 
  // @return Word: returns the next word considering the query algorithm which this function belongs to
  this.getNextWord = function() {
    if (this.words.length === 0) return undefined;
    
    if (this.words.length === this.index + 1) {
      this.index = -1;
      this.iterations++;
    } 
    this.index++;
    return this.words[this.index];
  };
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
// @return QueryAlgorithm.GrouWords: query algorithm object
QueryAlgorithm.GroupWords = function(words, groupSize, careAboutLastNAnswers) {
  if (groupSize === undefined) groupSize = 6;
  if (careAboutLastNAnswers === undefined) careAboutLastNAnswers = 4;
  
  this.groupSize = groupSize;
  this.careAboutLastNAnswers = careAboutLastNAnswers;
  this.words = words.slice().shuffle(); // shuffle a copy of the words array
  this.index = (groupSize < this.words.length) ? groupSize : 0;
  this.lastReturnedWord = null;
  
  this.currentGroup = [];
  
  if (this.words.length > groupSize) {
    this.currentGroup = this.words.slice(0, groupSize); // TODO: check slice parameters
  }
  else { // there are not enough words given to perform a senseful GroupWords QueryAlgorithm
    this.currentGroup = words;
  }
  
  
  
  // get next word
  // 
  // @return Word: returns the next word considering the query algorithm which this function belongs to
  this.getNextWord = function() {
    // go through all words in the current group and check of words which are known
    for (var i = 0; i < this.currentGroup.length; i++) {
      if (this.currentGroup[i].getKnownAverageOverLastNAnswers(this.careAboutLastNAnswers) === 1) {
        // a word is known
        this.index++; // increment the pointer which indicates where the this.currentGroup ends in the this.words array
        if (this.index >= this.words.length) { // gone through all words 
          this.index = 0; // reset index and restart at the beginning
        }
        this.currentGroup[i] = this.words[this.index];
      }
    }
    
    return this.currentGroup.getRandomElement();
  };
};


// query answer
//
// query answer object factory method
//
// @param int word: id of the word which has been answered
// @param byte correct: answer was correct? 0 = wrong; 1 = correct
// @param QueryType type: type of the query (buttons = 1 or text box = 0)
// @param QueryDirection direction: direction of the query (0 = first to second language; 1 = 2nd to 1st)
// @param int id: id of the answer
// @param int time: unix time stamp when the query has been answered
//
// @return QueryAnswer: query answer object
function QueryAnswer(word, correct, type, direction, id, time) {
  this.word = word;
  this.correct = correct;
  this.type = type;
  this.direction = direction;

  if (time === undefined) 
    this.time = Date.seconds();
  else 
    this.time = time;

  if (id === undefined) 
    this.id = undefined;
  else 
    this.id = id;
}



// enumerations
var QueryAlgorithmEnum = Object.freeze({
  Random: 0, 
  UnderAverage: 1, 
  GroupWords: 2,
  InOrder: 3
});

var QueryDirection = Object.freeze({
  Both: -1, 
  Ltr: 0, 
  Rtl: 1
});

var QueryType = Object.freeze({
  TextBox: 0, 
  Buttons: 1
});

var QueryAnswerState = Object.freeze({
  Start: 0, 
  NotSureClicked: 1,
  Known: 2,
  NotKnown: 3,
  WaitToContinue: 4,
  NotKnownClicked: 5
});


var queryLabels = null;
var queryAttachments = null;
var queryLists = null;

var querySelectedLabel = [];
var querySelectedLists = [];

// refresh query label list
//
// Before starting a query the user can selct lists which he wants to learn. Multiple lists can be selected at the same time by selecting a whole label in the label list.
// This label list is downloaded and added to the DOM by the following function.
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function refreshQueryLabelList(showLoadingInformation) {
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


    // labels
    queryLabels = data.labels;

    // label list attachments
    // information which list is attached to which label
    queryAttachments = data.label_list_attachments;

    // word lists
    queryLists = [];
    for (var i = 0; i < data.lists.length; i++) {
      queryLists.push(
        new List(
          data.lists[i].id, 
          data.lists[i].name, 
          data.lists[i].creator, 
          data.lists[i].comment, 
          data.lists[i].language1,
          data.lists[i].language2, 
          data.lists[i].creationTime, 
          data.lists[i].words
        )
      );
    }

    $(page['query']).find('#query-selection').html('<p><input id="query-start-button" type="button" value="Start test" class="width-100 height-50px font-size-20px" disabled="true"/></p><div id="query-label-selection"></div><div id="query-list-selection"></div><br class="clear-both">');

    // provide label selection
    $(page['query']).find('#query-label-selection').html(getHtmlTableOfLabelsQuery(queryLabels));

    // provide list selection
    refreshQueryListSelection();


    // start query button click event
    $(page['query']).find('#query-start-button').on('click', function() {
      if (queryRunning) {
        stopQuery();
      }
      else {
        startQuery();
      }
    });

    // checkbox click event
    $(page['query']).find('#query-label-selection tr').on('click', function(){
      // read label id from checkbox data tag
      var labelId = $(this).data('query-label-id');
      // checkbox has been unchecked
      if($(this).data('checked') === true) {
        removeLabelFromQuery(labelId);
      }
      // checkbox has been checked
      else if ($(this).data('checked') === false) { 
        addLabelToQuery(labelId);
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
  });
}


// refresh query list selection
//
// Refreshes the list of word lists where the user can select the lists for the test.
//
function refreshQueryListSelection() {
  var html = '';

  queryLists.sort(compareListsByName);

  for (var i = 0; i < queryLists.length; i++) {
    var selected = false;
    if (querySelectedLists.contains(queryLists[i].id)) 
      selected = true;

    html += getListRow(queryLists[i], selected);
  }


  $(page['query']).find('#query-list-selection').html('<table class="box-table cursor-pointer no-flex"><tr class="cursor-default"><th colspan="2">Lists</th></tr>' + html + '</table');

  // checkbox click event
  $(page['query']).find('#query-list-selection tr').on('click', function(){
    // read list id from checkbox data tag
    var listId = $(this).data('query-list-id');
    
    // checkbox has been unchecked
    if($(this).data('checked') === true) {
      removeListFromQuery(listId);
    }
    // checkbox has been checked
    else if ($(this).data('checked') === false) { 
      addListToQuery(listId);
    }
  });
}

// add label to query
//
// When the user has clicked on a label the related lists (which are connected to the label) have to be marked and added to the test
//
// @param int labelId: the id of the label
function addLabelToQuery(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < queryAttachments.length; i++) {
    if (queryAttachments[i].label == labelId) {
      addListToQuery(queryAttachments[i].list);
    }
  }

  $(page['query']).find('#query-label-selection tr[data-query-label-id=' + labelId + ']').addClass('active').data('checked', true);
  querySelectedLabel.push(labelId);
}


// remove label from query
// 
// opposite of add label to query
//
// @param int labelId: id of the label to remove
function removeLabelFromQuery(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < queryAttachments.length; i++) {
    if (queryAttachments[i].label == labelId) {
      removeListFromQuery(queryAttachments[i].list);
    }
  }
  $(page['query']).find('#query-label-selection tr[data-query-label-id=' + labelId + ']').removeClass('active').data('checked', false);
  querySelectedLabel.removeAll(labelId); // remove all removes all occurences of the passed object
}


// add list to query
//
// adds a list with its words to the test
//
// @param int listId: list id
function addListToQuery(listId) {
  querySelectedLists.push(getListById(listId));
  $(page['query']).find('#query-list-selection tr[data-query-list-id=' + listId + ']').data('checked', true).addClass('active');
  checkStartQueryButtonEnable();

  // update information about the language of the selected words
  updateQueryListLanguageInformation(getLanguagesOfWordLists(querySelectedLists));
}


// remove list from query
// 
// opposite of add list from query
//
// @param int listId: list id
function removeListFromQuery(listId) {
  querySelectedLists.removeAll(getListById(listId));
  $(page['query']).find('#query-list-selection tr[data-query-list-id=' + listId + ']').data('checked', false).removeClass('active');
  checkStartQueryButtonEnable();

  // update information about the language of the selected words
  updateQueryListLanguageInformation(getLanguagesOfWordLists(querySelectedLists));
}


// get list row
// 
// creates a formatted <tr> HTML-element for the table of word lists
// 
// @param List list: list for which the row will be created
// @param bool selected: true if the list is selected for the next test
// 
// @return <tr> HTML-element for the table of word lists
function getListRow(list, selected) {
  return '<tr' + (selected?'class="active"':'') + ' data-query-list-id="' + list.id + '" data-checked="false"><td>' + list.name + '</td><td>' + list.words.length + '</td></tr>';
}

// check start query button enable
//
// enables the start query button if words have been selected due clicking on lists or labels
function checkStartQueryButtonEnable() {
  $(page['query']).find('#query-start-button').prop('disabled', querySelectedLists.length === 0);
}

// compare lists by name
//
// @param string a: list one name
// @param string b: list two name
//
// @return int: returns like a spaceship operator (<=>)
function compareListsByName(a, b) {
  if (a.name < b.name) return -1; 
  if (a.name > b.name) return 1; 
  return 0; 
}


// get html table of labels
// 
// creates a <table> HTML-element containig the passed query labels
//
// @param Label[] queryLabels: array of labels
// 
// @return string: <table> HTML-element or paragraph that no labels exist
function getHtmlTableOfLabelsQuery(queryLabels) {
  // method returns the HTML code of the label list
  var html = getHtmlListOfLabelIdQuery(queryLabels, 0, 0);

  if (html.length > 0) {
    html = '<table class="box-table cursor-pointer"><tr class="cursor-default"><th>Labels</th></tr>' + html + '</table>';
  }
  else {
    // if there was no code returned there are no labels to show
    html = noLabels;
  }
  return html;
}

// get HTML list of label id
// 
// recursively creates a list of labels and sub-labels, etc.
//
// @param Label[] queryLabels: array of labels
// @param int id: id of the current label
// @param unsigned int indenting: indenting of the current label (increases with every recursion by one)
//
// @return string: HTML-ist showing a label and its sub-labels
function getHtmlListOfLabelIdQuery(queryLabels, id, indenting) {
  var output = '';
  var labelIds = getLabelIdsWithIndenting(queryLabels, indenting);
  for (var i = 0; i < labelIds.length; i++) {
    var currentLabel = queryLabels[getLabelIndexByLabelId(queryLabels, labelIds[i])];
    if (currentLabel.parent_label == id) {
      output += getSingleListElementOfLabelListQuery(currentLabel, indenting);
      output += getHtmlListOfLabelIdQuery(queryLabels, labelIds[i], indenting + 1);
    }
  }
  return output;
}


// get single list element of label list
//
// @param Label label: label object
// @param unsigned int indenting: indenting of the label
//
// @return string: HTML-row of a single label
function getSingleListElementOfLabelListQuery(label, indenting) {
  var subLabelsCount = numberOfSubLabels(queryLabels, label.id);
  var expanded = false; // show all labels collapsed

  return '<tr data-checked="false" data-query-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting === 0)?'':' style="display: none; "') + '><td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount === 0) ? 16 : 0)) + 'px; ">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;' + label.name + '</td></tr>';
}

// get list by id
// 
// searches through the queryLists array
// 
// @param int id: id of the list
//
// @return List: object of the list which has the passed id
function getListById(id) {
  for (var i = 0; i < queryLists.length; i++) {
    if (queryLists[i].id === id) {
      return queryLists[i];
    }
  }
  return undefined;
}




var queryWords = [], // array of all words which the user selected for the query
    queryChosenAlgorithm = QueryAlgorithmEnum.Random, // the algorithm the user has chosen
    queryChosenDirection = QueryDirection.Both, // the query direction the user has chosen
    queryChosenType = QueryType.TextBox, // type (text box or buttons to answer the question)
    queryRunning = false, // true if a query is running
    currentWord = null, // reference to the Word object which is currently asked
    queryCurrentDirection = null, // the query direction (0 or 1)
    currentWordCorrectAnswer = null, // the string value containing the currect answer for the current word
    queryWrongAnswerGiven = false, // true if the user already gave the wrong answer
    queryAnswers = [], // array of answers the user already gave
    nextIndexToUpload = 0, // first index of answers which has not been uploaded already (if queryAnswers[] contains 4 words and 3 of them have been uploaded the var will hav the value 3)
    queryCurrentAnswerState = QueryAnswerState.Start, // query answer state
    queryInOrderAlgorithm, 
    queryGroupWordsAlgorithm;
    

// start query
function startQuery() {
  queryRunning = true;

  // update start query button and test fields
  $(page['query']).find('#query-start-button').attr('value', 'Stop test');
  $(page['query']).find('#query-not-started-info').addClass('display-none');
  $(page['query']).find('#query-content-table').removeClass('display-none');
  
  queryRunning = true;

  // produce one array containing all query words
  queryWords = [];
  for (var i = 0; i < querySelectedLists.length; i++) {
    queryWords = queryWords.concat(querySelectedLists[i].words);
  }


  // update information about the language of the selected words
  updateQueryListLanguageInformation(getLanguagesOfWordLists(getListsOfWords(queryWords)));

  // array of ids of words selecte for the query
  var wordIds = [];
  for (var j = 0; j < queryWords.length; j++) {
    wordIds.push(queryWords[j].id);
  }
  
  queryInOrderAlgorithm = new QueryAlgorithm.InOrder(queryWords);
  queryGroupWordsAlgorithm = new QueryAlgorithm.GroupWords(queryWords);

  nextWord(); // actually start the query

  //$(page['query']).find('#query-select-box img[data-action="collapse"]').trigger('collapse');
  $(page['query']).find('#query-box img[data-action="expand"]').trigger('expand'); // expand query container

}


// stop query
function stopQuery() {
  queryRunning = false;

  $('#query-start-button').attr('value', 'Start test');
  $('#query-not-started-info').removeClass('display-none');
  $('#query-content-table').addClass('display-none');
}


// next word
//
// gets the next word for the test
// updates the DOM (show the word)
function nextWord() {
  queryCurrentAnswerState = QueryAnswerState.Start;
  
  queryWrongAnswerGiven = false;
  
  $(page['query']).find('#query-answer-not-known').prop('disabled', false);
  $(page['query']).find('#query-answer-known').attr('value', 'I know!');
  $(page['query']).find('#query-answer-not-known').attr('value', 'No idea.');
  $(page['query']).find('#query-answer-buttons').hide();
  $(page['query']).find('#correct-answer').hide();
  $(page['query']).find('#query-answer-not-sure').prop('disabled', false);

  
  currentWord = getNextWord();
  var listOfTheWord = getListById(currentWord.list);

  if (queryChosenDirection == QueryDirection.Both) { // both directions
    queryCurrentDirection = Math.round(Math.random()); // get random direction
  }
  else {
    queryCurrentDirection = queryChosenDirection;
  }

  // fill the question fields
  if (queryCurrentDirection == QueryDirection.Ltr) {
    $(page['query']).find('#query-lang1').html(listOfTheWord.language1);
    $(page['query']).find('#query-lang2').html(listOfTheWord.language2);
    $(page['query']).find('#query-question').html(currentWord.language1);
    currentWordCorrectAnswer = currentWord.language2;
  }
  else if (queryCurrentDirection == QueryDirection.Rtl) {
    $(page['query']).find('#query-lang1').html(listOfTheWord.language2);
    $(page['query']).find('#query-lang2').html(listOfTheWord.language1);
    $(page['query']).find('#query-question').html(currentWord.language2);
    currentWordCorrectAnswer = currentWord.language1;
  }
  
  $(page['query']).find('#query-answer').val('').focus();

  // known average for single word information
  $(page['query']).find('#query-word-mark').html(Math.round(currentWord.getKnownAverage() * 100) + "%");
}

// get next word
//
// determines the next word depending on the selected query algorithm
//
// @return Word: word object
function getNextWord() {
  switch (queryChosenAlgorithm) {
    case QueryAlgorithmEnum.Random:
      return queryWords.getRandomElement();
    case QueryAlgorithmEnum.UnderAverage:
      var avg = Word.getKnownAverageOfArray(queryWords);
      return Word.getWordKnownBelow(queryWords, avg);
    case QueryAlgorithmEnum.InOrder:
      return queryInOrderAlgorithm.getNextWord();
    case QueryAlgorithmEnum.GroupWords:
      return queryGroupWordsAlgorithm.getNextWord();
  }
}

// allow enter pressing to check the user's answer
$(page['query']).find('#query-answer').on('keypress', function(e) {
  if (e.which == 13) {
    if (checkAnswer($(this).val(), currentWordCorrectAnswer)) { // correct answer  
      if (queryCurrentAnswerState == QueryAnswerState.NotKnown || queryCurrentAnswerState == QueryAnswerState.NotSureClicked || queryCurrentAnswerState == QueryAnswerState.WaitToContinue) {
        nextWord();
      }
      else {
        queryCurrentAnswerState = QueryAnswerState.Known;
        processQueryCurrentAnswerState();
      }
    }
    else { // wrong answer
      if (queryCurrentAnswerState == QueryAnswerState.NotKnown) { // answer already shown and already saved that the user didn't know the word
        return;
      }
      
      queryCurrentAnswerState = QueryAnswerState.NotKnown;
      processQueryCurrentAnswerState();

    }
  }
});


// add query to answer
//
// push into arrays whether the user has answered correctly
//
// @param Word word: word which has been answered
// @param byte correct: right or wrong answer (0 = wrong; 1 = right)
function addQueryAnswer(word, correct) {
  var answer = new QueryAnswer(word.id, correct, queryChosenType, queryCurrentDirection); 
  queryAnswers.push(answer);
  refreshQueryResultsUploadCounter();
  word.answers.push(answer);
}

// check answer
//
// compares two string for "equality"
// 
// eqality means that e.g. spaces at the end don't matter
//
// @param string user: the user's string
// @param string correct: the correct string
function checkAnswer(user, correct) {
  return (user.trim() == correct.trim());

  // TODO: more complex checking
}


// refresh query results upload button
//
// enables or disables the "Upload query results" button depending on the amount of answers which have not been uploaded yet
function refreshQueryResultsUploadButton() {
  var notUploadedAnswersCount = queryAnswers.length - nextIndexToUpload;
  $(page['query']).find('#query-results-upload-button').prop('disabled', !(notUploadedAnswersCount > 0)).attr('value', 'Upload ' + ((notUploadedAnswersCount > 0)? notUploadedAnswersCount + ' ' : '') + 'answer' + ((notUploadedAnswersCount == 1) ? '' : 's'));
}


// refresh query results upload counter
//
// The upload counter is an information like "Uploaded 0/0 test answers.". 
// The functions updates the values.
function refreshQueryResultsUploadCounter() {
  $(page['query']).find('#query-results-upload-counter').html('Uploaded ' + nextIndexToUpload + '/' + queryAnswers.length + ' test answers.');
}

$(page['query']).find('#query-results-upload-button').on('click', uploadQueryResults);


// upload query results
//
// uploads the answers which have not been uploaded yet
// queryAnswers[] stores all answers
// nextIndexToUpload points to the first element in queryAnswers which has not been uploaded yet
function uploadQueryResults() {
  var startedUploadIndex = nextIndexToUpload; 
  var answersToUpload = queryAnswers.slice(nextIndexToUpload);
  nextIndexToUpload = queryAnswers.length;

  refreshQueryResultsUploadButton();

  $.ajax({
    type: 'POST',
    url: 'server.php?action=upload-query-results',
    data: { 'answers': JSON.stringify(answersToUpload)},
    error: function(jqXHR, textStatus, errorThrown) {
      // remove the (because of the errror) not uploaded answers and append them to the array again to ensure they will be re-uploaded later
      queryAnswers.splice(startedUploadIndex, answersToUpload.length);
      nextIndexToUpload -= answersToUpload.length;
      queryAnswers.pushElements(answersToUpload);
      refreshQueryResultsUploadButton();
    }
  })
  .done( function( data ) {
    data = handleAjaxResponse(data);
    refreshQueryResultsUploadCounter();
  });
}


// query answer buttons events (know, not sure, don't know)
// known
$(page['query']).find('#query-answer-known').on('click', function() {
  // known button click event
  if (queryCurrentAnswerState == QueryAnswerState.WaitToContinue || queryCurrentAnswerState == QueryAnswerState.NotKnown) {
    nextWord();
  }
  else {
    queryCurrentAnswerState = QueryAnswerState.Known;
    processQueryCurrentAnswerState();
  }
});
// not sure
$(page['query']).find('#query-answer-not-sure').on('click', function() {
  // not sure button click event
  queryCurrentAnswerState = QueryAnswerState.NotSureClicked;
  processQueryCurrentAnswerState();
});
// not known
$(page['query']).find('#query-answer-not-known').on('click', function() {
  // not known button click event
  queryCurrentAnswerState = QueryAnswerState.NotKnownClicked;
  processQueryCurrentAnswerState();
});


// process query current answer state
function processQueryCurrentAnswerState() {
  switch (queryCurrentAnswerState) {
    case queryCurrentAnswerState.Start:
      return;
    case QueryAnswerState.Known:
      $(page['query']).find('#query-box').trigger('shadow-blink-green');
      addQueryAnswer(currentWord, 1);
      tryAutoUpload();
      nextWord();
      return;
    case QueryAnswerState.NotSureClicked:
      $(page['query']).find('#query-answer-not-sure').prop('disabled', true);
      $(page['query']).find('#query-answer-known').attr('value', 'I knew that!');
      $(page['query']).find('#query-answer-not-known').attr('value', 'I didn\'t know that.');
      showQuerySolution();
      return;
    case QueryAnswerState.NotKnownClicked:
      queryCurrentAnswerState = QueryAnswerState.WaitToContinue;
      // no break here
    case QueryAnswerState.NotKnown:
      $(page['query']).find('#query-answer-not-known').prop('disabled', true);
      $(page['query']).find('#query-answer-not-sure').prop('disabled', true);
      $(page['query']).find('#query-answer-known').attr('value', 'Continue.');
      $(page['query']).find('#query-word-mark').html(Math.round(currentWord.getKnownAverage() * 100) + "%");
      showQuerySolution();
      addQueryAnswer(currentWord, 0);
      tryAutoUpload();
      return;
  }
}


// show query solution
//
// shows the query solution to the user
function showQuerySolution() {
  $(page['query']).find('#query-answer-buttons').show().html(currentWordCorrectAnswer);
  $(page['query']).find('#correct-answer').show().html(currentWordCorrectAnswer);
  $(page['query']).find('#query-answer').select();
}




// settings (algorithm, direction and type)

// query algorithm
$(page['query']).find('#query-algorithm tr').on('click', function() {
  $(page['query']).find('#query-algorithm tr').removeClass('active');
  $(this).addClass('active');
  queryChosenAlgorithm = parseInt($(this).data('algorithm'));
});

// query direction
$(page['query']).find('#query-direction tr').on('click', function() {
  $(page['query']).find('#query-direction tr').removeClass('active');
  $(this).addClass('active');
  queryChosenDirection = parseInt($(this).data('direction'));
});

// query type
$(page['query']).find('#query-type tr').on('click', function() {
  $(page['query']).find('#query-type tr').removeClass('active');
  $(this).addClass('active');
  setQueryType(parseInt($(this).data('type')));
});


// set query type
//
// @param QueryType queryType: query type (buttons or text box)
function setQueryType(queryType) {
  if (queryChosenType != queryType) {
    queryChosenType = queryType;
    
    if (queryType == QueryType.Buttons) {
      $(page['query']).find('#query-answer-table-cell-text-box').hide();
      $(page['query']).find('#query-answer-table-cell-buttons').show();
      
    }
    else if (queryType == QueryType.TextBox) {
      $(page['query']).find('#query-answer-table-cell-buttons').hide();
      $(page['query']).find('#query-answer-table-cell-text-box').show();
      $(page['query']).find('#query-answer').focus();
    }
  }
}



// try auto upload
//
// query results auto upload
function tryAutoUpload() {
  if (autoUploadEnabled()) 
    uploadQueryResults();
  else
    refreshQueryResultsUploadButton();
}

// auto upload enabled
//
// @return bool: returns if the user has enabled auto upload of their query answers
function autoUploadEnabled() {
  return $(page['query']).find('#query-results-auto-upload').is(':checked');
}

// query auto upload checkbox event listener
$(page['query']).find('#query-results-auto-upload').on('click', function() {
  if (autoUploadEnabled()) 
    uploadQueryResults();
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
function updateQueryListLanguageInformation(languages) {
  if (languages[0] === undefined || languages[1] === undefined) {
    languages = ["First language", "Second language"];
  }

  $(page['query']).find('span[data-value="first-language-information"]').html(languages[0]);
  $(page['query']).find('span[data-value="second-language-information"]').html(languages[1]);
}



// get lists of words
//
// gets the lists which the words belong to
// e.g. a word contained in the passed array has a list with an id "5" the return array will have contain the list object
// this happens with all passed words
//
// @param Word[] word: array of words
//
// @return List[]: array of lists which belongs to the words
function getListsOfWords(word) {

  var list = [];

  for (var i = 0; i < word.length; i++) {
    var listOfCurrentWord = getListById(word[i].list);
    if (!list.contains(listOfCurrentWord)) {
      list.push(listOfCurrentWord);
    }
  }

  return list;
}



// detect languages of word lists
// 
// @param List[] list: lists to detect langues
//
// @return string[2]: an array with two elements containing the two languages of the given word lists
// @return string[2]: if the lists have different language the function will return [undefined, undefined]
function getLanguagesOfWordLists(list) {

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
}



// initial loading
refreshQueryLabelList(true);