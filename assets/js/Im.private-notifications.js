(function (global) {
    'use strict';

    var app = global.Im,
        alertEngine = global.Pooh,

        // global private notifications flag; stream url in content attribute;
        NOTIFICATIONS = 'meta[name="im-private-notifications-url"]',
        // data-attribute names;
        USER_ELEM = 'data-im-user-id',
        NOT_VIEWED_MESSAGES_COUNT = 'data-im-not-viewed-count',
        NOT_VIEWED_MESSAGES_LABEL = 'data-im-not-viewed-count-label',

        template,
        notifications,
        url,
        eventSource;


    notifications = document.querySelector(NOTIFICATIONS);
    if (notifications !== null) {
        url = notifications.getAttribute('content');
        eventSource = new EventSource(url);
    } else {
        return;
    }


    // notification balloon template;
    template =  '<a href="{{=it.url}}">{{=it.userFromName}}</a>:' +
                '<br>' +
                '<span>{{=it.content}}</span>';
    template = app.templateEngine.template(template);


    // EVENTS.
    eventSource.addEventListener('message', function (event) {
        var data,
            message;

        try { data = JSON.parse(event.data); } catch (e) {}

        if (data.hasOwnProperty('content') === false) {
            return;
        }

        message = template(data);
        message = app.smileEngine.decode(message);
        alertEngine(message);

        if (data.hasOwnProperty('userFromId') === true) {
            var userElem,
                notViewedMessagesCount,
                notViewedMessagesLabel;

            userElem = document.querySelector('[' + USER_ELEM + '="' + data.userFromId + '"]');
            if (userElem !== null) {
                notViewedMessagesCount = userElem.getAttribute(NOT_VIEWED_MESSAGES_COUNT);
                notViewedMessagesLabel = userElem.querySelector(NOT_VIEWED_MESSAGES_LABEL);

                notViewedMessagesCount++;
                userElem.setAttribute(NOT_VIEWED_MESSAGES_COUNT, notViewedMessagesCount);
                notViewedMessagesLabel.innerHTML = notViewedMessagesCount;
            }
        }
    });
}(this));