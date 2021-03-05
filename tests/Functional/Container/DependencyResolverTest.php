<?php
declare(strict_types=1);

namespace SergonieTest\Unit\Container;

use Sergonie\Container\DependencyResolver;
use SergonieTest\Fixtures\A;
use SergonieTest\Fixtures\B;
use SergonieTest\Fixtures\Bar;
use SergonieTest\Fixtures\BInterface;
use SergonieTest\Fixtures\Boo;
use SergonieTest\Fixtures\C;
use SergonieTest\Fixtures\D;
use SergonieTest\Fixtures\Foo;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DependencyResolverTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $container = Mockery::mock(ContainerInterface::class);

        $instance = new DependencyResolver($container);
        self::assertInstanceOf(DependencyResolver::class, $instance);
    }

    public function testResolve(): void
    {
        $psrContainerMock = Mockery::mock(ContainerInterface::class);
        $psrContainerMock->shouldReceive('has')->with(A::class)->andReturn(true);
        $psrContainerMock->shouldReceive('has')->with(B::class)->andReturn(true);
        $psrContainerMock->shouldReceive('get')->with(A::class, Foo::class)->andReturn(new A(1));
        $psrContainerMock->shouldReceive('get')->with(B::class, Foo::class)->andReturn(new B(2));

        $dependencyResolver = new DependencyResolver($psrContainerMock);
        $result = $dependencyResolver->resolve(Foo::class);

        self::assertInstanceOf(Foo::class, $result);
    }

    public function testAutoResolve(): void
    {
        $psrContainerMock = Mockery::mock(ContainerInterface::class);
        $psrContainerMock->shouldReceive('has')->with(C::class)->andReturn(false);
        $psrContainerMock->shouldReceive('has')->with(D::class)->andReturn(false);
        $psrContainerMock->shouldReceive('has')->with(Bar::class)->andReturn(true);
        $psrContainerMock->shouldReceive('get')->with(Bar::class, C::class)->andReturn(
            $test_bar = new Bar(new B(10))
        );

        $resolver = new DependencyResolver($psrContainerMock);

        /** @var D $result */
        $result = $resolver->resolve(D::class);

        $this->assertInstanceOf(D::class, $result);
        $this->assertEquals(1, $result->getNum());
        $this->assertNull($result->getString());

        $this->assertInstanceOf(C::class, $result->getC());
        $this->assertEquals('test', $result->getC()->getName());

        $this->assertEquals($test_bar, $result->getC()->getBar());
    }
}
