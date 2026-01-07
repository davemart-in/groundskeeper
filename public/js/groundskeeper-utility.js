// -------------------------------------------------------------
// VARS
// -------------------------------------------------------------
let sessionStart = new Date().getTime();
let lastEventTime = sessionStart;
let uuid = window.location.pathname.split('/').pop();
// -------------------------------------------------------------
// UTILITY FUNCTIONS
// -------------------------------------------------------------
//console.log(_$('#Logo'));
function _$(query) { if (!query) { return console.error('_$ error'); } var results = document.querySelectorAll(query); if (results.length === 1) { return document.querySelector(query); } else if (results.length === 0) { return false; } else { return results; } }
// AJAX request to server
function _ajax(method, url, data, callback) { /* method should be GET or POST */ var request = new XMLHttpRequest(); request.open(method, url); /* If data is formData, don't send Content-Type header */ if (data && data.constructor.name !== 'FormData') { request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); } request.onload = function () { if (request.status >= 200 && request.status < 400) { if (callback) { return callback(JSON.parse(request.responseText)); } } else { console.error('Ajax error occured.'); } }; request.onerror = function () { console.error('There seems to be a connection error. Check your internet.'); }; request.send(data); }
//_append(document.getElementById('Logo'), '<div>fun</div>');
function _append(el, html) { if (!el || !html) { return console.error('_append error'); } if (typeof html === 'string') { el.insertAdjacentHTML('beforeend', html); } else { el.appendChild(html); } }
//console.log(_attrGet(document.getElementById('Logo'), 'id'));
function _attrGet(el, value) { if (!el || !value) { return console.error('_attrGet error'); } return el.getAttribute(value); }
//_attrRemove(document.getElementById('Logo'), 'id');
function _attrRemove(el, name) { if (!el || !name) { return console.error('_attrRemove error'); } return el.removeAttribute(name); }
//_attrSet(document.getElementById('Logo'), 'name', 'fun');
function _attrSet(el, name, value) { if (!el || !name || value === '') { return console.error('_attrSet error'); } return el.setAttribute(name, value); }
// Insert node before another node
function _before(newNode, node) { if (!newNode || !node) { return console.error('_insertBefore error'); } return node.insertAdjacentHTML('beforebegin', newNode); }
// Toggle boolean value
function _boolToggle(bool) { return (bool = !bool); }
// Capitalize first letter of string
function _capitalize(string) { if (!string) { return console.error('_capitalizeFirstLetter error'); } return string.charAt(0).toUpperCase() + string.slice(1); }
//_classAdd(document.getElementById('Logo'), 'fun');
function _classAdd(el, cl) { if (!el || !cl) { return console.error('_classAdd error'); } if (el) { if (el.classList) { el.classList.add(cl); } else { el.className += ' ' + cl; } } }
//console.log(_classHas(document.getElementById('Logo'), 'fun'));
function _classHas(el, className) { if (!el || !className) { return console.error('_classHas error'); } if (!el.classList) { return false; } return el.classList.contains(className); }
//_classRemove(document.getElementById('Logo'), 'fun');
function _classRemove(el, cl) { if (!el || !cl) { return console.error('_classRemove error'); } if (el.classList) { return el.classList.remove(cl); } if (el.className) { el.className = el.className.replace(new RegExp('(^|\\b)' + cl.split(' ').join('|') + '(\\b|$)', 'gi'), ' ').trim(); } }
// Toggle class on/off
function _classToggle(el, cl) { return el.classList.toggle(cl); }
// Get cookie
function _cookieGet(name) { if (!name) { return console.error('_cookieGet error'); } let nameEQ = name + "="; let ca = document.cookie.split(';'); for (let i = 0; i < ca.length; i++) { let c = ca[i]; while (c.charAt(0) === ' ') c = c.substring(1, c.length); if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length); } return null; }
// Add Cookie
function _cookieSet(name = '', value = '', expires = 0, domain = '', path = '/', prefix = '', samesite = 'Lax') { let cookieValue = encodeURIComponent(prefix + name) + "=" + encodeURIComponent(value); if (expires > 0) { const date = new Date(); date.setTime(date.getTime() + (expires * 1000)); cookieValue += "; expires=" + date.toUTCString(); } cookieValue += "; path=" + path; if (domain) { cookieValue += "; domain=" + domain; } cookieValue += "; secure"; if (['None', 'Lax', 'Strict'].includes(samesite)) { cookieValue += "; samesite=" + samesite; } else { cookieValue += "; samesite=Lax"; } document.cookie = cookieValue; }
// Currency formatter
function _currencyFormatter(currencyCode, amount) { if (!currencyCode || !amount) { return console.error('_currencyFormatter error'); } return new Intl.NumberFormat(_locale(), { style: 'currency', currency: currencyCode, currencyDisplay: 'narrowSymbol' }).format(amount); }
// document.addEventListener('scroll', _debounce(function _scroll() { var scrolled = parseInt(document.body.scrollTop); console.log(scrolled); }, 300));
function _debounce(fn, delay) { if (!fn || !delay) { return console.error('_debounce error'); } let timer; return function () { var context = this, args = arguments; clearTimeout(timer); timer = setTimeout(function () { fn.apply(context, args); }, delay); }; }
// Decode HTML Entities
function _decodeHtmlEntities(str) { var textArea = document.createElement('textarea'); textArea.innerHTML = str; return textArea.value; }
// Dispatch Apline event
function _dispatch(templateId, eventName, props = {}) { if (templateId) { var templateContent = document.getElementById(templateId).content; var clone = document.importNode(templateContent, true); var wrapper = document.createElement('div'); wrapper.appendChild(clone); props.modalContent = wrapper.innerHTML; } const customEvent = new CustomEvent(eventName, { detail: props }); window.dispatchEvent(customEvent); }
// Trigger error event
function _error(message) { const customEvent = new CustomEvent('error', { detail: message }); window.dispatchEvent(customEvent); }
// Escape function for templates
function _escape(str) { if (str == null) return ''; return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
// Send event to server
function _event(eventString) { if (!_exists(eventString)) { return false; } let currentTime = new Date().getTime(); let timeDiff = currentTime - lastEventTime; let humanReadableDiff = _formatTimeDiff(timeDiff); lastEventTime = currentTime; let data = `event=${encodeURIComponent(eventString)}&time_diff=${encodeURIComponent(humanReadableDiff)}`; _ajax('POST', `/${uuid}`, data, function (response) { console.log(response); }); }
// Check to see if element exists
function _exists(el) { return (typeof (el) != 'undefined' && el != null); }
// _$('p').each(function( el ) { _text(el, 'foo'); });
Object.prototype.each = function (callback) { if (typeof callback !== 'function') throw new Error('Each error'); if (this.nodeType) { callback(this); return this; } for (var i = 0; i < this.length; i++) { callback(this[i]); } return this; }
// Fade element in
function _fadeIn(el) { if (!_exists(el)) { return false; } var op = 0.1; el.style.display = 'block'; var timer = setInterval(function () { if (op >= 1) { clearInterval(timer); } el.style.opacity = op; el.style.filter = 'alpha(opacity=' + op * 100 + ")"; op += op * 0.1; }, 10); }
// Fade element out
function _fadeOutAndRemove(el) { if (!_exists(el)) { return false; } var op = 1; var timer = setInterval(function () { if (op <= 0.1) { clearInterval(timer); el.remove(); } el.style.opacity = op; el.style.filter = 'alpha(opacity=' + op * 100 + ")"; op -= op * 0.1; }, 10); }
// Check to see if file exists
function _fileExists(url) { if (!url) { return console.error('_fileExists error'); } var http = new XMLHttpRequest(); http.open('HEAD', url, false); http.send(); return http.status != 404 && !http.responseURL.includes('/404/'); }
// Remove everything but alpa + numeric + punctuation (like emojis)
function _filterString(str) { return str.replace(/[^a-zA-Z0-9\s!"#$%&'()*+,\-.\/:;<=>?@[\\\]^_`{|}~]/g, ''); }
// Add focus to element
function _focus(el) { if (!_exists(el)) { return false; } el.focus(); }
// Get all focussable elements
function _focusable(el) { var focusable = el.querySelectorAll('a[href], button, input, textarea, select,[tabindex]:not([tabindex="-1"])'), focusableArr = Array.from(focusable); for (var i = 0; i < focusableArr.length; i++) { if (focusableArr[i].style.display === 'none') { focusableArr.splice(i, 1); } } return focusableArr; }
// Format time diff
function _formatTimeDiff(ms) { let seconds = Math.floor(ms / 1000); let minutes = Math.floor(seconds / 60); seconds = seconds % 60; let hours = Math.floor(minutes / 60); minutes = minutes % 60; let parts = []; if (hours > 0) parts.push(hours + ' hour' + (hours > 1 ? 's' : '')); if (minutes > 0) parts.push(minutes + ' minute' + (minutes > 1 ? 's' : '')); if (seconds > 0 || parts.length === 0) parts.push(seconds + ' second' + (seconds > 1 ? 's' : '')); return parts.join(', '); }
// Hide element
function _hide(el) { if (!_exists(el)) { return false; } return el.style.display = 'none'; }
//_html(document.getElementById('Logo'), '<div>Fun</div>');
function _html(el, html) { if (!el) { return false; } return el.innerHTML = _purify(html); }
// Is responsive
function _isResponsive() { return window.innerWidth < 768; }
//_jsonGet('https://example.net/something.json', accountSettingsSuccess);
function _jsonGet(url, callback) { if (!url || !callback) { return console.error('_jsonGet error'); } try { var request = new XMLHttpRequest(); request.open('GET', url, true); request.responseType = 'text'; request.onload = function () { if (request.responseURL.includes('/404/')) { return false } callback(JSON.parse(request.response.toString('utf8'))); }; request.onerror = function () { return false }; request.send(); } catch (error) { return console.error('Oops. There was an error in loading new content.'); } }
//_loadCss('https://example.net/something.css');
function _loadCss(url) { if (!url) { return console.error('_loadCss error'); } var link = document.createElement('link'); link.rel = 'stylesheet'; link.type = 'text/css'; link.href = url; document.getElementsByTagName('head')[0].appendChild(link); link.onload = function () { cssIsLoaded = true; }; }
//_loadTemplate('<p class="text"><%=value%></p>', 'blockText');
function _loadTemplate(content, id) { if (!content || !id) { return console.error('_loadTemplate error'); } var script = document.createElement("script"); script.type = "text/html"; script.id = id; script.text = content; document.querySelector('#howdy-frame').appendChild(script); }
// Get users locale
function _locale() { if (navigator.languages != undefined) { return navigator.languages[0]; } return navigator.language || navigator.userLanguage; }
// Is mobile?
function _isMobile() { return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent); }
// Is email
function _isEmail(email) { if (!email) { return console.error('_isEmail Called with empty input'); } var regex = /^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/; return regex.test(email); }
// Checks if object is empty
function _isEmptyObject(obj) { return Object.keys(obj).length === 0 && obj.constructor === Object; }
//_objectToSortedArray(obj);
function _objectToSortedArray(obj) { if (!obj) { return console.error('_objectToSortedArray error'); } var sorted = {}, key, arr = [], i; arr = Object.keys(obj).map(p => Object.assign(obj[p], { obj: p })); arr.sort(function (a, b) { return a.s - b.s; }); return arr; }
//_on(_$('.head'), 'click', function(e) {});
function _on(el, event, fn) { if (!el || !event || !fn) { return false; } if (event === 'click') { const wrappedHandler = function (e) { if (this._eventFired) return; this._eventFired = true; setTimeout(() => { this._eventFired = false; }, 100); fn.call(this, e); }; if (typeof el === 'object' && el.length !== undefined) { for (let i = 0; i < el.length; i++) { if (el[i]) { el[i].addEventListener('touchend', wrappedHandler, { passive: false }); el[i].addEventListener('click', wrappedHandler); } } } else if (el) { el.addEventListener('touchend', wrappedHandler, { passive: false }); el.addEventListener('click', wrappedHandler); } } else { if (typeof el === 'object' && el.length !== undefined) { for (let i = 0; i < el.length; i++) { if (el[i]) { el[i].addEventListener(event, fn); } } } else if (el) { el.addEventListener(event, fn); } } }
//_off(_$('.head'), 'click', function(e) {});
function _off(el, event, fn) { if (!el || !event || !fn) { return false; } if (event === 'click') { if (typeof el === 'object' && el.length !== undefined) { for (let i = 0; i < el.length; i++) { if (el[i]) { el[i].removeEventListener('touchend', fn); el[i].removeEventListener('click', fn); } } } else if (el) { el.removeEventListener('touchend', fn); el.removeEventListener('click', fn); } } else { if (typeof el === 'object' && el.length !== undefined) { for (let i = 0; i < el.length; i++) { if (el[i]) { el[i].removeEventListener(event, fn); } } } else if (el) { el.removeEventListener(event, fn); } } }
//_prepend(document.getElementById('Logo'), '<div>fun</div>');
function _prepend(el, html) { if (!el || !html) { return console.error('_prepend error'); } if (typeof html === 'string') { return el.insertAdjacentHTML('afterbegin', html); } return el.insertBefore(html, el.firstChild); }
// Add purify everywhere we add HTML to the DOM
// _purify(html);
function _purify(dirty) { if (typeof dirty === 'object') { return Array.isArray(dirty) ? _purifyArray(dirty) : _purifyObject(dirty); } return DOMPurify.sanitize(dirty); }
// Iterate through object and purify all strings
function _purifyObject(dirty) { var clean = {}; for (var key in dirty) { if (!dirty.hasOwnProperty(key)) continue; if (dirty[key] === null || dirty[key] === undefined) { clean[key] = dirty[key]; } else if (typeof dirty[key] === 'number' || typeof dirty[key] === 'boolean') { clean[key] = dirty[key]; } else if (typeof dirty[key] === 'string') { clean[key] = DOMPurify.sanitize(dirty[key]); } else if (typeof dirty[key] === 'object') { clean[key] = Array.isArray(dirty[key]) ? _purifyArray(dirty[key]) : _purifyObject(dirty[key]); } } return clean; }
// Iterate through array and purify all strings
function _purifyArray(dirty) { return dirty.map(item => _purify(item)); }
//_remove(document.getElementById('Logo'));
function _remove(el) { if (!el) { return console.error('_remove error'); } return el.parentNode.removeChild(el); }
// Scroll to element
function _scrollTo(element, options = {}) { const defaults = { offset: 0, duration: 300, container: 'body' }; const settings = { ...defaults, ...options }; const container = _$(settings.container); if (!container) return; const elementPosition = element.offsetTop; const offsetPosition = elementPosition + settings.offset; container.scrollTo({ top: offsetPosition, behavior: 'smooth' }); }
// Set and get objects from session (perishable) storage
// _sessionGet('name');
function _sessionGet(id) { var value = sessionStorage.getItem(id); try { return JSON.parse(value); } catch (e) { return value; } }
// _sessionSet('name', {first: 'Elroy', last : 'The Rooster'});
function _sessionSet(id, value) { if (typeof value === 'object') value = JSON.stringify(value); sessionStorage.setItem(id, value); }
async function _sha256(email) { const encoder = new TextEncoder(); const data = encoder.encode(email.trim().toLowerCase()); const hashBuffer = await crypto.subtle.digest('SHA-256', data); const hashArray = Array.from(new Uint8Array(hashBuffer)); const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join(''); return hashHex; }
// Show element
function _show(el, as = 'block') { if (!_exists(el)) { console.error('Element does not exist or is not passed correctly'); return false; } if (el.style) { return el.style.display = as; } else { console.error('The passed object is not a valid DOM element'); return false; } }
// console.log(_text(_$('#IntroSidebar p')));
function _text(el, txt) { if (!el) { return console.error('_text error'); } return el.textContent = txt; }
// Convert MySQL datetimestamp to human readable date (Jan, 20, 2022)
function _timestampToDate(timestamp) { if (!timestamp || typeof timestamp !== 'string') { return console.error('_timestampToDate error'); } var t = timestamp.split(/[- :]/); var m = { 1: "Jan", 2: "Feb", 3: "Mar", 4: "Apr", 5: "May", 6: "Jun", 7: "Jul", 8: "Aug", 9: "Sep", 10: "Oct", 11: "Nov", 12: "Dec" }; var d = new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4], t[5])); return m[d.getMonth() + 1] + ' ' + d.getDate() + ', ' + d.getFullYear(); }
// Simple JavaScript Templating
// John Resig - http://ejohn.org/blog/javascript-micro-templating/ - MIT Licensed
//_tmpl('staging', stagingData);
function _tmpl(str, data) { var tmplCache = []; var fn = !/\W/.test(str) ? tmplCache[str] = tmplCache[str] || _tmpl(document.getElementById(str).innerHTML) : new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};" + "with(obj){p.push('" + str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").replace(/\t-(.*?)%>/g, "',_escape($1),'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');"); return data ? fn(data) : fn; }//_urlParams().msg
// Truncate text with ellipsis
function _truncateText(text, maxLength) { return text.length > maxLength ? text.substring(0, maxLength) + '...' : text; }
// Get URL params
function _urlParams() { var queryString = window.location.search.slice(1); var obj = {}; if (queryString) { queryString = queryString.split('#')[0]; var arr = queryString.split('&'); for (var i = 0; i < arr.length; i++) { var a = arr[i].split('='); var paramName = a[0]; var paramValue = typeof (a[1]) === 'undefined' ? true : a[1]; paramName = paramName.toLowerCase(); if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase(); if (paramName.match(/\[(\d+)?\]$/)) { var key = paramName.replace(/\[(\d+)?\]/, ''); if (!obj[key]) obj[key] = []; if (paramName.match(/\[\d+\]$/)) { var index = /\[(\d+)\]/.exec(paramName)[1]; obj[key][index] = paramValue; } else { obj[key].push(paramValue); } } else { if (!obj[paramName]) { obj[paramName] = paramValue; } else if (obj[paramName] && typeof obj[paramName] === 'string') { obj[paramName] = [obj[paramName]]; obj[paramName].push(paramValue); } else { obj[paramName].push(paramValue); } } } } return obj; }
// Check if element is visible
function _visible(el) { if (!el) { return false; } var style = window.getComputedStyle(el); return (style.display !== 'none'); }
// Calculate width of element
function _width(el) { if (!el) { return console.log('_width error'); } return el.clientWidth; }
// Error handling
window.onerror = function (message, file, line, column, error) { console.error(error); if (error && error.stack) { message = message + ' - ' + error.stack; } _ajax('POST', '/api/error/', 'message=' + encodeURIComponent(message) + '&file=' + encodeURIComponent(file) + '&line=' + encodeURIComponent(line) + '&url=' + window.location.href, false); };