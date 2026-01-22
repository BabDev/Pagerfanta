<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\View\ViewFactory;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class ViewFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        /** @var Stub&ViewInterface $view1 */
        $view1 = $this->createStub(ViewInterface::class);

        /** @var Stub&ViewInterface $view2 */
        $view2 = $this->createStub(ViewInterface::class);

        /** @var Stub&ViewInterface $view3 */
        $view3 = $this->createStub(ViewInterface::class);

        /** @var Stub&ViewInterface $view4 */
        $view4 = $this->createStub(ViewInterface::class);

        $factory = new ViewFactory();

        $factory->set('foo', $view1);
        $factory->set('bar', $view2);

        $this->assertSame(['foo' => $view1, 'bar' => $view2], $factory->all());

        $this->assertSame($view1, $factory->get('foo'));
        $this->assertSame($view2, $factory->get('bar'));

        try {
            $factory->get('foobar');
            self::fail('The view factory should raise an exception if an unknown view is requested');
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

        $factory->clear();
        $this->assertSame([], $factory->all());
    }
}
