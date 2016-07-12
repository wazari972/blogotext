(function () {  

    var help = function () { alert("Do you need help?"); }
    var options = {
        helpButton: { handler: help },
                    strings: { quoteexample: "whatever you\'re quoting, put it right here" }
    };

    $(".form-ecrire").each(function() {
        var uid = $(this).attr("id").replace("form-ecrire", "");

        var converter = new Markdown.Converter();

        if (window.location.hostname == "capoeira.0x972.info") {
            converter.autoNewLine = true;
        }
        
        var output = $("#wmd-output"+uid);

        converter.hooks.chain("postConversion", function (text) {
            if (window.location.hostname == "capoeira.0x972.info") {
                text = do_capo_split(text)
	    }
            // save transformed text in textarea for sending with the form
            output.text(text);
        
            return text.replace(new RegExp("<iframe", "g"), "&laquo;iframe" )
                .replace(new RegExp("</iframe>", "g"), "&laquo;/iframe&raquo;" );
        });
        
        var editor = new Markdown.Editor(converter, uid, options);
        editor.run();
    })
    
    
    
})();
