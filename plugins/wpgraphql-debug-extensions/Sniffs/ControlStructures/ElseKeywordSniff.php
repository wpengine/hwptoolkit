<?php
/**
 * Sniff to detect usage of else keywords in if statements
 */
namespace HWPStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ElseKeywordSniff implements Sniff
{
	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array
	 */
	public function register()
	{
		return [T_ELSE, T_ELSEIF];
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int                         $stackPtr  The position of the current token in the stack.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		$token = $tokens[$stackPtr];

		if ($token['code'] === T_ELSE) {
			$warning = 'Usage of "else" detected; consider refactoring to avoid else branches';
			$phpcsFile->addWarning($warning, $stackPtr, 'ElseDetected');

			return;
		}

		if ($token['code'] === T_ELSEIF) {
			$warning = 'Usage of "elseif" detected; consider refactoring to avoid else branches';
			$phpcsFile->addWarning($warning, $stackPtr, 'ElseIfDetected');
		}
	}
}