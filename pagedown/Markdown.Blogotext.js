(function () {                
    var converter = new Markdown.Converter();

    if (window.location.hostname == "capoeira.0x972.info") {
      converter.autoNewLine = true;
    }

    converter.hooks.chain("postConversion", function (text) {
        if (window.location.hostname == "capoeira.0x972.info") {
          text = do_capo_split(text)
	}
        // save transformed text in textarea for sending with the form
        document.getElementById("wmd-output").value = text;

        
        return text.replace("<iframe", "&laquo;iframe" )
                   .replace("</iframe>", "&laquo;/iframe&raquo;" );
    });
                
    var help = function () { alert("Do you need help?"); }
    var options = {
        helpButton: { handler: help },
                    strings: { quoteexample: "whatever you\'re quoting, put it right here" }
    };
    var editor = new Markdown.Editor(converter, "", options);
    
    editor.run();
})();
