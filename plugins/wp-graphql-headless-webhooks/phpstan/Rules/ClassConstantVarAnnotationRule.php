<?php

declare(strict_types=1);

namespace HWP\Previews\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Comment\Doc;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Stmt\ClassConst>
 */
class ClassConstantVarAnnotationRule implements Rule
{
	public function getNodeType(): string
	{
		return Node\Stmt\ClassConst::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$docComment = $node->getDocComment();
		if (!$docComment instanceof Doc) {
			return [
				RuleErrorBuilder::message('Class constant must have a @var annotation in its docblock.')->build(),
			];
		}

		$docText = $docComment->getText();
		if (!preg_match('/@var\s+\S+/', $docText)) {
			return [
				RuleErrorBuilder::message('Class constant docblock must contain a non-empty @var annotation.')->build(),
			];
		}

		return [];
	}
}