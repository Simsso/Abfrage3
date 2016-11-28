// repeats a given string n times
String.prototype.repeat = function(n) {
  if (n === 0)
    return '';
  n = n || 1;
  return Array(n + 1).join(this);
};


// returns true if the array contains the passed object
Array.prototype.contains = function(obj) {
  for (var i = 0; i < this.length; i++) {
    if (this[i] === obj) {
      return true;
    }
  }
  return false;
};

// removes the first occurence of the given object in an array
Array.prototype.remove = function(obj) {
  var index = this.indexOf(obj);
  if (index !== -1) {
    this.splice(index, 1);
  }
  return this;
};

// removes all occurences of the given object in an array
Array.prototype.removeAll = function(obj) {
  for(var i = 0; i < this.length; i++) {
    if (this[i] === obj) {
      this.splice(i, 1);
      i--;
    }
  }
  return this;
};

// randomizes array element order in-place
Array.prototype.shuffle = function() {
  var swapIndex;
  for (var i = 0; i < this.length; i++) {
    swapIndex = Math.floor(Math.random() * this.length);
    this.swap(i, swapIndex);
  }
  return this;
};


// swaps two elements
Array.prototype.swap = function(a, b) {
  var tmp = this[a];
  this[a] = this[b];
  this[b] = tmp;
  return this;
};

// pushes elements of an array into another array
Array.prototype.pushElements = function(array) {
  for (var i = 0; i < array.length; i++) {
    this.push(array[i]);
  }
};

// returns a random elemenf from an array 
Array.prototype.getRandomElement = function() {
  if (this.length === 0) return undefined;
  return this[Math.round(Math.random() * (this.length - 1))];
};


// removes all elements which are undefined
Array.prototype.removeUndefined = function() {
  for (var i = 0; i < this.length; i++) {
    if (this[i] === undefined) {
      this.splice(i, 1);
      i--;
    }
  }
}

// converts a number to an English string
Number.prototype.toEnglishString = function() {
  if (this.valueOf() > 12) {
    return this.toString();
  }
  var strings = ["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten", "eleven", "twelve"];
  return strings[this.valueOf()];
};

// converts a number to a English month name abbreviation
Number.prototype.toMonthAbbreviation = function() {
  if (this.valueOf() > 12) return undefined;
  var monthNames = ["Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dez"];
  return monthNames[this.valueOf() - 1];
};

// adds leading zeros to a number
Number.prototype.addLeadingZeros = function(n) {
  var s = this.valueOf() + "";
  while (s.length < n) s = "0" + s;
  return s;
};

// sets a single bit
Number.prototype.setBit = function(x) {
  return this.valueOf() | 1 << x;
};

// clear a single bit
Number.prototype.clearBit = function(x) {
  return this.valueOf() & ~(1 << x);
};

// toggles a single bit
Number.prototype.toggleBit = function(x) {
  return this.valueOf() ^ 1 << x;
};

// get a single bit
Number.prototype.getBit = function(x) {
  return (this.valueOf() >> x) & 1;
};

// check a single bit agains value
Number.prototype.changeBitTo = function(x, value) {
  return this.valueOf() ^ (-value ^ this.valueOf()) & (1 << x);
};

// maps a value from two bounds to another two
Math.map = function(x, in_min, in_max, out_min, out_max) {
  return (x-in_min) * (out_max-out_min) / (in_max-in_min) + out_min;
};

// sign function
Math.sgn = function(x) {
  if (x < 0)
    return -1;
  if (x === 0)
    return 0;
  return 1;
};

// converts radian to degree
Math.rad2deg = function(rad) {
  return (rad / 0.0174532925);
};

// converts degree to radian
Math.deg2rad = function(deg) {
  return (deg * 0.0174532925);
};



// returns a default data string of type dd.mm.yyyy hh:mm:ss
Date.prototype.toDefaultString = function() {
  return this.getDate().addLeadingZeros(2) + ". " + 
    (this.getMonth()+1).toMonthAbbreviation() + " " + 
    this.getFullYear().addLeadingZeros(4) + " " + 
    this.getHours().addLeadingZeros(2) + ":" + 
    this.getMinutes().addLeadingZeros(2);
};

// returns the current time in milliseconds
Date.millis = function () {
  return new Date().getTime();
};

// returns the current time in seconds
Date.seconds = function () {
  return Math.round(Date.millis() / 1000 - 0.5, 0);
};

Date.differenceToStringKey = function(fromUnix, tillUnix) {
  var delta = Math.abs(fromUnix - tillUnix);
  if (delta < 5) return "just_now";
  if (delta < 60) return "less_than_a_minute_ago";
  if (delta < 60*2) return "a_minute_ago";
  if (delta < 60*10) return "a_few_minutes_ago";
  if (delta < 60*60) return "less_than_an_hour_ago";
  return "more_than_an_hour_ago";
};