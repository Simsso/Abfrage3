// repeats a given string n times
String.prototype.repeat = function(n) {
  if (n === 0)
  return '';
  n = n || 1;
  return Array(n + 1).join(this);
};


// returns true if the array contains the object
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
  var index = this.indexOf(obj) != -1;
  if (index != -1) {
    this.splice(index, 1);
  }
};

// removes all occurences of the given object in an array
Array.prototype.removeAll = function(obj) {
  for(var i = 0; i < this.length; i++) {
    if (this[i] === obj) {
      this.splice(i, 1);
      i--;
    }
  }
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
  return this.getDate() + "." + (this.getMonth()+1) + "." + this.getFullYear() + " " + this.getHours() + ":" + this.getMinutes() + ":" + this.getSeconds();
};

// returns the current time in milliseconds
Date.millis = function () {
  return new Date().getTime();
};

// returns the current time in seconds
Date.seconds = function () {
  return Math.round(Date.millis() / 1000 - 0.5, 0);
};
