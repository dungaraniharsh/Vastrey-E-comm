define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'mage/validation',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, validation) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        gethtml: function (row) { return row[this.index + '_html']; },
        getTitle: function (row) { return row[this.index + '_title'] },
        getLabel: function (row) { return row[this.index + '_html'] },
        getResponse: function (row) {
            var res = JSON.parse(row[this.index + '_response']);
            //console.log(res);
            var str = JSON.stringify(res, undefined, 4);
            return  this.syntaxHighlight(str);
        },
        preview: function (row) {
            var modalHtml = '<style>pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }.string { color: green; }'+
            '.number { color: darkorange; }.boolean { color: blue; }.null { color: magenta; }'+
            '.key { color: red; }</style>'+
            '<pre id="json">'+this.getResponse(row)+'</pre>';
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: $.mage.__( this.getTitle(row)),
                innerScroll: true,
                modalClass: '_response-box',
                buttons: [{
                    type:'button',
                    text: $.mage.__('close'),
                    class: 'action close-popup wide',
                    }
            ]}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        },
        syntaxHighlight: function(json) {
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                var cls = 'number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'key';
                    } else {
                        cls = 'string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'boolean';
                } else if (/null/.test(match)) {
                    cls = 'null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }
    });
});
