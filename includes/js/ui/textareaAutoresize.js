/* http://jsfiddle.net/ImpressiveWebs/fGNNT/1333/ */

<p>Code explanation: <a href="http://www.impressivewebs.com/textarea-auto-resize/">Textarea Auto Resize</a></p>

<textarea id="comments" placeholder="Type many lines of texts in here and you will see magic stuff" class="common"></textarea>






/*global document:false, $:false */
var txt = $('#comments'),
	hiddenDiv = $(document.createElement('div')),
	content = null;

txt.addClass('txtstuff');
hiddenDiv.addClass('hiddendiv common');

$('body').append(hiddenDiv);

txt.on('keyup', function () {

	content = $(this).val();

	content = content.replace(/\n/g, '<br>');
	hiddenDiv.html(content + '<br class="lbr">');

	$(this).css('height', hiddenDiv.height());

});



/* CSS */

body {
	 margin: 20px;
}

p {
	margin-bottom: 14px;
}

textarea {
	color: #444;
	padding: 5px;
}

.txtstuff {
	resize: none; /* remove this if you want the user to be able to resize it in modern browsers */
	overflow: hidden;
}

.hiddendiv {
	display: none;
	white-space: pre-wrap;
	word-wrap: break-word;
	overflow-wrap: break-word; /* future version of deprecated 'word-wrap' */
}

/* the styles for 'commmon' are applied to both the textarea and the hidden clone */
/* these must be the same for both */
.common {
	width: 500px;
	min-height: 50px;
	font-family: Arial, sans-serif;
	font-size: 13px;
	overflow: hidden;
}

.lbr {
	line-height: 3px;
}