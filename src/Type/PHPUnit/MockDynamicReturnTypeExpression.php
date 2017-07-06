<?php declare(strict_types = 1);

namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class MockDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public static function getClass(): string
	{
		return \PHPUnit_Framework_TestCase::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'createMock';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		if (count($methodCall->args) === 0) {
			return $methodReflection->getReturnType();
		}

		$arg = $methodCall->args[0]->value;
		if (!($arg instanceof \PhpParser\Node\Expr\ClassConstFetch)) {
			return $methodReflection->getReturnType();
		}

		$mockedClass = $arg->class;
		if (!($mockedClass instanceof \PhpParser\Node\Name)) {
			return $methodReflection->getReturnType();
		}

		$mockedClass = (string)$mockedClass;

		$type = new ObjectType($mockedClass);

		return TypeCombinator::combine($methodReflection->getReturnType(), $type);
	}

}
