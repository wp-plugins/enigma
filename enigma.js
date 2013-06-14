var enigma = {
    decode: function(str, div){
        jQuery("#"+div).replaceWith(str);
        jQuery("#s"+div).remove();
    }
};