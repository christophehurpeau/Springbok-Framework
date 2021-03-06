<?php
/**
 * Defines the interface for a rule used in the scanning of text for the purpose of document
 * partitioning or text styling.
 *
 * @see ICharacterScanner
 */
interface IRule{
	/**
	 * Evaluates the rule by examining the characters available from the provided character scanner.
	 * The token returned by this rule returns <code>true</code> when calling
	 * <code>isUndefined</code>, if the text that the rule investigated does not match the rule's
	 * requirements
	 *
	 * @param ICharacterScanner scanner the character scanner to be used by this rule
	 * @return IToken the token computed by the rule
	 */
	public function evaluate($scanner);
}
