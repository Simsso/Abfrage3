var queryLabels = null;
var queryAttachments = null;
var queryLists = null;

var querySelectedLabel = [];
var querySelectedLists = [];

// get label list of user
function refreshQueryLabelList(showLoadingInformation) {
  if (showLoadingInformation)
    $('#query-selection').html(loading);

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-query-data'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data);
    var dataJSON = jQuery.parseJSON(data); // parse JSON
    console.log(dataJSON); // debug
    queryLabels = dataJSON.labels;
    queryAttachments = dataJSON.label_list_attachments;
    queryLists = dataJSON.lists;
    
    $('#query-selection').html('<div id="query-label-selection" style="width: calc(50% - 12.5px); float: left; "></div><div id="query-list-selection" style="width: calc(50% - 12.5px); float: right; "></div><br class="clear-both"><p><input id="query-start-button" type="button" value="Start query" class="spacer-top-15 width-100 height-50px font-size-20px" disabled="true"/></p>');
    
    // provide label selection
    $('#query-label-selection').html(getHtmlTableOfLabelsQuery(queryLabels));
    
    // provide list selection
    refreshQueryListSelection();
    
    
    // start query button click event
    $('#query-start-button').on('click', startQuery);
    
    // checkbox click event
    $('#query-label-selection label').on('click', function(){
      // read label id from checkbox data tag
      var labelId = $(this).data('label-id');

      // checkbox has been unchecked
      if($(this).data('checked') == 'true') {
        removeLabelFromQuery(labelId);
      }
      // checkbox has been checked
      else { 
        addLabelToQuery(labelId);
      }
    });
    
    // expand functionallity
    // expand single labels
    $('#query-label-selection .small-exp-col-icon').on('click', function() {
      var $this = $(this);
      var expand = ($this.data('state') == 'collapsed');

      var i = 0;
      var $row = $this.parent().parent();
      var allFollowing = $row.nextAll();
      var selfIndenting = $row.data('indenting');
      // show all following rows which have a higher indenting (are sub-labels) or don't have an indenting (are "add sub-label" formular rows)
      while (allFollowing.eq(i).length > 0 && (allFollowing.eq(i).data('indenting') > selfIndenting || allFollowing.eq(i).data('indenting') == undefined)) {
        if (allFollowing.eq(i).data('indenting') == selfIndenting + 1 || !expand) {
          if (expand) // expand
          allFollowing.eq(i).show();

          else { // collapse
            allFollowing.eq(i).hide();
            allFollowing.eq(i).find('.small-exp-col-icon').attr('src', 'img/expand.svg').data('state', 'collapsed');

            // refresh array of expanded labels
            expandedLabelsIds.removeAll(parseInt(allFollowing.eq(i).data('label-id')));
          }
        }
        i++;
      }

      if (expand) {
        $this.data('state', 'expanded').attr('src', 'img/collapse.svg'); // flip image
        expandedLabelsIds.push(parseInt($row.data('label-id'))); // refresh array of expanded labels
      }
      else {
        $this.data('state', 'collapsed').attr('src', 'img/expand.svg'); // flip image
        expandedLabelsIds.removeAll(parseInt($row.data('label-id'))); // refresh array of expanded labels
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
  
  
  $('#query-list-selection').html('<table class="box-table clickable">' + html + '</table');
  
  // checkbox click event
  $('#query-list-selection label').click( function(){
    // read list id from checkbox data tag
    var listId = $(this).data('list-id');

    // checkbox has been unchecked
    if($(this).data('checked') == 'true') {
      removeListFromQuery(listId);
    }
    // checkbox has been checked
    else { 
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
  
  $('#query-label-selection tr[data-label-id=' + labelId + ']').addClass('active').find('label').data('checked', 'true');
  querySelectedLabel.push(labelId);
}

function removeLabelFromQuery(labelId) {
  // add lists which belong to the added label
  for (var i = 0; i < queryAttachments.length; i++) {
    if (queryAttachments[i].label == labelId) {
      removeListFromQuery(queryAttachments[i].list);
    }
  }
  $('#query-label-selection tr[data-label-id=' + labelId + ']').removeClass('active').find('label').data('checked', 'false');
  querySelectedLabel.removeAll(labelId);
}

function addListToQuery(listId) {
  querySelectedLists.push(listId);
  $('#query-list-selection label[data-list-id=' + listId + ']').data('checked', 'true').parent().parent().addClass('active');
  checkStartQueryButtonEnable();
}

function removeListFromQuery(listId) {
  querySelectedLists.removeAll(listId);
  $('#query-list-selection label[data-list-id=' + listId + ']').data('checked', 'false').parent().parent().removeClass('active');
  checkStartQueryButtonEnable();
}

function getListRow(list, selected) {
  return '<tr' + (selected?'class="active"':'') + '><td><label class="checkbox-wrapper" data-list-id="' + list.id + '" data-checked="false">' + list.name + ' (' + list.words.length + ' word' + ((list.words.length == 1) ? '': 's') + ')</label></td></tr>';
}

function checkStartQueryButtonEnable() {
  $('#query-start-button').prop('disabled', querySelectedLists.length == 0);
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
    html = '<table class="box-table clickable">' + html + '</table>';
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

  return '<tr data-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting == 0)?'':' style="display: none; "') + '><td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount == 0) ? 16 : 0)) + 'px; ">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;<label class="checkbox-wrapper" data-checked="false" data-label-id="' + label.id + '">' + label.name + '</label></td></tr>';
}

function getListById(id) {
  for (var i = 0; i < queryLists.length; i++) {
    if (queryLists[i].id === id) {
      return queryLists[i];
    }
  }
  return undefined;
}




var queryWords = [], queryAlgorithm = 'random', queryDirection = -1, queryType = 'text-box', queryRunning = false, 
    currentWord = null, currentDirection = null, currentWordCorrectAnswer = null, queryWrongAnswerGiven = false;

function startQuery() {
  $('#query').removeClass('display-none');
  queryRunning = true;
  
  // produce one array containing all query words
  queryWords = [];
  for (var i = 0; i < querySelectedLists.length; i++) {
    queryWords = queryWords.concat(getListById(querySelectedLists[i]).words);
  }
  
  nextWord();
  
  // $('#query-select-box img[data-action="collapse"]').trigger('collapse');
  $('#query-box img[data-action="expand"]').trigger('expand'); // expand query container
}

function nextWord() {  
  currentWord = getNextWord();
  var listOfTheWord = getListById(currentWord.list);
  
  if (queryType == 'text-box') {
    if (queryDirection == -1) { // both directions
      currentDirection = Math.round(Math.random()); // get random direction
    }
    else {
      currentDirection = queryDirection;
    }
    
    // fill the question fields
    if (currentDirection == 0) {
      $('#query-lang1').html(listOfTheWord.language1);
      $('#query-lang2').html(listOfTheWord.language2);
      $('#query-question').html(currentWord.language1);
      currentWordCorrectAnswer = currentWord.language2;
    }
    else {
      $('#query-lang1').html(listOfTheWord.language2);
      $('#query-lang2').html(listOfTheWord.language1);
      $('#query-question').html(currentWord.language2);
      currentWordCorrectAnswer = currentWord.language1;
    }
    
        
    setTimeout(function() {$('#query-answer').val('').focus(); }, 10);
  }
}

function getNextWord() {
  if (queryAlgorithm == 'random') {
    return queryWords[Math.round(Math.random() * (queryWords.length - 1))];
  }
}


$('#query-answer').on('keypress', function(e) {
    if (e.which == 13) {
      if (checkAnswer($(this).val(), currentWordCorrectAnswer)) { // correct answer
        if (queryWrongAnswerGiven) {
          queryWrongAnswerGiven = false;
          $('#correct-answer').hide();
        }
        
        $('#query-box').trigger('shadow-blink-green');
        
        nextWord();
      }
      else { // wrong answer
        $('#correct-answer').show().html(currentWordCorrectAnswer);
        $(this).select();
        queryWrongAnswerGiven = true;
      }
    }
});
      
function checkAnswer(user, correct) {
  return (user == correct);
}


function QueryAnswer(word, correct) {
  this.word = word;
  this.correct = correct;
  this.time = Date.seconds();
}



refreshQueryLabelList(true);