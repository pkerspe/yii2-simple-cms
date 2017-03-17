CKEDITOR.plugins.add('faqItem', {
    icons: 'faqItem',
    init: function (editor) {
        editor.addCommand('insertFaqItem', {
            exec: function (editor) {
                //check if selection is inside an existing faq list
                var path = editor.elementPath().elements;
                console.log("Path to current item", path);
                var parentListContainer = null;
                var parentFaqEntry = null;

                for (pathEntry = 0; pathEntry < path.length; pathEntry++) {
                    if (path[pathEntry].getAttribute('class') != null) {
                        if (path[pathEntry].getAttribute('class').indexOf('faq-list') > -1) {
                            parentListContainer = path[pathEntry];
                        } else if (path[pathEntry].getAttribute('class').indexOf('faq-item') > -1) {
                            parentFaqEntry = path[pathEntry];
                        }
                    }
                }

                var identifier = (new Date()).getTime();
                var panelItem = '<div class="panel panel-default faq-item" id="faqEntry' + identifier + '"><div class="panel-heading" role="tab" id="heading' + identifier + '"><h4 class="panel-title"><a role="button" data-toggle="collapse" data-parent="#accordion' + identifier + '" href="#collapse' + identifier + '" aria-expanded="true" aria-controls="collapse' + identifier + '">Faq Question # <span class="glyphicon glyphicon-chevron-down">&nbsp;</span></a></h4></div>' +
                    '<div id="collapse' + identifier + '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading' + identifier + '"><div class="panel-body">Faq Answer #</div></div>';

                if (parentListContainer == null) {
                    //create new list container and item
                    editor.insertHtml(
                        '<div class="faq-list"><div class="panel-group" id="accordion' + identifier + '" role="tablist" aria-multiselectable="true">' +
                        '<div>' + panelItem + '</div>' +
                        '</div></div>'
                    );
                } else {
                    //check if faq item has been selected (in focus) during click on insert
                    if (parentFaqEntry == null) {
                        var element = document.createElement('div');
                        element.innerHTML = panelItem;
                        parentListContainer.$.firstChild.appendChild(element);
                    } else {
                        //no item selected so we insert a new one at the end of the list
                        var element = document.createElement('div');
                        element.innerHTML = panelItem;
                        parentFaqEntry.$.parentNode.insertBefore(element, parentFaqEntry.$.nextSibling);
                    }

                }
            }
        });

        editor.ui.addButton('FaqItem', {
            label: 'Insert FAQ Item',
            command: 'insertFaqItem',
            toolbar: 'insert'
        });
    },
    onLoad: function () {
        CKEDITOR.addCss(
            '.faq-list::before {padding:3px;margin:0;border:1px solid grey;background-color:#fff;font-size:10px;color:#000;content: "faq items"} ' +
            '.panel-group {border:1px dotted grey;padding:10px;} ' +
            '.panel-default::before {padding:3px;margin:0;border:1px solid grey;background-color:#fff;font-size:10px;color:#000;content: "faq entry"}' +
            '.panel-heading {border:1px dotted black;background-color:#f4f8ef;color:#72b73a;text-decoration:none;font-size:20px;} ' +
            '.panel-collapse {border:1px dotted black;display:block;background-color:#ddd;min-height:10px;} '
        );
    }
});