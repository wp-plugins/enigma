var enigma = {
  cache : {},
  decode : function(str, div) {
    jQuery("#" + div).replaceWith(str);
    jQuery("#s" + div).remove();
  }
};

jQuery(document).ready(function () {
  var cache = enigma.cache;
  for (var id in cache) {
    enigma.decode(cache[id], id);
    delete cache[id];
  }
})