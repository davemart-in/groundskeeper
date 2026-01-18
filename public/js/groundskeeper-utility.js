// -------------------------------------------------------------
// UTILITY FUNCTIONS
// -------------------------------------------------------------

// Escape function for templates
function _escape(str) {
	if (str == null) return '';
	return String(str)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;');
}

// Simple JavaScript Templating
// John Resig - http://ejohn.org/blog/javascript-micro-templating/ - MIT Licensed
// Usage: _tmpl('template-id', data) or _tmpl('<%= value %>', data)
function _tmpl(str, data) {
	var tmplCache = [];
	var fn = !/\W/.test(str)
		? tmplCache[str] = tmplCache[str] || _tmpl(document.getElementById(str).innerHTML)
		: new Function("obj",
			"var p=[],print=function(){p.push.apply(p,arguments);};" +
			"with(obj){p.push('" +
			str
				.replace(/[\r\t\n]/g, " ")
				.split("<%").join("\t")
				.replace(/((^|%>)[^\t]*)'/g, "$1\r")
				.replace(/\t=(.*?)%>/g, "',$1,'")
				.replace(/\t-(.*?)%>/g, "',_escape($1),'")
				.split("\t").join("');")
				.split("%>").join("p.push('")
				.split("\r").join("\\'") +
			"');}return p.join('');");
	return data ? fn(data) : fn;
}
