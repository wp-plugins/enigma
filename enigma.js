var enigma = {
    decode: function(str, div){
        var strs = str.split(' ');
        var res = "";
        for(var i = 0; i < strs.length; ++i){
            res += String.fromCharCode(parseInt(strs[i]));
        }
        var masks = jQuery("#"+div);
        if (masks != null && masks.length > 0) {
            masks.remove();
            document.write(res);
        }
    }
};