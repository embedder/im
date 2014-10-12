(function (global) {
    'use strict';

    var app = global.Im,
        guiModule = app.gui,
        inputModule = app.input,

        // data-attribute name;
        STREAM = 'data-im-stream-url',

        template,
        stream,
        url,
        eventSource,
        lastEventId,
        urlPattern = /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[.\!\/\\w]*))?)/;


    stream = document.querySelector('[' + STREAM + ']');
    if (app.stream !== null) {
        url = stream.getAttribute(STREAM);
        eventSource = new EventSource(url);
    } else {
        return;
    }


    // message template;
    template =  '{{?it.day}}' +
                '<li class="day">{{=it.day}}</li>' +
                '{{?}}' +
                '<li>' +
                    '{{?it.userFromName}}' +
                        '<span class="user-from-name">{{=it.userFromName}}</span>' +
                    '{{?}}' +
                    '<time>{{=it.time}}</time>' +
                    '<span class="content">{{=it.content}}</span>' +
                '</li>';
    template = app.templateEngine.template(template);


    function streamHandler(event) {
        var data,
            message,
            urls = [];

        try { data = JSON.parse(event.data); } catch (e) {}

        if (data.hasOwnProperty('isTyping')) {
            inputModule.showOpponentIsTypingLabel();
        } else {
            inputModule.hideOpponentIsTypingLabel();
        }

        if (event.lastEventId && event.lastEventId !== lastEventId) {

            if (data.hasOwnProperty('content')) {
                data.content = app.smileApi.decode(data.content);

                urls = data.content.match(urlPattern);
                if (urls !== null) {
                    data.content = data.content.replace(urlPattern, '<a href="$1" target="_blank">$1</a>');
                }

                message = template(data);
                app.messageList.innerHTML += message;
            }

            lastEventId = event.lastEventId;
            guiModule.adjust();
        }
    }


    eventSource.addEventListener('message', streamHandler);
}(this));