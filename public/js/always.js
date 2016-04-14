/* always.js */

/**
 * update content-box height.
 * press the footer to bottom.
 *
 * @return <void>
 */
function footerToBottom() {
	//	content-box ID
	var contentContainerID = '#demo-chat-body .nano';
	//	footer ID
	var footerID = '#footer';
	//	header ID
	var headerID = '#header';
	var browserHeight = $(window).outerHeight();
	var footerHeight = $(footerID).outerHeight();
	var headerHeight = $(headerID).outerHeight();

	var computedHeight = browserHeight - footerHeight - headerHeight;
	//	width of content box
	$(contentContainerID).css({
		'height': computedHeight
	});
}

/**
 * Function logs content of the parameter to console
 *
 * @param <mixed> message - What to log
 */
function log(message) {
	if (window.rhjcjdjr && rhjcjdjr.DEBUG == true)
		console.log(message);
}

/**
 * Function generates a random string for use in unique IDs, etc
 *
 * @param <int> n - The length of the string
 */
function randomString(n) {
    if (! n) {
        n = 5;
    }

    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i=0; i < n; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;
}

/**
 * Function replaces "\n" to "<br>"
 *
 * @param <string> str - String raplacement to be performed in
 */
function nl2br(str) {
	return str.replace(/([^>])\n+/g, '$1<br>');
}

/**
 * Function escapes string from xss.
 * Turns html to text.
 *
 * @param <string> str - String of text
 */
function e(str) {
	var div = document.createElement('div');
	var text = document.createTextNode(str);
	div.appendChild(text);
	return div.innerHTML;
}

/**
 * Function check if it's parameter is empty
 *
 * @see php empty()
 *
 * @param <mixed> mixedVar - Value to be checked
 */
function empty(mixedVar) {
	return (	mixedVar === "" ||
				mixedVar === 0 ||
				mixedVar === "0" ||
				mixedVar === null ||
				mixedVar === false ||
				(is_array(mixedVar) && mixedVar.length === 0)
	);
}

/**
 * Function check if it's parameter is an array
 *
 * @see php is_array()
 *
 * @param <mixed> mixedVar - Value to be checked
 */
function is_array(mixedVar) {
	return (mixedVar instanceof Array);
}

/**
 * Function check if it's parameter is an integer
 *
 * @see php is_int()
 *
 * @param <mixed> mixedVar - Value to be checked
 */
function is_int(mixedVar) {
    return Number(mixedVar) === mixedVar && mixedVar % 1 === 0;
}

/**
 * Function check if it's parameter is an float (double)
 *
 * @see php is_float()
 *
 * @param <mixed> mixedVar - Value to be checked
 */
function is_float(mixedVar){
    return Number(mixedVar) === mixedVar && mixedVar % 1 !== 0;
}

/**
 * Function check if it's parameter is a scalar value
 *
 * @see php is_scalar()
 *
 * @param <mixed> mixedVar - Value to be checked
 */
function is_scalar(mixedVar) {
  return (/boolean|number|string/).test(typeof mixedVar);
}

/**
 * @see php preg_replace()
 *
 * @param <regExp> regExp - Regular expression
 * @param <scalar> replacement - Value to be replaced with
 * @param <string> subject - Where to replace
 *
 * @return <array>
 */
function preg_replace(regExp, replacement, subject) {
	//	/(?:\r\n?|\n){2,}/
	return subject.replace(regExp, replacement);
}

/**
 * Function escapes string, replaces more than 2 "\r\n" to "<br><br>"
 *
 * @param <string> string - String to be precessed
 *
 * @return <string>
 */
function escape(string) {
	return nl2br(preg_replace(/(?:\r\n?|\n){2,}/, '<br><br>', e(string)));
}

/**
 * Function checks if connection to Enternet is available
 *
 * @return <void>
 */
function connectionAlright(trueCb, falseCb) {
	$.ajax({
		method: 'head',
		url: '/',
		cache: false,
	})
	.done(function() {
		trueCb && trueCb();
	})
	.fail(function() {
		falseCb && falseCb();
	});
}

/**
 * Function escapes string, replaces more than 2 "\r\n" to "<br><br>"
 *
 * @deprecated. Use function "escape" instead.
 *
 * @return <string>
 */

//	String.prototype.escape = function() {
//		return nl2br(preg_replace(/(?:\r\n?|\n){2,}/, '<br><br>', e(this)));
//	}