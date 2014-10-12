(function (global) {
    'use strict';

    var app = global.Im,
        inputModule = app.namespace('input'),
        smileApi = app.smileApi,

        // data-attribute names;
        IS_TYPING_LABEL = 'data-im-is-typing-label',
        SAVE_MY_TYPING_STATE_URL = 'data-im-save-my-typing-state-url',

        _form,
        _input,

        _myTypingTimer,
        _saveMyTypingStateUrl,
        _opponentIsTypingLabel,

        // exported;
        _module = {
            hideOpponentIsTypingLabel: _hideOpponentIsTypingLabel,
            showOpponentIsTypingLabel: _showOpponentsIsTypingLabel
        };



    _form = app.container.getElementsByTagName('form')[0];
    _input = _form.querySelector('[contenteditable]');
    _saveMyTypingStateUrl = _form.getAttribute(SAVE_MY_TYPING_STATE_URL);
    _opponentIsTypingLabel = document.querySelector('[' + IS_TYPING_LABEL + ']');



    function _submitHandler(event) {
        var message,
            xhr = new XMLHttpRequest();

        message = smileApi.getValue();
        if (message.length > 0) {
            xhr.open('POST', _form.action);
            xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            _form.classList.add('loading');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        _form.classList.remove('loading');
                        _myTypingTimer = undefined;
                        smileApi.clear();
                    }
                }
            };

            xhr.send(JSON.stringify({ message: message }));
        }

        event && event.preventDefault();
    }

    function _myTypingHandler() {
        // send typing start/stop notifies to opponent;
        var xhr = new XMLHttpRequest();

        if (_saveMyTypingStateUrl !== null) {
            if (typeof _myTypingTimer === 'undefined') {
                xhr.open('POST', _saveMyTypingStateUrl);
                xhr.send('start');
            }
            else {
                clearTimeout(_myTypingTimer);
            }

            _myTypingTimer = setTimeout(function () {
                xhr.open('POST', _saveMyTypingStateUrl);
                xhr.send('stop');
                _myTypingTimer = undefined;
            }, 3000);
        }
    }

    function _hideOpponentIsTypingLabel() {
        if (_opponentIsTypingLabel && _opponentIsTypingLabel.classList.contains('visible')) {
            _opponentIsTypingLabel.classList.remove('visible');
        }
    }

    function _showOpponentsIsTypingLabel() {
        if (_opponentIsTypingLabel && !_opponentIsTypingLabel.classList.contains('visible')) {
            _opponentIsTypingLabel.classList.add('visible');
        }
    }


    // MAIN.

    _input.focus();



    // EVENTS.

    _form.addEventListener('submit', _submitHandler, false);
    window.addEventListener('keydown', function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            _submitHandler();
        }
    });
    _input.addEventListener('keyup', _myTypingHandler);


    // EXPORT;
    for (var key in _module){
        if(_module.hasOwnProperty(key)){
            inputModule[key] = _module[key];
        }
    }
}(this));