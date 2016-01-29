var SEP = '<hr />'

function do_capo_split(src) {    
    str = src.split(SEP);
    
    var before = str[0]
    var tr = str[1]
    var rest = str.splice(2).join(SEP)

    return'<div>' +
            '<div class="capoeira-chant">' + before + '</div>' + 
            '<div class="capoeira-traduction">' + tr + '</div>' +
	    '<div class="capoeira-break"></div> ' +
          '</div>' + SEP + 
          '<div class="capoeira-after">' + rest + '</div>';
}
