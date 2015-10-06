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
function List(id, name, creator, comment, language1, language2, creationTime, words, sharings) {
  this.id = id;
  this.name = name;
  this.creator = creator;
  this.language1 = language1;
  this.language2 = language2;
  this.comment = comment;
  this.creationTime = creationTime;
  this.sharings = sharings;

  this.words = [];
  // convert parsed JSON data to "Word" objects
  for (var i = 0; i < words.length; i++) {
    this.words.push(new Word(words[i].id, words[i].list, words[i].language1, words[i].language2, words[i].comment, words[i].answers)); 
  }
}

// get name
//
// @return string: name of the list
List.prototype.getName = function() {
  return this.name;
};


// get known average
//
// goes through all words and looks in their answer history how often they have been answered correctly
// calculates the known average of the list by calculating the average of all single words
//
// @return float: known average of the list
List.prototype.getKnownAverage = function() {

};


// compare lists by name
//
// @param string a: list one name
// @param string b: list two name
//
// @return int: returns like a spaceship operator (<=>)
List.compareListsByName = function(a, b) {
  if (a.name < b.name) return -1; 
  if (a.name > b.name) return 1; 
  return 0; 
}


List.prototype.getWordById = function(id) {
  for (var i = this.words.length - 1; i >= 0; i--) {
    if (this.words[i].id === id)
      return this.words[i];
  };
};



// Word
//
// word object factory method
//
// @param int id: id
// @param int list: id of the list which the word belongs to
// @param string language1: meaning of the word in the first language of the list
// @param string language2: meaning of the word in the second language of the list
// @param string comment: additional comment to the word
// @param QueryAnswer[] answers: array of query answers
// 
// @return Word: word object
function Word(id, list, language1, language2, comment, answers) {
  this.id = id;
  this.language1 = language1;
  this.language2 = language2;
  this.comment = comment;
  this.list = list;

  this.answers = [];
  // convert JSON-parsed answers into Answer objects
  for (var i = 0; i < answers.length; i++) {
    this.answers.push(new QueryAnswer(answers[i].word, answers[i].correct, answers[i].type, answers[i].direction, answers[i].id, answers[i].time));
  }
}


// get known average
//
// goes through all answers and determines how often the word has been known
// if it has been known 3 time in 4 queries the return value will be 0.75
//
// @param QueryAnswer[]|undefined ignoreAnswers: query answers which shall be ignored
//
// @return float: known average of the word
Word.prototype.getKnownAverage = function(ignoreAnswers) {
  if (typeof ignoreAnswers === 'undefined') ignoreAnswers = [];
  return this.getKnownAverageOverLastNAnswers(this.answers.length, ignoreAnswers);
};


// get known average over last n answers
//
// goes through the last n answers and determines how often the word has been known in average
// if the word has been answers zero times the known average is defined as 0.0
//
// @param int n: number of answers to consider 
// @param QueryAnswer[]|undefined ignoreAnswers: query answers which shall be ignored
//
// @return float: known average of the word considering the last n answers
Word.prototype.getKnownAverageOverLastNAnswers = function(n, ignoreAnswers) {
  if (typeof ignoreAnswers === 'undefined') ignoreAnswers = [];
  if (this.answers.length === 0) return 0;

  if (this.answers.length < n) n = this.answers.length; // call requests more answers than the word even has
  var minIndex = this.answers.length - n, validAnswers = 0, iterations = 0;
  for (var i = this.answers.length - 1; i >= minIndex && i >= 0; i--) {
    if (ignoreAnswers.contains(this.answers[i])) {
      minIndex--;
    }
    else {
      validAnswers++;
    } 
    iterations++;
  }

  if (validAnswers === 0) return 0;

  var knownCount = 0.0;
  for (var i = this.answers.length - 1; i >= this.answers.length - iterations; i--) {
    if (ignoreAnswers.contains(this.answers[i])) {
      continue;
    }
    if (this.answers[i].correct === 1) {
      knownCount++;
    }
  }
  return knownCount / validAnswers;
};


// get word known below or equal the percentage over last n answers
//
// @param Word[] wordArray: array of words to consider
// @param float percentage: percentage known
// @param int n: consider last n answers
// 
// @return Word: random word out of the passed array which has been known below the passed percentage or undefined if no word is below the avg
Word.getWordKnownBelow = function(wordArray, percentage, n) {
  var wordsBelow = [];
  // search for all words below given percentage
  for (var i = 0; i < wordArray.length; i++) {
    if (wordArray[i].getKnownAverageOverLastNAnswers(n) <= percentage) {
      wordsBelow.push(wordArray[i]);
    }
  }
  return wordsBelow.getRandomElement();
};

// get known average of array
//
// @param Word[] wordArray: array of words to consider
// @param QueryAnswer[]|undefined ignoreAnswers: query answers which shall be ignored
// 
// @return float: known average of the words in the given array
Word.getKnownAverageOfArray = function(wordArray, ignoreAnswers) {
  if (typeof ignoreAnswers === 'undefined') ignoreAnswers = [];
  if (wordArray.length === 0) return 0;

  var sum = 0.0;
  for (var i = 0; i < wordArray.length; i++) {
    sum += wordArray[i].getKnownAverage(ignoreAnswers);
  }

  return sum / wordArray.length;
};



// get known average of array over last n ansers
//
// @param Word[] wordArray: array of words to consider
// @param int n: number of answers per word to care about
// @param QueryAnswer[]|undefined ignoreAnswers: query answers which shall be ignored
// 
// @return float: known average of the words in the given array
Word.getKnownAverageOfArrayOverLastNAnswers = function(wordArray, n, ignoreAnswers) {
  if (typeof ignoreAnswers === 'undefined') ignoreAnswers = [];
  if (wordArray.length === 0) return 0;

  var sum = 0.0;
  for (var i = 0; i < wordArray.length; i++) {
    sum += wordArray[i].getKnownAverageOverLastNAnswers(n, ignoreAnswers);
  }

  return sum / wordArray.length;
};


// query answer
//
// query answer object factory method
//
// @param int word: id of the word which has been answered
// @param byte correct: answer was correct? 0 = wrong; 1 = correct
// @param Query.TypeEnum type: type of the query (buttons = 1 or text box = 0)
// @param Query.DirectionEnum direction: direction of the query (0 = first to second language; 1 = 2nd to 1st)
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

QueryAnswer.sortByTime = function(a, b) {
  if (a.time < b.time) return -1;
  if (a.time > b.time) return 1;
  return 0;
};