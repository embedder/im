(function (global) {
    'use strict';


    /**
     * @param container Element or selector
     * @param options
     * @constructor
     */
    function Mimic(container, options) {

        var input, // contenteditable div;
            button, // smile container visibility toggle button;
            list, // smiles list;

            // custom emoji smile list;
            emoji = 'sunny,first_quarter_moon_with_face,evergreen_tree,' +
                        'umbrella,sunflower,tulip,palm_tree,christmas_tree,gift,' +
                            'alarm_clock,tada,telephone_receiver,tractor,red_car,boat';


        if (typeof container === 'string') {
            container = document.querySelector(container);
        }

        if (container === null) {
            return;
        }


        // prepare chat controls;
        input = document.createElement('div');
        input.setAttribute('contenteditable', true);
        container.appendChild(input);

        button = document.createElement('button');
        container.appendChild(button);

        list = document.createElement('ul');
        list.classList.add('hidden');
        container.appendChild(list);


        // prepare smile list;
        emoji = emoji.split(',');
        emoji.forEach(function (smileName) {
            var li = document.createElement('li'),
                img = document.createElement('img');

            img.src = options.emojiPath + smileName + options.emojiExt;
            img.alt = img.title = smileName;
            img.setAttribute('data-mimic-smile-name', smileName);
            img.setAttribute('data-mimic-smile-pack', 'emoji');
            list.appendChild(li.appendChild(img));
        });


        /**
         * Add smile html to input.
         * At the end - when input is not focused.
         * Overwrites selection, if exist.
         * @param {HTMLElement} smileElem
         * @private
         */
        function _insertSmile(smileElem) {
            var selection = window.getSelection(),
                range = selection.rangeCount === 1 ? selection.getRangeAt(0) : null,
                insertAtEnd = true;

            if (range !== null) {
                var rangeRoot = range.commonAncestorContainer;
                if (rangeRoot.nodeType === Node.TEXT_NODE) {
                    rangeRoot = rangeRoot.parentNode;
                }
                insertAtEnd = rangeRoot !== input;
            }
            
            if (insertAtEnd === true) {
                input.appendChild(smileElem);
            } else {
                if (range.collapsed === false) {
                    range.deleteContents();
                }
                range.insertNode(smileElem);
            }

            selection.removeAllRanges();
            range = document.createRange();
            range.setStartAfter(smileElem);
            selection.addRange(range);

            input.focus();

            try { document.execCommand('enableObjectResizing', false, false); } catch (e) {}
            if ('onresizestart' in smileElem) { // IE
                smileElem.onresizestart = function () { return false; };
            }
        }

        /**
         * Convert html smiles to smile codes.
         * @param html
         * @returns {XML|string}
         */
        function _encode(html) {
            html = html.replace(/<img.*?data-mimic-smile-name="(\w+)" data-mimic-smile-pack="(\w+)">/g, ' $2 $1 ');
            html = html.replace(/emoji (\w+)/g, ':$1:');
            return html;
        }

        /**
         * Opera's contenteditable weird behavior fix.
         * http://stackoverflow.com/questions/16245056/contenteditable-divs-has-a-weird-behaviour-on-focus-in-opera
         */
        if (navigator.userAgent.indexOf('Opera') !== -1) {
            input.addEventListener('click', function (event) {
                var selection = window.getSelection(),
                    range,
                    last;

                if (selection.rangeCount === 1) {
                    range = selection.getRangeAt(0);

                    if (range.collapsed === true) {
                        var cont = range.startContainer;
                        if (cont.nodeType === Node.TEXT_NODE && cont.previousSibling === container) {
                            last = this.lastChild;
                            range.setStartAfter(last);
                            range.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                    }
                }
            });
        }


        // EVENTS.

        // toggle smile container visibility;
        button.addEventListener('click', function (event) {
            list.classList.toggle('hidden');
            event.preventDefault();
        });

        // smile click event in container;
        list.addEventListener('click', function (event) {
            if (event.target.nodeName.toLowerCase() === 'img') {
                _insertSmile(event.target.cloneNode());
            }
        });

        // hide smile container after click beyond borders;
        document.addEventListener('click', function (event) {
            var target = event.target;

            while (target !== document) {
                if (target === container) {
                    return;
                }
                target = target.parentNode;
            }

            list.classList.add('hidden');
        });


        /**
         * Text value of chat input with encoded smiles.
         * @returns {XML|string}
         */
        Mimic.prototype.getValue = function () {
            return _encode(input.innerHTML);
        };

        /**
         * Clear chat input (after submit, for example).
         */
        Mimic.prototype.clear = function () {
            input.innerHTML = '';
        };

        /**
         * Decode smile codes to html entities.
         * Used in message list, notification balloons, etc.
         */
        Mimic.prototype.decode = function (str) {
            str = str.replace(/:([\+-]1|[a-z_]+):/g, '<img src="' + options.emojiPath + '$1' + options.emojiExt + '" title="$1" alt="$1" data-mimic-smile-name="$1" data-mimic-smile-pack="emoji">');
            return str;
        };
    }

    global.Mimic = Mimic;

}(this));