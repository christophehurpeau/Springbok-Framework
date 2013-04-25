includeCore('base/listenable');
/* http://code.jquery.com/ui/jquery-ui-git.js - http://www.cambiaresearch.com/articles/15/javascript-char-codes-key-codes*/
//var and not : S.ui.keyCodes : private.
S.ui={};
/**
 * Enum for key codes.
 * @enum {number}
 */
var keyCodes={
	BACKSPACE: 8,
	COMMA: 188,
	DELETE: 46,
	ENTER: 13,
	ESCAPE: 27,
	NUMPAD_ADD: 107,
	NUMPAD_DECIMAL: 110,
	NUMPAD_DIVIDE: 111,
	NUMPAD_ENTER: 108,
	NUMPAD_MULTIPLY: 106,
	NUMPAD_SUBTRACT: 109,
	PERIOD: 190,
	SPACE: 32,
	TAB: 9,
	
	
	SHIFT:16,
	CTRL:17,
	ALT:18,
	PAUSE_BREAK:19,
	CAPS_LOCK:20,
	
	PAGE_UP: 33,
	PAGE_DOWN: 34,
	END: 35,
	HOME: 36,
	LEFT: 37,
	UP: 38,
	RIGHT: 39,
	DOWN: 40
};

includeCore('ui/widget');
