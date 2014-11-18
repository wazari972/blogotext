(function () {                
    var converter = new Markdown.Converter();

    converter.hooks.chain("postConversion", function (text) {
        document.getElementById("wmd-output").value = text;
        return text;
    });
                
    var help = function () { alert("Do you need help?"); }
    var options = {
        helpButton: { handler: help },
                    strings: { quoteexample: "whatever you\'re quoting, put it right here" }
    };
    var editor = new Markdown.Editor(converter, "", options);
    
    editor.run();
})();