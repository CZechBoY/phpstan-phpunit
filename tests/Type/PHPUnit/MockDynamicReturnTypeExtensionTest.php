<?php declare(strict_types = 1);

namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;

final class MockDynamicReturnTypeExtensionTest extends \PHPUnit_Framework_TestCase
{

	/** @var \PHPStan\Type\PHPUnit\MockDynamicReturnTypeExtension */
	private $extension;

	protected function setUp()
	{
		$this->extension = new MockDynamicReturnTypeExtension();
	}

	/**
	 * @dataProvider dataMockedClass
	 *
	 * @param string $mockedClass
	 */
	public function testKnowReturnType(string $mockedClass)
	{
		$methodReflection = $this->createMock(MethodReflection::class);
		$methodReflection
			->method('getReturnType')
			->willReturn(new ObjectType(\PHPUnit_Framework_MockObject_MockObject::class));

		$scope = $this->createMock(Scope::class);

		$methodCall = $this->createMock(MethodCall::class);
		$arg = $this->createMock(Arg::class);
		$value = $this->createMock(ClassConstFetch::class);
		$value->class = new \PhpParser\Node\Name($mockedClass);
		$methodCall->args = [
			0 => $arg,
		];
		$arg->value = $value;

		$resultType = $this->extension->getTypeFromMethodCall($methodReflection, $methodCall, $scope);

		$this->assertSame(sprintf('%s|%s', $mockedClass, \PHPUnit_Framework_MockObject_MockObject::class), $resultType->describe());
	}

	/**
	 * @return array
	 */
	public function dataMockedClass(): array
	{
		return [
			['AbcClass'],
		];
	}

}
