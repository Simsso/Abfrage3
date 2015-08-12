String.prototype.repeat = function(n) {
    if (n == 0) 
        return '';
    n= n || 1;
    return Array(n + 1).join(this);
}



Array.prototype.contains = function(obj) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
}

Array.prototype.remove = function(obj) {
    var index = this.indexOf(obj) != -1
    if (index != -1) {
        array.splice(i, 1);
    }
}

Array.prototype.removeAll = function(obj) {
    for(var i = 0; i < this.length; i++) {
	   if (this[i] === obj) {
           this.splice(i, 1);
       }
    }
}



Math.map = function(x, in_min, in_max, out_min, out_max) { 
    return (x-in_min) * (out_max-out_min) / (in_max-in_min) + out_min; 
}

Math.sgn = function(x) { 
    if (x < 0) 
        return -1; 
    if (x == 0) 
        return 0; 
    return 1; 
}

Math.rad2deg = function(rad) {
    return (rad / 0.0174532925);
}

Math.desg2rad = function(deg) {
    return (deg * 0.0174532925);
}



Date.prototype.toDefaultString = function() {
    return this.getDate() + "." + (this.getMonth()+1) + "." + this.getFullYear() + " " + this.getHours() + ":" + this.getMinutes() + ":" + this.getSeconds();
}

Date.millis = function () { 
    return new Date().getTime(); 
}

Date.seconds = function () {
    return round(Date.millis() / 1000 - 0.5, 0);
}