(function (global) {
    'use strict';

    var app = global.Im,
        guiModule = app.namespace('gui'),

        // messages container data-attribute;
        MESSAGE_LIST_CONTAINER = 'data-im-message-list-container',

        // document height after resize;
        _documentHeight,
        _newContainerHeight,
        _newMessageListContainerHeight,
        _messageListContainer,

        // exported;
        _module = {
            adjust: _adjust
        };


    _messageListContainer = document.querySelector('[' + MESSAGE_LIST_CONTAINER + ']');


    /**
     * Adjust chat size to new document size.
     */
    function _adjust() {
        _documentHeight = document.documentElement.clientHeight;

        // expand chat to document height;
        _newContainerHeight = _documentHeight - 125;
        app.container.style.height = _newContainerHeight + 'px';

        // message list smaller than chat container;
        _newMessageListContainerHeight = _newContainerHeight - 85;
        _messageListContainer.style.height = _newMessageListContainerHeight + 'px';
        // message list position is absolute, and bottom = 0; restrict max height;
        app.messageList.style.maxHeight = _newMessageListContainerHeight + 'px';

        // scroll down message list;
        app.messageList.scrollTop = app.messageList.scrollHeight;
    }


    // MAIN.

    setTimeout(function () {
        _adjust();
        // scroll down to page bottom;
        window.scrollTo(0, document.documentElement.scrollHeight);
    }, 300);


    // EVENTS.

    window.addEventListener('resize', _adjust);


    // EXPORT;
    for (var key in _module) {
        if (_module.hasOwnProperty(key)) {
            guiModule[key] = _module[key];
        }
    }
}(this));