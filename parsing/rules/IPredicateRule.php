<?php
/**
 * Defines the interface for a rule used in the scanning of text for the purpose of
 * document partitioning or text styling. A predicate rule can only return one single
 * token after having successfully detected content. This token is called success token.
 * Also, it also returns a token indicating that this rule has not been successful.
 *
 * @see ICharacterScanner
 */
interface IPredicateRule{
	/**
	 * Returns the success token of this predicate rule.
	 *
	 * @return IToken the success token of this rule
	 */
	 public function getSuccessToken();

	/**
	 * Evaluates the rule by examining the characters available from
	 * the provided character scanner. The token returned by this rule
	 * returns <code>true</code> when calling <code>isUndefined</code>,
	 * if the text that the rule investigated does not match the rule's requirements. Otherwise,
	 * this method returns this rule's success token. If this rules relies on a text pattern
	 * comprising a opening and a closing character sequence this method can also be called
	 * when the scanner is positioned already between the opening and the closing sequence.
	 * In this case, <code>resume</code> must be set to <code>true</code>.
	 *
	 * @param ICharacterScanner scanner the character scanner to be used by this rule
	 * @param boolean resume indicates that the rule starts working between the opening and the closing character sequence
	 * @return IToken the token computed by the rule
	 */
	public function evaluate($scanner,$resume);
}
