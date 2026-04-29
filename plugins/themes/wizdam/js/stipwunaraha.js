(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){

},{}],2:[function(require,module,exports){
module.exports = function () {
    var orig = Error.prepareStackTrace;
    Error.prepareStackTrace = function (_, stack) {
        return stack;
    };
    var err = new Error();
    Error.captureStackTrace(err, arguments.callee);
    var stack = err.stack;
    Error.prepareStackTrace = orig;
    return stack;
};
},{}],3:[function(require,module,exports){
var scrollToAnchor = require('scroll-to-anchor');
var documentReady = require('document-ready');
var nanolocation = require('nanolocation');
var nanotiming = require('nanotiming');
var nanorouter = require('nanorouter');
var nanomorph = require('nanomorph');
var nanoquery = require('nanoquery');
var nanohref = require('nanohref');
var nanoraf = require('nanoraf');
var nanobus = require('nanobus');
var xtend = require('xtend');
module.exports = Choo;
var HISTORY_OBJECT = {};
function Choo(opts) {
    if (!(this instanceof Choo))
        return new Choo(opts);
    opts = opts || {};
    var self = this;
    this._events = {
        DOMCONTENTLOADED: 'DOMContentLoaded',
        DOMTITLECHANGE: 'DOMTitleChange',
        REPLACESTATE: 'replaceState',
        PUSHSTATE: 'pushState',
        NAVIGATE: 'navigate',
        POPSTATE: 'popState',
        RENDER: 'render'
    };
    this._historyEnabled = opts.history === undefined ? true : opts.history;
    this._hrefEnabled = opts.href === undefined ? true : opts.href;
    this._hasWindow = typeof window !== 'undefined';
    this._createLocation = nanolocation;
    this._loaded = false;
    this._tree = null;
    this.router = nanorouter({ curry: true });
    this.emitter = nanobus('choo.emit');
    this.state = { events: this._events };
    if (this._hasWindow)
        this.state.title = document.title;
    this.emitter.prependListener(this._events.DOMTITLECHANGE, function (title) {
        self.state.title = title;
        if (self._hasWindow)
            document.title = title;
    });
}
Choo.prototype.route = function (route, handler) {
    var self = this;
    this.router.on(route, function (params) {
        return function () {
            self.state.params = params;
            self.state.route = route;
            var routeTiming = nanotiming('choo.route(\'' + route + '\')');
            var res = handler(self.state, function (eventName, data) {
                self.emitter.emit(eventName, data);
            });
            routeTiming();
            return res;
        };
    });
};
Choo.prototype.use = function (cb) {
    var msg = 'choo.use';
    msg = cb.storeName ? msg + '(' + cb.storeName + ')' : msg;
    var endTiming = nanotiming(msg);
    cb(this.state, this.emitter, this);
    endTiming();
};
Choo.prototype.start = function () {
    var self = this;
    if (this._historyEnabled) {
        this.emitter.prependListener(this._events.NAVIGATE, function () {
            self.state.query = nanoquery(window.location.search);
            if (self._loaded) {
                self.emitter.emit(self._events.RENDER);
                setTimeout(scrollToAnchor.bind(null, window.location.hash), 0);
            }
        });
        this.emitter.prependListener(this._events.POPSTATE, function () {
            self.emitter.emit(self._events.NAVIGATE);
        });
        this.emitter.prependListener(this._events.PUSHSTATE, function (href) {
            window.history.pushState(HISTORY_OBJECT, null, href);
            self.emitter.emit(self._events.NAVIGATE);
        });
        this.emitter.prependListener(this._events.REPLACESTATE, function (href) {
            window.history.replaceState(HISTORY_OBJECT, null, href);
            self.emitter.emit(self._events.NAVIGATE);
        });
        window.onpopstate = function () {
            self.emitter.emit(self._events.POPSTATE);
        };
        if (self._hrefEnabled) {
            nanohref(function (location) {
                var href = location.href;
                var currHref = window.location.href;
                if (href === currHref)
                    return;
                self.emitter.emit(self._events.PUSHSTATE, href);
            });
        }
    }
    this.state.href = this._createLocation();
    this.state.query = nanoquery(window.location.search);
    this._tree = this.router(this.state.href);
    this.emitter.prependListener(self._events.RENDER, nanoraf(function () {
        var renderTiming = nanotiming('choo.render');
        self.state.href = self._createLocation();
        var newTree = self.router(self.state.href);
        var morphTiming = nanotiming('choo.morph');
        nanomorph(self._tree, newTree);
        morphTiming();
        renderTiming();
    }));
    documentReady(function () {
        self.emitter.emit(self._events.DOMCONTENTLOADED);
        self._loaded = true;
    });
    return this._tree;
};
Choo.prototype.mount = function mount(selector) {
    var self = this;
    documentReady(function () {
        var renderTiming = nanotiming('choo.render');
        var newTree = self.start();
        self._tree = document.querySelector(selector);
        var morphTiming = nanotiming('choo.morph');
        nanomorph(self._tree, newTree);
        morphTiming();
        renderTiming();
    });
};
Choo.prototype.toString = function (location, state) {
    this.state = xtend(this.state, state || {});
    this.state.href = location.replace(/\?.+$/, '');
    this.state.query = nanoquery(location);
    var html = this.router(location);
    return html.toString();
};
},{"document-ready":4,"nanobus":5,"nanohref":6,"nanolocation":7,"nanomorph":8,"nanoquery":11,"nanoraf":12,"nanorouter":13,"nanotiming":14,"scroll-to-anchor":17,"xtend":20}],4:[function(require,module,exports){
'use strict';
module.exports = ready;
function ready(callback) {
    var state = document.readyState;
    if (state === 'complete' || state === 'interactive') {
        return setTimeout(callback, 0);
    }
    document.addEventListener('DOMContentLoaded', function onLoad() {
        callback();
    });
}
},{}],5:[function(require,module,exports){
var splice = require('remove-array-items');
var nanotiming = require('nanotiming');
module.exports = Nanobus;
function Nanobus(name) {
    if (!(this instanceof Nanobus))
        return new Nanobus(name);
    this._name = name || 'nanobus';
    this._starListeners = [];
    this._listeners = {};
}
Nanobus.prototype.emit = function (eventName) {
    var data = [];
    for (var i = 1, len = arguments.length; i < len; i++) {
        data.push(arguments[i]);
    }
    var emitTiming = nanotiming(this._name + '(\'' + eventName + '\')');
    var listeners = this._listeners[eventName];
    if (listeners && listeners.length > 0) {
        this._emit(this._listeners[eventName], data);
    }
    if (this._starListeners.length > 0) {
        this._emit(this._starListeners, eventName, data, emitTiming.uuid);
    }
    emitTiming();
    return this;
};
Nanobus.prototype.on = Nanobus.prototype.addListener = function (eventName, listener) {
    if (eventName === '*') {
        this._starListeners.push(listener);
    } else {
        if (!this._listeners[eventName])
            this._listeners[eventName] = [];
        this._listeners[eventName].push(listener);
    }
    return this;
};
Nanobus.prototype.prependListener = function (eventName, listener) {
    if (eventName === '*') {
        this._starListeners.unshift(listener);
    } else {
        if (!this._listeners[eventName])
            this._listeners[eventName] = [];
        this._listeners[eventName].unshift(listener);
    }
    return this;
};
Nanobus.prototype.once = function (eventName, listener) {
    var self = this;
    this.on(eventName, once);
    function once() {
        listener.apply(self, arguments);
        self.removeListener(eventName, once);
    }
    return this;
};
Nanobus.prototype.prependOnceListener = function (eventName, listener) {
    var self = this;
    this.prependListener(eventName, once);
    function once() {
        listener.apply(self, arguments);
        self.removeListener(eventName, once);
    }
    return this;
};
Nanobus.prototype.removeListener = function (eventName, listener) {
    if (eventName === '*') {
        this._starListeners = this._starListeners.slice();
        return remove(this._starListeners, listener);
    } else {
        if (typeof this._listeners[eventName] !== 'undefined') {
            this._listeners[eventName] = this._listeners[eventName].slice();
        }
        return remove(this._listeners[eventName], listener);
    }
    function remove(arr, listener) {
        if (!arr)
            return;
        var index = arr.indexOf(listener);
        if (index !== -1) {
            splice(arr, index, 1);
            return true;
        }
    }
};
Nanobus.prototype.removeAllListeners = function (eventName) {
    if (eventName) {
        if (eventName === '*') {
            this._starListeners = [];
        } else {
            this._listeners[eventName] = [];
        }
    } else {
        this._starListeners = [];
        this._listeners = {};
    }
    return this;
};
Nanobus.prototype.listeners = function (eventName) {
    var listeners = eventName !== '*' ? this._listeners[eventName] : this._starListeners;
    var ret = [];
    if (listeners) {
        var ilength = listeners.length;
        for (var i = 0; i < ilength; i++)
            ret.push(listeners[i]);
    }
    return ret;
};
Nanobus.prototype._emit = function (arr, eventName, data, uuid) {
    if (typeof arr === 'undefined')
        return;
    if (arr.length === 0)
        return;
    if (data === undefined) {
        data = eventName;
        eventName = null;
    }
    if (eventName) {
        if (uuid !== undefined) {
            data = [
                eventName,
                uuid
            ].concat(data);
        } else {
            data = [eventName].concat(data);
        }
    }
    var length = arr.length;
    for (var i = 0; i < length; i++) {
        var listener = arr[i];
        listener.apply(listener, data);
    }
};
},{"nanotiming":14,"remove-array-items":16}],6:[function(require,module,exports){
var safeExternalLink = /(noopener|noreferrer) (noopener|noreferrer)/;
var protocolLink = /^[\w-_]+:/;
module.exports = href;
function href(cb, root) {
    root = root || window.document;
    window.addEventListener('click', function (e) {
        if (e.button && e.button !== 0 || e.ctrlKey || e.metaKey || e.altKey || e.shiftKey || e.defaultPrevented)
            return;
        var anchor = function traverse(node) {
            if (!node || node === root)
                return;
            if (node.localName !== 'a' || node.href === undefined) {
                return traverse(node.parentNode);
            }
            return node;
        }(e.target);
        if (!anchor)
            return;
        if (window.location.origin !== anchor.origin || anchor.hasAttribute('download') || anchor.getAttribute('target') === '_blank' && safeExternalLink.test(anchor.getAttribute('rel')) || protocolLink.test(anchor.getAttribute('href')))
            return;
        e.preventDefault();
        cb(anchor);
    });
}
},{}],7:[function(require,module,exports){
module.exports = nanolocation;
function nanolocation() {
    var pathname = window.location.pathname.replace(/\/$/, '');
    var hash = window.location.hash.replace(/^#/, '/');
    return pathname + hash;
}
},{}],8:[function(require,module,exports){
var morph = require('./lib/morph');
var TEXT_NODE = 3;
module.exports = nanomorph;
function nanomorph(oldTree, newTree) {
    var tree = walk(newTree, oldTree);
    return tree;
}
function walk(newNode, oldNode) {
    if (!oldNode) {
        return newNode;
    } else if (!newNode) {
        return null;
    } else if (newNode.isSameNode && newNode.isSameNode(oldNode)) {
        return oldNode;
    } else if (newNode.tagName !== oldNode.tagName) {
        return newNode;
    } else {
        morph(newNode, oldNode);
        updateChildren(newNode, oldNode);
        return oldNode;
    }
}
function updateChildren(newNode, oldNode) {
    var oldChild, newChild, morphed, oldMatch;
    var offset = 0;
    for (var i = 0;; i++) {
        oldChild = oldNode.childNodes[i];
        newChild = newNode.childNodes[i - offset];
        if (!oldChild && !newChild) {
            break;
        } else if (!newChild) {
            oldNode.removeChild(oldChild);
            i--;
        } else if (!oldChild) {
            oldNode.appendChild(newChild);
            offset++;
        } else if (same(newChild, oldChild)) {
            morphed = walk(newChild, oldChild);
            if (morphed !== oldChild) {
                oldNode.replaceChild(morphed, oldChild);
                offset++;
            }
        } else {
            oldMatch = null;
            for (var j = i; j < oldNode.childNodes.length; j++) {
                if (same(oldNode.childNodes[j], newChild)) {
                    oldMatch = oldNode.childNodes[j];
                    break;
                }
            }
            if (oldMatch) {
                morphed = walk(newChild, oldMatch);
                if (morphed !== oldMatch)
                    offset++;
                oldNode.insertBefore(morphed, oldChild);
            } else if (!newChild.id && !oldChild.id) {
                morphed = walk(newChild, oldChild);
                if (morphed !== oldChild) {
                    oldNode.replaceChild(morphed, oldChild);
                    offset++;
                }
            } else {
                oldNode.insertBefore(newChild, oldChild);
                offset++;
            }
        }
    }
}
function same(a, b) {
    if (a.id)
        return a.id === b.id;
    if (a.isSameNode)
        return a.isSameNode(b);
    if (a.tagName !== b.tagName)
        return false;
    if (a.type === TEXT_NODE)
        return a.nodeValue === b.nodeValue;
    return false;
}
},{"./lib/morph":10}],9:[function(require,module,exports){
module.exports = [
    'onclick',
    'ondblclick',
    'onmousedown',
    'onmouseup',
    'onmouseover',
    'onmousemove',
    'onmouseout',
    'onmouseenter',
    'onmouseleave',
    'ontouchcancel',
    'ontouchend',
    'ontouchmove',
    'ontouchstart',
    'ondragstart',
    'ondrag',
    'ondragenter',
    'ondragleave',
    'ondragover',
    'ondrop',
    'ondragend',
    'onkeydown',
    'onkeypress',
    'onkeyup',
    'onunload',
    'onabort',
    'onerror',
    'onresize',
    'onscroll',
    'onselect',
    'onchange',
    'onsubmit',
    'onreset',
    'onfocus',
    'onblur',
    'oninput',
    'oncontextmenu',
    'onfocusin',
    'onfocusout'
];
},{}],10:[function(require,module,exports){
var events = require('./events');
var eventsLength = events.length;
var ELEMENT_NODE = 1;
var TEXT_NODE = 3;
var COMMENT_NODE = 8;
module.exports = morph;
function morph(newNode, oldNode) {
    var nodeType = newNode.nodeType;
    var nodeName = newNode.nodeName;
    if (nodeType === ELEMENT_NODE) {
        copyAttrs(newNode, oldNode);
    }
    if (nodeType === TEXT_NODE || nodeType === COMMENT_NODE) {
        if (oldNode.nodeValue !== newNode.nodeValue) {
            oldNode.nodeValue = newNode.nodeValue;
        }
    }
    if (nodeName === 'INPUT')
        updateInput(newNode, oldNode);
    else if (nodeName === 'OPTION')
        updateOption(newNode, oldNode);
    else if (nodeName === 'TEXTAREA')
        updateTextarea(newNode, oldNode);
    copyEvents(newNode, oldNode);
}
function copyAttrs(newNode, oldNode) {
    var oldAttrs = oldNode.attributes;
    var newAttrs = newNode.attributes;
    var attrNamespaceURI = null;
    var attrValue = null;
    var fromValue = null;
    var attrName = null;
    var attr = null;
    for (var i = newAttrs.length - 1; i >= 0; --i) {
        attr = newAttrs[i];
        attrName = attr.name;
        attrNamespaceURI = attr.namespaceURI;
        attrValue = attr.value;
        if (attrNamespaceURI) {
            attrName = attr.localName || attrName;
            fromValue = oldNode.getAttributeNS(attrNamespaceURI, attrName);
            if (fromValue !== attrValue) {
                oldNode.setAttributeNS(attrNamespaceURI, attrName, attrValue);
            }
        } else {
            if (!oldNode.hasAttribute(attrName)) {
                oldNode.setAttribute(attrName, attrValue);
            } else {
                fromValue = oldNode.getAttribute(attrName);
                if (fromValue !== attrValue) {
                    if (attrValue === 'null' || attrValue === 'undefined') {
                        oldNode.removeAttribute(attrName);
                    } else {
                        oldNode.setAttribute(attrName, attrValue);
                    }
                }
            }
        }
    }
    for (var j = oldAttrs.length - 1; j >= 0; --j) {
        attr = oldAttrs[j];
        if (attr.specified !== false) {
            attrName = attr.name;
            attrNamespaceURI = attr.namespaceURI;
            if (attrNamespaceURI) {
                attrName = attr.localName || attrName;
                if (!newNode.hasAttributeNS(attrNamespaceURI, attrName)) {
                    oldNode.removeAttributeNS(attrNamespaceURI, attrName);
                }
            } else {
                if (!newNode.hasAttributeNS(null, attrName)) {
                    oldNode.removeAttribute(attrName);
                }
            }
        }
    }
}
function copyEvents(newNode, oldNode) {
    for (var i = 0; i < eventsLength; i++) {
        var ev = events[i];
        if (newNode[ev]) {
            oldNode[ev] = newNode[ev];
        } else if (oldNode[ev]) {
            oldNode[ev] = undefined;
        }
    }
}
function updateOption(newNode, oldNode) {
    updateAttribute(newNode, oldNode, 'selected');
}
function updateInput(newNode, oldNode) {
    var newValue = newNode.value;
    var oldValue = oldNode.value;
    updateAttribute(newNode, oldNode, 'checked');
    updateAttribute(newNode, oldNode, 'disabled');
    if (newValue !== oldValue) {
        oldNode.setAttribute('value', newValue);
        oldNode.value = newValue;
    }
    if (newValue === 'null') {
        oldNode.value = '';
        oldNode.removeAttribute('value');
    }
    if (!newNode.hasAttributeNS(null, 'value')) {
        oldNode.removeAttribute('value');
    } else if (oldNode.type === 'range') {
        oldNode.value = newValue;
    }
}
function updateTextarea(newNode, oldNode) {
    var newValue = newNode.value;
    if (newValue !== oldNode.value) {
        oldNode.value = newValue;
    }
    if (oldNode.firstChild && oldNode.firstChild.nodeValue !== newValue) {
        if (newValue === '' && oldNode.firstChild.nodeValue === oldNode.placeholder) {
            return;
        }
        oldNode.firstChild.nodeValue = newValue;
    }
}
function updateAttribute(newNode, oldNode, name) {
    if (newNode[name] !== oldNode[name]) {
        oldNode[name] = newNode[name];
        if (newNode[name]) {
            oldNode.setAttribute(name, '');
        } else {
            oldNode.removeAttribute(name);
        }
    }
}
},{"./events":9}],11:[function(require,module,exports){
var reg = /([^?=&]+)(=([^&]*))?/g;
module.exports = qs;
function qs(url) {
    var obj = {};
    url.replace(/^.*\?/, '').replace(reg, function (a0, a1, a2, a3) {
        obj[decodeURIComponent(a1)] = decodeURIComponent(a3);
    });
    return obj;
}
},{}],12:[function(require,module,exports){
'use strict';
module.exports = nanoraf;
function nanoraf(render, raf) {
    if (!raf)
        raf = window.requestAnimationFrame;
    var redrawScheduled = false;
    var args = null;
    return function frame() {
        if (args === null && !redrawScheduled) {
            redrawScheduled = true;
            raf(function redraw() {
                redrawScheduled = false;
                var length = args.length;
                var _args = new Array(length);
                for (var i = 0; i < length; i++)
                    _args[i] = args[i];
                render.apply(render, _args);
                args = null;
            });
        }
        args = arguments;
    };
}
},{}],13:[function(require,module,exports){
var wayfarer = require('wayfarer');
var isLocalFile = /file:\/\//.test(typeof window === 'object' && window.location && window.location.origin);
var electron = '^(file://|/)(.*.html?/?)?';
var protocol = '^(http(s)?(://))?(www.)?';
var domain = '[a-zA-Z0-9-_.]+(:[0-9]{1,5})?(/{1})?';
var qs = '[?].*$';
var stripElectron = new RegExp(electron);
var prefix = new RegExp(protocol + domain);
var normalize = new RegExp('#');
var suffix = new RegExp(qs);
module.exports = Nanorouter;
function Nanorouter(opts) {
    opts = opts || {};
    var router = wayfarer(opts.default || '/404');
    var curry = opts.curry || false;
    var prevCallback = null;
    var prevRoute = null;
    emit.router = router;
    emit.on = on;
    return emit;
    function on(routename, listener) {
        routename = routename.replace(/^[#/]/, '');
        router.on(routename, listener);
    }
    function emit(route) {
        if (!curry) {
            return router(route);
        } else {
            route = pathname(route, isLocalFile);
            if (route === prevRoute) {
                return prevCallback();
            } else {
                prevRoute = route;
                prevCallback = router(route);
                return prevCallback();
            }
        }
    }
}
function pathname(route, isElectron) {
    if (isElectron)
        route = route.replace(stripElectron, '');
    else
        route = route.replace(prefix, '');
    return route.replace(suffix, '').replace(normalize, '/');
}
},{"wayfarer":18}],14:[function(require,module,exports){
var onIdle = require('./lib/on-idle');
var perf;
var disabled = true;
try {
    perf = window.performance;
    disabled = window.localStorage.DISABLE_NANOTIMING === 'true' || !perf.mark;
} catch (e) {
}
module.exports = nanotiming;
function nanotiming(name) {
    if (disabled)
        return noop;
    var uuid = (perf.now() * 10000).toFixed() % Number.MAX_SAFE_INTEGER;
    var startName = 'start-' + uuid + '-' + name;
    perf.mark(startName);
    function end(cb) {
        var endName = 'end-' + uuid + '-' + name;
        perf.mark(endName);
        onIdle(function () {
            var measureName = name + ' [' + uuid + ']';
            perf.measure(measureName, startName, endName);
            perf.clearMarks(startName);
            perf.clearMarks(endName);
            if (cb)
                cb(name);
        });
    }
    end.uuid = uuid;
    return end;
}
function noop(cb) {
    if (cb)
        onIdle(cb);
}
},{"./lib/on-idle":15}],15:[function(require,module,exports){
var dftOpts = {};
var hasWindow = typeof window !== 'undefined';
var hasIdle = hasWindow && window.requestIdleCallback;
module.exports = onIdle;
function onIdle(cb, opts) {
    opts = opts || dftOpts;
    var timerId;
    if (hasIdle) {
        timerId = window.requestIdleCallback(function (idleDeadline) {
            if (idleDeadline.timeRemaining() <= 10 && !idleDeadline.didTimeout) {
                return onIdle(cb, opts);
            } else {
                cb(idleDeadline);
            }
        }, opts);
        return window.cancelIdleCallback.bind(window, timerId);
    } else if (hasWindow) {
        timerId = setTimeout(cb, 0);
        return clearTimeout.bind(window, timerId);
    }
}
},{}],16:[function(require,module,exports){
'use strict';
module.exports = function removeItems(arr, startIdx, removeCount) {
    var i, length = arr.length;
    if (startIdx >= length || removeCount === 0) {
        return;
    }
    removeCount = startIdx + removeCount > length ? length - startIdx : removeCount;
    var len = length - removeCount;
    for (i = startIdx; i < len; ++i) {
        arr[i] = arr[i + removeCount];
    }
    arr.length = len;
};
},{}],17:[function(require,module,exports){
module.exports = scrollToAnchor;
function scrollToAnchor(anchor, options) {
    if (anchor) {
        try {
            var el = document.querySelector(anchor);
            if (el)
                el.scrollIntoView(options);
        } catch (e) {
        }
    }
}
},{}],18:[function(require,module,exports){
var trie = require('./trie');
module.exports = Wayfarer;
function Wayfarer(dft) {
    if (!(this instanceof Wayfarer))
        return new Wayfarer(dft);
    var _default = (dft || '').replace(/^\//, '');
    var _trie = trie();
    emit._trie = _trie;
    emit.emit = emit;
    emit.on = on;
    emit._wayfarer = true;
    return emit;
    function on(route, cb) {
        route = route || '/';
        cb.route = route;
        if (cb && cb._wayfarer && cb._trie) {
            _trie.mount(route, cb._trie.trie);
        } else {
            var node = _trie.create(route);
            node.cb = cb;
        }
        return emit;
    }
    function emit(route) {
        var args = new Array(arguments.length);
        for (var i = 1; i < args.length; i++) {
            args[i] = arguments[i];
        }
        var node = _trie.match(route);
        if (node && node.cb) {
            args[0] = node.params;
            var cb = node.cb;
            return cb.apply(cb, args);
        }
        var dft = _trie.match(_default);
        if (dft && dft.cb) {
            args[0] = dft.params;
            var dftcb = dft.cb;
            return dftcb.apply(dftcb, args);
        }
        throw new Error('route \'' + route + '\' did not match');
    }
}
},{"./trie":19}],19:[function(require,module,exports){
var mutate = require('xtend/mutable');
var xtend = require('xtend');
module.exports = Trie;
function Trie() {
    if (!(this instanceof Trie))
        return new Trie();
    this.trie = { nodes: {} };
}
Trie.prototype.create = function (route) {
    var routes = route.replace(/^\//, '').split('/');
    function createNode(index, trie) {
        var thisRoute = routes.hasOwnProperty(index) && routes[index];
        if (thisRoute === false)
            return trie;
        var node = null;
        if (/^:|^\*/.test(thisRoute)) {
            if (!trie.nodes.hasOwnProperty('$$')) {
                node = { nodes: {} };
                trie.nodes['$$'] = node;
            } else {
                node = trie.nodes['$$'];
            }
            if (thisRoute[0] === '*') {
                trie.wildcard = true;
            }
            trie.name = thisRoute.replace(/^:|^\*/, '');
        } else if (!trie.nodes.hasOwnProperty(thisRoute)) {
            node = { nodes: {} };
            trie.nodes[thisRoute] = node;
        } else {
            node = trie.nodes[thisRoute];
        }
        return createNode(index + 1, node);
    }
    return createNode(0, this.trie);
};
Trie.prototype.match = function (route) {
    var routes = route.replace(/^\//, '').split('/');
    var params = {};
    function search(index, trie) {
        if (trie === undefined)
            return undefined;
        var thisRoute = routes[index];
        if (thisRoute === undefined)
            return trie;
        if (trie.nodes.hasOwnProperty(thisRoute)) {
            return search(index + 1, trie.nodes[thisRoute]);
        } else if (trie.name) {
            try {
                params[trie.name] = decodeURIComponent(thisRoute);
            } catch (e) {
                return search(index, undefined);
            }
            return search(index + 1, trie.nodes['$$']);
        } else if (trie.wildcard) {
            try {
                params['wildcard'] = decodeURIComponent(routes.slice(index).join('/'));
            } catch (e) {
                return search(index, undefined);
            }
            return trie.nodes['$$'];
        } else {
            return search(index + 1);
        }
    }
    var node = search(0, this.trie);
    if (!node)
        return undefined;
    node = xtend(node);
    node.params = params;
    return node;
};
Trie.prototype.mount = function (route, trie) {
    var split = route.replace(/^\//, '').split('/');
    var node = null;
    var key = null;
    if (split.length === 1) {
        key = split[0];
        node = this.create(key);
    } else {
        var headArr = split.splice(0, split.length - 1);
        var head = headArr.join('/');
        key = split[0];
        node = this.create(head);
    }
    mutate(node.nodes, trie.nodes);
    if (trie.name)
        node.name = trie.name;
    if (node.nodes['']) {
        Object.keys(node.nodes['']).forEach(function (key) {
            if (key === 'nodes')
                return;
            node[key] = node.nodes[''][key];
        });
        mutate(node.nodes, node.nodes[''].nodes);
        delete node.nodes[''].nodes;
    }
};
},{"xtend":20,"xtend/mutable":21}],20:[function(require,module,exports){
module.exports = extend;
var hasOwnProperty = Object.prototype.hasOwnProperty;
function extend() {
    var target = {};
    for (var i = 0; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
            if (hasOwnProperty.call(source, key)) {
                target[key] = source[key];
            }
        }
    }
    return target;
}
},{}],21:[function(require,module,exports){
module.exports = extend;
var hasOwnProperty = Object.prototype.hasOwnProperty;
function extend(target) {
    for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
            if (hasOwnProperty.call(source, key)) {
                target[key] = source[key];
            }
        }
    }
    return target;
}
},{}],22:[function(require,module,exports){
'use strict';
var colorString = require('color-string');
var convert = require('color-convert');
var _slice = [].slice;
var skippedModels = [
    'keyword',
    'gray',
    'hex'
];
var hashedModelKeys = {};
Object.keys(convert).forEach(function (model) {
    hashedModelKeys[_slice.call(convert[model].labels).sort().join('')] = model;
});
var limiters = {};
function Color(obj, model) {
    if (!(this instanceof Color)) {
        return new Color(obj, model);
    }
    if (model && model in skippedModels) {
        model = null;
    }
    if (model && !(model in convert)) {
        throw new Error('Unknown model: ' + model);
    }
    var i;
    var channels;
    if (!obj) {
        this.model = 'rgb';
        this.color = [
            0,
            0,
            0
        ];
        this.valpha = 1;
    } else if (obj instanceof Color) {
        this.model = obj.model;
        this.color = obj.color.slice();
        this.valpha = obj.valpha;
    } else if (typeof obj === 'string') {
        var result = colorString.get(obj);
        if (result === null) {
            throw new Error('Unable to parse color from string: ' + obj);
        }
        this.model = result.model;
        channels = convert[this.model].channels;
        this.color = result.value.slice(0, channels);
        this.valpha = typeof result.value[channels] === 'number' ? result.value[channels] : 1;
    } else if (obj.length) {
        this.model = model || 'rgb';
        channels = convert[this.model].channels;
        var newArr = _slice.call(obj, 0, channels);
        this.color = zeroArray(newArr, channels);
        this.valpha = typeof obj[channels] === 'number' ? obj[channels] : 1;
    } else if (typeof obj === 'number') {
        obj &= 16777215;
        this.model = 'rgb';
        this.color = [
            obj >> 16 & 255,
            obj >> 8 & 255,
            obj & 255
        ];
        this.valpha = 1;
    } else {
        this.valpha = 1;
        var keys = Object.keys(obj);
        if ('alpha' in obj) {
            keys.splice(keys.indexOf('alpha'), 1);
            this.valpha = typeof obj.alpha === 'number' ? obj.alpha : 0;
        }
        var hashedKeys = keys.sort().join('');
        if (!(hashedKeys in hashedModelKeys)) {
            throw new Error('Unable to parse color from object: ' + JSON.stringify(obj));
        }
        this.model = hashedModelKeys[hashedKeys];
        var labels = convert[this.model].labels;
        var color = [];
        for (i = 0; i < labels.length; i++) {
            color.push(obj[labels[i]]);
        }
        this.color = zeroArray(color);
    }
    if (limiters[this.model]) {
        channels = convert[this.model].channels;
        for (i = 0; i < channels; i++) {
            var limit = limiters[this.model][i];
            if (limit) {
                this.color[i] = limit(this.color[i]);
            }
        }
    }
    this.valpha = Math.max(0, Math.min(1, this.valpha));
    if (Object.freeze) {
        Object.freeze(this);
    }
}
Color.prototype = {
    toString: function () {
        return this.string();
    },
    toJSON: function () {
        return this[this.model]();
    },
    string: function (places) {
        var self = this.model in colorString.to ? this : this.rgb();
        self = self.round(typeof places === 'number' ? places : 1);
        var args = self.valpha === 1 ? self.color : self.color.concat(this.valpha);
        return colorString.to[self.model](args);
    },
    percentString: function (places) {
        var self = this.rgb().round(typeof places === 'number' ? places : 1);
        var args = self.valpha === 1 ? self.color : self.color.concat(this.valpha);
        return colorString.to.rgb.percent(args);
    },
    array: function () {
        return this.valpha === 1 ? this.color.slice() : this.color.concat(this.valpha);
    },
    object: function () {
        var result = {};
        var channels = convert[this.model].channels;
        var labels = convert[this.model].labels;
        for (var i = 0; i < channels; i++) {
            result[labels[i]] = this.color[i];
        }
        if (this.valpha !== 1) {
            result.alpha = this.valpha;
        }
        return result;
    },
    unitArray: function () {
        var rgb = this.rgb().color;
        rgb[0] /= 255;
        rgb[1] /= 255;
        rgb[2] /= 255;
        if (this.valpha !== 1) {
            rgb.push(this.valpha);
        }
        return rgb;
    },
    unitObject: function () {
        var rgb = this.rgb().object();
        rgb.r /= 255;
        rgb.g /= 255;
        rgb.b /= 255;
        if (this.valpha !== 1) {
            rgb.alpha = this.valpha;
        }
        return rgb;
    },
    round: function (places) {
        places = Math.max(places || 0, 0);
        return new Color(this.color.map(roundToPlace(places)).concat(this.valpha), this.model);
    },
    alpha: function (val) {
        if (arguments.length) {
            return new Color(this.color.concat(Math.max(0, Math.min(1, val))), this.model);
        }
        return this.valpha;
    },
    red: getset('rgb', 0, maxfn(255)),
    green: getset('rgb', 1, maxfn(255)),
    blue: getset('rgb', 2, maxfn(255)),
    hue: getset([
        'hsl',
        'hsv',
        'hsl',
        'hwb',
        'hcg'
    ], 0, function (val) {
        return (val % 360 + 360) % 360;
    }),
    saturationl: getset('hsl', 1, maxfn(100)),
    lightness: getset('hsl', 2, maxfn(100)),
    saturationv: getset('hsv', 1, maxfn(100)),
    value: getset('hsv', 2, maxfn(100)),
    chroma: getset('hcg', 1, maxfn(100)),
    gray: getset('hcg', 2, maxfn(100)),
    white: getset('hwb', 1, maxfn(100)),
    wblack: getset('hwb', 2, maxfn(100)),
    cyan: getset('cmyk', 0, maxfn(100)),
    magenta: getset('cmyk', 1, maxfn(100)),
    yellow: getset('cmyk', 2, maxfn(100)),
    black: getset('cmyk', 3, maxfn(100)),
    x: getset('xyz', 0, maxfn(100)),
    y: getset('xyz', 1, maxfn(100)),
    z: getset('xyz', 2, maxfn(100)),
    l: getset('lab', 0, maxfn(100)),
    a: getset('lab', 1),
    b: getset('lab', 2),
    keyword: function (val) {
        if (arguments.length) {
            return new Color(val);
        }
        return convert[this.model].keyword(this.color);
    },
    hex: function (val) {
        if (arguments.length) {
            return new Color(val);
        }
        return colorString.to.hex(this.rgb().round().color);
    },
    rgbNumber: function () {
        var rgb = this.rgb().color;
        return (rgb[0] & 255) << 16 | (rgb[1] & 255) << 8 | rgb[2] & 255;
    },
    luminosity: function () {
        var rgb = this.rgb().color;
        var lum = [];
        for (var i = 0; i < rgb.length; i++) {
            var chan = rgb[i] / 255;
            lum[i] = chan <= 0.03928 ? chan / 12.92 : Math.pow((chan + 0.055) / 1.055, 2.4);
        }
        return 0.2126 * lum[0] + 0.7152 * lum[1] + 0.0722 * lum[2];
    },
    contrast: function (color2) {
        var lum1 = this.luminosity();
        var lum2 = color2.luminosity();
        if (lum1 > lum2) {
            return (lum1 + 0.05) / (lum2 + 0.05);
        }
        return (lum2 + 0.05) / (lum1 + 0.05);
    },
    level: function (color2) {
        var contrastRatio = this.contrast(color2);
        if (contrastRatio >= 7.1) {
            return 'AAA';
        }
        return contrastRatio >= 4.5 ? 'AA' : '';
    },
    dark: function () {
        var rgb = this.rgb().color;
        var yiq = (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000;
        return yiq < 128;
    },
    light: function () {
        return !this.dark();
    },
    negate: function () {
        var rgb = this.rgb();
        for (var i = 0; i < 3; i++) {
            rgb.color[i] = 255 - rgb.color[i];
        }
        return rgb;
    },
    lighten: function (ratio) {
        var hsl = this.hsl();
        hsl.color[2] += hsl.color[2] * ratio;
        return hsl;
    },
    darken: function (ratio) {
        var hsl = this.hsl();
        hsl.color[2] -= hsl.color[2] * ratio;
        return hsl;
    },
    saturate: function (ratio) {
        var hsl = this.hsl();
        hsl.color[1] += hsl.color[1] * ratio;
        return hsl;
    },
    desaturate: function (ratio) {
        var hsl = this.hsl();
        hsl.color[1] -= hsl.color[1] * ratio;
        return hsl;
    },
    whiten: function (ratio) {
        var hwb = this.hwb();
        hwb.color[1] += hwb.color[1] * ratio;
        return hwb;
    },
    blacken: function (ratio) {
        var hwb = this.hwb();
        hwb.color[2] += hwb.color[2] * ratio;
        return hwb;
    },
    grayscale: function () {
        var rgb = this.rgb().color;
        var val = rgb[0] * 0.3 + rgb[1] * 0.59 + rgb[2] * 0.11;
        return Color.rgb(val, val, val);
    },
    fade: function (ratio) {
        return this.alpha(this.valpha - this.valpha * ratio);
    },
    opaquer: function (ratio) {
        return this.alpha(this.valpha + this.valpha * ratio);
    },
    rotate: function (degrees) {
        var hsl = this.hsl();
        var hue = hsl.color[0];
        hue = (hue + degrees) % 360;
        hue = hue < 0 ? 360 + hue : hue;
        hsl.color[0] = hue;
        return hsl;
    },
    mix: function (mixinColor, weight) {
        var color1 = mixinColor.rgb();
        var color2 = this.rgb();
        var p = weight === undefined ? 0.5 : weight;
        var w = 2 * p - 1;
        var a = color1.alpha() - color2.alpha();
        var w1 = ((w * a === -1 ? w : (w + a) / (1 + w * a)) + 1) / 2;
        var w2 = 1 - w1;
        return Color.rgb(w1 * color1.red() + w2 * color2.red(), w1 * color1.green() + w2 * color2.green(), w1 * color1.blue() + w2 * color2.blue(), color1.alpha() * p + color2.alpha() * (1 - p));
    }
};
Object.keys(convert).forEach(function (model) {
    if (skippedModels.indexOf(model) !== -1) {
        return;
    }
    var channels = convert[model].channels;
    Color.prototype[model] = function () {
        if (this.model === model) {
            return new Color(this);
        }
        if (arguments.length) {
            return new Color(arguments, model);
        }
        var newAlpha = typeof arguments[channels] === 'number' ? channels : this.valpha;
        return new Color(assertArray(convert[this.model][model].raw(this.color)).concat(newAlpha), model);
    };
    Color[model] = function (color) {
        if (typeof color === 'number') {
            color = zeroArray(_slice.call(arguments), channels);
        }
        return new Color(color, model);
    };
});
function roundTo(num, places) {
    return Number(num.toFixed(places));
}
function roundToPlace(places) {
    return function (num) {
        return roundTo(num, places);
    };
}
function getset(model, channel, modifier) {
    model = Array.isArray(model) ? model : [model];
    model.forEach(function (m) {
        (limiters[m] || (limiters[m] = []))[channel] = modifier;
    });
    model = model[0];
    return function (val) {
        var result;
        if (arguments.length) {
            if (modifier) {
                val = modifier(val);
            }
            result = this[model]();
            result.color[channel] = val;
            return result;
        }
        result = this[model]().color[channel];
        if (modifier) {
            result = modifier(result);
        }
        return result;
    };
}
function maxfn(max) {
    return function (v) {
        return Math.max(0, Math.min(max, v));
    };
}
function assertArray(val) {
    return Array.isArray(val) ? val : [val];
}
function zeroArray(arr, length) {
    for (var i = 0; i < length; i++) {
        if (typeof arr[i] !== 'number') {
            arr[i] = 0;
        }
    }
    return arr;
}
module.exports = Color;
},{"color-convert":24,"color-string":27}],23:[function(require,module,exports){
var cssKeywords = require('color-name');
var reverseKeywords = {};
for (var key in cssKeywords) {
    if (cssKeywords.hasOwnProperty(key)) {
        reverseKeywords[cssKeywords[key]] = key;
    }
}
var convert = module.exports = {
    rgb: {
        channels: 3,
        labels: 'rgb'
    },
    hsl: {
        channels: 3,
        labels: 'hsl'
    },
    hsv: {
        channels: 3,
        labels: 'hsv'
    },
    hwb: {
        channels: 3,
        labels: 'hwb'
    },
    cmyk: {
        channels: 4,
        labels: 'cmyk'
    },
    xyz: {
        channels: 3,
        labels: 'xyz'
    },
    lab: {
        channels: 3,
        labels: 'lab'
    },
    lch: {
        channels: 3,
        labels: 'lch'
    },
    hex: {
        channels: 1,
        labels: ['hex']
    },
    keyword: {
        channels: 1,
        labels: ['keyword']
    },
    ansi16: {
        channels: 1,
        labels: ['ansi16']
    },
    ansi256: {
        channels: 1,
        labels: ['ansi256']
    },
    hcg: {
        channels: 3,
        labels: [
            'h',
            'c',
            'g'
        ]
    },
    apple: {
        channels: 3,
        labels: [
            'r16',
            'g16',
            'b16'
        ]
    },
    gray: {
        channels: 1,
        labels: ['gray']
    }
};
for (var model in convert) {
    if (convert.hasOwnProperty(model)) {
        if (!('channels' in convert[model])) {
            throw new Error('missing channels property: ' + model);
        }
        if (!('labels' in convert[model])) {
            throw new Error('missing channel labels property: ' + model);
        }
        if (convert[model].labels.length !== convert[model].channels) {
            throw new Error('channel and label counts mismatch: ' + model);
        }
        var channels = convert[model].channels;
        var labels = convert[model].labels;
        delete convert[model].channels;
        delete convert[model].labels;
        Object.defineProperty(convert[model], 'channels', { value: channels });
        Object.defineProperty(convert[model], 'labels', { value: labels });
    }
}
convert.rgb.hsl = function (rgb) {
    var r = rgb[0] / 255;
    var g = rgb[1] / 255;
    var b = rgb[2] / 255;
    var min = Math.min(r, g, b);
    var max = Math.max(r, g, b);
    var delta = max - min;
    var h;
    var s;
    var l;
    if (max === min) {
        h = 0;
    } else if (r === max) {
        h = (g - b) / delta;
    } else if (g === max) {
        h = 2 + (b - r) / delta;
    } else if (b === max) {
        h = 4 + (r - g) / delta;
    }
    h = Math.min(h * 60, 360);
    if (h < 0) {
        h += 360;
    }
    l = (min + max) / 2;
    if (max === min) {
        s = 0;
    } else if (l <= 0.5) {
        s = delta / (max + min);
    } else {
        s = delta / (2 - max - min);
    }
    return [
        h,
        s * 100,
        l * 100
    ];
};
convert.rgb.hsv = function (rgb) {
    var r = rgb[0];
    var g = rgb[1];
    var b = rgb[2];
    var min = Math.min(r, g, b);
    var max = Math.max(r, g, b);
    var delta = max - min;
    var h;
    var s;
    var v;
    if (max === 0) {
        s = 0;
    } else {
        s = delta / max * 1000 / 10;
    }
    if (max === min) {
        h = 0;
    } else if (r === max) {
        h = (g - b) / delta;
    } else if (g === max) {
        h = 2 + (b - r) / delta;
    } else if (b === max) {
        h = 4 + (r - g) / delta;
    }
    h = Math.min(h * 60, 360);
    if (h < 0) {
        h += 360;
    }
    v = max / 255 * 1000 / 10;
    return [
        h,
        s,
        v
    ];
};
convert.rgb.hwb = function (rgb) {
    var r = rgb[0];
    var g = rgb[1];
    var b = rgb[2];
    var h = convert.rgb.hsl(rgb)[0];
    var w = 1 / 255 * Math.min(r, Math.min(g, b));
    b = 1 - 1 / 255 * Math.max(r, Math.max(g, b));
    return [
        h,
        w * 100,
        b * 100
    ];
};
convert.rgb.cmyk = function (rgb) {
    var r = rgb[0] / 255;
    var g = rgb[1] / 255;
    var b = rgb[2] / 255;
    var c;
    var m;
    var y;
    var k;
    k = Math.min(1 - r, 1 - g, 1 - b);
    c = (1 - r - k) / (1 - k) || 0;
    m = (1 - g - k) / (1 - k) || 0;
    y = (1 - b - k) / (1 - k) || 0;
    return [
        c * 100,
        m * 100,
        y * 100,
        k * 100
    ];
};
function comparativeDistance(x, y) {
    return Math.pow(x[0] - y[0], 2) + Math.pow(x[1] - y[1], 2) + Math.pow(x[2] - y[2], 2);
}
convert.rgb.keyword = function (rgb) {
    var reversed = reverseKeywords[rgb];
    if (reversed) {
        return reversed;
    }
    var currentClosestDistance = Infinity;
    var currentClosestKeyword;
    for (var keyword in cssKeywords) {
        if (cssKeywords.hasOwnProperty(keyword)) {
            var value = cssKeywords[keyword];
            var distance = comparativeDistance(rgb, value);
            if (distance < currentClosestDistance) {
                currentClosestDistance = distance;
                currentClosestKeyword = keyword;
            }
        }
    }
    return currentClosestKeyword;
};
convert.keyword.rgb = function (keyword) {
    return cssKeywords[keyword];
};
convert.rgb.xyz = function (rgb) {
    var r = rgb[0] / 255;
    var g = rgb[1] / 255;
    var b = rgb[2] / 255;
    r = r > 0.04045 ? Math.pow((r + 0.055) / 1.055, 2.4) : r / 12.92;
    g = g > 0.04045 ? Math.pow((g + 0.055) / 1.055, 2.4) : g / 12.92;
    b = b > 0.04045 ? Math.pow((b + 0.055) / 1.055, 2.4) : b / 12.92;
    var x = r * 0.4124 + g * 0.3576 + b * 0.1805;
    var y = r * 0.2126 + g * 0.7152 + b * 0.0722;
    var z = r * 0.0193 + g * 0.1192 + b * 0.9505;
    return [
        x * 100,
        y * 100,
        z * 100
    ];
};
convert.rgb.lab = function (rgb) {
    var xyz = convert.rgb.xyz(rgb);
    var x = xyz[0];
    var y = xyz[1];
    var z = xyz[2];
    var l;
    var a;
    var b;
    x /= 95.047;
    y /= 100;
    z /= 108.883;
    x = x > 0.008856 ? Math.pow(x, 1 / 3) : 7.787 * x + 16 / 116;
    y = y > 0.008856 ? Math.pow(y, 1 / 3) : 7.787 * y + 16 / 116;
    z = z > 0.008856 ? Math.pow(z, 1 / 3) : 7.787 * z + 16 / 116;
    l = 116 * y - 16;
    a = 500 * (x - y);
    b = 200 * (y - z);
    return [
        l,
        a,
        b
    ];
};
convert.hsl.rgb = function (hsl) {
    var h = hsl[0] / 360;
    var s = hsl[1] / 100;
    var l = hsl[2] / 100;
    var t1;
    var t2;
    var t3;
    var rgb;
    var val;
    if (s === 0) {
        val = l * 255;
        return [
            val,
            val,
            val
        ];
    }
    if (l < 0.5) {
        t2 = l * (1 + s);
    } else {
        t2 = l + s - l * s;
    }
    t1 = 2 * l - t2;
    rgb = [
        0,
        0,
        0
    ];
    for (var i = 0; i < 3; i++) {
        t3 = h + 1 / 3 * -(i - 1);
        if (t3 < 0) {
            t3++;
        }
        if (t3 > 1) {
            t3--;
        }
        if (6 * t3 < 1) {
            val = t1 + (t2 - t1) * 6 * t3;
        } else if (2 * t3 < 1) {
            val = t2;
        } else if (3 * t3 < 2) {
            val = t1 + (t2 - t1) * (2 / 3 - t3) * 6;
        } else {
            val = t1;
        }
        rgb[i] = val * 255;
    }
    return rgb;
};
convert.hsl.hsv = function (hsl) {
    var h = hsl[0];
    var s = hsl[1] / 100;
    var l = hsl[2] / 100;
    var smin = s;
    var lmin = Math.max(l, 0.01);
    var sv;
    var v;
    l *= 2;
    s *= l <= 1 ? l : 2 - l;
    smin *= lmin <= 1 ? lmin : 2 - lmin;
    v = (l + s) / 2;
    sv = l === 0 ? 2 * smin / (lmin + smin) : 2 * s / (l + s);
    return [
        h,
        sv * 100,
        v * 100
    ];
};
convert.hsv.rgb = function (hsv) {
    var h = hsv[0] / 60;
    var s = hsv[1] / 100;
    var v = hsv[2] / 100;
    var hi = Math.floor(h) % 6;
    var f = h - Math.floor(h);
    var p = 255 * v * (1 - s);
    var q = 255 * v * (1 - s * f);
    var t = 255 * v * (1 - s * (1 - f));
    v *= 255;
    switch (hi) {
    case 0:
        return [
            v,
            t,
            p
        ];
    case 1:
        return [
            q,
            v,
            p
        ];
    case 2:
        return [
            p,
            v,
            t
        ];
    case 3:
        return [
            p,
            q,
            v
        ];
    case 4:
        return [
            t,
            p,
            v
        ];
    case 5:
        return [
            v,
            p,
            q
        ];
    }
};
convert.hsv.hsl = function (hsv) {
    var h = hsv[0];
    var s = hsv[1] / 100;
    var v = hsv[2] / 100;
    var vmin = Math.max(v, 0.01);
    var lmin;
    var sl;
    var l;
    l = (2 - s) * v;
    lmin = (2 - s) * vmin;
    sl = s * vmin;
    sl /= lmin <= 1 ? lmin : 2 - lmin;
    sl = sl || 0;
    l /= 2;
    return [
        h,
        sl * 100,
        l * 100
    ];
};
convert.hwb.rgb = function (hwb) {
    var h = hwb[0] / 360;
    var wh = hwb[1] / 100;
    var bl = hwb[2] / 100;
    var ratio = wh + bl;
    var i;
    var v;
    var f;
    var n;
    if (ratio > 1) {
        wh /= ratio;
        bl /= ratio;
    }
    i = Math.floor(6 * h);
    v = 1 - bl;
    f = 6 * h - i;
    if ((i & 1) !== 0) {
        f = 1 - f;
    }
    n = wh + f * (v - wh);
    var r;
    var g;
    var b;
    switch (i) {
    default:
    case 6:
    case 0:
        r = v;
        g = n;
        b = wh;
        break;
    case 1:
        r = n;
        g = v;
        b = wh;
        break;
    case 2:
        r = wh;
        g = v;
        b = n;
        break;
    case 3:
        r = wh;
        g = n;
        b = v;
        break;
    case 4:
        r = n;
        g = wh;
        b = v;
        break;
    case 5:
        r = v;
        g = wh;
        b = n;
        break;
    }
    return [
        r * 255,
        g * 255,
        b * 255
    ];
};
convert.cmyk.rgb = function (cmyk) {
    var c = cmyk[0] / 100;
    var m = cmyk[1] / 100;
    var y = cmyk[2] / 100;
    var k = cmyk[3] / 100;
    var r;
    var g;
    var b;
    r = 1 - Math.min(1, c * (1 - k) + k);
    g = 1 - Math.min(1, m * (1 - k) + k);
    b = 1 - Math.min(1, y * (1 - k) + k);
    return [
        r * 255,
        g * 255,
        b * 255
    ];
};
convert.xyz.rgb = function (xyz) {
    var x = xyz[0] / 100;
    var y = xyz[1] / 100;
    var z = xyz[2] / 100;
    var r;
    var g;
    var b;
    r = x * 3.2406 + y * -1.5372 + z * -0.4986;
    g = x * -0.9689 + y * 1.8758 + z * 0.0415;
    b = x * 0.0557 + y * -0.204 + z * 1.057;
    r = r > 0.0031308 ? 1.055 * Math.pow(r, 1 / 2.4) - 0.055 : r * 12.92;
    g = g > 0.0031308 ? 1.055 * Math.pow(g, 1 / 2.4) - 0.055 : g * 12.92;
    b = b > 0.0031308 ? 1.055 * Math.pow(b, 1 / 2.4) - 0.055 : b * 12.92;
    r = Math.min(Math.max(0, r), 1);
    g = Math.min(Math.max(0, g), 1);
    b = Math.min(Math.max(0, b), 1);
    return [
        r * 255,
        g * 255,
        b * 255
    ];
};
convert.xyz.lab = function (xyz) {
    var x = xyz[0];
    var y = xyz[1];
    var z = xyz[2];
    var l;
    var a;
    var b;
    x /= 95.047;
    y /= 100;
    z /= 108.883;
    x = x > 0.008856 ? Math.pow(x, 1 / 3) : 7.787 * x + 16 / 116;
    y = y > 0.008856 ? Math.pow(y, 1 / 3) : 7.787 * y + 16 / 116;
    z = z > 0.008856 ? Math.pow(z, 1 / 3) : 7.787 * z + 16 / 116;
    l = 116 * y - 16;
    a = 500 * (x - y);
    b = 200 * (y - z);
    return [
        l,
        a,
        b
    ];
};
convert.lab.xyz = function (lab) {
    var l = lab[0];
    var a = lab[1];
    var b = lab[2];
    var x;
    var y;
    var z;
    y = (l + 16) / 116;
    x = a / 500 + y;
    z = y - b / 200;
    var y2 = Math.pow(y, 3);
    var x2 = Math.pow(x, 3);
    var z2 = Math.pow(z, 3);
    y = y2 > 0.008856 ? y2 : (y - 16 / 116) / 7.787;
    x = x2 > 0.008856 ? x2 : (x - 16 / 116) / 7.787;
    z = z2 > 0.008856 ? z2 : (z - 16 / 116) / 7.787;
    x *= 95.047;
    y *= 100;
    z *= 108.883;
    return [
        x,
        y,
        z
    ];
};
convert.lab.lch = function (lab) {
    var l = lab[0];
    var a = lab[1];
    var b = lab[2];
    var hr;
    var h;
    var c;
    hr = Math.atan2(b, a);
    h = hr * 360 / 2 / Math.PI;
    if (h < 0) {
        h += 360;
    }
    c = Math.sqrt(a * a + b * b);
    return [
        l,
        c,
        h
    ];
};
convert.lch.lab = function (lch) {
    var l = lch[0];
    var c = lch[1];
    var h = lch[2];
    var a;
    var b;
    var hr;
    hr = h / 360 * 2 * Math.PI;
    a = c * Math.cos(hr);
    b = c * Math.sin(hr);
    return [
        l,
        a,
        b
    ];
};
convert.rgb.ansi16 = function (args) {
    var r = args[0];
    var g = args[1];
    var b = args[2];
    var value = 1 in arguments ? arguments[1] : convert.rgb.hsv(args)[2];
    value = Math.round(value / 50);
    if (value === 0) {
        return 30;
    }
    var ansi = 30 + (Math.round(b / 255) << 2 | Math.round(g / 255) << 1 | Math.round(r / 255));
    if (value === 2) {
        ansi += 60;
    }
    return ansi;
};
convert.hsv.ansi16 = function (args) {
    return convert.rgb.ansi16(convert.hsv.rgb(args), args[2]);
};
convert.rgb.ansi256 = function (args) {
    var r = args[0];
    var g = args[1];
    var b = args[2];
    if (r === g && g === b) {
        if (r < 8) {
            return 16;
        }
        if (r > 248) {
            return 231;
        }
        return Math.round((r - 8) / 247 * 24) + 232;
    }
    var ansi = 16 + 36 * Math.round(r / 255 * 5) + 6 * Math.round(g / 255 * 5) + Math.round(b / 255 * 5);
    return ansi;
};
convert.ansi16.rgb = function (args) {
    var color = args % 10;
    if (color === 0 || color === 7) {
        if (args > 50) {
            color += 3.5;
        }
        color = color / 10.5 * 255;
        return [
            color,
            color,
            color
        ];
    }
    var mult = (~~(args > 50) + 1) * 0.5;
    var r = (color & 1) * mult * 255;
    var g = (color >> 1 & 1) * mult * 255;
    var b = (color >> 2 & 1) * mult * 255;
    return [
        r,
        g,
        b
    ];
};
convert.ansi256.rgb = function (args) {
    if (args >= 232) {
        var c = (args - 232) * 10 + 8;
        return [
            c,
            c,
            c
        ];
    }
    args -= 16;
    var rem;
    var r = Math.floor(args / 36) / 5 * 255;
    var g = Math.floor((rem = args % 36) / 6) / 5 * 255;
    var b = rem % 6 / 5 * 255;
    return [
        r,
        g,
        b
    ];
};
convert.rgb.hex = function (args) {
    var integer = ((Math.round(args[0]) & 255) << 16) + ((Math.round(args[1]) & 255) << 8) + (Math.round(args[2]) & 255);
    var string = integer.toString(16).toUpperCase();
    return '000000'.substring(string.length) + string;
};
convert.hex.rgb = function (args) {
    var match = args.toString(16).match(/[a-f0-9]{6}|[a-f0-9]{3}/i);
    if (!match) {
        return [
            0,
            0,
            0
        ];
    }
    var colorString = match[0];
    if (match[0].length === 3) {
        colorString = colorString.split('').map(function (char) {
            return char + char;
        }).join('');
    }
    var integer = parseInt(colorString, 16);
    var r = integer >> 16 & 255;
    var g = integer >> 8 & 255;
    var b = integer & 255;
    return [
        r,
        g,
        b
    ];
};
convert.rgb.hcg = function (rgb) {
    var r = rgb[0] / 255;
    var g = rgb[1] / 255;
    var b = rgb[2] / 255;
    var max = Math.max(Math.max(r, g), b);
    var min = Math.min(Math.min(r, g), b);
    var chroma = max - min;
    var grayscale;
    var hue;
    if (chroma < 1) {
        grayscale = min / (1 - chroma);
    } else {
        grayscale = 0;
    }
    if (chroma <= 0) {
        hue = 0;
    } else if (max === r) {
        hue = (g - b) / chroma % 6;
    } else if (max === g) {
        hue = 2 + (b - r) / chroma;
    } else {
        hue = 4 + (r - g) / chroma + 4;
    }
    hue /= 6;
    hue %= 1;
    return [
        hue * 360,
        chroma * 100,
        grayscale * 100
    ];
};
convert.hsl.hcg = function (hsl) {
    var s = hsl[1] / 100;
    var l = hsl[2] / 100;
    var c = 1;
    var f = 0;
    if (l < 0.5) {
        c = 2 * s * l;
    } else {
        c = 2 * s * (1 - l);
    }
    if (c < 1) {
        f = (l - 0.5 * c) / (1 - c);
    }
    return [
        hsl[0],
        c * 100,
        f * 100
    ];
};
convert.hsv.hcg = function (hsv) {
    var s = hsv[1] / 100;
    var v = hsv[2] / 100;
    var c = s * v;
    var f = 0;
    if (c < 1) {
        f = (v - c) / (1 - c);
    }
    return [
        hsv[0],
        c * 100,
        f * 100
    ];
};
convert.hcg.rgb = function (hcg) {
    var h = hcg[0] / 360;
    var c = hcg[1] / 100;
    var g = hcg[2] / 100;
    if (c === 0) {
        return [
            g * 255,
            g * 255,
            g * 255
        ];
    }
    var pure = [
        0,
        0,
        0
    ];
    var hi = h % 1 * 6;
    var v = hi % 1;
    var w = 1 - v;
    var mg = 0;
    switch (Math.floor(hi)) {
    case 0:
        pure[0] = 1;
        pure[1] = v;
        pure[2] = 0;
        break;
    case 1:
        pure[0] = w;
        pure[1] = 1;
        pure[2] = 0;
        break;
    case 2:
        pure[0] = 0;
        pure[1] = 1;
        pure[2] = v;
        break;
    case 3:
        pure[0] = 0;
        pure[1] = w;
        pure[2] = 1;
        break;
    case 4:
        pure[0] = v;
        pure[1] = 0;
        pure[2] = 1;
        break;
    default:
        pure[0] = 1;
        pure[1] = 0;
        pure[2] = w;
    }
    mg = (1 - c) * g;
    return [
        (c * pure[0] + mg) * 255,
        (c * pure[1] + mg) * 255,
        (c * pure[2] + mg) * 255
    ];
};
convert.hcg.hsv = function (hcg) {
    var c = hcg[1] / 100;
    var g = hcg[2] / 100;
    var v = c + g * (1 - c);
    var f = 0;
    if (v > 0) {
        f = c / v;
    }
    return [
        hcg[0],
        f * 100,
        v * 100
    ];
};
convert.hcg.hsl = function (hcg) {
    var c = hcg[1] / 100;
    var g = hcg[2] / 100;
    var l = g * (1 - c) + 0.5 * c;
    var s = 0;
    if (l > 0 && l < 0.5) {
        s = c / (2 * l);
    } else if (l >= 0.5 && l < 1) {
        s = c / (2 * (1 - l));
    }
    return [
        hcg[0],
        s * 100,
        l * 100
    ];
};
convert.hcg.hwb = function (hcg) {
    var c = hcg[1] / 100;
    var g = hcg[2] / 100;
    var v = c + g * (1 - c);
    return [
        hcg[0],
        (v - c) * 100,
        (1 - v) * 100
    ];
};
convert.hwb.hcg = function (hwb) {
    var w = hwb[1] / 100;
    var b = hwb[2] / 100;
    var v = 1 - b;
    var c = v - w;
    var g = 0;
    if (c < 1) {
        g = (v - c) / (1 - c);
    }
    return [
        hwb[0],
        c * 100,
        g * 100
    ];
};
convert.apple.rgb = function (apple) {
    return [
        apple[0] / 65535 * 255,
        apple[1] / 65535 * 255,
        apple[2] / 65535 * 255
    ];
};
convert.rgb.apple = function (rgb) {
    return [
        rgb[0] / 255 * 65535,
        rgb[1] / 255 * 65535,
        rgb[2] / 255 * 65535
    ];
};
convert.gray.rgb = function (args) {
    return [
        args[0] / 100 * 255,
        args[0] / 100 * 255,
        args[0] / 100 * 255
    ];
};
convert.gray.hsl = convert.gray.hsv = function (args) {
    return [
        0,
        0,
        args[0]
    ];
};
convert.gray.hwb = function (gray) {
    return [
        0,
        100,
        gray[0]
    ];
};
convert.gray.cmyk = function (gray) {
    return [
        0,
        0,
        0,
        gray[0]
    ];
};
convert.gray.lab = function (gray) {
    return [
        gray[0],
        0,
        0
    ];
};
convert.gray.hex = function (gray) {
    var val = Math.round(gray[0] / 100 * 255) & 255;
    var integer = (val << 16) + (val << 8) + val;
    var string = integer.toString(16).toUpperCase();
    return '000000'.substring(string.length) + string;
};
convert.rgb.gray = function (rgb) {
    var val = (rgb[0] + rgb[1] + rgb[2]) / 3;
    return [val / 255 * 100];
};
},{"color-name":26}],24:[function(require,module,exports){
var conversions = require('./conversions');
var route = require('./route');
var convert = {};
var models = Object.keys(conversions);
function wrapRaw(fn) {
    var wrappedFn = function (args) {
        if (args === undefined || args === null) {
            return args;
        }
        if (arguments.length > 1) {
            args = Array.prototype.slice.call(arguments);
        }
        return fn(args);
    };
    if ('conversion' in fn) {
        wrappedFn.conversion = fn.conversion;
    }
    return wrappedFn;
}
function wrapRounded(fn) {
    var wrappedFn = function (args) {
        if (args === undefined || args === null) {
            return args;
        }
        if (arguments.length > 1) {
            args = Array.prototype.slice.call(arguments);
        }
        var result = fn(args);
        if (typeof result === 'object') {
            for (var len = result.length, i = 0; i < len; i++) {
                result[i] = Math.round(result[i]);
            }
        }
        return result;
    };
    if ('conversion' in fn) {
        wrappedFn.conversion = fn.conversion;
    }
    return wrappedFn;
}
models.forEach(function (fromModel) {
    convert[fromModel] = {};
    Object.defineProperty(convert[fromModel], 'channels', { value: conversions[fromModel].channels });
    Object.defineProperty(convert[fromModel], 'labels', { value: conversions[fromModel].labels });
    var routes = route(fromModel);
    var routeModels = Object.keys(routes);
    routeModels.forEach(function (toModel) {
        var fn = routes[toModel];
        convert[fromModel][toModel] = wrapRounded(fn);
        convert[fromModel][toModel].raw = wrapRaw(fn);
    });
});
module.exports = convert;
},{"./conversions":23,"./route":25}],25:[function(require,module,exports){
var conversions = require('./conversions');
function buildGraph() {
    var graph = {};
    var models = Object.keys(conversions);
    for (var len = models.length, i = 0; i < len; i++) {
        graph[models[i]] = {
            distance: -1,
            parent: null
        };
    }
    return graph;
}
function deriveBFS(fromModel) {
    var graph = buildGraph();
    var queue = [fromModel];
    graph[fromModel].distance = 0;
    while (queue.length) {
        var current = queue.pop();
        var adjacents = Object.keys(conversions[current]);
        for (var len = adjacents.length, i = 0; i < len; i++) {
            var adjacent = adjacents[i];
            var node = graph[adjacent];
            if (node.distance === -1) {
                node.distance = graph[current].distance + 1;
                node.parent = current;
                queue.unshift(adjacent);
            }
        }
    }
    return graph;
}
function link(from, to) {
    return function (args) {
        return to(from(args));
    };
}
function wrapConversion(toModel, graph) {
    var path = [
        graph[toModel].parent,
        toModel
    ];
    var fn = conversions[graph[toModel].parent][toModel];
    var cur = graph[toModel].parent;
    while (graph[cur].parent) {
        path.unshift(graph[cur].parent);
        fn = link(conversions[graph[cur].parent][cur], fn);
        cur = graph[cur].parent;
    }
    fn.conversion = path;
    return fn;
}
module.exports = function (fromModel) {
    var graph = deriveBFS(fromModel);
    var conversion = {};
    var models = Object.keys(graph);
    for (var len = models.length, i = 0; i < len; i++) {
        var toModel = models[i];
        var node = graph[toModel];
        if (node.parent === null) {
            continue;
        }
        conversion[toModel] = wrapConversion(toModel, graph);
    }
    return conversion;
};
},{"./conversions":23}],26:[function(require,module,exports){
'use strict';
module.exports = {
    'aliceblue': [
        240,
        248,
        255
    ],
    'antiquewhite': [
        250,
        235,
        215
    ],
    'aqua': [
        0,
        255,
        255
    ],
    'aquamarine': [
        127,
        255,
        212
    ],
    'azure': [
        240,
        255,
        255
    ],
    'beige': [
        245,
        245,
        220
    ],
    'bisque': [
        255,
        228,
        196
    ],
    'black': [
        0,
        0,
        0
    ],
    'blanchedalmond': [
        255,
        235,
        205
    ],
    'blue': [
        0,
        0,
        255
    ],
    'blueviolet': [
        138,
        43,
        226
    ],
    'brown': [
        165,
        42,
        42
    ],
    'burlywood': [
        222,
        184,
        135
    ],
    'cadetblue': [
        95,
        158,
        160
    ],
    'chartreuse': [
        127,
        255,
        0
    ],
    'chocolate': [
        210,
        105,
        30
    ],
    'coral': [
        255,
        127,
        80
    ],
    'cornflowerblue': [
        100,
        149,
        237
    ],
    'cornsilk': [
        255,
        248,
        220
    ],
    'crimson': [
        220,
        20,
        60
    ],
    'cyan': [
        0,
        255,
        255
    ],
    'darkblue': [
        0,
        0,
        139
    ],
    'darkcyan': [
        0,
        139,
        139
    ],
    'darkgoldenrod': [
        184,
        134,
        11
    ],
    'darkgray': [
        169,
        169,
        169
    ],
    'darkgreen': [
        0,
        100,
        0
    ],
    'darkgrey': [
        169,
        169,
        169
    ],
    'darkkhaki': [
        189,
        183,
        107
    ],
    'darkmagenta': [
        139,
        0,
        139
    ],
    'darkolivegreen': [
        85,
        107,
        47
    ],
    'darkorange': [
        255,
        140,
        0
    ],
    'darkorchid': [
        153,
        50,
        204
    ],
    'darkred': [
        139,
        0,
        0
    ],
    'darksalmon': [
        233,
        150,
        122
    ],
    'darkseagreen': [
        143,
        188,
        143
    ],
    'darkslateblue': [
        72,
        61,
        139
    ],
    'darkslategray': [
        47,
        79,
        79
    ],
    'darkslategrey': [
        47,
        79,
        79
    ],
    'darkturquoise': [
        0,
        206,
        209
    ],
    'darkviolet': [
        148,
        0,
        211
    ],
    'deeppink': [
        255,
        20,
        147
    ],
    'deepskyblue': [
        0,
        191,
        255
    ],
    'dimgray': [
        105,
        105,
        105
    ],
    'dimgrey': [
        105,
        105,
        105
    ],
    'dodgerblue': [
        30,
        144,
        255
    ],
    'firebrick': [
        178,
        34,
        34
    ],
    'floralwhite': [
        255,
        250,
        240
    ],
    'forestgreen': [
        34,
        139,
        34
    ],
    'fuchsia': [
        255,
        0,
        255
    ],
    'gainsboro': [
        220,
        220,
        220
    ],
    'ghostwhite': [
        248,
        248,
        255
    ],
    'gold': [
        255,
        215,
        0
    ],
    'goldenrod': [
        218,
        165,
        32
    ],
    'gray': [
        128,
        128,
        128
    ],
    'green': [
        0,
        128,
        0
    ],
    'greenyellow': [
        173,
        255,
        47
    ],
    'grey': [
        128,
        128,
        128
    ],
    'honeydew': [
        240,
        255,
        240
    ],
    'hotpink': [
        255,
        105,
        180
    ],
    'indianred': [
        205,
        92,
        92
    ],
    'indigo': [
        75,
        0,
        130
    ],
    'ivory': [
        255,
        255,
        240
    ],
    'khaki': [
        240,
        230,
        140
    ],
    'lavender': [
        230,
        230,
        250
    ],
    'lavenderblush': [
        255,
        240,
        245
    ],
    'lawngreen': [
        124,
        252,
        0
    ],
    'lemonchiffon': [
        255,
        250,
        205
    ],
    'lightblue': [
        173,
        216,
        230
    ],
    'lightcoral': [
        240,
        128,
        128
    ],
    'lightcyan': [
        224,
        255,
        255
    ],
    'lightgoldenrodyellow': [
        250,
        250,
        210
    ],
    'lightgray': [
        211,
        211,
        211
    ],
    'lightgreen': [
        144,
        238,
        144
    ],
    'lightgrey': [
        211,
        211,
        211
    ],
    'lightpink': [
        255,
        182,
        193
    ],
    'lightsalmon': [
        255,
        160,
        122
    ],
    'lightseagreen': [
        32,
        178,
        170
    ],
    'lightskyblue': [
        135,
        206,
        250
    ],
    'lightslategray': [
        119,
        136,
        153
    ],
    'lightslategrey': [
        119,
        136,
        153
    ],
    'lightsteelblue': [
        176,
        196,
        222
    ],
    'lightyellow': [
        255,
        255,
        224
    ],
    'lime': [
        0,
        255,
        0
    ],
    'limegreen': [
        50,
        205,
        50
    ],
    'linen': [
        250,
        240,
        230
    ],
    'magenta': [
        255,
        0,
        255
    ],
    'maroon': [
        128,
        0,
        0
    ],
    'mediumaquamarine': [
        102,
        205,
        170
    ],
    'mediumblue': [
        0,
        0,
        205
    ],
    'mediumorchid': [
        186,
        85,
        211
    ],
    'mediumpurple': [
        147,
        112,
        219
    ],
    'mediumseagreen': [
        60,
        179,
        113
    ],
    'mediumslateblue': [
        123,
        104,
        238
    ],
    'mediumspringgreen': [
        0,
        250,
        154
    ],
    'mediumturquoise': [
        72,
        209,
        204
    ],
    'mediumvioletred': [
        199,
        21,
        133
    ],
    'midnightblue': [
        25,
        25,
        112
    ],
    'mintcream': [
        245,
        255,
        250
    ],
    'mistyrose': [
        255,
        228,
        225
    ],
    'moccasin': [
        255,
        228,
        181
    ],
    'navajowhite': [
        255,
        222,
        173
    ],
    'navy': [
        0,
        0,
        128
    ],
    'oldlace': [
        253,
        245,
        230
    ],
    'olive': [
        128,
        128,
        0
    ],
    'olivedrab': [
        107,
        142,
        35
    ],
    'orange': [
        255,
        165,
        0
    ],
    'orangered': [
        255,
        69,
        0
    ],
    'orchid': [
        218,
        112,
        214
    ],
    'palegoldenrod': [
        238,
        232,
        170
    ],
    'palegreen': [
        152,
        251,
        152
    ],
    'paleturquoise': [
        175,
        238,
        238
    ],
    'palevioletred': [
        219,
        112,
        147
    ],
    'papayawhip': [
        255,
        239,
        213
    ],
    'peachpuff': [
        255,
        218,
        185
    ],
    'peru': [
        205,
        133,
        63
    ],
    'pink': [
        255,
        192,
        203
    ],
    'plum': [
        221,
        160,
        221
    ],
    'powderblue': [
        176,
        224,
        230
    ],
    'purple': [
        128,
        0,
        128
    ],
    'rebeccapurple': [
        102,
        51,
        153
    ],
    'red': [
        255,
        0,
        0
    ],
    'rosybrown': [
        188,
        143,
        143
    ],
    'royalblue': [
        65,
        105,
        225
    ],
    'saddlebrown': [
        139,
        69,
        19
    ],
    'salmon': [
        250,
        128,
        114
    ],
    'sandybrown': [
        244,
        164,
        96
    ],
    'seagreen': [
        46,
        139,
        87
    ],
    'seashell': [
        255,
        245,
        238
    ],
    'sienna': [
        160,
        82,
        45
    ],
    'silver': [
        192,
        192,
        192
    ],
    'skyblue': [
        135,
        206,
        235
    ],
    'slateblue': [
        106,
        90,
        205
    ],
    'slategray': [
        112,
        128,
        144
    ],
    'slategrey': [
        112,
        128,
        144
    ],
    'snow': [
        255,
        250,
        250
    ],
    'springgreen': [
        0,
        255,
        127
    ],
    'steelblue': [
        70,
        130,
        180
    ],
    'tan': [
        210,
        180,
        140
    ],
    'teal': [
        0,
        128,
        128
    ],
    'thistle': [
        216,
        191,
        216
    ],
    'tomato': [
        255,
        99,
        71
    ],
    'turquoise': [
        64,
        224,
        208
    ],
    'violet': [
        238,
        130,
        238
    ],
    'wheat': [
        245,
        222,
        179
    ],
    'white': [
        255,
        255,
        255
    ],
    'whitesmoke': [
        245,
        245,
        245
    ],
    'yellow': [
        255,
        255,
        0
    ],
    'yellowgreen': [
        154,
        205,
        50
    ]
};
},{}],27:[function(require,module,exports){
var colorNames = require('color-name');
var swizzle = require('simple-swizzle');
var reverseNames = {};
for (var name in colorNames) {
    if (colorNames.hasOwnProperty(name)) {
        reverseNames[colorNames[name]] = name;
    }
}
var cs = module.exports = { to: {} };
cs.get = function (string) {
    var prefix = string.substring(0, 3).toLowerCase();
    var val;
    var model;
    switch (prefix) {
    case 'hsl':
        val = cs.get.hsl(string);
        model = 'hsl';
        break;
    case 'hwb':
        val = cs.get.hwb(string);
        model = 'hwb';
        break;
    default:
        val = cs.get.rgb(string);
        model = 'rgb';
        break;
    }
    if (!val) {
        return null;
    }
    return {
        model: model,
        value: val
    };
};
cs.get.rgb = function (string) {
    if (!string) {
        return null;
    }
    var abbr = /^#([a-f0-9]{3,4})$/i;
    var hex = /^#([a-f0-9]{6})([a-f0-9]{2})?$/i;
    var rgba = /^rgba?\(\s*([+-]?\d+)\s*,\s*([+-]?\d+)\s*,\s*([+-]?\d+)\s*(?:,\s*([+-]?[\d\.]+)\s*)?\)$/;
    var per = /^rgba?\(\s*([+-]?[\d\.]+)\%\s*,\s*([+-]?[\d\.]+)\%\s*,\s*([+-]?[\d\.]+)\%\s*(?:,\s*([+-]?[\d\.]+)\s*)?\)$/;
    var keyword = /(\D+)/;
    var rgb = [
        0,
        0,
        0,
        1
    ];
    var match;
    var i;
    var hexAlpha;
    if (match = string.match(hex)) {
        hexAlpha = match[2];
        match = match[1];
        for (i = 0; i < 3; i++) {
            var i2 = i * 2;
            rgb[i] = parseInt(match.slice(i2, i2 + 2), 16);
        }
        if (hexAlpha) {
            rgb[3] = Math.round(parseInt(hexAlpha, 16) / 255 * 100) / 100;
        }
    } else if (match = string.match(abbr)) {
        match = match[1];
        hexAlpha = match[3];
        for (i = 0; i < 3; i++) {
            rgb[i] = parseInt(match[i] + match[i], 16);
        }
        if (hexAlpha) {
            rgb[3] = Math.round(parseInt(hexAlpha + hexAlpha, 16) / 255 * 100) / 100;
        }
    } else if (match = string.match(rgba)) {
        for (i = 0; i < 3; i++) {
            rgb[i] = parseInt(match[i + 1], 0);
        }
        if (match[4]) {
            rgb[3] = parseFloat(match[4]);
        }
    } else if (match = string.match(per)) {
        for (i = 0; i < 3; i++) {
            rgb[i] = Math.round(parseFloat(match[i + 1]) * 2.55);
        }
        if (match[4]) {
            rgb[3] = parseFloat(match[4]);
        }
    } else if (match = string.match(keyword)) {
        if (match[1] === 'transparent') {
            return [
                0,
                0,
                0,
                0
            ];
        }
        rgb = colorNames[match[1]];
        if (!rgb) {
            return null;
        }
        rgb[3] = 1;
        return rgb;
    } else {
        return null;
    }
    for (i = 0; i < 3; i++) {
        rgb[i] = clamp(rgb[i], 0, 255);
    }
    rgb[3] = clamp(rgb[3], 0, 1);
    return rgb;
};
cs.get.hsl = function (string) {
    if (!string) {
        return null;
    }
    var hsl = /^hsla?\(\s*([+-]?\d*[\.]?\d+)(?:deg)?\s*,\s*([+-]?[\d\.]+)%\s*,\s*([+-]?[\d\.]+)%\s*(?:,\s*([+-]?[\d\.]+)\s*)?\)$/;
    var match = string.match(hsl);
    if (match) {
        var alpha = parseFloat(match[4]);
        var h = (parseFloat(match[1]) % 360 + 360) % 360;
        var s = clamp(parseFloat(match[2]), 0, 100);
        var l = clamp(parseFloat(match[3]), 0, 100);
        var a = clamp(isNaN(alpha) ? 1 : alpha, 0, 1);
        return [
            h,
            s,
            l,
            a
        ];
    }
    return null;
};
cs.get.hwb = function (string) {
    if (!string) {
        return null;
    }
    var hwb = /^hwb\(\s*([+-]?\d*[\.]?\d+)(?:deg)?\s*,\s*([+-]?[\d\.]+)%\s*,\s*([+-]?[\d\.]+)%\s*(?:,\s*([+-]?[\d\.]+)\s*)?\)$/;
    var match = string.match(hwb);
    if (match) {
        var alpha = parseFloat(match[4]);
        var h = (parseFloat(match[1]) % 360 + 360) % 360;
        var w = clamp(parseFloat(match[2]), 0, 100);
        var b = clamp(parseFloat(match[3]), 0, 100);
        var a = clamp(isNaN(alpha) ? 1 : alpha, 0, 1);
        return [
            h,
            w,
            b,
            a
        ];
    }
    return null;
};
cs.to.hex = function () {
    var rgba = swizzle(arguments);
    return '#' + hexDouble(rgba[0]) + hexDouble(rgba[1]) + hexDouble(rgba[2]) + (rgba[3] < 1 ? hexDouble(Math.round(rgba[3] * 255)) : '');
};
cs.to.rgb = function () {
    var rgba = swizzle(arguments);
    return rgba.length < 4 || rgba[3] === 1 ? 'rgb(' + Math.round(rgba[0]) + ', ' + Math.round(rgba[1]) + ', ' + Math.round(rgba[2]) + ')' : 'rgba(' + Math.round(rgba[0]) + ', ' + Math.round(rgba[1]) + ', ' + Math.round(rgba[2]) + ', ' + rgba[3] + ')';
};
cs.to.rgb.percent = function () {
    var rgba = swizzle(arguments);
    var r = Math.round(rgba[0] / 255 * 100);
    var g = Math.round(rgba[1] / 255 * 100);
    var b = Math.round(rgba[2] / 255 * 100);
    return rgba.length < 4 || rgba[3] === 1 ? 'rgb(' + r + '%, ' + g + '%, ' + b + '%)' : 'rgba(' + r + '%, ' + g + '%, ' + b + '%, ' + rgba[3] + ')';
};
cs.to.hsl = function () {
    var hsla = swizzle(arguments);
    return hsla.length < 4 || hsla[3] === 1 ? 'hsl(' + hsla[0] + ', ' + hsla[1] + '%, ' + hsla[2] + '%)' : 'hsla(' + hsla[0] + ', ' + hsla[1] + '%, ' + hsla[2] + '%, ' + hsla[3] + ')';
};
cs.to.hwb = function () {
    var hwba = swizzle(arguments);
    var a = '';
    if (hwba.length >= 4 && hwba[3] !== 1) {
        a = ', ' + hwba[3];
    }
    return 'hwb(' + hwba[0] + ', ' + hwba[1] + '%, ' + hwba[2] + '%' + a + ')';
};
cs.to.keyword = function (rgb) {
    return reverseNames[rgb.slice(0, 3)];
};
function clamp(num, min, max) {
    return Math.min(Math.max(min, num), max);
}
function hexDouble(num) {
    var str = num.toString(16).toUpperCase();
    return str.length < 2 ? '0' + str : str;
}
},{"color-name":26,"simple-swizzle":29}],28:[function(require,module,exports){
'use strict';
module.exports = function isArrayish(obj) {
    if (!obj || typeof obj === 'string') {
        return false;
    }
    return obj instanceof Array || Array.isArray(obj) || obj.length >= 0 && (obj.splice instanceof Function || Object.getOwnPropertyDescriptor(obj, obj.length - 1) && obj.constructor.name !== 'String');
};
},{}],29:[function(require,module,exports){
'use strict';
var isArrayish = require('is-arrayish');
var concat = Array.prototype.concat;
var slice = Array.prototype.slice;
var swizzle = module.exports = function swizzle(args) {
    var results = [];
    for (var i = 0, len = args.length; i < len; i++) {
        var arg = args[i];
        if (isArrayish(arg)) {
            results = concat.call(results, slice.call(arg));
        } else {
            results.push(arg);
        }
    }
    return results;
};
swizzle.wrap = function (fn) {
    return function () {
        return fn(swizzle(arguments));
    };
};
},{"is-arrayish":28}],30:[function(require,module,exports){
(function (global){
var topLevel = typeof global !== 'undefined' ? global : typeof window !== 'undefined' ? window : {};
var minDoc = require('min-document');
var doccy;
if (typeof document !== 'undefined') {
    doccy = document;
} else {
    doccy = topLevel['__GLOBAL_DOCUMENT_CACHE@4'];
    if (!doccy) {
        doccy = topLevel['__GLOBAL_DOCUMENT_CACHE@4'] = minDoc;
    }
}
module.exports = doccy;
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"min-document":1}],31:[function(require,module,exports){
(function (global){
var win;
if (typeof window !== 'undefined') {
    win = window;
} else if (typeof global !== 'undefined') {
    win = global;
} else if (typeof self !== 'undefined') {
    win = self;
} else {
    win = {};
}
module.exports = win;
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],32:[function(require,module,exports){
var isArray = require('./isArray');
function castArray() {
    if (!arguments.length) {
        return [];
    }
    var value = arguments[0];
    return isArray(value) ? value : [value];
}
module.exports = castArray;
},{"./isArray":33}],33:[function(require,module,exports){
var isArray = Array.isArray;
module.exports = isArray;
},{}],34:[function(require,module,exports){
(function (root) {
    var setTimeoutFunc = setTimeout;
    function noop() {
    }
    function bind(fn, thisArg) {
        return function () {
            fn.apply(thisArg, arguments);
        };
    }
    function Promise(fn) {
        if (typeof this !== 'object')
            throw new TypeError('Promises must be constructed via new');
        if (typeof fn !== 'function')
            throw new TypeError('not a function');
        this._state = 0;
        this._handled = false;
        this._value = undefined;
        this._deferreds = [];
        doResolve(fn, this);
    }
    function handle(self, deferred) {
        while (self._state === 3) {
            self = self._value;
        }
        if (self._state === 0) {
            self._deferreds.push(deferred);
            return;
        }
        self._handled = true;
        Promise._immediateFn(function () {
            var cb = self._state === 1 ? deferred.onFulfilled : deferred.onRejected;
            if (cb === null) {
                (self._state === 1 ? resolve : reject)(deferred.promise, self._value);
                return;
            }
            var ret;
            try {
                ret = cb(self._value);
            } catch (e) {
                reject(deferred.promise, e);
                return;
            }
            resolve(deferred.promise, ret);
        });
    }
    function resolve(self, newValue) {
        try {
            if (newValue === self)
                throw new TypeError('A promise cannot be resolved with itself.');
            if (newValue && (typeof newValue === 'object' || typeof newValue === 'function')) {
                var then = newValue.then;
                if (newValue instanceof Promise) {
                    self._state = 3;
                    self._value = newValue;
                    finale(self);
                    return;
                } else if (typeof then === 'function') {
                    doResolve(bind(then, newValue), self);
                    return;
                }
            }
            self._state = 1;
            self._value = newValue;
            finale(self);
        } catch (e) {
            reject(self, e);
        }
    }
    function reject(self, newValue) {
        self._state = 2;
        self._value = newValue;
        finale(self);
    }
    function finale(self) {
        if (self._state === 2 && self._deferreds.length === 0) {
            Promise._immediateFn(function () {
                if (!self._handled) {
                    Promise._unhandledRejectionFn(self._value);
                }
            });
        }
        for (var i = 0, len = self._deferreds.length; i < len; i++) {
            handle(self, self._deferreds[i]);
        }
        self._deferreds = null;
    }
    function Handler(onFulfilled, onRejected, promise) {
        this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
        this.onRejected = typeof onRejected === 'function' ? onRejected : null;
        this.promise = promise;
    }
    function doResolve(fn, self) {
        var done = false;
        try {
            fn(function (value) {
                if (done)
                    return;
                done = true;
                resolve(self, value);
            }, function (reason) {
                if (done)
                    return;
                done = true;
                reject(self, reason);
            });
        } catch (ex) {
            if (done)
                return;
            done = true;
            reject(self, ex);
        }
    }
    Promise.prototype['catch'] = function (onRejected) {
        return this.then(null, onRejected);
    };
    Promise.prototype.then = function (onFulfilled, onRejected) {
        var prom = new this.constructor(noop);
        handle(this, new Handler(onFulfilled, onRejected, prom));
        return prom;
    };
    Promise.all = function (arr) {
        var args = Array.prototype.slice.call(arr);
        return new Promise(function (resolve, reject) {
            if (args.length === 0)
                return resolve([]);
            var remaining = args.length;
            function res(i, val) {
                try {
                    if (val && (typeof val === 'object' || typeof val === 'function')) {
                        var then = val.then;
                        if (typeof then === 'function') {
                            then.call(val, function (val) {
                                res(i, val);
                            }, reject);
                            return;
                        }
                    }
                    args[i] = val;
                    if (--remaining === 0) {
                        resolve(args);
                    }
                } catch (ex) {
                    reject(ex);
                }
            }
            for (var i = 0; i < args.length; i++) {
                res(i, args[i]);
            }
        });
    };
    Promise.resolve = function (value) {
        if (value && typeof value === 'object' && value.constructor === Promise) {
            return value;
        }
        return new Promise(function (resolve) {
            resolve(value);
        });
    };
    Promise.reject = function (value) {
        return new Promise(function (resolve, reject) {
            reject(value);
        });
    };
    Promise.race = function (values) {
        return new Promise(function (resolve, reject) {
            for (var i = 0, len = values.length; i < len; i++) {
                values[i].then(resolve, reject);
            }
        });
    };
    Promise._immediateFn = typeof setImmediate === 'function' && function (fn) {
        setImmediate(fn);
    } || function (fn) {
        setTimeoutFunc(fn, 0);
    };
    Promise._unhandledRejectionFn = function _unhandledRejectionFn(err) {
        if (typeof console !== 'undefined' && console) {
            console.warn('Possible Unhandled Promise Rejection:', err);
        }
    };
    Promise._setImmediateFn = function _setImmediateFn(fn) {
        Promise._immediateFn = fn;
    };
    Promise._setUnhandledRejectionFn = function _setUnhandledRejectionFn(fn) {
        Promise._unhandledRejectionFn = fn;
    };
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = Promise;
    } else if (!root.Promise) {
        root.Promise = Promise;
    }
}(this));
},{}],35:[function(require,module,exports){
'use strict';
var errcode = require('err-code');
var retry = require('retry');
var hasOwn = Object.prototype.hasOwnProperty;
function isRetryError(err) {
    return err && err.code === 'EPROMISERETRY' && hasOwn.call(err, 'retried');
}
function promiseRetry(fn, options) {
    var temp;
    var operation;
    if (typeof fn === 'object' && typeof options === 'function') {
        temp = options;
        options = fn;
        fn = temp;
    }
    operation = retry.operation(options);
    return new Promise(function (resolve, reject) {
        operation.attempt(function (number) {
            Promise.resolve().then(function () {
                return fn(function (err) {
                    if (isRetryError(err)) {
                        err = err.retried;
                    }
                    throw errcode('Retrying', 'EPROMISERETRY', { retried: err });
                }, number);
            }).then(resolve, function (err) {
                if (isRetryError(err)) {
                    err = err.retried;
                    if (operation.retry(err || new Error())) {
                        return;
                    }
                }
                reject(err);
            });
        });
    });
}
module.exports = promiseRetry;
},{"err-code":36,"retry":37}],36:[function(require,module,exports){
'use strict';
function createError(msg, code, props) {
    var err = msg instanceof Error ? msg : new Error(msg);
    var key;
    if (typeof code === 'object') {
        props = code;
    } else if (code != null) {
        err.code = code;
    }
    if (props) {
        for (key in props) {
            err[key] = props[key];
        }
    }
    return err;
}
module.exports = createError;
},{}],37:[function(require,module,exports){
module.exports = require('./lib/retry');
},{"./lib/retry":38}],38:[function(require,module,exports){
var RetryOperation = require('./retry_operation');
exports.operation = function (options) {
    var timeouts = exports.timeouts(options);
    return new RetryOperation(timeouts, {
        forever: options && options.forever,
        unref: options && options.unref
    });
};
exports.timeouts = function (options) {
    if (options instanceof Array) {
        return [].concat(options);
    }
    var opts = {
        retries: 10,
        factor: 2,
        minTimeout: 1 * 1000,
        maxTimeout: Infinity,
        randomize: false
    };
    for (var key in options) {
        opts[key] = options[key];
    }
    if (opts.minTimeout > opts.maxTimeout) {
        throw new Error('minTimeout is greater than maxTimeout');
    }
    var timeouts = [];
    for (var i = 0; i < opts.retries; i++) {
        timeouts.push(this.createTimeout(i, opts));
    }
    if (options && options.forever && !timeouts.length) {
        timeouts.push(this.createTimeout(i, opts));
    }
    timeouts.sort(function (a, b) {
        return a - b;
    });
    return timeouts;
};
exports.createTimeout = function (attempt, opts) {
    var random = opts.randomize ? Math.random() + 1 : 1;
    var timeout = Math.round(random * opts.minTimeout * Math.pow(opts.factor, attempt));
    timeout = Math.min(timeout, opts.maxTimeout);
    return timeout;
};
exports.wrap = function (obj, options, methods) {
    if (options instanceof Array) {
        methods = options;
        options = null;
    }
    if (!methods) {
        methods = [];
        for (var key in obj) {
            if (typeof obj[key] === 'function') {
                methods.push(key);
            }
        }
    }
    for (var i = 0; i < methods.length; i++) {
        var method = methods[i];
        var original = obj[method];
        obj[method] = function retryWrapper() {
            var op = exports.operation(options);
            var args = Array.prototype.slice.call(arguments);
            var callback = args.pop();
            args.push(function (err) {
                if (op.retry(err)) {
                    return;
                }
                if (err) {
                    arguments[0] = op.mainError();
                }
                callback.apply(this, arguments);
            });
            op.attempt(function () {
                original.apply(obj, args);
            });
        };
        obj[method].options = options;
    }
};
},{"./retry_operation":39}],39:[function(require,module,exports){
function RetryOperation(timeouts, options) {
    if (typeof options === 'boolean') {
        options = { forever: options };
    }
    this._timeouts = timeouts;
    this._options = options || {};
    this._fn = null;
    this._errors = [];
    this._attempts = 1;
    this._operationTimeout = null;
    this._operationTimeoutCb = null;
    this._timeout = null;
    if (this._options.forever) {
        this._cachedTimeouts = this._timeouts.slice(0);
    }
}
module.exports = RetryOperation;
RetryOperation.prototype.stop = function () {
    if (this._timeout) {
        clearTimeout(this._timeout);
    }
    this._timeouts = [];
    this._cachedTimeouts = null;
};
RetryOperation.prototype.retry = function (err) {
    if (this._timeout) {
        clearTimeout(this._timeout);
    }
    if (!err) {
        return false;
    }
    this._errors.push(err);
    var timeout = this._timeouts.shift();
    if (timeout === undefined) {
        if (this._cachedTimeouts) {
            this._errors.splice(this._errors.length - 1, this._errors.length);
            this._timeouts = this._cachedTimeouts.slice(0);
            timeout = this._timeouts.shift();
        } else {
            return false;
        }
    }
    var self = this;
    var timer = setTimeout(function () {
        self._attempts++;
        if (self._operationTimeoutCb) {
            self._timeout = setTimeout(function () {
                self._operationTimeoutCb(self._attempts);
            }, self._operationTimeout);
            if (this._options.unref) {
                self._timeout.unref();
            }
        }
        self._fn(self._attempts);
    }, timeout);
    if (this._options.unref) {
        timer.unref();
    }
    return true;
};
RetryOperation.prototype.attempt = function (fn, timeoutOps) {
    this._fn = fn;
    if (timeoutOps) {
        if (timeoutOps.timeout) {
            this._operationTimeout = timeoutOps.timeout;
        }
        if (timeoutOps.cb) {
            this._operationTimeoutCb = timeoutOps.cb;
        }
    }
    var self = this;
    if (this._operationTimeoutCb) {
        this._timeout = setTimeout(function () {
            self._operationTimeoutCb();
        }, self._operationTimeout);
    }
    this._fn(this._attempts);
};
RetryOperation.prototype.try = function (fn) {
    console.log('Using RetryOperation.try() is deprecated');
    this.attempt(fn);
};
RetryOperation.prototype.start = function (fn) {
    console.log('Using RetryOperation.start() is deprecated');
    this.attempt(fn);
};
RetryOperation.prototype.start = RetryOperation.prototype.try;
RetryOperation.prototype.errors = function () {
    return this._errors;
};
RetryOperation.prototype.attempts = function () {
    return this._attempts;
};
RetryOperation.prototype.mainError = function () {
    if (this._errors.length === 0) {
        return null;
    }
    var counts = {};
    var mainError = null;
    var mainErrorCount = 0;
    for (var i = 0; i < this._errors.length; i++) {
        var error = this._errors[i];
        var message = error.message;
        var count = (counts[message] || 0) + 1;
        counts[message] = count;
        if (count >= mainErrorCount) {
            mainError = error;
            mainErrorCount = count;
        }
    }
    return mainError;
};
},{}],40:[function(require,module,exports){
'use strict';
var strictUriEncode = require('strict-uri-encode');
var objectAssign = require('object-assign');
function encoderForArrayFormat(opts) {
    switch (opts.arrayFormat) {
    case 'index':
        return function (key, value, index) {
            return value === null ? [
                encode(key, opts),
                '[',
                index,
                ']'
            ].join('') : [
                encode(key, opts),
                '[',
                encode(index, opts),
                ']=',
                encode(value, opts)
            ].join('');
        };
    case 'bracket':
        return function (key, value) {
            return value === null ? encode(key, opts) : [
                encode(key, opts),
                '[]=',
                encode(value, opts)
            ].join('');
        };
    default:
        return function (key, value) {
            return value === null ? encode(key, opts) : [
                encode(key, opts),
                '=',
                encode(value, opts)
            ].join('');
        };
    }
}
function parserForArrayFormat(opts) {
    var result;
    switch (opts.arrayFormat) {
    case 'index':
        return function (key, value, accumulator) {
            result = /\[(\d*)\]$/.exec(key);
            key = key.replace(/\[\d*\]$/, '');
            if (!result) {
                accumulator[key] = value;
                return;
            }
            if (accumulator[key] === undefined) {
                accumulator[key] = {};
            }
            accumulator[key][result[1]] = value;
        };
    case 'bracket':
        return function (key, value, accumulator) {
            result = /(\[\])$/.exec(key);
            key = key.replace(/\[\]$/, '');
            if (!result) {
                accumulator[key] = value;
                return;
            } else if (accumulator[key] === undefined) {
                accumulator[key] = [value];
                return;
            }
            accumulator[key] = [].concat(accumulator[key], value);
        };
    default:
        return function (key, value, accumulator) {
            if (accumulator[key] === undefined) {
                accumulator[key] = value;
                return;
            }
            accumulator[key] = [].concat(accumulator[key], value);
        };
    }
}
function encode(value, opts) {
    if (opts.encode) {
        return opts.strict ? strictUriEncode(value) : encodeURIComponent(value);
    }
    return value;
}
function keysSorter(input) {
    if (Array.isArray(input)) {
        return input.sort();
    } else if (typeof input === 'object') {
        return keysSorter(Object.keys(input)).sort(function (a, b) {
            return Number(a) - Number(b);
        }).map(function (key) {
            return input[key];
        });
    }
    return input;
}
exports.extract = function (str) {
    return str.split('?')[1] || '';
};
exports.parse = function (str, opts) {
    opts = objectAssign({ arrayFormat: 'none' }, opts);
    var formatter = parserForArrayFormat(opts);
    var ret = Object.create(null);
    if (typeof str !== 'string') {
        return ret;
    }
    str = str.trim().replace(/^(\?|#|&)/, '');
    if (!str) {
        return ret;
    }
    str.split('&').forEach(function (param) {
        var parts = param.replace(/\+/g, ' ').split('=');
        var key = parts.shift();
        var val = parts.length > 0 ? parts.join('=') : undefined;
        val = val === undefined ? null : decodeURIComponent(val);
        formatter(decodeURIComponent(key), val, ret);
    });
    return Object.keys(ret).sort().reduce(function (result, key) {
        var val = ret[key];
        if (Boolean(val) && typeof val === 'object' && !Array.isArray(val)) {
            result[key] = keysSorter(val);
        } else {
            result[key] = val;
        }
        return result;
    }, Object.create(null));
};
exports.stringify = function (obj, opts) {
    var defaults = {
        encode: true,
        strict: true,
        arrayFormat: 'none'
    };
    opts = objectAssign(defaults, opts);
    var formatter = encoderForArrayFormat(opts);
    return obj ? Object.keys(obj).sort().map(function (key) {
        var val = obj[key];
        if (val === undefined) {
            return '';
        }
        if (val === null) {
            return encode(key, opts);
        }
        if (Array.isArray(val)) {
            var result = [];
            val.slice().forEach(function (val2) {
                if (val2 === undefined) {
                    return;
                }
                result.push(formatter(key, val2, result.length));
            });
            return result.join('&');
        }
        return encode(key, opts) + '=' + encode(val, opts);
    }).filter(function (x) {
        return x.length > 0;
    }).join('&') : '';
};
},{"object-assign":41,"strict-uri-encode":42}],41:[function(require,module,exports){
'use strict';
var getOwnPropertySymbols = Object.getOwnPropertySymbols;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var propIsEnumerable = Object.prototype.propertyIsEnumerable;
function toObject(val) {
    if (val === null || val === undefined) {
        throw new TypeError('Object.assign cannot be called with null or undefined');
    }
    return Object(val);
}
function shouldUseNative() {
    try {
        if (!Object.assign) {
            return false;
        }
        var test1 = new String('abc');
        test1[5] = 'de';
        if (Object.getOwnPropertyNames(test1)[0] === '5') {
            return false;
        }
        var test2 = {};
        for (var i = 0; i < 10; i++) {
            test2['_' + String.fromCharCode(i)] = i;
        }
        var order2 = Object.getOwnPropertyNames(test2).map(function (n) {
            return test2[n];
        });
        if (order2.join('') !== '0123456789') {
            return false;
        }
        var test3 = {};
        'abcdefghijklmnopqrst'.split('').forEach(function (letter) {
            test3[letter] = letter;
        });
        if (Object.keys(Object.assign({}, test3)).join('') !== 'abcdefghijklmnopqrst') {
            return false;
        }
        return true;
    } catch (err) {
        return false;
    }
}
module.exports = shouldUseNative() ? Object.assign : function (target, source) {
    var from;
    var to = toObject(target);
    var symbols;
    for (var s = 1; s < arguments.length; s++) {
        from = Object(arguments[s]);
        for (var key in from) {
            if (hasOwnProperty.call(from, key)) {
                to[key] = from[key];
            }
        }
        if (getOwnPropertySymbols) {
            symbols = getOwnPropertySymbols(from);
            for (var i = 0; i < symbols.length; i++) {
                if (propIsEnumerable.call(from, symbols[i])) {
                    to[symbols[i]] = from[symbols[i]];
                }
            }
        }
    }
    return to;
};
},{}],42:[function(require,module,exports){
'use strict';
module.exports = function (str) {
    return encodeURIComponent(str).replace(/[!'()*]/g, function (c) {
        return '%' + c.charCodeAt(0).toString(16).toUpperCase();
    });
};
},{}],43:[function(require,module,exports){
var curry = require('ramda/src/curry');
var toString = require('ramda/src/toString');
var util = require('./internal/util');
function Either(left, right) {
    switch (arguments.length) {
    case 0:
        throw new TypeError('no arguments to Either');
    case 1:
        return function (right) {
            return right == null ? Either.Left(left) : Either.Right(right);
        };
    default:
        return right == null ? Either.Left(left) : Either.Right(right);
    }
}
Either.prototype['@@type'] = 'ramda-fantasy/Either';
Either.prototype.map = util.returnThis;
Either.of = Either.prototype.of = function (value) {
    return Either.Right(value);
};
Either.prototype.chain = util.returnThis;
Either.either = curry(function either(leftFn, rightFn, e) {
    if (e instanceof _Left) {
        return leftFn(e.value);
    } else if (e instanceof _Right) {
        return rightFn(e.value);
    } else {
        throw new TypeError('invalid type given to Either.either');
    }
});
Either.isLeft = function (x) {
    return x.isLeft;
};
Either.isRight = function (x) {
    return x.isRight;
};
function _Right(x) {
    this.value = x;
}
util.extend(_Right, Either);
_Right.prototype.isRight = true;
_Right.prototype.isLeft = false;
_Right.prototype.map = function (fn) {
    return new _Right(fn(this.value));
};
_Right.prototype.ap = function (that) {
    return that.map(this.value);
};
_Right.prototype.chain = function (f) {
    return f(this.value);
};
Either.chainRec = Either.prototype.chainRec = function (f, i) {
    var res, state = util.chainRecNext(i);
    while (state.isNext) {
        res = f(util.chainRecNext, util.chainRecDone, state.value);
        if (Either.isLeft(res)) {
            return res;
        }
        state = res.value;
    }
    return Either.Right(state.value);
};
_Right.prototype.bimap = function (_, f) {
    return new _Right(f(this.value));
};
_Right.prototype.extend = function (f) {
    return new _Right(f(this));
};
_Right.prototype.toString = function () {
    return 'Either.Right(' + toString(this.value) + ')';
};
_Right.prototype.equals = util.getEquals(_Right);
Either.Right = function (value) {
    return new _Right(value);
};
function _Left(x) {
    this.value = x;
}
util.extend(_Left, Either);
_Left.prototype.isLeft = true;
_Left.prototype.isRight = false;
_Left.prototype.ap = util.returnThis;
_Left.prototype.bimap = function (f) {
    return new _Left(f(this.value));
};
_Left.prototype.extend = util.returnThis;
_Left.prototype.toString = function () {
    return 'Either.Left(' + toString(this.value) + ')';
};
_Left.prototype.equals = util.getEquals(_Left);
Either.Left = function (value) {
    return new _Left(value);
};
Either.prototype.either = function instanceEither(leftFn, rightFn) {
    return this.isLeft ? leftFn(this.value) : rightFn(this.value);
};
module.exports = Either;
},{"./internal/util":45,"ramda/src/curry":49,"ramda/src/toString":110}],44:[function(require,module,exports){
var toString = require('ramda/src/toString');
var curry = require('ramda/src/curry');
var util = require('./internal/util.js');
function Maybe(x) {
    return x == null ? _nothing : Maybe.Just(x);
}
Maybe.prototype['@@type'] = 'ramda-fantasy/Maybe';
function Just(x) {
    this.value = x;
}
util.extend(Just, Maybe);
Just.prototype.isJust = true;
Just.prototype.isNothing = false;
function Nothing() {
}
util.extend(Nothing, Maybe);
Nothing.prototype.isNothing = true;
Nothing.prototype.isJust = false;
var _nothing = new Nothing();
Maybe.Nothing = function () {
    return _nothing;
};
Maybe.Just = function (x) {
    return new Just(x);
};
Maybe.of = Maybe.Just;
Maybe.prototype.of = Maybe.Just;
Maybe.isJust = function (x) {
    return x.isJust;
};
Maybe.isNothing = function (x) {
    return x.isNothing;
};
Maybe.maybe = curry(function (nothingVal, justFn, m) {
    return m.reduce(function (_, x) {
        return justFn(x);
    }, nothingVal);
});
Maybe.toMaybe = Maybe;
Just.prototype.concat = function (that) {
    return that.isNothing ? this : this.of(this.value.concat(that.value));
};
Nothing.prototype.concat = util.identity;
Just.prototype.map = function (f) {
    return this.of(f(this.value));
};
Nothing.prototype.map = util.returnThis;
Just.prototype.ap = function (m) {
    return m.map(this.value);
};
Nothing.prototype.ap = util.returnThis;
Just.prototype.chain = util.baseMap;
Nothing.prototype.chain = util.returnThis;
Maybe.chainRec = Maybe.prototype.chainRec = function (f, i) {
    var res, state = util.chainRecNext(i);
    while (state.isNext) {
        res = f(util.chainRecNext, util.chainRecDone, state.value);
        if (Maybe.isNothing(res)) {
            return res;
        }
        state = res.value;
    }
    return Maybe.Just(state.value);
};
Just.prototype.datatype = Just;
Nothing.prototype.datatype = Nothing;
Just.prototype.equals = util.getEquals(Just);
Nothing.prototype.equals = function (that) {
    return that === _nothing;
};
Maybe.prototype.isNothing = function () {
    return this === _nothing;
};
Maybe.prototype.isJust = function () {
    return this instanceof Just;
};
Just.prototype.getOrElse = function () {
    return this.value;
};
Nothing.prototype.getOrElse = function (a) {
    return a;
};
Just.prototype.reduce = function (f, x) {
    return f(x, this.value);
};
Nothing.prototype.reduce = function (f, x) {
    return x;
};
Just.prototype.toString = function () {
    return 'Maybe.Just(' + toString(this.value) + ')';
};
Nothing.prototype.toString = function () {
    return 'Maybe.Nothing()';
};
module.exports = Maybe;
},{"./internal/util.js":45,"ramda/src/curry":49,"ramda/src/toString":110}],45:[function(require,module,exports){
var _equals = require('ramda/src/equals');
module.exports = {
    baseMap: function (f) {
        return f(this.value);
    },
    getEquals: function (constructor) {
        return function equals(that) {
            return that instanceof constructor && _equals(this.value, that.value);
        };
    },
    extend: function (Child, Parent) {
        function Ctor() {
            this.constructor = Child;
        }
        Ctor.prototype = Parent.prototype;
        Child.prototype = new Ctor();
        Child.super_ = Parent.prototype;
    },
    identity: function (x) {
        return x;
    },
    notImplemented: function (str) {
        return function () {
            throw new Error(str + ' is not implemented');
        };
    },
    notCallable: function (fn) {
        return function () {
            throw new Error(fn + ' cannot be called directly');
        };
    },
    returnThis: function () {
        return this;
    },
    chainRecNext: function (v) {
        return {
            isNext: true,
            value: v
        };
    },
    chainRecDone: function (v) {
        return {
            isNext: false,
            value: v
        };
    },
    deriveAp: function (Type) {
        return function (fa) {
            return this.chain(function (f) {
                return fa.chain(function (a) {
                    return Type.of(f(a));
                });
            });
        };
    },
    deriveMap: function (Type) {
        return function (f) {
            return this.chain(function (a) {
                return Type.of(f(a));
            });
        };
    }
};
},{"ramda/src/equals":53}],46:[function(require,module,exports){
var _concat = require('./internal/_concat');
var _curry1 = require('./internal/_curry1');
var curryN = require('./curryN');
var addIndex = _curry1(function addIndex(fn) {
    return curryN(fn.length, function () {
        var idx = 0;
        var origFn = arguments[0];
        var list = arguments[arguments.length - 1];
        var args = Array.prototype.slice.call(arguments, 0);
        args[0] = function () {
            var result = origFn.apply(this, _concat(arguments, [
                idx,
                list
            ]));
            idx += 1;
            return result;
        };
        return fn.apply(this, args);
    });
});
module.exports = addIndex;
},{"./curryN":50,"./internal/_concat":61,"./internal/_curry1":64}],47:[function(require,module,exports){
var _arity = require('./internal/_arity');
var _curry2 = require('./internal/_curry2');
var bind = _curry2(function bind(fn, thisObj) {
    return _arity(fn.length, function () {
        return fn.apply(thisObj, arguments);
    });
});
module.exports = bind;
},{"./internal/_arity":57,"./internal/_curry2":65}],48:[function(require,module,exports){
var pipe = require('./pipe');
var reverse = require('./reverse');
function compose() {
    if (arguments.length === 0) {
        throw new Error('compose requires at least one argument');
    }
    return pipe.apply(this, reverse(arguments));
}
module.exports = compose;
},{"./pipe":101,"./reverse":107}],49:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var curryN = require('./curryN');
var curry = _curry1(function curry(fn) {
    return curryN(fn.length, fn);
});
module.exports = curry;
},{"./curryN":50,"./internal/_curry1":64}],50:[function(require,module,exports){
var _arity = require('./internal/_arity');
var _curry1 = require('./internal/_curry1');
var _curry2 = require('./internal/_curry2');
var _curryN = require('./internal/_curryN');
var curryN = _curry2(function curryN(length, fn) {
    if (length === 1) {
        return _curry1(fn);
    }
    return _arity(length, _curryN(length, [], fn));
});
module.exports = curryN;
},{"./internal/_arity":57,"./internal/_curry1":64,"./internal/_curry2":65,"./internal/_curryN":67}],51:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var defaultTo = _curry2(function defaultTo(d, v) {
    return v == null || v !== v ? d : v;
});
module.exports = defaultTo;
},{"./internal/_curry2":65}],52:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var _isArguments = require('./internal/_isArguments');
var _isArray = require('./internal/_isArray');
var _isObject = require('./internal/_isObject');
var _isString = require('./internal/_isString');
var empty = _curry1(function empty(x) {
    return x != null && typeof x['fantasy-land/empty'] === 'function' ? x['fantasy-land/empty']() : x != null && x.constructor != null && typeof x.constructor['fantasy-land/empty'] === 'function' ? x.constructor['fantasy-land/empty']() : x != null && typeof x.empty === 'function' ? x.empty() : x != null && x.constructor != null && typeof x.constructor.empty === 'function' ? x.constructor.empty() : _isArray(x) ? [] : _isString(x) ? '' : _isObject(x) ? {} : _isArguments(x) ? function () {
        return arguments;
    }() : void 0;
});
module.exports = empty;
},{"./internal/_curry1":64,"./internal/_isArguments":74,"./internal/_isArray":75,"./internal/_isObject":77,"./internal/_isString":79}],53:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var _equals = require('./internal/_equals');
var equals = _curry2(function equals(a, b) {
    return _equals(a, b, [], []);
});
module.exports = equals;
},{"./internal/_curry2":65,"./internal/_equals":69}],54:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var _dispatchable = require('./internal/_dispatchable');
var _filter = require('./internal/_filter');
var _isObject = require('./internal/_isObject');
var _reduce = require('./internal/_reduce');
var _xfilter = require('./internal/_xfilter');
var keys = require('./keys');
var filter = _curry2(_dispatchable(['filter'], _xfilter, function (pred, filterable) {
    return _isObject(filterable) ? _reduce(function (acc, key) {
        if (pred(filterable[key])) {
            acc[key] = filterable[key];
        }
        return acc;
    }, {}, keys(filterable)) : _filter(pred, filterable);
}));
module.exports = filter;
},{"./internal/_curry2":65,"./internal/_dispatchable":68,"./internal/_filter":70,"./internal/_isObject":77,"./internal/_reduce":84,"./internal/_xfilter":89,"./keys":94}],55:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var _dispatchable = require('./internal/_dispatchable');
var _xfind = require('./internal/_xfind');
var find = _curry2(_dispatchable(['find'], _xfind, function find(fn, list) {
    var idx = 0;
    var len = list.length;
    while (idx < len) {
        if (fn(list[idx])) {
            return list[idx];
        }
        idx += 1;
    }
}));
module.exports = find;
},{"./internal/_curry2":65,"./internal/_dispatchable":68,"./internal/_xfind":90}],56:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var identical = _curry2(function identical(a, b) {
    if (a === b) {
        return a !== 0 || 1 / a === 1 / b;
    } else {
        return a !== a && b !== b;
    }
});
module.exports = identical;
},{"./internal/_curry2":65}],57:[function(require,module,exports){
function _arity(n, fn) {
    switch (n) {
    case 0:
        return function () {
            return fn.apply(this, arguments);
        };
    case 1:
        return function (a0) {
            return fn.apply(this, arguments);
        };
    case 2:
        return function (a0, a1) {
            return fn.apply(this, arguments);
        };
    case 3:
        return function (a0, a1, a2) {
            return fn.apply(this, arguments);
        };
    case 4:
        return function (a0, a1, a2, a3) {
            return fn.apply(this, arguments);
        };
    case 5:
        return function (a0, a1, a2, a3, a4) {
            return fn.apply(this, arguments);
        };
    case 6:
        return function (a0, a1, a2, a3, a4, a5) {
            return fn.apply(this, arguments);
        };
    case 7:
        return function (a0, a1, a2, a3, a4, a5, a6) {
            return fn.apply(this, arguments);
        };
    case 8:
        return function (a0, a1, a2, a3, a4, a5, a6, a7) {
            return fn.apply(this, arguments);
        };
    case 9:
        return function (a0, a1, a2, a3, a4, a5, a6, a7, a8) {
            return fn.apply(this, arguments);
        };
    case 10:
        return function (a0, a1, a2, a3, a4, a5, a6, a7, a8, a9) {
            return fn.apply(this, arguments);
        };
    default:
        throw new Error('First argument to _arity must be a non-negative integer no greater than ten');
    }
}
module.exports = _arity;
},{}],58:[function(require,module,exports){
function _arrayFromIterator(iter) {
    var list = [];
    var next;
    while (!(next = iter.next()).done) {
        list.push(next.value);
    }
    return list;
}
module.exports = _arrayFromIterator;
},{}],59:[function(require,module,exports){
var _isArray = require('./_isArray');
function _checkForMethod(methodname, fn) {
    return function () {
        var length = arguments.length;
        if (length === 0) {
            return fn();
        }
        var obj = arguments[length - 1];
        return _isArray(obj) || typeof obj[methodname] !== 'function' ? fn.apply(this, arguments) : obj[methodname].apply(obj, Array.prototype.slice.call(arguments, 0, length - 1));
    };
}
module.exports = _checkForMethod;
},{"./_isArray":75}],60:[function(require,module,exports){
function _complement(f) {
    return function () {
        return !f.apply(this, arguments);
    };
}
module.exports = _complement;
},{}],61:[function(require,module,exports){
function _concat(set1, set2) {
    set1 = set1 || [];
    set2 = set2 || [];
    var idx;
    var len1 = set1.length;
    var len2 = set2.length;
    var result = [];
    idx = 0;
    while (idx < len1) {
        result[result.length] = set1[idx];
        idx += 1;
    }
    idx = 0;
    while (idx < len2) {
        result[result.length] = set2[idx];
        idx += 1;
    }
    return result;
}
module.exports = _concat;
},{}],62:[function(require,module,exports){
var _indexOf = require('./_indexOf');
function _contains(a, list) {
    return _indexOf(list, a, 0) >= 0;
}
module.exports = _contains;
},{"./_indexOf":73}],63:[function(require,module,exports){
function _containsWith(pred, x, list) {
    var idx = 0;
    var len = list.length;
    while (idx < len) {
        if (pred(x, list[idx])) {
            return true;
        }
        idx += 1;
    }
    return false;
}
module.exports = _containsWith;
},{}],64:[function(require,module,exports){
var _isPlaceholder = require('./_isPlaceholder');
function _curry1(fn) {
    return function f1(a) {
        if (arguments.length === 0 || _isPlaceholder(a)) {
            return f1;
        } else {
            return fn.apply(this, arguments);
        }
    };
}
module.exports = _curry1;
},{"./_isPlaceholder":78}],65:[function(require,module,exports){
var _curry1 = require('./_curry1');
var _isPlaceholder = require('./_isPlaceholder');
function _curry2(fn) {
    return function f2(a, b) {
        switch (arguments.length) {
        case 0:
            return f2;
        case 1:
            return _isPlaceholder(a) ? f2 : _curry1(function (_b) {
                return fn(a, _b);
            });
        default:
            return _isPlaceholder(a) && _isPlaceholder(b) ? f2 : _isPlaceholder(a) ? _curry1(function (_a) {
                return fn(_a, b);
            }) : _isPlaceholder(b) ? _curry1(function (_b) {
                return fn(a, _b);
            }) : fn(a, b);
        }
    };
}
module.exports = _curry2;
},{"./_curry1":64,"./_isPlaceholder":78}],66:[function(require,module,exports){
var _curry1 = require('./_curry1');
var _curry2 = require('./_curry2');
var _isPlaceholder = require('./_isPlaceholder');
function _curry3(fn) {
    return function f3(a, b, c) {
        switch (arguments.length) {
        case 0:
            return f3;
        case 1:
            return _isPlaceholder(a) ? f3 : _curry2(function (_b, _c) {
                return fn(a, _b, _c);
            });
        case 2:
            return _isPlaceholder(a) && _isPlaceholder(b) ? f3 : _isPlaceholder(a) ? _curry2(function (_a, _c) {
                return fn(_a, b, _c);
            }) : _isPlaceholder(b) ? _curry2(function (_b, _c) {
                return fn(a, _b, _c);
            }) : _curry1(function (_c) {
                return fn(a, b, _c);
            });
        default:
            return _isPlaceholder(a) && _isPlaceholder(b) && _isPlaceholder(c) ? f3 : _isPlaceholder(a) && _isPlaceholder(b) ? _curry2(function (_a, _b) {
                return fn(_a, _b, c);
            }) : _isPlaceholder(a) && _isPlaceholder(c) ? _curry2(function (_a, _c) {
                return fn(_a, b, _c);
            }) : _isPlaceholder(b) && _isPlaceholder(c) ? _curry2(function (_b, _c) {
                return fn(a, _b, _c);
            }) : _isPlaceholder(a) ? _curry1(function (_a) {
                return fn(_a, b, c);
            }) : _isPlaceholder(b) ? _curry1(function (_b) {
                return fn(a, _b, c);
            }) : _isPlaceholder(c) ? _curry1(function (_c) {
                return fn(a, b, _c);
            }) : fn(a, b, c);
        }
    };
}
module.exports = _curry3;
},{"./_curry1":64,"./_curry2":65,"./_isPlaceholder":78}],67:[function(require,module,exports){
var _arity = require('./_arity');
var _isPlaceholder = require('./_isPlaceholder');
function _curryN(length, received, fn) {
    return function () {
        var combined = [];
        var argsIdx = 0;
        var left = length;
        var combinedIdx = 0;
        while (combinedIdx < received.length || argsIdx < arguments.length) {
            var result;
            if (combinedIdx < received.length && (!_isPlaceholder(received[combinedIdx]) || argsIdx >= arguments.length)) {
                result = received[combinedIdx];
            } else {
                result = arguments[argsIdx];
                argsIdx += 1;
            }
            combined[combinedIdx] = result;
            if (!_isPlaceholder(result)) {
                left -= 1;
            }
            combinedIdx += 1;
        }
        return left <= 0 ? fn.apply(this, combined) : _arity(left, _curryN(length, combined, fn));
    };
}
module.exports = _curryN;
},{"./_arity":57,"./_isPlaceholder":78}],68:[function(require,module,exports){
var _isArray = require('./_isArray');
var _isTransformer = require('./_isTransformer');
function _dispatchable(methodNames, xf, fn) {
    return function () {
        if (arguments.length === 0) {
            return fn();
        }
        var args = Array.prototype.slice.call(arguments, 0);
        var obj = args.pop();
        if (!_isArray(obj)) {
            var idx = 0;
            while (idx < methodNames.length) {
                if (typeof obj[methodNames[idx]] === 'function') {
                    return obj[methodNames[idx]].apply(obj, args);
                }
                idx += 1;
            }
            if (_isTransformer(obj)) {
                var transducer = xf.apply(null, args);
                return transducer(obj);
            }
        }
        return fn.apply(this, arguments);
    };
}
module.exports = _dispatchable;
},{"./_isArray":75,"./_isTransformer":80}],69:[function(require,module,exports){
var _arrayFromIterator = require('./_arrayFromIterator');
var _containsWith = require('./_containsWith');
var _functionName = require('./_functionName');
var _has = require('./_has');
var identical = require('../identical');
var keys = require('../keys');
var type = require('../type');
function _uniqContentEquals(aIterator, bIterator, stackA, stackB) {
    var a = _arrayFromIterator(aIterator);
    var b = _arrayFromIterator(bIterator);
    function eq(_a, _b) {
        return _equals(_a, _b, stackA.slice(), stackB.slice());
    }
    return !_containsWith(function (b, aItem) {
        return !_containsWith(eq, aItem, b);
    }, b, a);
}
function _equals(a, b, stackA, stackB) {
    if (identical(a, b)) {
        return true;
    }
    var typeA = type(a);
    if (typeA !== type(b)) {
        return false;
    }
    if (a == null || b == null) {
        return false;
    }
    if (typeof a['fantasy-land/equals'] === 'function' || typeof b['fantasy-land/equals'] === 'function') {
        return typeof a['fantasy-land/equals'] === 'function' && a['fantasy-land/equals'](b) && typeof b['fantasy-land/equals'] === 'function' && b['fantasy-land/equals'](a);
    }
    if (typeof a.equals === 'function' || typeof b.equals === 'function') {
        return typeof a.equals === 'function' && a.equals(b) && typeof b.equals === 'function' && b.equals(a);
    }
    switch (typeA) {
    case 'Arguments':
    case 'Array':
    case 'Object':
        if (typeof a.constructor === 'function' && _functionName(a.constructor) === 'Promise') {
            return a === b;
        }
        break;
    case 'Boolean':
    case 'Number':
    case 'String':
        if (!(typeof a === typeof b && identical(a.valueOf(), b.valueOf()))) {
            return false;
        }
        break;
    case 'Date':
        if (!identical(a.valueOf(), b.valueOf())) {
            return false;
        }
        break;
    case 'Error':
        return a.name === b.name && a.message === b.message;
    case 'RegExp':
        if (!(a.source === b.source && a.global === b.global && a.ignoreCase === b.ignoreCase && a.multiline === b.multiline && a.sticky === b.sticky && a.unicode === b.unicode)) {
            return false;
        }
        break;
    }
    var idx = stackA.length - 1;
    while (idx >= 0) {
        if (stackA[idx] === a) {
            return stackB[idx] === b;
        }
        idx -= 1;
    }
    switch (typeA) {
    case 'Map':
        if (a.size !== b.size) {
            return false;
        }
        return _uniqContentEquals(a.entries(), b.entries(), stackA.concat([a]), stackB.concat([b]));
    case 'Set':
        if (a.size !== b.size) {
            return false;
        }
        return _uniqContentEquals(a.values(), b.values(), stackA.concat([a]), stackB.concat([b]));
    case 'Arguments':
    case 'Array':
    case 'Object':
    case 'Boolean':
    case 'Number':
    case 'String':
    case 'Date':
    case 'Error':
    case 'RegExp':
    case 'Int8Array':
    case 'Uint8Array':
    case 'Uint8ClampedArray':
    case 'Int16Array':
    case 'Uint16Array':
    case 'Int32Array':
    case 'Uint32Array':
    case 'Float32Array':
    case 'Float64Array':
    case 'ArrayBuffer':
        break;
    default:
        return false;
    }
    var keysA = keys(a);
    if (keysA.length !== keys(b).length) {
        return false;
    }
    var extendedStackA = stackA.concat([a]);
    var extendedStackB = stackB.concat([b]);
    idx = keysA.length - 1;
    while (idx >= 0) {
        var key = keysA[idx];
        if (!(_has(key, b) && _equals(b[key], a[key], extendedStackA, extendedStackB))) {
            return false;
        }
        idx -= 1;
    }
    return true;
}
module.exports = _equals;
},{"../identical":56,"../keys":94,"../type":111,"./_arrayFromIterator":58,"./_containsWith":63,"./_functionName":71,"./_has":72}],70:[function(require,module,exports){
function _filter(fn, list) {
    var idx = 0;
    var len = list.length;
    var result = [];
    while (idx < len) {
        if (fn(list[idx])) {
            result[result.length] = list[idx];
        }
        idx += 1;
    }
    return result;
}
module.exports = _filter;
},{}],71:[function(require,module,exports){
function _functionName(f) {
    var match = String(f).match(/^function (\w*)/);
    return match == null ? '' : match[1];
}
module.exports = _functionName;
},{}],72:[function(require,module,exports){
function _has(prop, obj) {
    return Object.prototype.hasOwnProperty.call(obj, prop);
}
module.exports = _has;
},{}],73:[function(require,module,exports){
var equals = require('../equals');
function _indexOf(list, a, idx) {
    var inf, item;
    if (typeof list.indexOf === 'function') {
        switch (typeof a) {
        case 'number':
            if (a === 0) {
                inf = 1 / a;
                while (idx < list.length) {
                    item = list[idx];
                    if (item === 0 && 1 / item === inf) {
                        return idx;
                    }
                    idx += 1;
                }
                return -1;
            } else if (a !== a) {
                while (idx < list.length) {
                    item = list[idx];
                    if (typeof item === 'number' && item !== item) {
                        return idx;
                    }
                    idx += 1;
                }
                return -1;
            }
            return list.indexOf(a, idx);
        case 'string':
        case 'boolean':
        case 'function':
        case 'undefined':
            return list.indexOf(a, idx);
        case 'object':
            if (a === null) {
                return list.indexOf(a, idx);
            }
        }
    }
    while (idx < list.length) {
        if (equals(list[idx], a)) {
            return idx;
        }
        idx += 1;
    }
    return -1;
}
module.exports = _indexOf;
},{"../equals":53}],74:[function(require,module,exports){
var _has = require('./_has');
var toString = Object.prototype.toString;
var _isArguments = function () {
    return toString.call(arguments) === '[object Arguments]' ? function _isArguments(x) {
        return toString.call(x) === '[object Arguments]';
    } : function _isArguments(x) {
        return _has('callee', x);
    };
};
module.exports = _isArguments;
},{"./_has":72}],75:[function(require,module,exports){
module.exports = Array.isArray || function _isArray(val) {
    return val != null && val.length >= 0 && Object.prototype.toString.call(val) === '[object Array]';
};
},{}],76:[function(require,module,exports){
var _curry1 = require('./_curry1');
var _isArray = require('./_isArray');
var _isString = require('./_isString');
var _isArrayLike = _curry1(function isArrayLike(x) {
    if (_isArray(x)) {
        return true;
    }
    if (!x) {
        return false;
    }
    if (typeof x !== 'object') {
        return false;
    }
    if (_isString(x)) {
        return false;
    }
    if (x.nodeType === 1) {
        return !!x.length;
    }
    if (x.length === 0) {
        return true;
    }
    if (x.length > 0) {
        return x.hasOwnProperty(0) && x.hasOwnProperty(x.length - 1);
    }
    return false;
});
module.exports = _isArrayLike;
},{"./_curry1":64,"./_isArray":75,"./_isString":79}],77:[function(require,module,exports){
function _isObject(x) {
    return Object.prototype.toString.call(x) === '[object Object]';
}
module.exports = _isObject;
},{}],78:[function(require,module,exports){
function _isPlaceholder(a) {
    return a != null && typeof a === 'object' && a['@@functional/placeholder'] === true;
}
module.exports = _isPlaceholder;
},{}],79:[function(require,module,exports){
function _isString(x) {
    return Object.prototype.toString.call(x) === '[object String]';
}
module.exports = _isString;
},{}],80:[function(require,module,exports){
function _isTransformer(obj) {
    return typeof obj['@@transducer/step'] === 'function';
}
module.exports = _isTransformer;
},{}],81:[function(require,module,exports){
function _map(fn, functor) {
    var idx = 0;
    var len = functor.length;
    var result = Array(len);
    while (idx < len) {
        result[idx] = fn(functor[idx]);
        idx += 1;
    }
    return result;
}
module.exports = _map;
},{}],82:[function(require,module,exports){
function _pipe(f, g) {
    return function () {
        return g.call(this, f.apply(this, arguments));
    };
}
module.exports = _pipe;
},{}],83:[function(require,module,exports){
function _quote(s) {
    var escaped = s.replace(/\\/g, '\\\\').replace(/[\b]/g, '\\b').replace(/\f/g, '\\f').replace(/\n/g, '\\n').replace(/\r/g, '\\r').replace(/\t/g, '\\t').replace(/\v/g, '\\v').replace(/\0/g, '\\0');
    return '"' + escaped.replace(/"/g, '\\"') + '"';
}
module.exports = _quote;
},{}],84:[function(require,module,exports){
var _isArrayLike = require('./_isArrayLike');
var _xwrap = require('./_xwrap');
var bind = require('../bind');
function _arrayReduce(xf, acc, list) {
    var idx = 0;
    var len = list.length;
    while (idx < len) {
        acc = xf['@@transducer/step'](acc, list[idx]);
        if (acc && acc['@@transducer/reduced']) {
            acc = acc['@@transducer/value'];
            break;
        }
        idx += 1;
    }
    return xf['@@transducer/result'](acc);
}
function _iterableReduce(xf, acc, iter) {
    var step = iter.next();
    while (!step.done) {
        acc = xf['@@transducer/step'](acc, step.value);
        if (acc && acc['@@transducer/reduced']) {
            acc = acc['@@transducer/value'];
            break;
        }
        step = iter.next();
    }
    return xf['@@transducer/result'](acc);
}
function _methodReduce(xf, acc, obj, methodName) {
    return xf['@@transducer/result'](obj[methodName](bind(xf['@@transducer/step'], xf), acc));
}
var symIterator = typeof Symbol !== 'undefined' ? Symbol.iterator : '@@iterator';
function _reduce(fn, acc, list) {
    if (typeof fn === 'function') {
        fn = _xwrap(fn);
    }
    if (_isArrayLike(list)) {
        return _arrayReduce(fn, acc, list);
    }
    if (typeof list['fantasy-land/reduce'] === 'function') {
        return _methodReduce(fn, acc, list, 'fantasy-land/reduce');
    }
    if (list[symIterator] != null) {
        return _iterableReduce(fn, acc, list[symIterator]());
    }
    if (typeof list.next === 'function') {
        return _iterableReduce(fn, acc, list);
    }
    if (typeof list.reduce === 'function') {
        return _methodReduce(fn, acc, list, 'reduce');
    }
    throw new TypeError('reduce: list must be array or iterable');
}
module.exports = _reduce;
},{"../bind":47,"./_isArrayLike":76,"./_xwrap":92}],85:[function(require,module,exports){
function _reduced(x) {
    return x && x['@@transducer/reduced'] ? x : {
        '@@transducer/value': x,
        '@@transducer/reduced': true
    };
}
module.exports = _reduced;
},{}],86:[function(require,module,exports){
var pad = function pad(n) {
    return (n < 10 ? '0' : '') + n;
};
var _toISOString = typeof Date.prototype.toISOString === 'function' ? function _toISOString(d) {
    return d.toISOString();
} : function _toISOString(d) {
    return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()) + 'T' + pad(d.getUTCHours()) + ':' + pad(d.getUTCMinutes()) + ':' + pad(d.getUTCSeconds()) + '.' + (d.getUTCMilliseconds() / 1000).toFixed(3).slice(2, 5) + 'Z';
};
module.exports = _toISOString;
},{}],87:[function(require,module,exports){
var _contains = require('./_contains');
var _map = require('./_map');
var _quote = require('./_quote');
var _toISOString = require('./_toISOString');
var keys = require('../keys');
var reject = require('../reject');
function _toString(x, seen) {
    var recur = function recur(y) {
        var xs = seen.concat([x]);
        return _contains(y, xs) ? '<Circular>' : _toString(y, xs);
    };
    var mapPairs = function (obj, keys) {
        return _map(function (k) {
            return _quote(k) + ': ' + recur(obj[k]);
        }, keys.slice().sort());
    };
    switch (Object.prototype.toString.call(x)) {
    case '[object Arguments]':
        return '(function() { return arguments; }(' + _map(recur, x).join(', ') + '))';
    case '[object Array]':
        return '[' + _map(recur, x).concat(mapPairs(x, reject(function (k) {
            return /^\d+$/.test(k);
        }, keys(x)))).join(', ') + ']';
    case '[object Boolean]':
        return typeof x === 'object' ? 'new Boolean(' + recur(x.valueOf()) + ')' : x.toString();
    case '[object Date]':
        return 'new Date(' + (isNaN(x.valueOf()) ? recur(NaN) : _quote(_toISOString(x))) + ')';
    case '[object Null]':
        return 'null';
    case '[object Number]':
        return typeof x === 'object' ? 'new Number(' + recur(x.valueOf()) + ')' : 1 / x === -Infinity ? '-0' : x.toString(10);
    case '[object String]':
        return typeof x === 'object' ? 'new String(' + recur(x.valueOf()) + ')' : _quote(x);
    case '[object Undefined]':
        return 'undefined';
    default:
        if (typeof x.toString === 'function') {
            var repr = x.toString();
            if (repr !== '[object Object]') {
                return repr;
            }
        }
        return '{' + mapPairs(x, keys(x)).join(', ') + '}';
    }
}
module.exports = _toString;
},{"../keys":94,"../reject":106,"./_contains":62,"./_map":81,"./_quote":83,"./_toISOString":86}],88:[function(require,module,exports){
module.exports = {
    init: function () {
        return this.xf['@@transducer/init']();
    },
    result: function (result) {
        return this.xf['@@transducer/result'](result);
    }
};
},{}],89:[function(require,module,exports){
var _curry2 = require('./_curry2');
var _xfBase = require('./_xfBase');
var XFilter = function () {
    function XFilter(f, xf) {
        this.xf = xf;
        this.f = f;
    }
    XFilter.prototype['@@transducer/init'] = _xfBase.init;
    XFilter.prototype['@@transducer/result'] = _xfBase.result;
    XFilter.prototype['@@transducer/step'] = function (result, input) {
        return this.f(input) ? this.xf['@@transducer/step'](result, input) : result;
    };
    return XFilter;
}();
var _xfilter = _curry2(function _xfilter(f, xf) {
    return new XFilter(f, xf);
});
module.exports = _xfilter;
},{"./_curry2":65,"./_xfBase":88}],90:[function(require,module,exports){
var _curry2 = require('./_curry2');
var _reduced = require('./_reduced');
var _xfBase = require('./_xfBase');
var XFind = function () {
    function XFind(f, xf) {
        this.xf = xf;
        this.f = f;
        this.found = false;
    }
    XFind.prototype['@@transducer/init'] = _xfBase.init;
    XFind.prototype['@@transducer/result'] = function (result) {
        if (!this.found) {
            result = this.xf['@@transducer/step'](result, void 0);
        }
        return this.xf['@@transducer/result'](result);
    };
    XFind.prototype['@@transducer/step'] = function (result, input) {
        if (this.f(input)) {
            this.found = true;
            result = _reduced(this.xf['@@transducer/step'](result, input));
        }
        return result;
    };
    return XFind;
}();
var _xfind = _curry2(function _xfind(f, xf) {
    return new XFind(f, xf);
});
module.exports = _xfind;
},{"./_curry2":65,"./_reduced":85,"./_xfBase":88}],91:[function(require,module,exports){
var _curry2 = require('./_curry2');
var _xfBase = require('./_xfBase');
var XMap = function () {
    function XMap(f, xf) {
        this.xf = xf;
        this.f = f;
    }
    XMap.prototype['@@transducer/init'] = _xfBase.init;
    XMap.prototype['@@transducer/result'] = _xfBase.result;
    XMap.prototype['@@transducer/step'] = function (result, input) {
        return this.xf['@@transducer/step'](result, this.f(input));
    };
    return XMap;
}();
var _xmap = _curry2(function _xmap(f, xf) {
    return new XMap(f, xf);
});
module.exports = _xmap;
},{"./_curry2":65,"./_xfBase":88}],92:[function(require,module,exports){
var XWrap = function () {
    function XWrap(fn) {
        this.f = fn;
    }
    XWrap.prototype['@@transducer/init'] = function () {
        throw new Error('init not implemented on XWrap');
    };
    XWrap.prototype['@@transducer/result'] = function (acc) {
        return acc;
    };
    XWrap.prototype['@@transducer/step'] = function (acc, x) {
        return this.f(acc, x);
    };
    return XWrap;
}();
function _xwrap(fn) {
    return new XWrap(fn);
}
module.exports = _xwrap;
},{}],93:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var empty = require('./empty');
var equals = require('./equals');
var isEmpty = _curry1(function isEmpty(x) {
    return x != null && equals(x, empty(x));
});
module.exports = isEmpty;
},{"./empty":52,"./equals":53,"./internal/_curry1":64}],94:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var _has = require('./internal/_has');
var _isArguments = require('./internal/_isArguments');
var hasEnumBug = !{ toString: null }.propertyIsEnumerable('toString');
var nonEnumerableProps = [
    'constructor',
    'valueOf',
    'isPrototypeOf',
    'toString',
    'propertyIsEnumerable',
    'hasOwnProperty',
    'toLocaleString'
];
var hasArgsEnumBug = function () {
    'use strict';
    return arguments.propertyIsEnumerable('length');
}();
var contains = function contains(list, item) {
    var idx = 0;
    while (idx < list.length) {
        if (list[idx] === item) {
            return true;
        }
        idx += 1;
    }
    return false;
};
var _keys = typeof Object.keys === 'function' && !hasArgsEnumBug ? function keys(obj) {
    return Object(obj) !== obj ? [] : Object.keys(obj);
} : function keys(obj) {
    if (Object(obj) !== obj) {
        return [];
    }
    var prop, nIdx;
    var ks = [];
    var checkArgsLength = hasArgsEnumBug && _isArguments(obj);
    for (prop in obj) {
        if (_has(prop, obj) && (!checkArgsLength || prop !== 'length')) {
            ks[ks.length] = prop;
        }
    }
    if (hasEnumBug) {
        nIdx = nonEnumerableProps.length - 1;
        while (nIdx >= 0) {
            prop = nonEnumerableProps[nIdx];
            if (_has(prop, obj) && !contains(ks, prop)) {
                ks[ks.length] = prop;
            }
            nIdx -= 1;
        }
    }
    return ks;
};
var keys = _curry1(_keys);
module.exports = keys;
},{"./internal/_curry1":64,"./internal/_has":72,"./internal/_isArguments":74}],95:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var _dispatchable = require('./internal/_dispatchable');
var _map = require('./internal/_map');
var _reduce = require('./internal/_reduce');
var _xmap = require('./internal/_xmap');
var curryN = require('./curryN');
var keys = require('./keys');
var map = _curry2(_dispatchable([
    'fantasy-land/map',
    'map'
], _xmap, function map(fn, functor) {
    switch (Object.prototype.toString.call(functor)) {
    case '[object Function]':
        return curryN(functor.length, function () {
            return fn.call(this, functor.apply(this, arguments));
        });
    case '[object Object]':
        return _reduce(function (acc, key) {
            acc[key] = fn(functor[key]);
            return acc;
        }, {}, keys(functor));
    default:
        return _map(fn, functor);
    }
}));
module.exports = map;
},{"./curryN":50,"./internal/_curry2":65,"./internal/_dispatchable":68,"./internal/_map":81,"./internal/_reduce":84,"./internal/_xmap":91,"./keys":94}],96:[function(require,module,exports){
var _curry3 = require('./internal/_curry3');
var _isObject = require('./internal/_isObject');
var mergeWithKey = require('./mergeWithKey');
var mergeDeepWithKey = _curry3(function mergeDeepWithKey(fn, lObj, rObj) {
    return mergeWithKey(function (k, lVal, rVal) {
        if (_isObject(lVal) && _isObject(rVal)) {
            return mergeDeepWithKey(fn, lVal, rVal);
        } else {
            return fn(k, lVal, rVal);
        }
    }, lObj, rObj);
});
module.exports = mergeDeepWithKey;
},{"./internal/_curry3":66,"./internal/_isObject":77,"./mergeWithKey":97}],97:[function(require,module,exports){
var _curry3 = require('./internal/_curry3');
var _has = require('./internal/_has');
var mergeWithKey = _curry3(function mergeWithKey(fn, l, r) {
    var result = {};
    var k;
    for (k in l) {
        if (_has(k, l)) {
            result[k] = _has(k, r) ? fn(k, l[k], r[k]) : l[k];
        }
    }
    for (k in r) {
        if (_has(k, r) && !_has(k, result)) {
            result[k] = r[k];
        }
    }
    return result;
});
module.exports = mergeWithKey;
},{"./internal/_curry3":66,"./internal/_has":72}],98:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var path = _curry2(function path(paths, obj) {
    var val = obj;
    var idx = 0;
    while (idx < paths.length) {
        if (val == null) {
            return;
        }
        val = val[paths[idx]];
        idx += 1;
    }
    return val;
});
module.exports = path;
},{"./internal/_curry2":65}],99:[function(require,module,exports){
var _curry3 = require('./internal/_curry3');
var defaultTo = require('./defaultTo');
var path = require('./path');
var pathOr = _curry3(function pathOr(d, p, obj) {
    return defaultTo(d, path(p, obj));
});
module.exports = pathOr;
},{"./defaultTo":51,"./internal/_curry3":66,"./path":98}],100:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var pick = _curry2(function pick(names, obj) {
    var result = {};
    var idx = 0;
    while (idx < names.length) {
        if (names[idx] in obj) {
            result[names[idx]] = obj[names[idx]];
        }
        idx += 1;
    }
    return result;
});
module.exports = pick;
},{"./internal/_curry2":65}],101:[function(require,module,exports){
var _arity = require('./internal/_arity');
var _pipe = require('./internal/_pipe');
var reduce = require('./reduce');
var tail = require('./tail');
function pipe() {
    if (arguments.length === 0) {
        throw new Error('pipe requires at least one argument');
    }
    return _arity(arguments[0].length, reduce(_pipe, arguments[0], tail(arguments)));
}
module.exports = pipe;
},{"./internal/_arity":57,"./internal/_pipe":82,"./reduce":105,"./tail":109}],102:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var path = require('./path');
var prop = _curry2(function prop(p, obj) {
    return path([p], obj);
});
module.exports = prop;
},{"./internal/_curry2":65,"./path":98}],103:[function(require,module,exports){
var _curry3 = require('./internal/_curry3');
var _has = require('./internal/_has');
var propOr = _curry3(function propOr(val, p, obj) {
    return obj != null && _has(p, obj) ? obj[p] : val;
});
module.exports = propOr;
},{"./internal/_curry3":66,"./internal/_has":72}],104:[function(require,module,exports){
var _curry2 = require('./internal/_curry2');
var props = _curry2(function props(ps, obj) {
    var len = ps.length;
    var out = [];
    var idx = 0;
    while (idx < len) {
        out[idx] = obj[ps[idx]];
        idx += 1;
    }
    return out;
});
module.exports = props;
},{"./internal/_curry2":65}],105:[function(require,module,exports){
var _curry3 = require('./internal/_curry3');
var _reduce = require('./internal/_reduce');
var reduce = _curry3(_reduce);
module.exports = reduce;
},{"./internal/_curry3":66,"./internal/_reduce":84}],106:[function(require,module,exports){
var _complement = require('./internal/_complement');
var _curry2 = require('./internal/_curry2');
var filter = require('./filter');
var reject = _curry2(function reject(pred, filterable) {
    return filter(_complement(pred), filterable);
});
module.exports = reject;
},{"./filter":54,"./internal/_complement":60,"./internal/_curry2":65}],107:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var _isString = require('./internal/_isString');
var reverse = _curry1(function reverse(list) {
    return _isString(list) ? list.split('').reverse().join('') : Array.prototype.slice.call(list, 0).reverse();
});
module.exports = reverse;
},{"./internal/_curry1":64,"./internal/_isString":79}],108:[function(require,module,exports){
var _checkForMethod = require('./internal/_checkForMethod');
var _curry3 = require('./internal/_curry3');
var slice = _curry3(_checkForMethod('slice', function slice(fromIndex, toIndex, list) {
    return Array.prototype.slice.call(list, fromIndex, toIndex);
}));
module.exports = slice;
},{"./internal/_checkForMethod":59,"./internal/_curry3":66}],109:[function(require,module,exports){
var _checkForMethod = require('./internal/_checkForMethod');
var _curry1 = require('./internal/_curry1');
var slice = require('./slice');
var tail = _curry1(_checkForMethod('tail', slice(1, Infinity)));
module.exports = tail;
},{"./internal/_checkForMethod":59,"./internal/_curry1":64,"./slice":108}],110:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var _toString = require('./internal/_toString');
var toString = _curry1(function toString(val) {
    return _toString(val, []);
});
module.exports = toString;
},{"./internal/_curry1":64,"./internal/_toString":87}],111:[function(require,module,exports){
var _curry1 = require('./internal/_curry1');
var type = _curry1(function type(val) {
    return val === null ? 'Null' : val === undefined ? 'Undefined' : Object.prototype.toString.call(val).slice(8, -1);
});
module.exports = type;
},{"./internal/_curry1":64}],112:[function(require,module,exports){
(function (global){
!function (global) {
    'use strict';
    var Op = Object.prototype;
    var hasOwn = Op.hasOwnProperty;
    var undefined;
    var $Symbol = typeof Symbol === 'function' ? Symbol : {};
    var iteratorSymbol = $Symbol.iterator || '@@iterator';
    var asyncIteratorSymbol = $Symbol.asyncIterator || '@@asyncIterator';
    var toStringTagSymbol = $Symbol.toStringTag || '@@toStringTag';
    var inModule = typeof module === 'object';
    var runtime = global.regeneratorRuntime;
    if (runtime) {
        if (inModule) {
            module.exports = runtime;
        }
        return;
    }
    runtime = global.regeneratorRuntime = inModule ? module.exports : {};
    function wrap(innerFn, outerFn, self, tryLocsList) {
        var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
        var generator = Object.create(protoGenerator.prototype);
        var context = new Context(tryLocsList || []);
        generator._invoke = makeInvokeMethod(innerFn, self, context);
        return generator;
    }
    runtime.wrap = wrap;
    function tryCatch(fn, obj, arg) {
        try {
            return {
                type: 'normal',
                arg: fn.call(obj, arg)
            };
        } catch (err) {
            return {
                type: 'throw',
                arg: err
            };
        }
    }
    var GenStateSuspendedStart = 'suspendedStart';
    var GenStateSuspendedYield = 'suspendedYield';
    var GenStateExecuting = 'executing';
    var GenStateCompleted = 'completed';
    var ContinueSentinel = {};
    function Generator() {
    }
    function GeneratorFunction() {
    }
    function GeneratorFunctionPrototype() {
    }
    var IteratorPrototype = {};
    IteratorPrototype[iteratorSymbol] = function () {
        return this;
    };
    var getProto = Object.getPrototypeOf;
    var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
    if (NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
        IteratorPrototype = NativeIteratorPrototype;
    }
    var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype);
    GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
    GeneratorFunctionPrototype.constructor = GeneratorFunction;
    GeneratorFunctionPrototype[toStringTagSymbol] = GeneratorFunction.displayName = 'GeneratorFunction';
    function defineIteratorMethods(prototype) {
        [
            'next',
            'throw',
            'return'
        ].forEach(function (method) {
            prototype[method] = function (arg) {
                return this._invoke(method, arg);
            };
        });
    }
    runtime.isGeneratorFunction = function (genFun) {
        var ctor = typeof genFun === 'function' && genFun.constructor;
        return ctor ? ctor === GeneratorFunction || (ctor.displayName || ctor.name) === 'GeneratorFunction' : false;
    };
    runtime.mark = function (genFun) {
        if (Object.setPrototypeOf) {
            Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
        } else {
            genFun.__proto__ = GeneratorFunctionPrototype;
            if (!(toStringTagSymbol in genFun)) {
                genFun[toStringTagSymbol] = 'GeneratorFunction';
            }
        }
        genFun.prototype = Object.create(Gp);
        return genFun;
    };
    runtime.awrap = function (arg) {
        return { __await: arg };
    };
    function AsyncIterator(generator) {
        function invoke(method, arg, resolve, reject) {
            var record = tryCatch(generator[method], generator, arg);
            if (record.type === 'throw') {
                reject(record.arg);
            } else {
                var result = record.arg;
                var value = result.value;
                if (value && typeof value === 'object' && hasOwn.call(value, '__await')) {
                    return Promise.resolve(value.__await).then(function (value) {
                        invoke('next', value, resolve, reject);
                    }, function (err) {
                        invoke('throw', err, resolve, reject);
                    });
                }
                return Promise.resolve(value).then(function (unwrapped) {
                    result.value = unwrapped;
                    resolve(result);
                }, reject);
            }
        }
        if (typeof global.process === 'object' && global.process.domain) {
            invoke = global.process.domain.bind(invoke);
        }
        var previousPromise;
        function enqueue(method, arg) {
            function callInvokeWithMethodAndArg() {
                return new Promise(function (resolve, reject) {
                    invoke(method, arg, resolve, reject);
                });
            }
            return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();
        }
        this._invoke = enqueue;
    }
    defineIteratorMethods(AsyncIterator.prototype);
    AsyncIterator.prototype[asyncIteratorSymbol] = function () {
        return this;
    };
    runtime.AsyncIterator = AsyncIterator;
    runtime.async = function (innerFn, outerFn, self, tryLocsList) {
        var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList));
        return runtime.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) {
            return result.done ? result.value : iter.next();
        });
    };
    function makeInvokeMethod(innerFn, self, context) {
        var state = GenStateSuspendedStart;
        return function invoke(method, arg) {
            if (state === GenStateExecuting) {
                throw new Error('Generator is already running');
            }
            if (state === GenStateCompleted) {
                if (method === 'throw') {
                    throw arg;
                }
                return doneResult();
            }
            context.method = method;
            context.arg = arg;
            while (true) {
                var delegate = context.delegate;
                if (delegate) {
                    var delegateResult = maybeInvokeDelegate(delegate, context);
                    if (delegateResult) {
                        if (delegateResult === ContinueSentinel)
                            continue;
                        return delegateResult;
                    }
                }
                if (context.method === 'next') {
                    context.sent = context._sent = context.arg;
                } else if (context.method === 'throw') {
                    if (state === GenStateSuspendedStart) {
                        state = GenStateCompleted;
                        throw context.arg;
                    }
                    context.dispatchException(context.arg);
                } else if (context.method === 'return') {
                    context.abrupt('return', context.arg);
                }
                state = GenStateExecuting;
                var record = tryCatch(innerFn, self, context);
                if (record.type === 'normal') {
                    state = context.done ? GenStateCompleted : GenStateSuspendedYield;
                    if (record.arg === ContinueSentinel) {
                        continue;
                    }
                    return {
                        value: record.arg,
                        done: context.done
                    };
                } else if (record.type === 'throw') {
                    state = GenStateCompleted;
                    context.method = 'throw';
                    context.arg = record.arg;
                }
            }
        };
    }
    function maybeInvokeDelegate(delegate, context) {
        var method = delegate.iterator[context.method];
        if (method === undefined) {
            context.delegate = null;
            if (context.method === 'throw') {
                if (delegate.iterator.return) {
                    context.method = 'return';
                    context.arg = undefined;
                    maybeInvokeDelegate(delegate, context);
                    if (context.method === 'throw') {
                        return ContinueSentinel;
                    }
                }
                context.method = 'throw';
                context.arg = new TypeError('The iterator does not provide a \'throw\' method');
            }
            return ContinueSentinel;
        }
        var record = tryCatch(method, delegate.iterator, context.arg);
        if (record.type === 'throw') {
            context.method = 'throw';
            context.arg = record.arg;
            context.delegate = null;
            return ContinueSentinel;
        }
        var info = record.arg;
        if (!info) {
            context.method = 'throw';
            context.arg = new TypeError('iterator result is not an object');
            context.delegate = null;
            return ContinueSentinel;
        }
        if (info.done) {
            context[delegate.resultName] = info.value;
            context.next = delegate.nextLoc;
            if (context.method !== 'return') {
                context.method = 'next';
                context.arg = undefined;
            }
        } else {
            return info;
        }
        context.delegate = null;
        return ContinueSentinel;
    }
    defineIteratorMethods(Gp);
    Gp[toStringTagSymbol] = 'Generator';
    Gp[iteratorSymbol] = function () {
        return this;
    };
    Gp.toString = function () {
        return '[object Generator]';
    };
    function pushTryEntry(locs) {
        var entry = { tryLoc: locs[0] };
        if (1 in locs) {
            entry.catchLoc = locs[1];
        }
        if (2 in locs) {
            entry.finallyLoc = locs[2];
            entry.afterLoc = locs[3];
        }
        this.tryEntries.push(entry);
    }
    function resetTryEntry(entry) {
        var record = entry.completion || {};
        record.type = 'normal';
        delete record.arg;
        entry.completion = record;
    }
    function Context(tryLocsList) {
        this.tryEntries = [{ tryLoc: 'root' }];
        tryLocsList.forEach(pushTryEntry, this);
        this.reset(true);
    }
    runtime.keys = function (object) {
        var keys = [];
        for (var key in object) {
            keys.push(key);
        }
        keys.reverse();
        return function next() {
            while (keys.length) {
                var key = keys.pop();
                if (key in object) {
                    next.value = key;
                    next.done = false;
                    return next;
                }
            }
            next.done = true;
            return next;
        };
    };
    function values(iterable) {
        if (iterable) {
            var iteratorMethod = iterable[iteratorSymbol];
            if (iteratorMethod) {
                return iteratorMethod.call(iterable);
            }
            if (typeof iterable.next === 'function') {
                return iterable;
            }
            if (!isNaN(iterable.length)) {
                var i = -1, next = function next() {
                        while (++i < iterable.length) {
                            if (hasOwn.call(iterable, i)) {
                                next.value = iterable[i];
                                next.done = false;
                                return next;
                            }
                        }
                        next.value = undefined;
                        next.done = true;
                        return next;
                    };
                return next.next = next;
            }
        }
        return { next: doneResult };
    }
    runtime.values = values;
    function doneResult() {
        return {
            value: undefined,
            done: true
        };
    }
    Context.prototype = {
        constructor: Context,
        reset: function (skipTempReset) {
            this.prev = 0;
            this.next = 0;
            this.sent = this._sent = undefined;
            this.done = false;
            this.delegate = null;
            this.method = 'next';
            this.arg = undefined;
            this.tryEntries.forEach(resetTryEntry);
            if (!skipTempReset) {
                for (var name in this) {
                    if (name.charAt(0) === 't' && hasOwn.call(this, name) && !isNaN(+name.slice(1))) {
                        this[name] = undefined;
                    }
                }
            }
        },
        stop: function () {
            this.done = true;
            var rootEntry = this.tryEntries[0];
            var rootRecord = rootEntry.completion;
            if (rootRecord.type === 'throw') {
                throw rootRecord.arg;
            }
            return this.rval;
        },
        dispatchException: function (exception) {
            if (this.done) {
                throw exception;
            }
            var context = this;
            function handle(loc, caught) {
                record.type = 'throw';
                record.arg = exception;
                context.next = loc;
                if (caught) {
                    context.method = 'next';
                    context.arg = undefined;
                }
                return !!caught;
            }
            for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                var entry = this.tryEntries[i];
                var record = entry.completion;
                if (entry.tryLoc === 'root') {
                    return handle('end');
                }
                if (entry.tryLoc <= this.prev) {
                    var hasCatch = hasOwn.call(entry, 'catchLoc');
                    var hasFinally = hasOwn.call(entry, 'finallyLoc');
                    if (hasCatch && hasFinally) {
                        if (this.prev < entry.catchLoc) {
                            return handle(entry.catchLoc, true);
                        } else if (this.prev < entry.finallyLoc) {
                            return handle(entry.finallyLoc);
                        }
                    } else if (hasCatch) {
                        if (this.prev < entry.catchLoc) {
                            return handle(entry.catchLoc, true);
                        }
                    } else if (hasFinally) {
                        if (this.prev < entry.finallyLoc) {
                            return handle(entry.finallyLoc);
                        }
                    } else {
                        throw new Error('try statement without catch or finally');
                    }
                }
            }
        },
        abrupt: function (type, arg) {
            for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                var entry = this.tryEntries[i];
                if (entry.tryLoc <= this.prev && hasOwn.call(entry, 'finallyLoc') && this.prev < entry.finallyLoc) {
                    var finallyEntry = entry;
                    break;
                }
            }
            if (finallyEntry && (type === 'break' || type === 'continue') && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc) {
                finallyEntry = null;
            }
            var record = finallyEntry ? finallyEntry.completion : {};
            record.type = type;
            record.arg = arg;
            if (finallyEntry) {
                this.method = 'next';
                this.next = finallyEntry.finallyLoc;
                return ContinueSentinel;
            }
            return this.complete(record);
        },
        complete: function (record, afterLoc) {
            if (record.type === 'throw') {
                throw record.arg;
            }
            if (record.type === 'break' || record.type === 'continue') {
                this.next = record.arg;
            } else if (record.type === 'return') {
                this.rval = this.arg = record.arg;
                this.method = 'return';
                this.next = 'end';
            } else if (record.type === 'normal' && afterLoc) {
                this.next = afterLoc;
            }
            return ContinueSentinel;
        },
        finish: function (finallyLoc) {
            for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                var entry = this.tryEntries[i];
                if (entry.finallyLoc === finallyLoc) {
                    this.complete(entry.completion, entry.afterLoc);
                    resetTryEntry(entry);
                    return ContinueSentinel;
                }
            }
        },
        'catch': function (tryLoc) {
            for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                var entry = this.tryEntries[i];
                if (entry.tryLoc === tryLoc) {
                    var record = entry.completion;
                    if (record.type === 'throw') {
                        var thrown = record.arg;
                        resetTryEntry(entry);
                    }
                    return thrown;
                }
            }
            throw new Error('illegal catch attempt');
        },
        delegateYield: function (iterable, resultName, nextLoc) {
            this.delegate = {
                iterator: values(iterable),
                resultName: resultName,
                nextLoc: nextLoc
            };
            if (this.method === 'next') {
                this.arg = undefined;
            }
            return ContinueSentinel;
        }
    };
}(typeof global === 'object' ? global : typeof window === 'object' ? window : typeof self === 'object' ? self : this);
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],113:[function(require,module,exports){
'use strict';
function hash(str) {
    var hash = 5381, i = str.length;
    while (i) {
        hash = hash * 33 ^ str.charCodeAt(--i);
    }
    return hash >>> 0;
}
module.exports = hash;
},{}],114:[function(require,module,exports){
(function (self) {
    'use strict';
    if (self.fetch) {
        return;
    }
    var support = {
        searchParams: 'URLSearchParams' in self,
        iterable: 'Symbol' in self && 'iterator' in Symbol,
        blob: 'FileReader' in self && 'Blob' in self && function () {
            try {
                new Blob();
                return true;
            } catch (e) {
                return false;
            }
        }(),
        formData: 'FormData' in self,
        arrayBuffer: 'ArrayBuffer' in self
    };
    if (support.arrayBuffer) {
        var viewClasses = [
            '[object Int8Array]',
            '[object Uint8Array]',
            '[object Uint8ClampedArray]',
            '[object Int16Array]',
            '[object Uint16Array]',
            '[object Int32Array]',
            '[object Uint32Array]',
            '[object Float32Array]',
            '[object Float64Array]'
        ];
        var isDataView = function (obj) {
            return obj && DataView.prototype.isPrototypeOf(obj);
        };
        var isArrayBufferView = ArrayBuffer.isView || function (obj) {
            return obj && viewClasses.indexOf(Object.prototype.toString.call(obj)) > -1;
        };
    }
    function normalizeName(name) {
        if (typeof name !== 'string') {
            name = String(name);
        }
        if (/[^a-z0-9\-#$%&'*+.\^_`|~]/i.test(name)) {
            throw new TypeError('Invalid character in header field name');
        }
        return name.toLowerCase();
    }
    function normalizeValue(value) {
        if (typeof value !== 'string') {
            value = String(value);
        }
        return value;
    }
    function iteratorFor(items) {
        var iterator = {
            next: function () {
                var value = items.shift();
                return {
                    done: value === undefined,
                    value: value
                };
            }
        };
        if (support.iterable) {
            iterator[Symbol.iterator] = function () {
                return iterator;
            };
        }
        return iterator;
    }
    function Headers(headers) {
        this.map = {};
        if (headers instanceof Headers) {
            headers.forEach(function (value, name) {
                this.append(name, value);
            }, this);
        } else if (Array.isArray(headers)) {
            headers.forEach(function (header) {
                this.append(header[0], header[1]);
            }, this);
        } else if (headers) {
            Object.getOwnPropertyNames(headers).forEach(function (name) {
                this.append(name, headers[name]);
            }, this);
        }
    }
    Headers.prototype.append = function (name, value) {
        name = normalizeName(name);
        value = normalizeValue(value);
        var oldValue = this.map[name];
        this.map[name] = oldValue ? oldValue + ',' + value : value;
    };
    Headers.prototype['delete'] = function (name) {
        delete this.map[normalizeName(name)];
    };
    Headers.prototype.get = function (name) {
        name = normalizeName(name);
        return this.has(name) ? this.map[name] : null;
    };
    Headers.prototype.has = function (name) {
        return this.map.hasOwnProperty(normalizeName(name));
    };
    Headers.prototype.set = function (name, value) {
        this.map[normalizeName(name)] = normalizeValue(value);
    };
    Headers.prototype.forEach = function (callback, thisArg) {
        for (var name in this.map) {
            if (this.map.hasOwnProperty(name)) {
                callback.call(thisArg, this.map[name], name, this);
            }
        }
    };
    Headers.prototype.keys = function () {
        var items = [];
        this.forEach(function (value, name) {
            items.push(name);
        });
        return iteratorFor(items);
    };
    Headers.prototype.values = function () {
        var items = [];
        this.forEach(function (value) {
            items.push(value);
        });
        return iteratorFor(items);
    };
    Headers.prototype.entries = function () {
        var items = [];
        this.forEach(function (value, name) {
            items.push([
                name,
                value
            ]);
        });
        return iteratorFor(items);
    };
    if (support.iterable) {
        Headers.prototype[Symbol.iterator] = Headers.prototype.entries;
    }
    function consumed(body) {
        if (body.bodyUsed) {
            return Promise.reject(new TypeError('Already read'));
        }
        body.bodyUsed = true;
    }
    function fileReaderReady(reader) {
        return new Promise(function (resolve, reject) {
            reader.onload = function () {
                resolve(reader.result);
            };
            reader.onerror = function () {
                reject(reader.error);
            };
        });
    }
    function readBlobAsArrayBuffer(blob) {
        var reader = new FileReader();
        var promise = fileReaderReady(reader);
        reader.readAsArrayBuffer(blob);
        return promise;
    }
    function readBlobAsText(blob) {
        var reader = new FileReader();
        var promise = fileReaderReady(reader);
        reader.readAsText(blob);
        return promise;
    }
    function readArrayBufferAsText(buf) {
        var view = new Uint8Array(buf);
        var chars = new Array(view.length);
        for (var i = 0; i < view.length; i++) {
            chars[i] = String.fromCharCode(view[i]);
        }
        return chars.join('');
    }
    function bufferClone(buf) {
        if (buf.slice) {
            return buf.slice(0);
        } else {
            var view = new Uint8Array(buf.byteLength);
            view.set(new Uint8Array(buf));
            return view.buffer;
        }
    }
    function Body() {
        this.bodyUsed = false;
        this._initBody = function (body) {
            this._bodyInit = body;
            if (!body) {
                this._bodyText = '';
            } else if (typeof body === 'string') {
                this._bodyText = body;
            } else if (support.blob && Blob.prototype.isPrototypeOf(body)) {
                this._bodyBlob = body;
            } else if (support.formData && FormData.prototype.isPrototypeOf(body)) {
                this._bodyFormData = body;
            } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
                this._bodyText = body.toString();
            } else if (support.arrayBuffer && support.blob && isDataView(body)) {
                this._bodyArrayBuffer = bufferClone(body.buffer);
                this._bodyInit = new Blob([this._bodyArrayBuffer]);
            } else if (support.arrayBuffer && (ArrayBuffer.prototype.isPrototypeOf(body) || isArrayBufferView(body))) {
                this._bodyArrayBuffer = bufferClone(body);
            } else {
                throw new Error('unsupported BodyInit type');
            }
            if (!this.headers.get('content-type')) {
                if (typeof body === 'string') {
                    this.headers.set('content-type', 'text/plain;charset=UTF-8');
                } else if (this._bodyBlob && this._bodyBlob.type) {
                    this.headers.set('content-type', this._bodyBlob.type);
                } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
                    this.headers.set('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
                }
            }
        };
        if (support.blob) {
            this.blob = function () {
                var rejected = consumed(this);
                if (rejected) {
                    return rejected;
                }
                if (this._bodyBlob) {
                    return Promise.resolve(this._bodyBlob);
                } else if (this._bodyArrayBuffer) {
                    return Promise.resolve(new Blob([this._bodyArrayBuffer]));
                } else if (this._bodyFormData) {
                    throw new Error('could not read FormData body as blob');
                } else {
                    return Promise.resolve(new Blob([this._bodyText]));
                }
            };
            this.arrayBuffer = function () {
                if (this._bodyArrayBuffer) {
                    return consumed(this) || Promise.resolve(this._bodyArrayBuffer);
                } else {
                    return this.blob().then(readBlobAsArrayBuffer);
                }
            };
        }
        this.text = function () {
            var rejected = consumed(this);
            if (rejected) {
                return rejected;
            }
            if (this._bodyBlob) {
                return readBlobAsText(this._bodyBlob);
            } else if (this._bodyArrayBuffer) {
                return Promise.resolve(readArrayBufferAsText(this._bodyArrayBuffer));
            } else if (this._bodyFormData) {
                throw new Error('could not read FormData body as text');
            } else {
                return Promise.resolve(this._bodyText);
            }
        };
        if (support.formData) {
            this.formData = function () {
                return this.text().then(decode);
            };
        }
        this.json = function () {
            return this.text().then(JSON.parse);
        };
        return this;
    }
    var methods = [
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'POST',
        'PUT'
    ];
    function normalizeMethod(method) {
        var upcased = method.toUpperCase();
        return methods.indexOf(upcased) > -1 ? upcased : method;
    }
    function Request(input, options) {
        options = options || {};
        var body = options.body;
        if (input instanceof Request) {
            if (input.bodyUsed) {
                throw new TypeError('Already read');
            }
            this.url = input.url;
            this.credentials = input.credentials;
            if (!options.headers) {
                this.headers = new Headers(input.headers);
            }
            this.method = input.method;
            this.mode = input.mode;
            if (!body && input._bodyInit != null) {
                body = input._bodyInit;
                input.bodyUsed = true;
            }
        } else {
            this.url = String(input);
        }
        this.credentials = options.credentials || this.credentials || 'omit';
        if (options.headers || !this.headers) {
            this.headers = new Headers(options.headers);
        }
        this.method = normalizeMethod(options.method || this.method || 'GET');
        this.mode = options.mode || this.mode || null;
        this.referrer = null;
        if ((this.method === 'GET' || this.method === 'HEAD') && body) {
            throw new TypeError('Body not allowed for GET or HEAD requests');
        }
        this._initBody(body);
    }
    Request.prototype.clone = function () {
        return new Request(this, { body: this._bodyInit });
    };
    function decode(body) {
        var form = new FormData();
        body.trim().split('&').forEach(function (bytes) {
            if (bytes) {
                var split = bytes.split('=');
                var name = split.shift().replace(/\+/g, ' ');
                var value = split.join('=').replace(/\+/g, ' ');
                form.append(decodeURIComponent(name), decodeURIComponent(value));
            }
        });
        return form;
    }
    function parseHeaders(rawHeaders) {
        var headers = new Headers();
        rawHeaders.split(/\r?\n/).forEach(function (line) {
            var parts = line.split(':');
            var key = parts.shift().trim();
            if (key) {
                var value = parts.join(':').trim();
                headers.append(key, value);
            }
        });
        return headers;
    }
    Body.call(Request.prototype);
    function Response(bodyInit, options) {
        if (!options) {
            options = {};
        }
        this.type = 'default';
        this.status = 'status' in options ? options.status : 200;
        this.ok = this.status >= 200 && this.status < 300;
        this.statusText = 'statusText' in options ? options.statusText : 'OK';
        this.headers = new Headers(options.headers);
        this.url = options.url || '';
        this._initBody(bodyInit);
    }
    Body.call(Response.prototype);
    Response.prototype.clone = function () {
        return new Response(this._bodyInit, {
            status: this.status,
            statusText: this.statusText,
            headers: new Headers(this.headers),
            url: this.url
        });
    };
    Response.error = function () {
        var response = new Response(null, {
            status: 0,
            statusText: ''
        });
        response.type = 'error';
        return response;
    };
    var redirectStatuses = [
        301,
        302,
        303,
        307,
        308
    ];
    Response.redirect = function (url, status) {
        if (redirectStatuses.indexOf(status) === -1) {
            throw new RangeError('Invalid status code');
        }
        return new Response(null, {
            status: status,
            headers: { location: url }
        });
    };
    self.Headers = Headers;
    self.Request = Request;
    self.Response = Response;
    self.fetch = function (input, init) {
        return new Promise(function (resolve, reject) {
            var request = new Request(input, init);
            var xhr = new XMLHttpRequest();
            xhr.onload = function () {
                var options = {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    headers: parseHeaders(xhr.getAllResponseHeaders() || '')
                };
                options.url = 'responseURL' in xhr ? xhr.responseURL : options.headers.get('X-Request-URL');
                var body = 'response' in xhr ? xhr.response : xhr.responseText;
                resolve(new Response(body, options));
            };
            xhr.onerror = function () {
                reject(new TypeError('Network request failed'));
            };
            xhr.ontimeout = function () {
                reject(new TypeError('Network request failed'));
            };
            xhr.open(request.method, request.url, true);
            if (request.credentials === 'include') {
                xhr.withCredentials = true;
            }
            if ('responseType' in xhr && support.blob) {
                xhr.responseType = 'blob';
            }
            request.headers.forEach(function (value, name) {
                xhr.setRequestHeader(name, value);
            });
            xhr.send(typeof request._bodyInit === 'undefined' ? null : request._bodyInit);
        });
    };
    self.fetch.polyfill = true;
}(typeof self !== 'undefined' ? self : this));
},{}],115:[function(require,module,exports){
(function (global){
'use strict';
var Promise = require('promise-polyfill');
if (!global.Promise) {
    global.Promise = Promise;
}
if (typeof Object.assign !== 'function') {
    Object.defineProperty(Object, 'assign', {
        value: function assign(target, varArgs) {
            'use strict';
            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }
            var to = Object(target);
            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];
                if (nextSource != null) {
                    for (var nextKey in nextSource) {
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true
    });
}
require('whatwg-fetch');
require('regenerator-runtime/runtime');
var app = require('app/init/palgrave')();
if (history.pushState) {
    app.mount('#journal-page', app.start());
}
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"app/init/palgrave":117,"promise-polyfill":34,"regenerator-runtime/runtime":112,"whatwg-fetch":114}],116:[function(require,module,exports){
'use strict';
function _defineProperty(obj, key, value) {
    if (key in obj) {
        Object.defineProperty(obj, key, {
            value: value,
            enumerable: true,
            configurable: true,
            writable: true
        });
    } else {
        obj[key] = value;
    }
    return obj;
}
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
var _require = require('lib/helpers'), curry = _require.curry, compose = _require.compose, SuperFactory = _require.SuperFactory, Maybe = _require.Maybe, createCaseDescriptors = _require.createCaseDescriptors;
var c = require('app/styles/constants');
var _require2 = require('app/styles/lib/helpers'), CSSObjectToString = _require2.CSSObjectToString;
var jsonDOM = require('lib/json-dom');
var Attrs = function Attrs(attrs) {
    return { attributes: attrs };
};
var ClassName = function ClassName(className) {
    return { attributes: { class: className } };
};
var StyleChildren = function StyleChildren() {
    for (var _len = arguments.length, children = Array(_len), _key = 0; _key < _len; _key++) {
        children[_key] = arguments[_key];
    }
    return { children: children };
};
var svgIcon16 = jsonDOM.$_svg.bind({
    attributes: {
        width: 16,
        height: 16,
        viewBox: '0 0 16 16'
    }
});
var svgIcon32 = jsonDOM.$_svg.bind({
    attributes: {
        width: 32,
        height: 32,
        viewBox: '0 0 32 32'
    }
});
var svgIcon1000 = jsonDOM.$_svg.bind({
    attributes: {
        width: 16,
        height: 16,
        viewBox: '0 0 1000 1000',
        transform: 'scale(1, -1)'
    }
});
jsonDOM.$_Icons = {
    arrowDown: svgIcon32.bind({
        append: [jsonDOM.$_path.bind({
                attributes: {
                    fill: 'inherit',
                    'fill-rule': 'evenodd',
                    d: 'M28 11.5c0-0.38-0.142-0.76-0.432-1.052-0.58-0.59-1.528-0.598-2.118-0.016l-9.492 10.166-9.404-10.162c-0.588-0.584-1.538-0.58-2.118 0.008-0.588 0.59-0.578 1.54 0.008 2.122l10.104 10.86c0.776 0.772 2.028 0.774 2.808 0.006l10.196-10.862c0.298-0.294 0.448-0.682 0.448-1.070z'
                }
            })()]
    }),
    arrowRight: svgIcon32.bind({
        append: [jsonDOM.$_path.bind({
                attributes: {
                    fill: 'inherit',
                    'fill-rule': 'evenodd',
                    d: 'M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z'
                }
            })()]
    }),
    externalLink: svgIcon16.bind({
        append: [jsonDOM.$_path.bind({
                attributes: {
                    fill: 'inherit',
                    'fill-rule': 'evenodd',
                    d: 'M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z'
                }
            })()]
    })
};
var rowTrait = function rowTrait() {
    return ClassName('row');
};
var liveAreaTrait = function liveAreaTrait() {
    return ClassName('live-area');
};
var liveAreaWrapperTrait = function liveAreaWrapperTrait() {
    return ClassName('live-area-wrapper');
};
var columnTrait = function columnTrait() {
    return ClassName('column');
};
var columnModifierTrait = curry(function (breakpoint, size) {
    return ClassName(breakpoint + '-' + size);
});
jsonDOM.$_div.row = jsonDOM.$_div.bind(rowTrait());
jsonDOM.$_section.row = jsonDOM.$_section.bind(rowTrait());
jsonDOM.$_div.liveArea = jsonDOM.$_div.bind(liveAreaTrait());
jsonDOM.$_section.liveArea = jsonDOM.$_section.bind(liveAreaTrait());
jsonDOM.$_div.liveAreaWrapper = jsonDOM.$_div.bind(liveAreaWrapperTrait());
jsonDOM.$_section.liveAreaWrapper = jsonDOM.$_section.bind(liveAreaWrapperTrait());
jsonDOM.$_aside.column = jsonDOM.$_aside.bind(columnTrait());
jsonDOM.$_div.column = jsonDOM.$_div.bind(columnTrait());
jsonDOM.$_section.column = jsonDOM.$_section.bind(columnTrait());
var columnSmall = columnModifierTrait('small');
var columnMedium = columnModifierTrait('medium');
var columnLarge = columnModifierTrait('large');
jsonDOM.$_Traits = {
    columnSmall: columnSmall,
    columnMedium: columnMedium,
    columnLarge: columnLarge
};
jsonDOM.$_div.column.m_1 = jsonDOM.$_div.column.bind(columnMedium(1));
jsonDOM.$_div.column.m_2 = jsonDOM.$_div.column.bind(columnMedium(2));
jsonDOM.$_div.column.m_3 = jsonDOM.$_div.column.bind(columnMedium(3));
jsonDOM.$_div.column.m_4 = jsonDOM.$_div.column.bind(columnMedium(4));
jsonDOM.$_div.column.m_5 = jsonDOM.$_div.column.bind(columnMedium(5));
jsonDOM.$_div.column.m_6 = jsonDOM.$_div.column.bind(columnMedium(6));
jsonDOM.$_div.column.m_7 = jsonDOM.$_div.column.bind(columnMedium(7));
jsonDOM.$_div.column.m_8 = jsonDOM.$_div.column.bind(columnMedium(8));
jsonDOM.$_div.column.m_9 = jsonDOM.$_div.column.bind(columnMedium(9));
jsonDOM.$_div.column.m_10 = jsonDOM.$_div.column.bind(columnMedium(10));
jsonDOM.$_div.column.m_11 = jsonDOM.$_div.column.bind(columnMedium(11));
jsonDOM.$_div.column.m_12 = jsonDOM.$_div.column.bind(columnMedium(12));
jsonDOM.$_section.column.m_1 = jsonDOM.$_section.column.bind(columnMedium(1));
jsonDOM.$_section.column.m_2 = jsonDOM.$_section.column.bind(columnMedium(2));
jsonDOM.$_section.column.m_3 = jsonDOM.$_section.column.bind(columnMedium(3));
jsonDOM.$_section.column.m_4 = jsonDOM.$_section.column.bind(columnMedium(4));
jsonDOM.$_section.column.m_5 = jsonDOM.$_section.column.bind(columnMedium(5));
jsonDOM.$_section.column.m_6 = jsonDOM.$_section.column.bind(columnMedium(6));
jsonDOM.$_section.column.m_7 = jsonDOM.$_section.column.bind(columnMedium(7));
jsonDOM.$_section.column.m_8 = jsonDOM.$_section.column.bind(columnMedium(8));
jsonDOM.$_section.column.m_9 = jsonDOM.$_section.column.bind(columnMedium(9));
jsonDOM.$_section.column.m_10 = jsonDOM.$_section.column.bind(columnMedium(10));
jsonDOM.$_section.column.m_11 = jsonDOM.$_section.column.bind(columnMedium(11));
jsonDOM.$_section.column.m_12 = jsonDOM.$_section.column.bind(columnMedium(12));
jsonDOM.$_aside.column.m_1 = jsonDOM.$_aside.column.bind(columnMedium(1));
jsonDOM.$_aside.column.m_2 = jsonDOM.$_aside.column.bind(columnMedium(2));
jsonDOM.$_aside.column.m_3 = jsonDOM.$_aside.column.bind(columnMedium(3));
jsonDOM.$_aside.column.m_4 = jsonDOM.$_aside.column.bind(columnMedium(4));
jsonDOM.$_aside.column.m_5 = jsonDOM.$_aside.column.bind(columnMedium(5));
jsonDOM.$_aside.column.m_6 = jsonDOM.$_aside.column.bind(columnMedium(6));
jsonDOM.$_aside.column.m_7 = jsonDOM.$_aside.column.bind(columnMedium(7));
jsonDOM.$_aside.column.m_8 = jsonDOM.$_aside.column.bind(columnMedium(8));
jsonDOM.$_aside.column.m_9 = jsonDOM.$_aside.column.bind(columnMedium(9));
jsonDOM.$_aside.column.m_10 = jsonDOM.$_aside.column.bind(columnMedium(10));
jsonDOM.$_aside.column.m_11 = jsonDOM.$_aside.column.bind(columnMedium(11));
jsonDOM.$_aside.column.m_12 = jsonDOM.$_aside.column.bind(columnMedium(12));
var buttonTrait = function buttonTrait() {
    return ClassName('btn');
};
var buttonPrimaryTrait = function buttonPrimaryTrait() {
    return ClassName('btn-primary');
};
var buttonSecondaryTrait = function buttonSecondaryTrait() {
    return ClassName('btn-secondary');
};
var buttonBlockTrait = function buttonBlockTrait() {
    return ClassName('btn-block');
};
var buttonInlineTrait = function buttonInlineTrait() {
    return ClassName('btn-inline');
};
var buttonWithIconTrait = function buttonWithIconTrait() {
    return ClassName('btn-icon');
};
jsonDOM.$_a.button = jsonDOM.$_a.bind(buttonTrait());
jsonDOM.$_a.button.primary = jsonDOM.$_a.button.bind(buttonPrimaryTrait());
jsonDOM.$_a.button.primary.icon = jsonDOM.$_a.button.primary.bind(buttonWithIconTrait());
jsonDOM.$_a.button.primary.block = jsonDOM.$_a.button.primary.bind(buttonBlockTrait());
jsonDOM.$_a.button.primary.block.icon = jsonDOM.$_a.button.primary.block.bind(buttonWithIconTrait());
jsonDOM.$_a.button.primary.inline = jsonDOM.$_a.button.primary.bind(buttonInlineTrait());
jsonDOM.$_a.button.primary.inline.icon = jsonDOM.$_a.button.primary.inline.bind(buttonWithIconTrait());
jsonDOM.$_a.button.secondary = jsonDOM.$_a.button.bind(buttonSecondaryTrait());
jsonDOM.$_a.button.secondary.icon = jsonDOM.$_a.button.secondary.bind(buttonWithIconTrait());
jsonDOM.$_a.button.secondary.block = jsonDOM.$_a.button.secondary.bind(buttonBlockTrait());
jsonDOM.$_a.button.secondary.block.icon = jsonDOM.$_a.button.secondary.block.bind(buttonWithIconTrait());
jsonDOM.$_a.button.secondary.inline = jsonDOM.$_a.button.secondary.bind(buttonInlineTrait());
jsonDOM.$_a.button.secondary.inline.icon = jsonDOM.$_a.button.secondary.inline.bind(buttonWithIconTrait());
jsonDOM.$_Layout = {
    liveAreaWrapper: compose(jsonDOM.$_div.liveAreaWrapper.call, jsonDOM.$_div.liveArea.call),
    liveAreaWrapperSection: compose(jsonDOM.$_section.liveAreaWrapper.call, jsonDOM.$_div.liveArea.call)
};
jsonDOM.$_div.box = jsonDOM.$_div.bind(ClassName('box')).bind({ styles: ['box'] });
jsonDOM.$_section.box = jsonDOM.$_section.bind(ClassName('box')).bind({ styles: ['box'] });
jsonDOM.$_JournalPage = jsonDOM.$_div.bind(ClassName('journal-page'));
jsonDOM.$_JournalHeader = jsonDOM.$_div.bind(ClassName('journal-header'));
jsonDOM.$_JournalStage = jsonDOM.$_div.bind(ClassName('journal-stage'));
jsonDOM.$_JournalContent = jsonDOM.$_div.bind(ClassName('journal-content'));
jsonDOM.$_Button = jsonDOM.$_button.bind({ styles: [c.buttonBase] });
jsonDOM.$_LinkedButton = jsonDOM.$_a.bind({ styles: [c.buttonBase] });
jsonDOM.$_ButtonLabel = jsonDOM.$_span.bind({ styles: [c.buttonLabel] });
jsonDOM.$_ButtonIcon = jsonDOM.$_span.bind({ styles: [c.buttonIcon] });
jsonDOM.$_headline = jsonDOM.$_h1.bind({ styles: [c.headline] });
var Styles = function Styles(styles) {
    return { styles: styles };
};
var mapStyle = function mapStyle(styleFunction) {
    return Styles([styleFunction.name]);
};
var customMerge = function customMerge(key, left, right) {
    var patterns = {
        'children': function children() {
            return [].concat(_toConsumableArray(left), _toConsumableArray(right));
        }
    };
    return patterns[key] ? patterns[key]() : right;
};
var Style = SuperFactory(customMerge, true)(function (styleObject) {
    return function (context) {
        var style = Object.create({}, createCaseDescriptors(styleObject));
        return [
            style.selector + ' {\n    /* ' + style.name + ' */\n    ' + CSSObjectToString(style.styles) + '\n}\n',
            (style.children || []).map(function (child) {
                return child.selector.replace(/&/, style.selector) + ' {' + CSSObjectToString(child.styles) + '}';
            }).join(''),
            (style.context || []).map(function (context) {
                return context.query + ' {' + (context.styles.children || []).map(function (child) {
                    return child.selector.replace(/&/, style.selector) + ' {' + CSSObjectToString(child.styles) + '}';
                }).join('') + '}';
            }).join('')
        ].join('');
    };
});
var MediaQuery = SuperFactory(null, true)(function (mediaQuery) {
    return function (styles) {
        return { context: _defineProperty({}, mediaQuery.query, [styles]) };
    };
});
MediaQuery.screen = {
    minWidth459: MediaQuery.bind({ query: 'screen and (min-width: 459px)' }),
    minWidth1280: MediaQuery.bind({ query: 'screen and (min-width: 1280px)' })
};
var Padding = function Padding() {
    if (!arguments.length)
        return;
    var paddingTop = arguments.length <= 0 ? undefined : arguments[0];
    var paddingRight = (arguments.length <= 1 ? undefined : arguments[1]) || paddingTop;
    var paddingBottom = (arguments.length <= 2 ? undefined : arguments[2]) || paddingTop;
    var paddingLeft = (arguments.length <= 3 ? undefined : arguments[3]) || paddingRight;
    return {
        paddingBottom: paddingBottom,
        paddingLeft: paddingLeft,
        paddingRight: paddingRight,
        paddingTop: paddingTop
    };
};
var addStyle = function addStyle(dep) {
    return jsonDOM.$_style.bind({ props: { innerHTML: dep } });
};
var Action = function Action(name, data) {
    return Maybe(name).map(function (name) {
        return Object.create({}, createCaseDescriptors({
            name: name,
            data: data || {}
        }));
    }).getOrElse(null);
};
module.exports = {
    addStyle: addStyle,
    Action: Action,
    Attrs: Attrs,
    ClassName: ClassName,
    StyleChildren: StyleChildren,
    Style: Style,
    Styles: Styles,
    mapStyle: mapStyle,
    Padding: Padding,
    MediaQuery: MediaQuery
};
module.exports.jsonDOM = jsonDOM;
},{"app/styles/constants":121,"app/styles/lib/helpers":122,"lib/helpers":140,"lib/json-dom":141}],117:[function(require,module,exports){
'use strict';
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
var journalStore = require('./store');
var createApp = require('lib/create-app');
var _require = require('lib/helpers'), Maybe = _require.Maybe;
var actionMap = {
    log: function log(event, context, data) {
        return console.log(event, context, data);
    },
    navigate: function navigate(event, context, data) {
        if (window.journalPageGlobalKeyState)
            return;
        event.preventDefault();
        event.target.className += ' menu-item--pending';
        context.emit('navigate', data);
    },
    toggle: function toggle(event, context, data) {
        event.preventDefault();
        event.target.nextSibling.classList.toggle('show');
    },
    watchSize: function watchSize(event, context, data) {
    },
    addToCart: function addToCart(event, context, data) {
        event.preventDefault();
        context.emit('addToCart', data);
    }
};
var __ = require('lib/element-core')(actionMap);
var _require$jsonDOM = require('app/init/common').jsonDOM, $_div = _require$jsonDOM.$_div, $_span = _require$jsonDOM.$_span, $_ul = _require$jsonDOM.$_ul, $_li = _require$jsonDOM.$_li, $_b = _require$jsonDOM.$_b, $_a = _require$jsonDOM.$_a, $_h1 = _require$jsonDOM.$_h1, $_nav = _require$jsonDOM.$_nav, $_JournalPage = _require$jsonDOM.$_JournalPage, $_JournalHeader = _require$jsonDOM.$_JournalHeader, $_JournalStage = _require$jsonDOM.$_JournalStage, $_JournalContent = _require$jsonDOM.$_JournalContent, $_Icons = _require$jsonDOM.$_Icons;
var _require2 = require('app/init/common'), Action = _require2.Action, ClassName = _require2.ClassName, addStyle = _require2.addStyle;
var _require3 = require('app/routes/actions/generic'), currentLinkClassTraitF = _require3.currentLinkClassTraitF, LinkList = _require3.LinkList;
var _require4 = require('./pre-render-transform'), postTransformTree = _require4.postTransformTree;
var ImpactFactor = function ImpactFactor(impactFactor) {
    return Maybe(impactFactor).map(function (impactFactor) {
        return $_div.bind(ClassName('impact-factor'))($_span.bind(ClassName('impact-factor__value'))(' ' + impactFactor.latestImpactFactor), $_span.bind(ClassName('impact-factor__info'))('Impact Factor ' + impactFactor.latestImpactFactorYear, Maybe(impactFactor.copyright).map(function (copyright) {
            return $_span.bind(ClassName('impact-factor__copyright'))(copyright);
        }).getOrElse(null)), addStyle(require('app/styles/palgrave/impact-factor.css')())());
    }).getOrElse(null);
};
var MainNavigation = function MainNavigation(linkList) {
    return $_nav.bind(ClassName('journal-navigation'))($_div.row($_ul.bind(ClassName('column'))([$_li.bind(ClassName('journal-navigation-header'))($_a($_b('Navigation'), $_Icons.arrowDown.bind(ClassName('journal-navigation-arrow-icon-size16'))()))].concat(_toConsumableArray(linkList)))), addStyle(require('app/styles/palgrave/main-navigation.css')())());
};
var JournalBasePage = function JournalBasePage(state, emit, children) {
    var journal = state.journal, currentRoute = state.currentRoute, pendingRoute = state.pendingRoute;
    if (!state || !state.journal)
        return null;
    var currentParentRoute = currentRoute.split('/')[4] || '';
    var currentLinkClassTrait = currentLinkClassTraitF({
        currentRoute: currentRoute,
        pendingRoute: pendingRoute,
        currentParentRoute: currentParentRoute
    });
    var pageTree = postTransformTree({ currentLinkClassTrait: currentLinkClassTrait })($_JournalPage($_JournalHeader.bind({ append: [addStyle(require('app/styles/palgrave/header.css')(journal.banner))()] })($_JournalStage($_div.bind(ClassName('live-area row'))($_div.column($_h1(journal.title), ImpactFactor(journal.impactFactor))), addStyle(require('app/styles/palgrave/stage.css')())()), MainNavigation(LinkList({
        currentLinkClassTrait: currentLinkClassTrait,
        parent: true
    })(journal.sitemap.main))), $_JournalContent(children)));
    return __({ emit: emit })(pageTree);
};
var JournalErrorPage = function JournalErrorPage(state, emit, children) {
    if (!state)
        return null;
    var pageTree = postTransformTree({})($_JournalPage($_JournalContent({
        tagName: 'h2',
        children: [state.errorMessage]
    }, 'live' !== 'live' ? {
        tagName: 'pre',
        children: [JSON.stringify(state.error, null, 2)]
    } : null)));
    return __()(pageTree);
};
var JournalPage = function JournalPage(state, emit) {
    if (!state.journal)
        return JournalErrorPage(state, emit);
    var mainPage = state.params.mainPage || '/';
    var subPage = state.params.subPage || null;
    var page = state.journal['pages/' + mainPage + (subPage ? '/' + subPage : '')];
    return JournalBasePage(state, emit, page ? page.content : null);
};
module.exports = function () {
    return createApp([
        [
            '/:sitePrefix/journal/:id',
            JournalPage
        ],
        [
            '/:sitePrefix/journal/:id/:mainPage',
            JournalPage
        ],
        [
            '/:sitePrefix/journal/:id/:mainPage/:subPage',
            JournalPage
        ]
    ], journalStore);
};
},{"./pre-render-transform":118,"./store":119,"app/init/common":116,"app/routes/actions/generic":120,"app/styles/palgrave/header.css":130,"app/styles/palgrave/impact-factor.css":131,"app/styles/palgrave/main-navigation.css":133,"app/styles/palgrave/stage.css":135,"lib/create-app":138,"lib/element-core":139,"lib/helpers":140}],118:[function(require,module,exports){
'use strict';
var _styleMap;
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
function _defineProperty(obj, key, value) {
    if (key in obj) {
        Object.defineProperty(obj, key, {
            value: value,
            enumerable: true,
            configurable: true,
            writable: true
        });
    } else {
        obj[key] = value;
    }
    return obj;
}
var hash = require('string-hash');
var _require = require('lib/helpers'), isEmpty = _require.isEmpty;
var _require2 = require('app/routes/actions/generic'), LinkList = _require2.LinkList;
var _require3 = require('app/init/common'), ClassName = _require3.ClassName, addStyle = _require3.addStyle;
var _require$jsonDOM = require('app/init/common').jsonDOM, $_div = _require$jsonDOM.$_div, $_ul = _require$jsonDOM.$_ul, $_li = _require$jsonDOM.$_li, $_section = _require$jsonDOM.$_section, $_h1 = _require$jsonDOM.$_h1, $_nav = _require$jsonDOM.$_nav, $_Icons = _require$jsonDOM.$_Icons;
var c = require('app/styles/constants');
var buttonStyles = require('app/styles/palgrave/button.css');
var typographyStyles = require('app/styles/palgrave/typography.css');
var boxStyles = require('app/styles/palgrave/box2.css');
var priceTable = require('app/styles/palgrave/price-table.css');
var detailsStyles = require('app/styles/palgrave/details.css');
var styleMap = (_styleMap = {}, _defineProperty(_styleMap, c.article, function () {
    return require('app/styles/palgrave/article.css')();
}), _defineProperty(_styleMap, c.issueArticle, function () {
    return require('app/styles/palgrave/issue-article.css')();
}), _defineProperty(_styleMap, c.box, function () {
    return require('app/styles/palgrave/boxes.css')();
}), _defineProperty(_styleMap, c.collection, function () {
    return require('app/styles/palgrave/collection.css')();
}), _defineProperty(_styleMap, c.buttonBase, buttonStyles.buttonBase), _defineProperty(_styleMap, c.buttonLabel, buttonStyles.buttonLabel), _defineProperty(_styleMap, c.buttonIcon, buttonStyles.buttonIcon), _defineProperty(_styleMap, c.headline, typographyStyles.headline), _defineProperty(_styleMap, c.asideHeadline, typographyStyles.asideHeadline), _defineProperty(_styleMap, c.box2, boxStyles.box2), _defineProperty(_styleMap, c.priceTable, priceTable.priceTable), _defineProperty(_styleMap, c.priceTableSmallPrint, priceTable.priceTableSmallPrint), _defineProperty(_styleMap, c.priceCell, priceTable.priceCell), _defineProperty(_styleMap, c.productTitle, priceTable.productTitle), _defineProperty(_styleMap, c.details, detailsStyles.details), _defineProperty(_styleMap, c.detailsSummary, detailsStyles.summary), _defineProperty(_styleMap, c.detailsSummaryLabel, detailsStyles.summaryLabel), _defineProperty(_styleMap, c.detailsMarker, detailsStyles.marker), _styleMap);
var resolveComponent = function resolveComponent(_ref) {
    var currentLinkClassTrait = _ref.currentLinkClassTrait;
    return function (node) {
        var componentMap = {
            icon: function icon() {
                return $_Icons.externalLink();
            },
            article: function article(_article) {
                return $_section.bind(ClassName('article')).bind({ styles: ['article'] })([
                    $_h1(_article.data.title),
                    $_section.bind(ClassName('article-body' + (_article.data.class ? ' ' + _article.data.class : '')))(_article.data.nodes)
                ]);
            },
            collection: function collection(_collection) {
                return $_section.bind(ClassName('collection')).bind({ styles: ['collection'] })([
                    $_h1(_collection.data.title),
                    $_div.bind({ props: { innerHTML: _collection.data.html } })()
                ].concat(_toConsumableArray(_collection.children)));
            },
            navigation: function navigation(linkList) {
                return $_nav.bind(ClassName('journal-subnav'))([
                    $_div.bind(ClassName('live'))($_ul.bind(ClassName('ul'))([$_li.bind(ClassName('journal-navigation-header'))('Header')].concat(_toConsumableArray(LinkList({ currentLinkClassTrait: currentLinkClassTrait })(linkList.data))))),
                    addStyle(require('app/styles/palgrave/sub-navigation.css')())()
                ]);
            }
        };
        if (node.type) {
            if (componentMap[node.type]) {
                node = componentMap[node.type](node);
            }
        }
        return node;
    };
};
var resolveStyles = function resolveStyles(node, _ref2) {
    var parent = _ref2.parent;
    if (node.styles) {
        return node.styles.map(function (styleName) {
            var style = styleMap[styleName];
            if (style) {
                var renderedStyle = style({
                    parent: parent,
                    self: node
                });
                if (typeof renderedStyle === 'function') {
                    var identifierClass = styleName + '-' + hash(node.path);
                    if (node.attributes) {
                        node.attributes.class = ((node.attributes.class || '') + ' ' + identifierClass).trim();
                    } else {
                        node.attributes = { class: identifierClass };
                    }
                    return renderedStyle.bind({ selector: '.' + identifierClass });
                } else {
                    return renderedStyle;
                }
            } else {
                return null;
            }
        });
    }
};
var isOutlineSection = function isOutlineSection(tagName) {
    return [
        'section',
        'aside',
        'nav',
        'article'
    ].indexOf(tagName) !== -1;
};
var isHeadline = function isHeadline(tagName) {
    return [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6'
    ].indexOf(tagName) !== -1;
};
var outliner = function outliner(node, _ref3) {
    var _ref3$outlineLevel = _ref3.outlineLevel, outlineLevel = _ref3$outlineLevel === undefined ? 1 : _ref3$outlineLevel, _ref3$lowestOutlineLe = _ref3.lowestOutlineLevel, lowestOutlineLevel = _ref3$lowestOutlineLe === undefined ? Infinity : _ref3$lowestOutlineLe;
    outlineLevel = isOutlineSection(node.tagName) ? outlineLevel + 1 : outlineLevel;
    if (isHeadline(node.tagName)) {
        var outlineLevelDiff = parseInt(node.tagName.replace('h', ''), 10) - lowestOutlineLevel;
        if (outlineLevelDiff > 0) {
            outlineLevel = outlineLevel + outlineLevelDiff;
        }
        node.tagName = 'h' + outlineLevel;
    }
    return {
        outlineLevel: outlineLevel,
        lowestOutlineLevel: lowestOutlineLevel
    };
};
var postTransformTree = function postTransformTree(_ref4) {
    var currentLinkClassTrait = _ref4.currentLinkClassTrait;
    return function (node) {
        var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
        var level = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
        var index = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
        var outline = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
        var styles = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : [];
        if (!isEmpty(node)) {
            node = JSON.parse(JSON.stringify(node));
            if (node) {
                node = node.value || node;
                if (typeof node !== 'string') {
                    node.index = index + 1;
                    node.level = level;
                    node.path = (parent ? parent.path : '') + '-' + node.level + '-' + node.index + '-';
                    node = resolveComponent({ currentLinkClassTrait: currentLinkClassTrait })(node);
                    outline = outliner(node, outline);
                    var resolvedStyles = resolveStyles(node, { parent: parent });
                    if (resolvedStyles) {
                        if (resolvedStyles[0] && resolvedStyles[0].expose) {
                            node.styles = resolvedStyles[0].expose().styles;
                            node.childStyles = resolvedStyles[0].expose().children;
                            styles.push(resolvedStyles[0]());
                        } else {
                            node.styles = resolvedStyles;
                            styles.push(resolvedStyles);
                        }
                    }
                    if (node.children) {
                        level++;
                        node.children = node.children.map(function (child, index) {
                            return postTransformTree({ currentLinkClassTrait: currentLinkClassTrait })(child, node, level, index, outline, styles);
                        });
                    }
                }
            }
        } else {
            return null;
        }
        if (parent === null) {
            node.children.push({
                tagName: 'style',
                props: { innerHTML: styles.join('\n') }
            });
        }
        return node;
    };
};
module.exports = { postTransformTree: postTransformTree };
},{"app/init/common":116,"app/routes/actions/generic":120,"app/styles/constants":121,"app/styles/palgrave/article.css":123,"app/styles/palgrave/box2.css":124,"app/styles/palgrave/boxes.css":125,"app/styles/palgrave/button.css":126,"app/styles/palgrave/collection.css":127,"app/styles/palgrave/details.css":129,"app/styles/palgrave/issue-article.css":132,"app/styles/palgrave/price-table.css":134,"app/styles/palgrave/sub-navigation.css":136,"app/styles/palgrave/typography.css":137,"lib/helpers":140,"string-hash":113}],119:[function(require,module,exports){
(function (global){
'use strict';
var _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
            if (Object.prototype.hasOwnProperty.call(source, key)) {
                target[key] = source[key];
            }
        }
    }
    return target;
};
function _asyncToGenerator(fn) {
    return function () {
        var gen = fn.apply(this, arguments);
        return new Promise(function (resolve, reject) {
            function step(key, arg) {
                try {
                    var info = gen[key](arg);
                    var value = info.value;
                } catch (error) {
                    reject(error);
                    return;
                }
                if (info.done) {
                    resolve(value);
                } else {
                    return Promise.resolve(value).then(function (value) {
                        step('next', value);
                    }, function (err) {
                        step('throw', err);
                    });
                }
            }
            return step('next');
        });
    };
}
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
var promiseRetry = require('promise-retry');
var window = require('global/window');
var document = require('global/document');
var isClient = !!document.activeElement;
var queryString = require('query-string');
var _require = require('lib/helpers'), safePush = _require.safePush, path = _require.path, setCookie = _require.setCookie;
var safeDataLayerPush = function safeDataLayerPush(window, event) {
    window.dataLayer = safePush(window.dataLayer)(event);
};
var parsed = isClient ? queryString.parse(location.search) : {};
var clientDebug = function clientDebug() {
    return isClient ? parsed['debug'] || window.clientDebug : false;
};
var diffArray = function diffArray() {
    var arrayA = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
    var arrayB = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
    return arrayA.reduce(function (diff, current) {
        if (arrayB.indexOf(current) === -1)
            diff.push(current);
        return diff;
    }, []);
};
var addProduct = function addProduct(_ref) {
    var product = _ref.product;
    return promiseRetry(function (retry, number) {
        return fetch('/app-jp/api/basket/product', {
            method: 'POST',
            headers: new Headers({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(product),
            credentials: 'same-origin'
        }).then(function (res) {
            if (res.status !== 200) {
                throw res;
            } else {
                return res;
            }
        }).catch(retry);
    }, {
        retries: 10,
        factor: 2,
        minTimeout: 100,
        maxTimeout: 4000
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        return data;
    }).catch(function (err) {
        console.error('Error on adding product: ' + err);
        return err;
    });
};
var getJournal = function getJournal(journalId, query) {
    var sitePrefix = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'gp';
    return promiseRetry(function (retry, number) {
        return fetch('/app-jp/api/' + sitePrefix + '/journal/' + journalId + '?' + queryString.stringify(query), { credentials: 'same-origin' }).then(function (res) {
            if (res.status !== 200) {
                throw res;
            } else {
                return res;
            }
        }).catch(retry);
    }, {
        retries: 10,
        factor: 2,
        minTimeout: 100,
        maxTimeout: 4000
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        return data;
    }).catch(console.log);
};
var DEPS = function () {
    var stage = [
        'title',
        'impactFactor',
        'banner'
    ];
    var description = [
        'issns',
        'usp',
        'editors',
        'headline',
        'description',
        'publisher'
    ];
    var defaultPage = [
        'id',
        'links'
    ].concat(stage);
    return [].concat(_toConsumableArray(defaultPage), description);
}();
var createPagePath = function createPagePath(route) {
    return 'pages/' + route;
};
module.exports = function (state, emitter) {
    state.journal = window.initialAppState ? window.initialAppState.journal : {};
    state.config = window.initialAppState ? window.initialAppState.config : {};
    state.currentRoute = global.location ? global.location.pathname : '/';
    var route = state.currentRoute.match(/^\/([a-z]{2})\/journal\/[0-9]+(\/?.*)/);
    state.sitePrefix = route && route[1] ? route[1] : 'gp';
    emitter.on('addToCart', function () {
        var _ref2 = _asyncToGenerator(regeneratorRuntime.mark(function _callee(_ref3) {
            var product = _ref3.product;
            var response, basketId;
            return regeneratorRuntime.wrap(function _callee$(_context) {
                while (1) {
                    switch (_context.prev = _context.next) {
                    case 0:
                        _context.next = 2;
                        return addProduct({ product: product });
                    case 2:
                        response = _context.sent;
                        if (!(response.status && response.status > 200)) {
                            _context.next = 5;
                            break;
                        }
                        return _context.abrupt('return');
                    case 5:
                        basketId = path([
                            'data',
                            'addProduct',
                            'id'
                        ], response);
                        setCookie('SPRCOMBASKET', basketId, { domain: '.palgrave.com' });
                        product.prices.map(function (price) {
                            safeDataLayerPush(window, {
                                'event': 'addToCart',
                                'ecommerce': {
                                    'currencyCode': price.currency,
                                    'add': {
                                        'products': [{
                                                'name': product.name,
                                                'id': product.id.toString(),
                                                'price': price.bestPrice,
                                                'brand': product.brand,
                                                'category': product.category,
                                                'variant': product.variant,
                                                'quantity': 1
                                            }]
                                    }
                                }
                            });
                        });
                        setTimeout(function () {
                            window.location.href = 'https://' + state.config.checkoutUrl + '/checkout';
                        }, 0);
                    case 9:
                    case 'end':
                        return _context.stop();
                    }
                }
            }, _callee, undefined);
        }));
        return function (_x4) {
            return _ref2.apply(this, arguments);
        };
    }());
    if (isClient) {
        document.addEventListener('keydown', function (event) {
            window.journalPageGlobalKeyState = true;
        }, false);
        document.addEventListener('keyup', function (event) {
            window.journalPageGlobalKeyState = false;
        }, false);
    }
    emitter.on('navigate', function (link) {
        emitter.emit('fetchJournal', link);
        window.history.pushState(null, '', link.href);
    });
    window.onpopstate = function (event) {
        var match = global.location.href.match(/\/([a-z]{2})\/journal\/[0-9]+\/?(.*)$/);
        emitter.emit('fetchJournal', {
            href: match[0],
            route: match[2]
        });
    };
    emitter.on('fetchJournal', function () {
        var _ref4 = _asyncToGenerator(regeneratorRuntime.mark(function _callee2(link) {
            var nextRouteConfig, pagePath, query;
            return regeneratorRuntime.wrap(function _callee2$(_context2) {
                while (1) {
                    switch (_context2.prev = _context2.next) {
                    case 0:
                        nextRouteConfig = DEPS;
                        pagePath = createPagePath(link.route);
                        if (nextRouteConfig.indexOf(pagePath) === -1) {
                            nextRouteConfig.push(pagePath);
                        }
                        query = { pick: diffArray(nextRouteConfig, Object.keys(state.journal)) };
                        if (state.journal.isPartialResponse) {
                            query.pick.push(pagePath);
                        }
                        if (!query.pick.length) {
                            _context2.next = 20;
                            break;
                        }
                        _context2.prev = 6;
                        _context2.t0 = _extends;
                        _context2.t1 = {};
                        _context2.t2 = state.journal;
                        _context2.next = 12;
                        return getJournal(state.journal.id, query, state.sitePrefix);
                    case 12:
                        _context2.t3 = _context2.sent;
                        state.journal = (0, _context2.t0)(_context2.t1, _context2.t2, _context2.t3);
                        if (state.journal.isPartialResponse) {
                            setTimeout(function () {
                                emitter.emit('fetchJournal', link);
                            }, 1000);
                        }
                        _context2.next = 20;
                        break;
                    case 17:
                        _context2.prev = 17;
                        _context2.t4 = _context2['catch'](6);
                        console.error(_context2.t4);
                    case 20:
                        state.currentRoute = link.href;
                        emitter.emit('render');
                    case 22:
                    case 'end':
                        return _context2.stop();
                    }
                }
            }, _callee2, undefined, [[
                    6,
                    17
                ]]);
        }));
        return function (_x5) {
            return _ref4.apply(this, arguments);
        };
    }());
};
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"global/document":30,"global/window":31,"lib/helpers":140,"promise-retry":35,"query-string":40}],120:[function(require,module,exports){
'use strict';
var _require = require('lib/helpers'), find = _require.find, path = _require.path, Maybe = _require.Maybe, prop = _require.prop;
var _require2 = require('app/init/common'), Action = _require2.Action, ClassName = _require2.ClassName;
var c = require('app/styles/constants');
var _require$jsonDOM = require('app/init/common').jsonDOM, $_a = _require$jsonDOM.$_a, $_div = _require$jsonDOM.$_div, $_headline = _require$jsonDOM.$_headline, $_section = _require$jsonDOM.$_section, $_li = _require$jsonDOM.$_li, $_img = _require$jsonDOM.$_img, $_span = _require$jsonDOM.$_span, $_Icons = _require$jsonDOM.$_Icons;
var findGlobalRoute = function findGlobalRoute(links, route) {
    return find(function (link) {
        return link.route.indexOf('/' + route) !== -1;
    })(links);
};
var internalLinkTrait = function internalLinkTrait(_ref) {
    var _ref$route = _ref.route, route = _ref$route === undefined ? '' : _ref$route, _ref$href = _ref.href, href = _ref$href === undefined ? '' : _ref$href;
    return {
        attributes: { href: href },
        events: {
            click: [Action('navigate', {
                    route: route,
                    href: href
                })]
        }
    };
};
var gaInternalClickTrackingTrait = function gaInternalClickTrackingTrait() {
    var targetName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
    return {
        attributes: {
            'data-track': 'click',
            'data-track-label': targetName
        }
    };
};
var gaExternalClickTrackingTrait = function gaExternalClickTrackingTrait() {
    return { attributes: { 'data-track': 'click' } };
};
var gaAddToCartTrackingTrait = function gaAddToCartTrackingTrait(product) {
    return { events: { click: [Action('addToCart', { product: product })] } };
};
var currentLinkClassTraitF = function currentLinkClassTraitF(_ref2) {
    var currentRoute = _ref2.currentRoute, pendingRoute = _ref2.pendingRoute, currentParentRoute = _ref2.currentParentRoute;
    return function (_ref3) {
        var _ref3$href = _ref3.href, href = _ref3$href === undefined ? '' : _ref3$href, _ref3$route = _ref3.route, route = _ref3$route === undefined ? '' : _ref3$route;
        var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
        return ClassName([currentRoute === href ? 'menu-item--current' : ''].concat([pendingRoute === href ? 'menu-item--pending' : ''], [parent && currentParentRoute === route.split('/')[0] ? 'menu-item--current menu-item--current-parent' : '']).join(' '));
    };
};
var LinkList = function LinkList(_ref4) {
    var _ref4$currentLinkClas = _ref4.currentLinkClassTrait, currentLinkClassTrait = _ref4$currentLinkClas === undefined ? ClassName('') : _ref4$currentLinkClas, parent = _ref4.parent;
    return function (links) {
        return Maybe(links).map(function (links) {
            return links.map(function (menuItem) {
                return Maybe(menuItem.name).map(function (name) {
                    return $_li.bind(typeof currentLinkClassTrait === 'function' ? currentLinkClassTrait(menuItem, parent) : {})($_a.bind(internalLinkTrait(menuItem))(name));
                }).getOrElse(null);
            });
        }).getOrElse([]);
    };
};
var createNavColumn = function createNavColumn(navigation) {
    return $_div.column.m_2({
        type: 'navigation',
        data: navigation
    });
};
var createSubPageLink = function createSubPageLink(parentPage) {
    var baseUrl = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    return function (subPage) {
        var name = subPage.data && subPage.data.shortTitle ? subPage.data.shortTitle || subPage.data.title : subPage.teaserTitle || subPage.name;
        var slug = subPage.data && subPage.data.urlSegment ? subPage.data.urlSegment : 'unnamed-segment';
        return {
            href: baseUrl + '/' + parentPage + '/' + slug,
            name: name,
            route: parentPage + '/' + slug,
            parent: parentPage,
            type: 'link'
        };
    };
};
var createJournalCover = function createJournalCover(journal) {
    return journal.currentIssue.map(function (currentIssue) {
        return $_img.bind({
            attributes: {
                class: 'journal-cover__image',
                src: 'https://static-content.springer.com/cover/journal/' + journal.id + '/' + currentIssue.volumeId + '/' + currentIssue.issueId + '.jpg',
                alt: 'Journal cover: ' + journal.id + ', Volume ' + currentIssue.volumeId + ', Issue ' + currentIssue.issueId
            }
        })();
    });
};
var createSocietyLogo = function createSocietyLogo(globalLinks) {
    return function (teaser) {
        return Maybe(teaser).map(function (teaser) {
            return {
                link: findGlobalRoute(globalLinks, path([
                    'data',
                    'target',
                    'urlSegment'
                ], teaser)),
                picture: Maybe(teaser.picture).map(function (picture) {
                    return $_img.bind({
                        attributes: {
                            src: picture.imageController + '/width/300',
                            class: 'society-logo'
                        }
                    })();
                }).getOrElse(null)
            };
        }).map(function (_ref5) {
            var link = _ref5.link, picture = _ref5.picture;
            return $_div.column.m_6([
                'Associated with:',
                Maybe(link).map(function (link) {
                    return $_a.bind({
                        attributes: {
                            href: link.href,
                            class: 'society-link'
                        },
                        events: {
                            click: [Action('navigate', {
                                    route: 'about/society',
                                    href: link.href
                                })]
                        }
                    })(picture);
                }).getOrElse($_div.bind(ClassName('society-link'))(picture))
            ]);
        }).getOrElse(null);
    };
};
var createCurrentIssueArticle = function createCurrentIssueArticle(description, title, subTitle, items) {
    var isTeaser = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;
    return {
        type: 'article',
        data: {
            title: '',
            shortTitle: 'Latest Issue',
            urlSegment: 'latest-issue',
            html: '\n                    ' + (description ? '<h3 class="kicker">' + description + '</h3>' : '') + '\n                    ' + (isTeaser ? '' : title ? '<h3>' + title + '</h3>' : '') + '\n                    ' + (subTitle ? '<p class="issue-title">' + subTitle + '</p>' : '') + '\n                    ' + items.map(function (item) {
                return '\n                    <div class="issue">\n                        ' + (item.type ? '<p class="type"><small>' + item.type + '</small></p>' : '') + '\n                        ' + (item.link && item.link.title && item.link.href ? '<h3 class="link">\n                            <a href="' + item.link.href + '" title="' + item.link.title + '" target="' + item.link.target + '">' + item.link.title + '</a>\n                        </h3>' : '') + '\n                        ' + (item.editors && item.editors.html ? '<p class="editors"><small>' + item.editors.html + '</small></p>' : '') + '\n                    </div>\n                    ';
            }).join('') + '\n                ',
            shortHtml: null,
            contentType: null,
            source: null,
            imageController: null,
            viewType: null,
            target: null,
            picture: null
        },
        children: []
    };
};
var createTeaser = function createTeaser(teaser, globalLinks) {
    return Maybe(teaser).map(function (teaser) {
        return $_section.bind(ClassName('teaser'))(Maybe(teaser.data.title ? teaser.data.title : teaser.data.shortTitle).map(function (title) {
            return $_headline(title);
        }), $_div.bind({ props: { innerHTML: teaser.data.html ? teaser.data.html : teaser.data.shortHtml } })(), Maybe(teaser.data.target).map(function (target) {
            return $_a.bind({
                attributes: { title: target.title },
                styles: [c.buttonBase]
            }).bind(teaser.data.target.type === 'ExternalLink' ? { attributes: { href: teaser.data.target.link } } : internalLinkTrait({
                route: Maybe(findGlobalRoute(globalLinks, target.urlSegment)).map(prop('route')).getOrElse(''),
                href: Maybe(findGlobalRoute(globalLinks, target.urlSegment)).map(prop('href')).getOrElse('')
            }))($_span.bind({ styles: [c.buttonLabel] })(teaser.data.overlayText.replace(/<\/?p>/g, '')), $_Icons.arrowRight.bind({ styles: [c.buttonIcon] })());
        }));
    });
};
module.exports = {
    createNavColumn: createNavColumn,
    createSubPageLink: createSubPageLink,
    createSocietyLogo: createSocietyLogo,
    createJournalCover: createJournalCover,
    createCurrentIssueArticle: createCurrentIssueArticle,
    createTeaser: createTeaser,
    findGlobalRoute: findGlobalRoute,
    internalLinkTrait: internalLinkTrait,
    gaInternalClickTrackingTrait: gaInternalClickTrackingTrait,
    gaExternalClickTrackingTrait: gaExternalClickTrackingTrait,
    gaAddToCartTrackingTrait: gaAddToCartTrackingTrait,
    currentLinkClassTraitF: currentLinkClassTraitF,
    LinkList: LinkList
};
},{"app/init/common":116,"app/styles/constants":121,"lib/helpers":140}],121:[function(require,module,exports){
'use strict';
module.exports = {
    article: 'article',
    issueArticle: 'issue-article',
    box: 'box',
    box2: 'box2',
    priceTable: 'price-table',
    priceCell: 'price-cell',
    priceTableSmallPrint: 'price-table-small-print',
    productTitle: 'product-title',
    collection: 'collection',
    buttonBase: 'button-base',
    buttonLabel: 'button-label',
    buttonIcon: 'button-icon',
    headline: 'headline',
    details: 'details',
    detailsSummary: 'details-summary',
    detailsSummaryLabel: 'details-summary-label',
    detailsMarker: 'details-marker',
    asideHeadline: 'aside-headline'
};
},{}],122:[function(require,module,exports){
'use strict';
var _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
            if (Object.prototype.hasOwnProperty.call(source, key)) {
                target[key] = source[key];
            }
        }
    }
    return target;
};
var _slicedToArray = function () {
    function sliceIterator(arr, i) {
        var _arr = [];
        var _n = true;
        var _d = false;
        var _e = undefined;
        try {
            for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
                _arr.push(_s.value);
                if (i && _arr.length === i)
                    break;
            }
        } catch (err) {
            _d = true;
            _e = err;
        } finally {
            try {
                if (!_n && _i['return'])
                    _i['return']();
            } finally {
                if (_d)
                    throw _e;
            }
        }
        return _arr;
    }
    return function (arr, i) {
        if (Array.isArray(arr)) {
            return arr;
        } else if (Symbol.iterator in Object(arr)) {
            return sliceIterator(arr, i);
        } else {
            throw new TypeError('Invalid attempt to destructure non-iterable instance');
        }
    };
}();
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
var Color = require('color');
var stack = require('callsite');
var flatMap = function flatMap(a, cb) {
    var _ref;
    return (_ref = []).concat.apply(_ref, _toConsumableArray(a.map(cb)));
};
var nest = function nest(parent, children) {
    return children.replace(/&/g, parent);
};
var $nestRoot = function $nestRoot($$, children) {
    return children.replace(/&/g, '.' + $$.root);
};
var $nestRootNS = function $nestRootNS($$, children) {
    return children.replace(/&/g, '.' + $$.root + ($$.autoNS ? '-' + $$.autoNS : ''));
};
var locate = function locate(stack) {
    if (!stack)
        return;
    return '/* ./src/' + stack[0].getFileName().replace(/\/(.*)\/src\//g, '') + ':' + stack[0].getLineNumber() + ' (' + (stack[0].getFunctionName() || 'anonymous') + ') */';
};
var renderCSSDeclaration = function renderCSSDeclaration(prop, value) {
    prop = prop.split('_')[0];
    prop = prop.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    prop = prop.replace(/^(webkit|moz|ms|o)-/g, '-$1-');
    return value ? prop + ': ' + value + ';' : '';
};
var CSSObjectToString = function CSSObjectToString(declarations) {
    if (!declarations)
        return '';
    return '' + Object.keys(declarations).sort().map(function (key) {
        var value = declarations[key];
        if (typeof value === 'number') {
            value = value + 'px';
        }
        return typeof value === 'string' || typeof value === 'number' ? renderCSSDeclaration(key, value) : CSSObjectToString(key, value);
    }).join(' ');
};
var RGB2HSL = function RGB2HSL(rgb) {
    var r = rgb[0] / 255;
    var g = rgb[1] / 255;
    var b = rgb[2] / 255;
    var min = Math.min(r, g, b);
    var max = Math.max(r, g, b);
    var delta = max - min;
    var h = void 0;
    var s = void 0;
    var l = void 0;
    if (max === min) {
        h = 0;
    } else if (r === max) {
        h = (g - b) / delta;
    } else if (g === max) {
        h = 2 + (b - r) / delta;
    } else if (b === max) {
        h = 4 + (r - g) / delta;
    }
    h = Math.min(h * 60, 360);
    if (h < 0) {
        h += 360;
    }
    l = (min + max) / 2;
    if (max === min) {
        s = 0;
    } else if (l <= 0.5) {
        s = delta / (max + min);
    } else {
        s = delta / (2 - max - min);
    }
    return [
        h,
        s * 100,
        l * 100
    ];
};
var RGB2HEX = function RGB2HEX(args) {
    var integer = ((Math.round(args[0]) & 255) << 16) + ((Math.round(args[1]) & 255) << 8) + (Math.round(args[2]) & 255);
    var string = integer.toString(16).toUpperCase();
    return '000000'.substring(string.length) + string;
};
var HEX2RGB = function HEX2RGB(args) {
    var match = args.toString(16).match(/[a-f0-9]{6}/i);
    if (!match) {
        return [
            0,
            0,
            0
        ];
    }
    var integer = parseInt(match[0], 16);
    var r = integer >> 16 & 255;
    var g = integer >> 8 & 255;
    var b = integer & 255;
    return [
        r,
        g,
        b
    ];
};
var HSL2RGB = function HSL2RGB(hsl) {
    var h = hsl[0] / 360;
    var s = hsl[1] / 100;
    var l = hsl[2] / 100;
    var t1 = void 0;
    var t2 = void 0;
    var t3 = void 0;
    var rgb = void 0;
    var val = void 0;
    if (s === 0) {
        val = l * 255;
        return [
            val,
            val,
            val
        ];
    }
    if (l < 0.5) {
        t2 = l * (1 + s);
    } else {
        t2 = l + s - l * s;
    }
    t1 = 2 * l - t2;
    rgb = [
        0,
        0,
        0
    ];
    for (var i = 0; i < 3; i++) {
        t3 = h + 1 / 3 * -(i - 1);
        if (t3 < 0) {
            t3++;
        }
        if (t3 > 1) {
            t3--;
        }
        if (6 * t3 < 1) {
            val = t1 + (t2 - t1) * 6 * t3;
        } else if (2 * t3 < 1) {
            val = t2;
        } else if (3 * t3 < 2) {
            val = t1 + (t2 - t1) * (2 / 3 - t3) * 6;
        } else {
            val = t1;
        }
        rgb[i] = val * 255;
    }
    return rgb;
};
var HEX2HSL = function HEX2HSL(args) {
    return RGB2HSL(HEX2RGB(args));
};
var HSL2HEX = function HSL2HEX(hsl) {
    return RGB2HEX(HSL2RGB(hsl));
};
var contrastColor = function contrastColor(backgroundColor) {
    var contrastFactor = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0.84;
    var middle = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0.5;
    var color = Color(backgroundColor);
    var _RGB2HSL = RGB2HSL(color.color), _RGB2HSL2 = _slicedToArray(_RGB2HSL, 3), h = _RGB2HSL2[0], s = _RGB2HSL2[1], l = _RGB2HSL2[2];
    var diff = (l > 50 ? l : 100 - l) * contrastFactor;
    var invertBoostFactor = 1 + 0.16 * ((100 - l) / 100);
    var saturationContrastFactor = contrastFactor > 0.5 ? contrastFactor : 1 - contrastFactor;
    var newLum = color.luminosity() > middle ? l - diff : (l + diff) * invertBoostFactor;
    newLum = newLum >= 100 ? 100 : newLum;
    return '#' + HSL2HEX([
        h,
        s * saturationContrastFactor,
        newLum
    ]).toLowerCase();
};
var normalizeBoundaries = function normalizeBoundaries(style) {
    var fontSizeLineHeightDiffNorm = style.styles.lineHeight - style.styles.fontSize;
    style.styles = _extends({}, style.styles, {
        marginBottom: 'calc(-0.25em - ' + fontSizeLineHeightDiffNorm + 'px)',
        transform: 'translateY(calc(-0.1em - ' + fontSizeLineHeightDiffNorm / 2 + 'px))'
    });
    return style;
};
var applyLineHeight = function applyLineHeight(style) {
    style.styles = _extends({}, style.styles, { lineHeight: style.styles.fontSize + 4 });
    return style;
};
module.exports = {
    flatMap: flatMap,
    nest: nest,
    $nestRoot: $nestRoot,
    $nestRootNS: $nestRootNS,
    locate: locate,
    stack: stack,
    CSSObjectToString: CSSObjectToString,
    RGB2HSL: RGB2HSL,
    RGB2HEX: RGB2HEX,
    HEX2RGB: HEX2RGB,
    HSL2RGB: HSL2RGB,
    HEX2HSL: HEX2HSL,
    HSL2HEX: HSL2HEX,
    contrastColor: contrastColor,
    normalizeBoundaries: normalizeBoundaries,
    applyLineHeight: applyLineHeight
};
},{"callsite":2,"color":22}],123:[function(require,module,exports){
'use strict';
var issueArticle = require('app/styles/palgrave/issue-article.css');
module.exports = function () {
    return '\n    ' + issueArticle() + '\n    \n    .article h3 {\n        margin-top: 30px;\n    }\n\n    .article-body {\n        margin-top: 10px;\n    }\n\n    .article-body > * + * {\n        margin-top: 10px;\n    }\n\n    .article-body .twoColumnSeparatorLeft--paragraph {\n        border-top: 1px solid #ddd;\n        height: 0;\n        margin-top: 20px;\n        padding-top: 10px;\n    }\n\n    .article-body .float--right {\n        float: right;\n        margin-left: 15px;\n    }\n\n    .article-body .float--left {\n        float: left;\n        margin-right: 15px;\n    }\n\n    .article-body table {\n        border-collapse: collapse;\n        margin-bottom: 30px;\n        width: 100%;\n    }\n\n    .article-body table a {\n        display: block;\n    }\n\n    .article-body table a img {\n        width: 100%;\n    }\n\n    .article-body tbody tr td {\n        border-bottom: 1px solid #eee;\n        padding: 15px 0;\n        vertical-align: top;\n    }\n\n    .article-body tbody tr:first-child td {\n        border-bottom: 3px solid #777;\n        padding: 10px 0;\n    }\n\n    .article-body tbody tr td:first-child {\n        width: 29%;\n        padding-right: 20px;\n    }\n\n    .article-body.cover-gallery table {\n        table-layout: fixed;\n        margin-bottom: 10px;\n    }\n\n    .article-body.cover-gallery tbody tr td {\n        width: auto;\n        padding: 15px 10px;\n    }\n\n    .article-body.cover-gallery tbody tr td:first-child {\n        padding-left: 0;\n        padding-right: 20px;\n    }\n\n    .article-body.cover-gallery tbody tr td:last-child {\n        padding-left: 20px;\n        padding-right: 0;\n    }\n\n    .article-body .flapHead {\n        border-top: 1px solid #ddd;\n        color: #00768A;\n        cursor: pointer;\n        padding-bottom: 15px;\n        padding-left: 10px;\n        padding-right: 10px;\n        padding-top: 15px;\n        user-select: none;\n    }\n\n    .article-body .collapsible-wrapper {\n        padding-left: 10px;\n        padding-right: 10px;\n        transition: height 0.5s;\n    }\n\n    .collapsible {\n        padding-bottom: 10px;\n    }\n\n    .collapsible-wrapper,\n    .collapsible-wrapper + .flapHead {\n        margin-top: 0;\n    }\n    \n    .collapsible-wrapper {\n        display: none;\n    }\n\n    .collapsible-wrapper.show,\n    html.no-js .collapsible-wrapper {\n        display: block;\n    }\n';
};
},{"app/styles/palgrave/issue-article.css":132}],124:[function(require,module,exports){
'use strict';
var styleConfig = require('app/styles/palgrave/config');
var _require = require('app/init/common'), Style = _require.Style, Styles = _require.Styles, StyleChildren = _require.StyleChildren, Padding = _require.Padding;
var box2 = function box2(_ref) {
    var parent = _ref.parent, self = _ref.self;
    return Style.bind(Styles({ backgroundColor: styleConfig.colors.lightGray_f4f4f4 })).bind(Styles(Padding('20px'))).bind(StyleChildren({
        selector: '& > * + *',
        styles: { marginTop: 15 }
    }));
};
module.exports = { box2: box2 };
},{"app/init/common":116,"app/styles/palgrave/config":128}],125:[function(require,module,exports){
'use strict';
var styleConfig = require('app/styles/palgrave/config');
module.exports = function () {
    return '\n    .box {\n        background-color: ' + styleConfig.colors.lightGray_f4f4f4 + ';\n        padding: 30px 20px;\n    }\n    \n    .box > * + *,\n    .box > div > * + *,\n    .box > section > * + * {\n        margin-top: 15px;\n    }\n\n';
};
},{"app/styles/palgrave/config":128}],126:[function(require,module,exports){
'use strict';
var _require = require('lib/helpers'), pathOr = _require.pathOr;
var _require2 = require('app/styles/lib/helpers'), contrastColor = _require2.contrastColor, applyLineHeight = _require2.applyLineHeight, normalizeBoundaries = _require2.normalizeBoundaries;
var styleConfig = require('app/styles/palgrave/config');
var _require3 = require('app/init/common'), Style = _require3.Style, Styles = _require3.Styles, StyleChildren = _require3.StyleChildren;
var parentDefault = {
    attributes: { class: 'parent' },
    state: { contrastFactor: 0.94 },
    styles: { backgroundColor: '#f00' }
};
var buttonBase = function buttonBase(_ref) {
    var self = _ref.self, parent = _ref.parent;
    var backgroundColor = pathOr('transparent', [
        'state',
        'backgroundColor'
    ], self);
    var styles = Style.bind(Styles({
        backgroundColor: backgroundColor,
        border: backgroundColor === 'transparent' ? '1px solid ' + styleConfig.colors.turquoise_brandSecondary_00768a : '0',
        borderRadius: '0',
        webkitFontSmoothing: 'antialiased',
        display: 'flex',
        justifyContent: 'space-between',
        paddingBottom: 9,
        paddingLeft: 11,
        paddingRight: 11,
        paddingTop: 9,
        transition: 'all 0.2s',
        width: '100%'
    })).bind(StyleChildren({
        selector: '&:hover',
        styles: {
            backgroundColor: backgroundColor === 'transparent' ? '' + styleConfig.colors.turquoise_brandSecondary_00768a : contrastColor(backgroundColor, 0.07),
            textDecoration: 'none'
        }
    }));
    return styles;
};
var foregroundContrastFactor = 0.805;
var buttonLabel = function buttonLabel(_ref2) {
    var parent = _ref2.parent;
    parent = parent || parentDefault;
    var parentStyles = pathOr({ backgroundColor: '#f00' }, ['styles'], parent);
    var parentStylesHover = pathOr({ backgroundColor: '#f00' }, [
        'childStyles',
        '0',
        'styles'
    ], parent);
    return Style.bind(Styles({
        color: parentStyles.backgroundColor === 'transparent' ? '' + styleConfig.colors.turquoise_brandSecondary_00768a : contrastColor(parentStyles.backgroundColor, foregroundContrastFactor),
        fontFamily: '"Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif',
        fontSize: 16,
        lineHeight: 22,
        textDecoration: 'none',
        transition: 'all 0.2s'
    })).bind(StyleChildren({
        selector: '.' + parent.attributes.class + ':hover &',
        styles: { color: contrastColor(parentStylesHover.backgroundColor, foregroundContrastFactor) }
    }));
};
var buttonIcon = function buttonIcon(_ref3) {
    var parent = _ref3.parent;
    parent = parent || parentDefault;
    var parentStyles = pathOr({ backgroundColor: '#f00' }, ['styles'], parent);
    var parentStylesHover = pathOr({ backgroundColor: '#f00' }, [
        'childStyles',
        '0',
        'styles'
    ], parent);
    return Style.bind(Styles({
        alignSelf: 'center',
        display: 'inline-flex',
        transformBox: 'fill-box',
        transition: 'all 0.2s',
        width: 16,
        height: 16,
        fill: parentStyles.backgroundColor === 'transparent' ? '' + styleConfig.colors.turquoise_brandSecondary_00768a : contrastColor(parentStyles.backgroundColor, foregroundContrastFactor)
    })).bind(StyleChildren({
        selector: '.' + parent.attributes.class + ':hover &',
        styles: { fill: contrastColor(parentStylesHover.backgroundColor, foregroundContrastFactor) }
    }));
};
module.exports = {
    buttonBase: buttonBase,
    buttonLabel: buttonLabel,
    buttonIcon: buttonIcon
};
},{"app/init/common":116,"app/styles/lib/helpers":122,"app/styles/palgrave/config":128,"lib/helpers":140}],127:[function(require,module,exports){
'use strict';
module.exports = function () {
    return '\n    .collection > * + * {\n        margin-top: 20px;\n    }\n\n    .collection > section + section {\n        margin-top: 20px;\n        padding-top: 20px;\n    }\n';
};
},{}],128:[function(require,module,exports){
'use strict';
var _require = require('app/styles/lib/helpers'), CSSObjectToString = _require.CSSObjectToString;
var selectors = {
    JOURNAL_STAGE: '.journal-stage',
    JOURNAL_CONTENT: '.journal-content',
    JOURNAL_NAVIGATION: '.journal-navigation'
};
var colors = {
    white_fff: '#fff',
    turquoise_brandSecondary_00768a: '#00768a',
    lightGray_f4f4f4: '#f4f4f4',
    mediumGray_ddd: '#ddd',
    gray_444: '#444',
    gray_555: '#555',
    gray_999: '#999',
    black_2b2b2b: '#2b2b2b'
};
var typography = {
    type: {
        primary_BlissRegular: '"Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif',
        secondary: '"Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif',
        secondary_BlissBold: '"Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif',
        tertiary_Verdana: 'Verdana, sans-serif'
    },
    sizes: {
        xxs: '12px',
        xxs_12px: 12,
        xs: '14px',
        xs_14px: '14px',
        s: '16px',
        s_16px: '16px',
        m: '18px',
        m_18px: '18px',
        l: '22px',
        l_22px: '22px',
        xl: '28px',
        _xl_28px: '28px',
        xxl: '32px',
        xxl_32px: '32px'
    }
};
var h2 = function h2() {
    var color = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : colors.black_2b2b2b;
    var lineHeight = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '32px';
    return CSSObjectToString({
        color: color,
        fontFamily: typography.type._secondary_BlissBold,
        fontSize: typography.sizes._xl_28px,
        fontWeight: 'normal',
        lineHeight: lineHeight
    });
};
var h3 = function h3() {
    var color = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : colors.black_2b2b2b;
    var lineHeight = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '26px';
    return CSSObjectToString({
        color: color,
        fontFamily: typography.type.secondary,
        fontSize: typography.sizes.l,
        fontWeight: 'normal',
        lineHeight: lineHeight
    });
};
var h4 = function h4() {
    var color = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : colors.black_2b2b2b;
    var lineHeight = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '22px';
    return CSSObjectToString({
        color: color,
        fontFamily: typography.type.secondary,
        fontSize: typography.sizes.m,
        fontWeight: 'normal',
        lineHeight: lineHeight
    });
};
module.exports = {
    selectors: selectors,
    colors: colors,
    typography: typography,
    presets: {
        h2: h2,
        h3: h3,
        h4: h4
    }
};
},{"app/styles/lib/helpers":122}],129:[function(require,module,exports){
'use strict';
var _require = require('app/styles/lib/helpers'), contrastColor = _require.contrastColor, applyLineHeight = _require.applyLineHeight, normalizeBoundaries = _require.normalizeBoundaries;
var styleConfig = require('app/styles/palgrave/config');
var _require2 = require('app/init/common'), Style = _require2.Style, Styles = _require2.Styles, StyleChildren = _require2.StyleChildren;
var details = function details(_ref) {
    var self = _ref.self, parent = _ref.parent;
    return Style.bind(Styles({})).bind(StyleChildren({
        selector: '& > * + *',
        styles: { marginTop: 16 }
    }, {
        selector: '& > *:not(summary)',
        styles: { marginLeft: 10 }
    }, {
        selector: '& li',
        styles: {
            fontSize: styleConfig.typography.sizes.xxs_12px,
            lineHeight: 18
        }
    }, {
        selector: '& li + li',
        styles: { marginTop: 4 }
    }));
};
var summary = function summary(_ref2) {
    var self = _ref2.self, parent = _ref2.parent;
    return Style.bind(Styles({
        cursor: 'pointer',
        display: 'flex',
        outline: '0',
        position: 'relative',
        userSelect: 'none',
        marginBottom: -8
    })).bind(StyleChildren({
        selector: '&::-webkit-details-marker',
        styles: { display: 'none' }
    }, {
        selector: '& > * + *',
        styles: { marginLeft: 5 }
    }));
};
var summaryLabel = function summaryLabel() {
    return Style.bind(Styles({
        color: styleConfig.colors.turquoise_brandSecondary_00768a,
        fontFamily: styleConfig.typography.type.tertiary_Verdana,
        fontSize: styleConfig.typography.sizes.xxs_12px,
        fontWeight: 'normal'
    })).map(applyLineHeight).map(normalizeBoundaries);
};
var marker = function marker(_ref3) {
    var self = _ref3.self, parent = _ref3.parent;
    return Style.bind(Styles({
        transform: 'rotate(90deg)',
        width: 12,
        height: 12,
        alignSelf: 'center',
        display: 'inline-flex'
    })).bind(StyleChildren({
        selector: 'details[open] &',
        styles: { transform: 'rotate(-90deg)' }
    }, {
        selector: '& path',
        styles: { fill: styleConfig.colors.turquoise_brandSecondary_00768a }
    }));
};
module.exports = {
    details: details,
    summary: summary,
    summaryLabel: summaryLabel,
    marker: marker
};
},{"app/init/common":116,"app/styles/lib/helpers":122,"app/styles/palgrave/config":128}],130:[function(require,module,exports){
'use strict';
module.exports = function (banner) {
    return '' + (banner && banner[0] ? '.journal-header {\n    background-color: transparent;\n    background-image: url(\'' + banner[0].imageController + '/width/1600\');\n    background-position: 100% 0;\n    background-repeat: no-repeat;\n    background-size: cover;\n}' : '.journal-header {\n    background-image: linear-gradient(rgba(0,0,0,0),rgba(0,0,0,0.3));\n    box-shadow: 9px 12px 14px 0px rgba(0, 0, 0, 0.15) inset;\n}\n\n.journal-header h1 {\n    color: #444;\n}\n');
};
},{}],131:[function(require,module,exports){
'use strict';
module.exports = function () {
    return '.impact-factor {\n    display: inline-block;\n    text-align: center;\n}\n\n.impact-factor__value {\n    background-color: #fff;\n    border-radius: 50%;\n    color: #2b2b2b;\n    display: inline-block;\n    height: 70px;\n    font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n    font-size: 22px;\n    font-weight: normal;\n    line-height: 1.18;\n    padding-top: 1em;\n    text-align: center;\n    width: 70px;\n}\n\n.impact-factor__info {\n    color: #fff;\n    display: block;\n    font-family: Verdana;\n    font-size: 12px;\n    line-height: 1.83;\n    text-align: center;\n    \n    position: relative;\n}\n\n.impact-factor__info::after {\n    color: #fff;\n    content: " !";\n    font-family: "sn_pm_icons";\n    font-size: 13px;\n}\n\n.impact-factor__copyright {\n    background-color: rgba(255, 255, 255, 0.8);\n    color: #222;\n    display: none;\n    font-family: Verdana;\n    font-size: 11px;\n    font-style: italic;\n    line-height: 1.83;\n    text-align: center;\n    position: absolute;\n    padding: 4px 10px;\n    right: 0;\n    border-radius: 2px;\n    width: 16em;\n}\n\n.impact-factor__info:hover .impact-factor__copyright {\n    display: block;\n} \n';
};
},{}],132:[function(require,module,exports){
'use strict';
module.exports = function () {
    return '    \n    .issue-title {\n        color: #2b2b2b;\n        font-size: 22px;\n        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n        line-height: 26px;\n        margin-bottom: 30px;\n        margin-top: 18px !important;\n    }\n\n    .journal-content .kicker,\n    .article-body .kicker {\n        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n        font-size: 18px;\n        font-weight: normal;\n        margin-top: 0;\n    }\n    \n    .article-body .kicker + h3 {\n        font-size: 28px;\n        margin-top: 18px;\n    }\n    \n    .issue {\n        margin-top: 15px;\n        padding-top: 15px;\n        border-top: 1px solid #ddd;\n    }\n\n    h3 + .issue {\n        margin-top: 30px;\n    }\n    \n    .issue p.type {\n        color: #999;\n    }\n\n    .issue p.type small {\n        font-size: 12px;\n    }\n    \n    .issue h3.link {\n        margin-top: 0;\n        font-size: 18px;\n        font-weight: normal;\n        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n    }\n\n    .issue h3.link a {\n        color: #d83b5b;\n    }\n    \n    .issue p.editors small {\n        color: #555;\n        font-size: 14px;\n        line-height: 28px;\n    }\n';
};
},{}],133:[function(require,module,exports){
'use strict';
module.exports = function () {
    return '\n    .journal-navigation {\n        background-color: rgba(0, 0, 0, 0.2);\n    }\n    \n    .journal-navigation-arrow-icon-size16 {\n        width: 16px;\n        height: 16px;\n    }\n\n    .journal-navigation ul {\n        list-style: none;\n    }\n\n    @media screen and (max-width: 577px) {\n        .journal-navigation li + li {\n            height: 0;\n            box-shadow: 0 -1px rgba(255, 255, 255, 0.2);\n            opacity: 0;\n            transition-delay: 0.7s;\n            transition-duration: 0.5s;\n            transition-property: all;\n        }\n\n        .journal-navigation ul:hover li {\n            height: 45px;\n            opacity: 1;\n            transition-delay: 0s;\n            transition-duration: 0.2s;\n        }\n    }\n\n    .journal-navigation-header {\n        position: relative;\n    }\n\n    .journal-navigation-header svg {\n        position: absolute;\n        right: 0;\n    }\n\n    .journal-navigation-header svg path {\n        fill: #fff;\n    }\n\n    @media screen and (min-width: 578px) {\n        .journal-navigation-header {\n            display: none;\n        }\n    }\n\n    .journal-navigation a {\n        color: #fff;\n        display: block;\n        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n        font-size: 18px;\n        font-weight: normal;\n        padding-bottom: 0.734em;\n        padding-top: 0.6em;\n        text-align: left;\n        text-decoration: none;\n        transition: all 0.2s;\n    }\n\n    .journal-navigation .menu-item--current a {\n        font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n    }\n\n    .journal-navigation a:hover {\n        text-decoration: underline;\n    }\n\n    .ie9 .journal-navigation a {\n        padding-left: 1em;\n        padding-right: 1em;\n    }\n\n    .journal-navigation-header a {\n        align-items: center;\n        display: flex;\n    }\n\n    @media screen and (min-width: 578px) {\n        .journal-navigation ul {\n            display: flex;\n            flex-flow: row wrap;\n            height: 2.813em;\n        }\n\n        .journal-navigation li {\n            flex: 1 1 auto;\n        }\n\n        .journal-navigation a {\n            text-align: center;\n        }\n\n        .journal-navigation .menu-item--current a,\n        .journal-navigation .menu-item--current a:hover {\n            background-color: #fff;\n            color: #333;\n            font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n            text-decoration: none;\n        }\n\n        .journal-navigation a.menu-item--pending {\n            background-color: rgba(255, 255, 255, 0.3);\n            color: #fff;\n            font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;\n            text-decoration: none;\n        }\n\n        .ie9 .journal-navigation li {\n            display: inline-block;\n        }\n    }\n\n    @media screen and (min-width: 900px) {\n        .journal-navigation ul {\n            padding-right: 45%;\n        }\n    }\n';
};
},{}],134:[function(require,module,exports){
'use strict';
var styleConfig = require('app/styles/palgrave/config');
var _require = require('app/init/common'), Style = _require.Style, Styles = _require.Styles, Padding = _require.Padding;
var _require2 = require('app/styles/lib/helpers'), contrastColor = _require2.contrastColor, applyLineHeight = _require2.applyLineHeight, normalizeBoundaries = _require2.normalizeBoundaries;
var priceTable = function priceTable(_ref) {
    var parent = _ref.parent, self = _ref.self;
    return Style.bind(Styles({
        display: 'flex',
        width: '100%',
        fontSize: 20,
        lineHeight: 22
    })).bind({
        children: [
            {
                selector: ' tbody',
                styles: {
                    display: 'flex',
                    flexDirection: 'column',
                    width: '100%'
                }
            },
            {
                selector: ' tbody > tr + tr',
                styles: { marginTop: 10 }
            },
            {
                selector: ' tr',
                styles: {
                    alignItems: 'baseline',
                    display: 'flex',
                    justifyContent: 'space-between'
                }
            },
            {
                selector: ' td',
                styles: {
                    display: 'flex',
                    flexFlow: 'column'
                }
            }
        ]
    }).map(normalizeBoundaries);
};
var priceCell = function priceCell(_ref2) {
    var parent = _ref2.parent, self = _ref2.self;
    return Style.bind(Styles({
        color: styleConfig.colors.black_2b2b2b,
        fontFamily: styleConfig.typography.type.secondary_BlissBold,
        fontSize: 22,
        lineHeight: 26
    })).map(normalizeBoundaries);
};
var productTitle = function productTitle() {
    return Style.bind(Styles({
        color: styleConfig.colors.black_2b2b2b,
        fontFamily: styleConfig.typography.type.secondary_BlissBold,
        fontSize: 18,
        lineHeight: 22
    })).map(normalizeBoundaries);
};
var priceTableSmallPrint = function priceTableSmallPrint(_ref3) {
    var parent = _ref3.parent, self = _ref3.self;
    return Style.bind(Styles({
        color: styleConfig.colors.gray_999,
        fontFamily: styleConfig.typography.type.tertiary_Verdana,
        fontSize: 12,
        lineHeight: 18,
        textAlign: 'right',
        width: '100%'
    })).map(normalizeBoundaries);
};
module.exports = {
    priceTable: priceTable,
    priceCell: priceCell,
    productTitle: productTitle,
    priceTableSmallPrint: priceTableSmallPrint
};
},{"app/init/common":116,"app/styles/lib/helpers":122,"app/styles/palgrave/config":128}],135:[function(require,module,exports){
'use strict';
var styleConfig = require('app/styles/palgrave/config');
module.exports = function () {
    return '\n    .journal-stage {\n        padding: 2.6175% 0;\n    }\n    \n    @media screen and (min-width: 1280px) {\n        .journal-stage {\n            padding: 33.5px 0;\n        }    \n    }\n    \n    .journal-stage .row > .column {\n        align-items: center;\n        display: flex;\n        justify-content: space-between;\n        min-height: 93px;\n    }\n    \n    .journal-stage .live-area > * {\n        \n    }\n    \n    .journal-stage h1 {\n        color: ' + styleConfig.colors.white_fff + ';\n        font-family: ' + styleConfig.typography.type.secondary_BlissBold + ';\n        font-weight: normal;\n        max-width: 24em;\n    }\n    \n    .journal-stage h1 {\n        font-size: 7vw;\n        line-height: 1.2;\n    }\n    \n    @media screen and (min-width: 459px) {\n        .journal-stage h1 {\n            font-size: 32px;\n            line-height: 36px;\n        }\n    }\n    \n    .journal-stage h1 small {\n        display: block;\n        font-family: ' + styleConfig.typography.type.primary_BlissRegular + ';\n        font-size: 12px;\n        line-height: 20px;\n    }\n';
};
},{"app/styles/palgrave/config":128}],136:[function(require,module,exports){
'use strict';
var styleConfig = require('app/styles/palgrave/config');
module.exports = function () {
    return '\n    .journal-subnav ul {\n        list-style: none;\n    }\n\n    .journal-subnav li {\n        font-family: ' + styleConfig.typography.type.primary_BlissRegular + ';\n        font-size: 16px;\n        line-height: 22px;\n        margin-left: 0;\n    }\n\n    .journal-subnav a {\n        color: ' + styleConfig.colors.gray_444 + ';\n        text-decoration: none;\n        padding: 8px 0;\n        display: block;\n    }\n\n    .journal-subnav a:hover {\n        color: ' + styleConfig.colors.turquoise_brandSecondary_00768a + ';\n    }\n    \n    .journal-subnav .menu-item--current a {\n        color: ' + styleConfig.colors.turquoise_brandSecondary_00768a + ';\n        font-family: ' + styleConfig.typography.type.secondary_BlissBold + ';\n    }\n    \n    .teaser-navigation .journal-subnav {\n        columns: 2;\n    }\n    \n    .teaser-navigation .journal-subnav li {\n        font-family: Verdana;\n        font-size: 14px;\n        line-height: 18px;\n        break-inside: avoid;\n    }\n    \n    .teaser-navigation .journal-subnav a {\n        color: ' + styleConfig.colors.turquoise_brandSecondary_00768a + ';\n        padding: 6px 0;\n    }\n    \n    .teaser-navigation .journal-subnav a:hover {\n        text-decoration: underline;\n    }\n    \n    .teaser-navigation > * + * {\n        margin-top: 15px;\n    }\n';
};
},{"app/styles/palgrave/config":128}],137:[function(require,module,exports){
'use strict';
var styleConfig = require('app/styles/palgrave/config');
var _require = require('app/styles/lib/helpers'), applyLineHeight = _require.applyLineHeight, normalizeBoundaries = _require.normalizeBoundaries;
var _require2 = require('app/init/common'), Style = _require2.Style, Styles = _require2.Styles;
var parsePx = function parsePx(px) {
    return parseFloat(px.replace(/px/, ''));
};
var headlineSizes = {
    h1: { fontSize: 32 },
    h2: { fontSize: 28 },
    h3: { fontSize: 22 },
    h4: { fontSize: 18 },
    h5: { fontSize: 12 }
};
var headline = function headline(_ref) {
    var parent = _ref.parent, self = _ref.self;
    return Style.bind(Styles({
        color: styleConfig.colors.black_2b2b2b,
        fontFamily: styleConfig.typography.type.secondary,
        fontWeight: 'normal'
    })).bind(Styles(headlineSizes[self.tagName] || {})).map(applyLineHeight).map(normalizeBoundaries);
};
var asideHeadline = function asideHeadline(_ref2) {
    var parent = _ref2.parent, self = _ref2.self;
    return Style.bind(Styles({
        color: styleConfig.colors.gray_444,
        fontFamily: styleConfig.typography.type.tertiary_Verdana,
        fontSize: styleConfig.typography.sizes.xxs_12px,
        fontWeight: 'normal',
        lineHeight: 18
    })).map(normalizeBoundaries);
};
module.exports = {
    headline: headline,
    asideHeadline: asideHeadline
};
},{"app/init/common":116,"app/styles/lib/helpers":122,"app/styles/palgrave/config":128}],138:[function(require,module,exports){
'use strict';
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
var document = require('global/document');
var isClient = !!document.activeElement;
var queryString = require('query-string');
var choo = require('choo');
var parsed = isClient ? queryString.parse(location.search) : {};
var logger = function logger(state, emitter) {
    emitter.on('*', function (messageName, data) {
        var handler = console.log;
        handler('event', messageName, data);
    });
};
module.exports = function () {
    var routes = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
    var store = arguments[1];
    var app = choo({
        href: false,
        history: false
    });
    if (parsed['debug'] !== undefined) {
        app.use(logger);
    }
    app.use(store);
    routes.map(function (route) {
        return app.route.apply(app, _toConsumableArray(route));
    });
    return app;
};
},{"choo":3,"global/document":30,"query-string":40}],139:[function(require,module,exports){
'use strict';
var document = require('global/document');
var Maybe = require('ramda-fantasy/src/Maybe');
var createElement = function createElement(tagName) {
    var ns = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'html';
    return tagName ? ns === 'html' ? document.createElement(tagName) : document.createElementNS('http://www.w3.org/2000/svg', tagName) : null;
};
var bindEvents = function bindEvents(actionMap) {
    return function (context) {
        return function (events) {
            return Maybe(events).map(function (events) {
                return Object.keys(events).reduce(function (props, current) {
                    props['on' + current] = function (event) {
                        (events[current] || []).forEach(function (action) {
                            actionMap[action.name](event, context, action.data);
                        });
                    };
                    return props;
                }, {});
            }).getOrElse();
        };
    };
};
var __ = function __(actionMap) {
    return function (context) {
        return function (_ref) {
            var _ref$ns = _ref.ns, ns = _ref$ns === undefined ? 'html' : _ref$ns, _ref$tagName = _ref.tagName, tagName = _ref$tagName === undefined ? 'div' : _ref$tagName, _ref$attributes = _ref.attributes, attributes = _ref$attributes === undefined ? {} : _ref$attributes, _ref$props = _ref.props, props = _ref$props === undefined ? {} : _ref$props, _ref$events = _ref.events, events = _ref$events === undefined ? {} : _ref$events, _ref$children = _ref.children, children = _ref$children === undefined ? [] : _ref$children;
            var node = createElement(tagName, ns);
            Object.keys(attributes).forEach(function (attribute) {
                node.setAttribute(attribute, attributes[attribute]);
            });
            if (children) {
                children.forEach(function (child) {
                    if (child) {
                        child = typeof child === 'string' ? document.createTextNode(child) : __(actionMap)(context)(child);
                        node.appendChild(child);
                    }
                });
            }
            Object.assign(node, props, bindEvents(actionMap)(context)(events));
            return node;
        };
    };
};
module.exports = __;
},{"global/document":30,"ramda-fantasy/src/Maybe":44}],140:[function(require,module,exports){
'use strict';
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
function _defineProperty(obj, key, value) {
    if (key in obj) {
        Object.defineProperty(obj, key, {
            value: value,
            enumerable: true,
            configurable: true,
            writable: true
        });
    } else {
        obj[key] = value;
    }
    return obj;
}
var mergeDeepWithKey = require('ramda/src/mergeDeepWithKey');
var find = require('ramda/src/find');
var path = require('ramda/src/path');
var pathOr = require('ramda/src/pathOr');
var curry = require('ramda/src/curry');
var compose = require('ramda/src/compose');
var pipe = require('ramda/src/pipe');
var pick = require('ramda/src/pick');
var prop = require('ramda/src/prop');
var propOr = require('ramda/src/propOr');
var props = require('ramda/src/props');
var isEmpty = require('ramda/src/isEmpty');
var map = require('ramda/src/map');
var addIndex = require('ramda/src/addIndex');
var Maybe = require('ramda-fantasy/src/Maybe');
var Either = require('ramda-fantasy/src/Either');
var castArray = require('lodash/castArray');
var SuperFactory = require('lib/super-factory');
var setCookie = require('lib/set-cookie');
var createCaseDescriptorsF = function createCaseDescriptorsF() {
    var writable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
    return function (obj) {
        return obj ? Object.keys(obj).reduce(function (caseDescriptors, key) {
            return Object.assign({}, caseDescriptors, function () {
                return obj[key] !== undefined ? _defineProperty({}, key, {
                    value: obj[key],
                    enumerable: true,
                    writable: writable
                }) : {};
            }());
        }, {}) : null;
    };
};
var createCaseDescriptors = createCaseDescriptorsF();
var createCaseDescriptorsImmutable = createCaseDescriptorsF(false);
var orElse = function orElse() {
    for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
    }
    var arg = void 0;
    for (var i = 0; i < args.length; i++) {
        if (args[i] !== undefined) {
            arg = args[i];
            break;
        }
    }
    return arg || null;
};
var safePush = function safePush(array) {
    return function (item) {
        return [].concat(_toConsumableArray(array || []), [item]);
    };
};
var resourceReplacer = function resourceReplacer(substitutions) {
    return function (match, p1) {
        return [substitutions[p1]].join('');
    };
};
var lookupResource = function lookupResource(resources) {
    return function (path, substitutions) {
        if (resources[path] === undefined)
            return path;
        if (resources[path].value === '')
            return null;
        var translation = resources[path].value;
        return substitutions ? translation.replace(/\{(\d)\}/g, resourceReplacer(substitutions)) : translation;
    };
};
var safePath = curry(compose(Maybe, path));
var maybeToArray = function maybeToArray(m) {
    return m.map(function (a) {
        return [a];
    }).getOrElse([]);
};
var maybeToEither = function maybeToEither(err) {
    return function (m) {
        return m.map(Either.Right).getOrElse(Either.Left(err));
    };
};
module.exports = {
    createCaseDescriptors: createCaseDescriptors,
    createCaseDescriptorsImmutable: createCaseDescriptorsImmutable,
    orElse: orElse,
    SuperFactory: SuperFactory,
    map: map,
    castArray: castArray,
    addIndex: addIndex,
    compose: compose,
    pipe: pipe,
    curry: curry,
    find: find,
    mergeDeepWithKey: mergeDeepWithKey,
    path: path,
    pathOr: pathOr,
    prop: prop,
    propOr: propOr,
    props: props,
    isEmpty: isEmpty,
    pick: pick,
    Maybe: Maybe,
    safePush: safePush,
    lookupResource: lookupResource,
    setCookie: setCookie,
    safePath: safePath,
    maybeToArray: maybeToArray,
    maybeToEither: maybeToEither
};
},{"lib/set-cookie":142,"lib/super-factory":143,"lodash/castArray":32,"ramda-fantasy/src/Either":43,"ramda-fantasy/src/Maybe":44,"ramda/src/addIndex":46,"ramda/src/compose":48,"ramda/src/curry":49,"ramda/src/find":55,"ramda/src/isEmpty":93,"ramda/src/map":95,"ramda/src/mergeDeepWithKey":96,"ramda/src/path":98,"ramda/src/pathOr":99,"ramda/src/pick":100,"ramda/src/pipe":101,"ramda/src/prop":102,"ramda/src/propOr":103,"ramda/src/props":104}],141:[function(require,module,exports){
'use strict';
var _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
            if (Object.prototype.hasOwnProperty.call(source, key)) {
                target[key] = source[key];
            }
        }
    }
    return target;
};
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}
var _require = require('lib/helpers'), castArray = _require.castArray, createCaseDescriptors = _require.createCaseDescriptors, SuperFactory = _require.SuperFactory;
var isHeadline = function isHeadline(tagName) {
    return [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6'
    ].indexOf(tagName) !== -1;
};
var customMerge = function customMerge(key, left, right) {
    var patterns = {
        'class': function _class() {
            return [].concat(_toConsumableArray(left.split(' ')), _toConsumableArray(right.split(' '))).join(' ').trim();
        },
        'style': function style() {
            return [].concat(_toConsumableArray(left.split(';')), _toConsumableArray(right.split(';'))).join(';').trim();
        }
    };
    return patterns[key] ? patterns[key]() : right;
};
var Node = SuperFactory(customMerge, true)(function (_ref) {
    var _ref$tagName = _ref.tagName, tagName = _ref$tagName === undefined ? 'div' : _ref$tagName, ns = _ref.ns, _ref$attributes = _ref.attributes, attributes = _ref$attributes === undefined ? {} : _ref$attributes, _ref$prepend = _ref.prepend, prepend = _ref$prepend === undefined ? [] : _ref$prepend, _ref$append = _ref.append, append = _ref$append === undefined ? [] : _ref$append, props = _ref.props, events = _ref.events, styles = _ref.styles, state = _ref.state;
    return function () {
        for (var _len = arguments.length, children = Array(_len), _key = 0; _key < _len; _key++) {
            children[_key] = arguments[_key];
        }
        children = children && children.length > 1 ? castArray(children) : castArray.apply(undefined, _toConsumableArray(children));
        append = castArray(append);
        prepend = castArray(prepend);
        return Object.create({}, _extends({
            tagName: {
                value: tagName,
                enumerable: true,
                writable: isHeadline(tagName)
            }
        }, createCaseDescriptors({
            attributes: attributes,
            children: children || prepend.length || append.length ? [].concat(_toConsumableArray(prepend), _toConsumableArray(castArray(children)), _toConsumableArray(append)) : null,
            props: props,
            events: events,
            styles: styles,
            ns: ns,
            state: state
        })));
    };
});
var jsonDOM = {
    $_title: Node.bind({ tagName: 'title' }),
    $_body: Node.bind({ tagName: 'body' }),
    $_h1: Node.bind({ tagName: 'h1' }),
    $_h2: Node.bind({ tagName: 'h2' }),
    $_h3: Node.bind({ tagName: 'h3' }),
    $_h4: Node.bind({ tagName: 'h4' }),
    $_h5: Node.bind({ tagName: 'h5' }),
    $_h6: Node.bind({ tagName: 'h6' }),
    $_p: Node.bind({ tagName: 'p' }),
    $_br: Node.bind({ tagName: 'br' }),
    $_hr: Node.bind({ tagName: 'hr' }),
    $_a: Node.bind({ tagName: 'a' }),
    $_nav: Node.bind({ tagName: 'nav' }),
    $_link: Node.bind({ tagName: 'link' }),
    $_ul: Node.bind({ tagName: 'ul' }),
    $_ol: Node.bind({ tagName: 'ol' }),
    $_li: Node.bind({ tagName: 'li' }),
    $_dl: Node.bind({ tagName: 'dl' }),
    $_dt: Node.bind({ tagName: 'dt' }),
    $_dd: Node.bind({ tagName: 'dd' }),
    $_table: Node.bind({ tagName: 'table' }),
    $_caption: Node.bind({ tagName: 'caption' }),
    $_th: Node.bind({ tagName: 'th' }),
    $_tr: Node.bind({ tagName: 'tr' }),
    $_td: Node.bind({ tagName: 'td' }),
    $_thead: Node.bind({ tagName: 'thead' }),
    $_tbody: Node.bind({ tagName: 'tbody' }),
    $_tfoot: Node.bind({ tagName: 'tfoot' }),
    $_col: Node.bind({ tagName: 'col' }),
    $_colgroup: Node.bind({ tagName: 'colgroup' }),
    $_form: Node.bind({ tagName: 'form' }),
    $_input: Node.bind({ tagName: 'input' }),
    $_textarea: Node.bind({ tagName: 'textarea' }),
    $_button: Node.bind({ tagName: 'button' }),
    $_select: Node.bind({ tagName: 'select' }),
    $_optgroup: Node.bind({ tagName: 'optgroup' }),
    $_option: Node.bind({ tagName: 'option' }),
    $_label: Node.bind({ tagName: 'label' }),
    $_fieldset: Node.bind({ tagName: 'fieldset' }),
    $_legend: Node.bind({ tagName: 'legend' }),
    $_iframe: Node.bind({ tagName: 'iframe' }),
    $_img: Node.bind({ tagName: 'img' }),
    $_figcaption: Node.bind({ tagName: 'figcaption' }),
    $_figure: Node.bind({ tagName: 'figure' }),
    $_picture: Node.bind({ tagName: 'picture' }),
    $_audio: Node.bind({ tagName: 'audio' }),
    $_source: Node.bind({ tagName: 'source' }),
    $_track: Node.bind({ tagName: 'track' }),
    $_video: Node.bind({ tagName: 'video' }),
    $_style: Node.bind({
        tagName: 'style',
        attributes: { type: 'text/css' }
    }),
    $_div: Node.bind({ tagName: 'div' }),
    $_span: Node.bind({ tagName: 'span' }),
    $_header: Node.bind({ tagName: 'header' }),
    $_footer: Node.bind({ tagName: 'footer' }),
    $_main: Node.bind({ tagName: 'main' }),
    $_section: Node.bind({ tagName: 'section' }),
    $_article: Node.bind({ tagName: 'article' }),
    $_aside: Node.bind({ tagName: 'aside' }),
    $_details: Node.bind({ tagName: 'details' }),
    $_dialog: Node.bind({ tagName: 'dialog' }),
    $_summary: Node.bind({ tagName: 'summary' }),
    $_data: Node.bind({ tagName: 'data' }),
    $_abbr: Node.bind({ tagName: 'abbr' }),
    $_address: Node.bind({ tagName: 'address' }),
    $_b: Node.bind({ tagName: 'b' }),
    $_blockquote: Node.bind({ tagName: 'blockquote' }),
    $_cite: Node.bind({ tagName: 'cite' }),
    $_code: Node.bind({ tagName: 'code' }),
    $_del: Node.bind({ tagName: 'del' }),
    $_em: Node.bind({ tagName: 'em' }),
    $_mark: Node.bind({ tagName: 'mark' }),
    $_pre: Node.bind({ tagName: 'pre' }),
    $_s: Node.bind({ tagName: 's' }),
    $_small: Node.bind({ tagName: 'small' }),
    $_strong: Node.bind({ tagName: 'strong' }),
    $_sub: Node.bind({ tagName: 'sub' }),
    $_sup: Node.bind({ tagName: 'sup' }),
    $_time: Node.bind({ tagName: 'time' }),
    $_svg: Node.bind({
        tagName: 'svg',
        ns: 'svg'
    }),
    $_path: Node.bind({
        tagName: 'path',
        ns: 'svg'
    }),
    $_g: Node.bind({
        tagName: 'g',
        ns: 'svg'
    }),
    $_polygon: Node.bind({
        tagName: 'polygon',
        ns: 'svg'
    }),
    $_radialGradient: Node.bind({
        tagName: 'radialGradient',
        ns: 'svg'
    }),
    $_stop: Node.bind({
        tagName: 'stop',
        ns: 'svg'
    }),
    $_defs: Node.bind({
        tagName: 'defs',
        ns: 'svg'
    }),
    $_clipPath: Node.bind({
        tagName: 'clipPath',
        ns: 'svg'
    }),
    $_use: Node.bind({
        tagName: 'use',
        ns: 'svg'
    })
};
module.exports = jsonDOM;
},{"lib/helpers":140}],142:[function(require,module,exports){
'use strict';
var document = require('global/document');
module.exports = function (name, value) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    if (!name || !value)
        return null;
    var _options$expiryDays = options.expiryDays, expiryDays = _options$expiryDays === undefined ? 365 : _options$expiryDays, _options$path = options.path, path = _options$path === undefined ? '/' : _options$path, domain = options.domain;
    var expireDate = new Date();
    expireDate.setDate(expireDate.getDate() + expiryDays);
    var cookie = [
        name + '=' + value,
        'expires=' + expireDate.toUTCString(),
        'path=' + path
    ];
    if (domain)
        cookie.push('domain=' + domain);
    document.cookie = cookie.join(';');
};
},{"global/document":30}],143:[function(require,module,exports){
'use strict';
var mergeDeepWithKey = require('ramda/src/mergeDeepWithKey');
module.exports = function () {
    var customMerge = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function (key, left, right) {
        return right;
    };
    var immediateCall = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var Factory = function Factory(fn) {
        var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var Fn = immediateCall ? fn(args) : {};
        Fn.bind = function () {
            for (var _len = arguments.length, argsRight = Array(_len), _key = 0; _key < _len; _key++) {
                argsRight[_key] = arguments[_key];
            }
            return Factory(fn, mergeDeepWithKey.apply(undefined, [
                customMerge,
                args
            ].concat(argsRight)));
        };
        Fn.map = function (mapper) {
            return Factory(fn, mapper(args));
        };
        Fn.call = fn(args);
        Fn.callOrElse = function (orElse) {
            return fn(args) || orElse;
        };
        Fn.expose = function () {
            return args;
        };
        return Fn;
    };
    return Factory;
};
},{"ramda/src/mergeDeepWithKey":96}]},{},[115]);
