var SEP = '<hr />'

function do_capo_split(src) {    
    str = src.split(SEP);
    
    var chant = str[0]
    var trad = str[1]
    var vid = str[2]
    var rest = str.splice(3).join(SEP)

    return'<div>' +
            '<div class="capoeira-chant">' + chant + '</div>' + 
            '<div class="capoeira-traduction">' + trad + '</div>' +
	    '<div class="capoeira-break"></div> ' +
          '</div>' + SEP + 
          '<div class="capoeira-video">' + vid + '</div>'
    +rest;
}
