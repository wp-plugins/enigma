function Enigma() {
  
}

Enigma.prototype.GetValue = function(element) {
  return jQuery(element).attr('data-enigmav');
};

Enigma.prototype.Replace = function(element) {
  var value = this.GetValue(element);
  if (!value) {
    return;
  }
  jQuery(element).replaceWith(eval('"' + value + '"'));
};

Enigma.prototype.Clickable = function(element) {
  var link = jQuery("<a />", {
    href : "#",
    text : jQuery(element).text(),
    'data-enigmav' : this.GetValue(element),
    onclick : 'javascript:new Enigma().Replace(this);return false;'
  });
  jQuery(element).replaceWith(link);
};

Enigma.prototype.Run = function() {
  var thisEnigma = this;
  jQuery('span[id^="engimadiv"]').each(function(idx, element) {
    if (jQuery(element).attr('data-enigmar')) {
      return;
    }
    if (jQuery(element).attr('data-enigmad') === 'y') {
      return thisEnigma.Clickable(element);
    }
    thisEnigma.Replace(element);
  });
};

jQuery(function() {
  new Enigma().Run();
  setInterval(function() { new Enigma().Run();}, 1000);
});