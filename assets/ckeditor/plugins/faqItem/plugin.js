CKEDITOR.plugins.add('faqItem', {
    icons: 'faqItem',
    init: function (editor) {
        editor.addCommand('insertFaqItem', {
            exec: function (editor) {
                editor.insertHtml('<li class="faq-item"><span class="faq-headline">headline</span><span class="faq-content">content</span></li>');
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
            'li.faq-item {display:block;padding-bottom:10px;list-style:none;}' +
            'span.faq-headline {border:1px dotted black;display:block;background-color:#eee;min-height:10px;}' +
            'span.faq-content {border:1px dotted black;display:block;background-color:#ddd;min-height:10px;}'
        );
    }
});