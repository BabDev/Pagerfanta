<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\View\ViewFactory;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ViewFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        /** @var MockObject&ViewInterface $view1 */
        $view1 = $this->createMock(ViewInterface::class);

        /** @var MockObject&ViewInterface $view2 */
        $view2 = $this->createMock(ViewInterface::class);

        /** @var MockObject&ViewInterface $view3 */
        $view3 = $this->createMock(ViewInterface::class);

        /** @var MockObject&ViewInterface $view4 */
        $view4 = $this->createMock(ViewInterface::class);

        $factory = new ViewFactory();

        $factory->set('foo', $view1);
        $factory->set('bar', $view2);

        self::assertSame(['foo' => $view1, 'bar' => $view2], $factory->all());

        self::assertSame($view1, $factory->get('foo'));
        self::assertSame($view2, $factory->get('bar'));

        try {
            $factory->get('foobar');
            self::fail('The view factory should raise an exception if an unknown view is requested');
        } catch (\Exception $e) {
            self::assertInstanceOf(InvalidArgumentException::class, $e);
        }

        self::assertTrue($factory->has('foo'));
        self::assertTrue($factory->has('bar'));
        self::assertFalse($factory->has('foobar'));

        $factory->add([
            'ups' => $view3,
            'man' => $view4,
        ]);
        self::assertSame($view3, $factory->get('ups'));
        self::assertSame($view4, $factory->get('man'));
        self::assertTrue($factory->has('ups'));
        self::assertTrue($factory->has('man'));
        self::assertSame([
            'foo' => $view1,
            'bar' => $view2,
            'ups' => $view3,
            'man' => $view4,
        ], $factory->all());

        $factory->remove('bar');
        self::assertFalse($factory->has('bar'));
        self::assertTrue($factory->has('foo'));
        self::assertTrue($factory->has('ups'));
        self::assertTrue($factory->has('man'));
        self::assertSame([
            'foo' => $view1,
            'ups' => $view3,
            'man' => $view4,
        ], $factory->all());

        $factory->clear();
        self::assertSame([], $factory->all());
    }
}
