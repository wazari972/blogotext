var SEP = '<hr>'

$(function() {
    $('.post-content').each(function(){
    var str = $(this).html();
    
    str = str.split(SEP);
    
    var before = str[0]
    var tr = str[1]
    var rest = str.splice(2).join(SEP)
    
    $(this).html(
        '<div class="capoeira-chant">' + before + '</div>' + SEP +
            '<div class="capoeira-traduction">' + tr + '</div>' +
            SEP + rest);
    })
})
