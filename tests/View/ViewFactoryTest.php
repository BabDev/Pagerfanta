<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\View\ViewFactory;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\TestCase;

class ViewFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $view1 = $this->createMock(ViewInterface::class);
        $view2 = $this->createMock(ViewInterface::class);
        $view3 = $this->createMock(ViewInterface::class);
        $view4 = $this->createMock(ViewInterface::class);

        $factory = new ViewFactory();

        $factory->set('foo', $view1);
        $factory->set('bar', $view2);

        $this->assertSame(['foo' => $view1, 'bar' => $view2], $factory->all());

        $this->assertSame($view1, $factory->get('foo'));
        $this->assertSame($view2, $factory->get('bar'));
        try {
            $factory->get('foobar');
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }

        $this->assertTrue($factory->has('foo'));
        $this->assertTrue($factory->has('bar'));
        $this->assertFalse($factory->has('foobar'));

        $factory->add([
            'ups' => $view3,
            'man' => $view4,
        ]);
        $this->assertSame($view3, $factory->get('ups'));
        $this->assertSame($view4, $factory->get('man'));
        $this->assertTrue($factory->has('ups'));
        $this->assertTrue($factory->has('man'));
        $this->assertSame([
            'foo' => $view1,
            'bar' => $view2,
            'ups' => $view3,
            'man' => $view4,
        ], $factory->all());

        $factory->remove('bar');
        $this->assertFalse($factory->has('bar'));
        $this->assertTrue($factory->has('foo'));
        $this->assertTrue($factory->has('ups'));
        $this->assertTrue($factory->has('man'));
        $this->assertSame([
            'foo' => $view1,
            'ups' => $view3,
            'man' => $view4,
        ], $factory->all());
        try {
            $factory->remove('foobar');
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }

        $factory->clear();
        $this->assertSame([], $factory->all());
    }
}
