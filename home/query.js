/* jshint browser: true */
/* global jQuery: false */
/* global $: false */
/* global loading: false */
/* global handleAjaxResponse: false */

// constructors

// word list
function List(id, name, creator, comment, language1, language2, creation_time, words) {
  this.id = id;
  this.name = name;
  this.creator = creator;
  this.language1 = language1;
  this.language2 = language2;
  this.comment = comment;
  this.creation_time = creation_time;

  this.words = [];
  for (var i = 0; i < words.length; i++) {
    this.words.push(new Word(words[i].id, words[i].list, words[i].language1, words[i].language2, words[i].answers)); 
  }


  // methods

  this.getName = function() {
    return this.name;
  };

  this.getKnownAverage = function() {
    if (this.words.length === 0) return 0;

    var sum = 0.0;
    for (var i = 0; i < this.words.length; i++) {
      sum += this.words[i].getKnownAverage();
    }

    return sum / this.words.length;
  };
}

// word
function Word(id, list, language1, language2, answers) {
  this.id = id;
  this.language1 = language1;
  this.language2 = language2;
  this.list = list;

  this.answers = [];
  for (var i = 0; i < answers.length; i++) {
    this.answers.push(new QueryAnswer(answers[i].word, answers[i].correct, answers[i].id, answers[i].time));
  }


  // methods
  this.getKnownAverage = function() {
    return this.getKnownAverageOverLastNAnswers(this.answers.length);
  };
  
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

QueryAlgorithm.InOrder = function(words) {
  this.index = -1;
  this.words = words;
  this.iterations = 0;
  
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
  
  
  this.getNextWord = function() {
    // go through all words in the current group and check of words which are known
    for (var i = 0; i < this.currentGroup.length; i++) {
      if (this.currentGroup[i].getKnownAverageOverLastNAnswers(this.careAboutLastNAnswers) === 1) {
        // a word is known
        this.index++; // increment the pointer which indicates where the this.currentGroup ends in the this.words array
        console.log("------------------> took the next word!!");
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
function QueryAnswer(word, correct, id, time) {
  this.word = word;
  this.correct = correct;

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

// get label list of user
function refreshQueryLabelList(showLoadingInformation) {
  if (showLoadingInformation) {
    $('#query-selection').html(loading);
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
    var dataJSON = handleAjaxResponse(data);


    // labels
    queryLabels = dataJSON.labels;

    // label list attachments
    queryAttachments = dataJSON.label_list_attachments;

    // lists
    queryLists = [];
    for (var i = 0; i < dataJSON.lists.length; i++) {
      queryLists.push(
        new List(
          dataJSON.lists[i].id, 
          dataJSON.lists[i].name, 
          dataJSON.lists[i].creator, 
          dataJSON.lists[i].comment, 
          dataJSON.lists[i].language1,
          dataJSON.lists[i].language2, 
          dataJSON.lists[i].creation_time, 
          dataJSON.lists[i].words
        )
      );
    }

    $('#query-selection').html('<p><input id="query-start-button" type="button" value="Start test" class="width-100 height-50px font-size-20px" disabled="true"/></p><div id="query-label-selection"></div><div id="query-list-selection"></div><br class="clear-both">');

    // provide label selection
    $('#query-label-selection').html(getHtmlTableOfLabelsQuery(queryLabels));

    // provide list selection
    refreshQueryListSelection();


    // start query button click event
    $('#query-start-button').on('click', startQuery);

    // checkbox click event
    $('#query-label-selection tr').on('click', function(){
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
    $('#query-label-selection .small-exp-col-icon').on('click', function(e) {
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

// lists list function

function refreshQueryListSelection() {
  var html = '';

  queryLists.sort(compareListsByName);

  for (var i = 0; i < queryLists.length; i++) {
    var selected = false;
    if (querySelectedLists.contains(queryLists[i].id)) 
      selected = true;

    html += getListRow(queryLists[i], selected);
  }


  $('#query-list-selection').html('<table class="box-table cursor-pointer"><tr class="cursor-default"><th colspan="2">Lists</th></tr>' + html + '</table');

  // checkbox click event
  $('#query-list-selection tr').on('click', function(){
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

function addLabelToQuery(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < queryAttachments.length; i++) {
    if (queryAttachments[i].label == labelId) {
      addListToQuery(queryAttachments[i].list);
    }
  }

  $('#query-label-selection tr[data-query-label-id=' + labelId + ']').addClass('active').data('checked', true);
  querySelectedLabel.push(labelId);
}

function removeLabelFromQuery(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < queryAttachments.length; i++) {
    if (queryAttachments[i].label == labelId) {
      removeListFromQuery(queryAttachments[i].list);
    }
  }
  $('#query-label-selection tr[data-query-label-id=' + labelId + ']').removeClass('active').data('checked', false);
  querySelectedLabel.removeAll(labelId);
}

function addListToQuery(listId) {
  querySelectedLists.push(listId);
  $('#query-list-selection tr[data-query-list-id=' + listId + ']').data('checked', true).addClass('active');
  checkStartQueryButtonEnable();
}

function removeListFromQuery(listId) {
  querySelectedLists.removeAll(listId);
  $('#query-list-selection tr[data-query-list-id=' + listId + ']').data('checked', false).removeClass('active');
  checkStartQueryButtonEnable();
}

function getListRow(list, selected) {
  return '<tr' + (selected?'class="active"':'') + ' data-query-list-id="' + list.id + '" data-checked="false"><td>' + list.name + '</td><td>' + list.words.length + ' word' + ((list.words.length == 1) ? '': 's') + '</td></tr>';
}

function checkStartQueryButtonEnable() {
  $('#query-start-button').prop('disabled', querySelectedLists.length === 0);
}

function compareListsByName(a, b) {
  if (a.name < b.name) return -1; 
  if (a.name > b.name) return 1; 
  return 0; 
}


// label list functions

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
// returns the HTML list showing a label and it's sub-labels
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

// returns the HTML-row of a single label
function getSingleListElementOfLabelListQuery(label, indenting) {
  var subLabelsCount = numberOfSubLabels(queryLabels, label.id);
  var expanded = false; // show all labels collapsed

  return '<tr data-checked="false" data-query-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting === 0)?'':' style="display: none; "') + '><td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount === 0) ? 16 : 0)) + 'px; ">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;' + label.name + '</td></tr>';
}

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
    currentDirection = null, // the query direction (0 or 1)
    currentWordCorrectAnswer = null, // the string value containing the currect answer for the current word
    queryWrongAnswerGiven = false, // true if the user already gave the wrong answer
    queryAnswers = [], // array of answers the user already gave
    nextIndexToUpload = 0, // first index of answers which has not been uploaded already (if queryAnswers[] contains 4 words and 3 of them have been uploaded the var will hav the value 3)
    queryCurrentAnswerState = QueryAnswerState.Start, // query answer state
    queryInOrderAlgorithm, 
    queryGroupWordsAlgorithm;
    
    
function startQuery() {
  $('#query-not-started-info').addClass('display-none');
  $('#query-content-table').removeClass('display-none');
  queryRunning = true;

  // produce one array containing all query words
  queryWords = [];
  for (var i = 0; i < querySelectedLists.length; i++) {
    queryWords = queryWords.concat(getListById(querySelectedLists[i]).words);
  }

  // array of ids of words selecte for the query
  var wordIds = [];
  for (var j = 0; j < queryWords.length; j++) {
    wordIds.push(queryWords[j].id);
  }
  
  queryInOrderAlgorithm = new QueryAlgorithm.InOrder(queryWords);
  queryGroupWordsAlgorithm = new QueryAlgorithm.GroupWords(queryWords);

  console.log("b");
  nextWord();

  //$('#query-select-box img[data-action="collapse"]').trigger('collapse');
  $('#query-box img[data-action="expand"]').trigger('expand'); // expand query container

}

function nextWord() {
  queryCurrentAnswerState = QueryAnswerState.Start;
  
  queryWrongAnswerGiven = false;
  
  $('#query-answer-not-known').prop('disabled', false);
  $('#query-answer-known').attr('value', 'I know!');
  $('#query-answer-not-known').attr('value', 'No idea.');
  $('#query-answer-buttons').hide();
  $('#correct-answer').hide();
  $('#query-answer-not-sure').prop('disabled', false);

  
  currentWord = getNextWord();
  var listOfTheWord = getListById(currentWord.list);

  if (queryChosenDirection == QueryDirection.Both) { // both directions
    currentDirection = Math.round(Math.random()); // get random direction
  }
  else {
    currentDirection = queryChosenDirection;
  }

  // fill the question fields
  if (currentDirection == QueryDirection.Ltr) {
    $('#query-lang1').html(listOfTheWord.language1);
    $('#query-lang2').html(listOfTheWord.language2);
    $('#query-question').html(currentWord.language1);
    currentWordCorrectAnswer = currentWord.language2;
  }
  else if (currentDirection == QueryDirection.Rtl) {
    $('#query-lang1').html(listOfTheWord.language2);
    $('#query-lang2').html(listOfTheWord.language1);
    $('#query-question').html(currentWord.language2);
    currentWordCorrectAnswer = currentWord.language1;
  }
  
  setTimeout(function() {$('#query-answer').val('').focus(); }, 10);

  // known average for single word information
  $('#query-word-mark').html(Math.round(currentWord.getKnownAverage() * 100) + "%");
}

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
      for (var i = 0; i < queryGroupWordsAlgorithm.currentGroup.length; i++) {
        console.log(queryGroupWordsAlgorithm.currentGroup[i].getKnownAverageOverLastNAnswers(queryGroupWordsAlgorithm.careAboutLastNAnswers) + ' ' + queryGroupWordsAlgorithm.currentGroup[i].language1);
      }
      return queryGroupWordsAlgorithm.getNextWord();
  }
}

// allow enter pressing to check the user's answer
$('#query-answer').on('keypress', function(e) {
  if (e.which == 13) {
    if (checkAnswer($(this).val(), currentWordCorrectAnswer)) { // correct answer  
      if (queryCurrentAnswerState == QueryAnswerState.NotKnown || queryCurrentAnswerState == QueryAnswerState.NotSureClicked || queryCurrentAnswerState == QueryAnswerState.WaitToContinue) {
        console.log("c");
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

// push into arrays whether the user has answered correctly
function addQueryAnswer(word, correct) {
  var answer = new QueryAnswer(word.id, correct);
  queryAnswers.push(answer);
  word.answers.push(answer);
}

function checkAnswer(user, correct) {
  return (user.trim() == correct.trim());
}

function refreshQueryResultsUploadButton() {
  var notUploadedAnswersCount = queryAnswers.length - nextIndexToUpload;
  $('#query-results-upload-button').prop('disabled', !(notUploadedAnswersCount > 0)).attr('value', 'Upload ' + ((notUploadedAnswersCount > 0)? notUploadedAnswersCount + ' ' : '') + 'answer' + ((notUploadedAnswersCount == 1) ? '' : 's'));
}

$('#query-results-upload-button').on('click', uploadQueryResults);

function uploadQueryResults() {
  var answersToUpload = queryAnswers.slice(nextIndexToUpload);
  nextIndexToUpload = queryAnswers.length;

  refreshQueryResultsUploadButton();

  $.ajax({
    type: 'POST',
    url: 'server.php?action=upload-query-results',
    data: { 'answers': JSON.stringify(answersToUpload)},
    error: function(jqXHR, textStatus, errorThrown) {
      queryAnswers.pushElements(answersToUpload);
      refreshQueryResultsUploadButton();
    }
  })
  .done( function( data ) {
    data = handleAjaxResponse(data);
  });
}


// query answer buttons events (know, not sure, don't know)
$('#query-answer-known').on('click', function() {
  // known button click event
  if (queryCurrentAnswerState == QueryAnswerState.WaitToContinue || queryCurrentAnswerState == QueryAnswerState.NotKnown) {
    console.log("d");
    nextWord();
  }
  else {
    queryCurrentAnswerState = QueryAnswerState.Known;
    processQueryCurrentAnswerState();
  }
});
$('#query-answer-not-sure').on('click', function() {
  // not sure button click event
  queryCurrentAnswerState = QueryAnswerState.NotSureClicked;
  processQueryCurrentAnswerState();
});
$('#query-answer-not-known').on('click', function() {
  // not known button click event
  queryCurrentAnswerState = QueryAnswerState.NotKnownClicked;
  processQueryCurrentAnswerState();
});

function processQueryCurrentAnswerState() {
  switch (queryCurrentAnswerState) {
    case queryCurrentAnswerState.Start:
      return;
    case QueryAnswerState.Known:
      $('#query-box').trigger('shadow-blink-green');
      addQueryAnswer(currentWord, 1);
      tryAutoUpload();
      console.log("a");
      nextWord();
      return;
    case QueryAnswerState.NotSureClicked:
      $('#query-answer-not-sure').prop('disabled', true);
      $('#query-answer-known').attr('value', 'I knew that!');
      $('#query-answer-not-known').attr('value', 'I didn\'t know that.');
      showQuerySolution();
      return;
    case QueryAnswerState.NotKnownClicked:
      queryCurrentAnswerState = QueryAnswerState.WaitToContinue;
      // no break here
    case QueryAnswerState.NotKnown:
      $('#query-answer-not-known').prop('disabled', true);
      $('#query-answer-not-sure').prop('disabled', true);
      $('#query-answer-known').attr('value', 'Continue.');
      $('#query-word-mark').html(Math.round(currentWord.getKnownAverage() * 100) + "%");
      showQuerySolution();
      addQueryAnswer(currentWord, 0);
      tryAutoUpload();
      return;
  }
}

function showQuerySolution() {
  $('#query-answer-buttons').show().html(currentWordCorrectAnswer);
  $('#correct-answer').show().html(currentWordCorrectAnswer);
  $('#query-answer').select();
}






// settings (algorithm, direction and type)

// query algorithm
$('#query-algorithm tr').on('click', function() {
  $('#query-algorithm tr').removeClass('active');
  $(this).addClass('active');
  queryChosenAlgorithm = parseInt($(this).data('algorithm'));
});

// query direction
$('#query-direction tr').on('click', function() {
  $('#query-direction tr').removeClass('active');
  $(this).addClass('active');
  queryChosenDirection = parseInt($(this).data('direction'));
});

// query type
$('#query-type tr').on('click', function() {
  $('#query-type tr').removeClass('active');
  $(this).addClass('active');
  setQueryType(parseInt($(this).data('type')));
});

function setQueryType(queryType) {
  if (queryChosenType != queryType) {
    queryChosenType = queryType;
    
    if (queryType == QueryType.Buttons) {
      $('#query-answer-table-cell-text-box').hide();
      $('#query-answer-table-cell-buttons').show();
      
    }
    else if (queryType == QueryType.TextBox) {
      $('#query-answer-table-cell-buttons').hide();
      $('#query-answer-table-cell-text-box').show();
      $('#query-answer').focus();
    }
  }
}




// query results auto upload

function tryAutoUpload() {
  if (autoUploadEnabled()) 
    uploadQueryResults();
  else
    refreshQueryResultsUploadButton();
}

function autoUploadEnabled() {
  return $('#query-results-auto-upload').is(':checked');
}

$('#query-results-auto-upload').on('click', function() {
  if (autoUploadEnabled()) 
    uploadQueryResults();
});



refreshQueryLabelList(true);