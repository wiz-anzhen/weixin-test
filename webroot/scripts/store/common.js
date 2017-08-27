function parameterToUrl(parameters) {
  var str = [];
  for(var p in parameters)
     str.push(encodeURIComponent(p) + "=" + encodeURIComponent(parameters[p]));
  return str.join("&");
}

function isAndroid() {
  return window.navigator.userAgent.match(/Android/i);
}

function isiOS() {
  return window.navigator.userAgent.match(/(:?iPad|iPhone|iPod)/i);
}

function redirect(path, parameters) {
  var frame = document.getElementById('mobileFrame');
  if (isiOS || isAndroid()) {
    parameters['source'] = 'weixin';
    var src = 'eleme://' + path + "?" + parameterToUrl(parameters);
    frame.src = src;
  }
}

function params(name) {
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.search);
  if (results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

function setCookie(name, value, days) {
  var expires = "";
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toGMTString();
  }
  document.cookie = name + "=" + encodeURI(value) + expires + "; path=/";
}

function getCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ')
      c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) == 0)
      return decodeURI(c.substring(nameEQ.length, c.length));
  }
  return null;
}

function removeCookie(name) {
  setCookie(name, "", -1);
}

function get(s) {
    return document.getElementById(s);
}

HTMLElement.prototype.show = function() {
  this.style.display = 'block';
};

HTMLElement.prototype.hide = function() {
  this.style.display = 'none';
};

String.prototype.Trim = function() {
  var m = this.match(/^\s*(\S+(\s+\S+)*)\s*$/);
  return (m == null) ? "" : m[1];
};

String.prototype.isTelOrMobile = function() {
  return (/^1[3|4|5|8][0-9]\d{4,8}$/.test(this.Trim())) ||
    (/^(([0+]d{2,3}-)?(0d{2,3})-)(d{7,8})(-(d{3,}))?$/.test(this.Trim()));
};
