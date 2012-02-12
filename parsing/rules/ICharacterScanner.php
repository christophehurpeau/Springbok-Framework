<?php
interface ICharacterScanner{
	const EOF= -1;
	
	/**
	 * Provides rules access to the legal line delimiters. The returned
	 * object may not be modified by clients.
	 *
	 * @return char[][] the legal line delimiters
	 */
	public function getLegalLineDelimiters();
	
	/**
	 * Returns the column of the character scanner.
	 *
	 * @return int the column of the character scanner
	 */
	public function getColumn();

	/**
	 * Returns the next character or EOF if end of file has been reached
	 *
	 * @return int the next character or EOF
	 */
	public function read();

	/**
	 * Rewinds the scanner before the last read character.
	 */
	public function unread();
}
