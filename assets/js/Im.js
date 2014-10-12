(function (global) {
    'use strict';

    var Im = {

        templateEngine: global.doT,
        smileEngine: global.Mimic,

        // data-attribute names;
        CONTAINER: 'data-im-container',
        MESSAGE_LIST: 'data-im-message-list',
        INPUT_CONTAINER: 'data-im-input-container',

        namespace: function (namespaceString) {
            var node = Im,
                nodes = namespaceString.split('.'),
                i;

            if (nodes[0] === 'Im') {
                nodes = nodes.slice(1);
            }

            for (i = 0; i < nodes.length; i++) {
                if (typeof node[nodes[i]] === 'undefined') {
                    node[nodes[i]] = {};
                }
                node = node[nodes[i]];
            }

            return node;
        }
    };


    Im.container = document.querySelector('[' + Im.CONTAINER + ']');
    Im.messageList = document.querySelector('[' + Im.MESSAGE_LIST + ']');
    Im.smileApi = new Im.smileEngine('[' + Im.INPUT_CONTAINER + ']', {
        emojiPath: '/img/smiles/emoji/',
        emojiExt: '.png'
    });

    global.Im = Im;
}(this));