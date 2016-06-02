<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\PaginationBundle\Tests\Service;

use AnimeDb\Bundle\PaginationBundle\Service\Configuration;
use AnimeDb\Bundle\PaginationBundle\Service\View;
use AnimeDb\Bundle\PaginationBundle\Entity\Node;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package AnimeDb\Bundle\PaginationBundle\Tests\Service
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Configuration
     */
    protected $config;

    /**
     * @var View
     */
    protected $view;

    protected function setUp()
    {
        $this->config = $this->getMock(Configuration::class);
        $this->view = new View($this->config);
    }

    public function testGetTotal()
    {
        $this->config
            ->expects($this->once())
            ->method('getTotalPages')
            ->will($this->returnValue('110'));

        $this->assertEquals(110, $this->view->getTotal());
    }

    /**
     * @return array
     */
    public function getFailNodes()
    {
        return [
            ['getFirst', 1],
            ['getPrev', 1],
            ['getNext', 110],
            ['getLast', 110]
        ];
    }

    /**
     * @dataProvider getFailNodes
     *
     * @param string $method
     * @param int $current_page
     */
    public function testGetNodeFail($method, $current_page)
    {
        $this->config
            ->expects($this->any())
            ->method('getTotalPages')
            ->will($this->returnValue(110));
        $this->config
            ->expects($this->any())
            ->method('getCurrentPage')
            ->will($this->returnValue($current_page));

        $this->assertNull(call_user_func([$this->view, $method]));
    }

    /**
     * @return array
     */
    public function getPageLinks()
    {
        return [
            ['page_%s.html'],
            [function ($number) { return 'page_'.$number.'.html'; }],
        ];
    }

    /**
     * @return array
     */
    public function getFirstPageLinks()
    {
        return [
            ['page_%s.html', ''],
            ['page_%s.html', '/index.html'],
            [function ($number) { return 'page_'.$number.'.html'; }, ''],
            [function ($number) { return 'page_'.$number.'.html'; }, '/index.html'],
        ];
    }

    /**
     * @param string|callback $page_link
     * @param integer $number
     *
     * @return string
     */
    protected function getLink($page_link, $number)
    {
        return is_callable($page_link) ? call_user_func($page_link, $number) : sprintf($page_link, $number);
    }

    /**
     * @dataProvider getFirstPageLinks
     *
     * @param string|callback $page_link
     * @param string $first_page_link
     */
    public function testGetFirst($page_link, $first_page_link)
    {
        $this->config
            ->expects($this->once())
            ->method('getCurrentPage')
            ->will($this->returnValue(10));
        $this->config
            ->expects($first_page_link ? $this->atLeastOnce() : $this->once())
            ->method('getFirstPageLink')
            ->will($this->returnValue($first_page_link));
        $this->config
            ->expects($first_page_link ? $this->never() : $this->atLeastOnce())
            ->method('getPageLink')
            ->will($this->returnValue($page_link));

        $node = $this->view->getFirst();
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(1, $node->getPage());
        if ($first_page_link) {
            $this->assertEquals($first_page_link, $node->getLink());
        } else {
            $this->assertEquals($this->getLink($page_link, 1), $node->getLink());
        }
    }

    /**
     * @dataProvider getPageLinks
     *
     * @param string|callback $page_link
     */
    public function testGetPrev($page_link)
    {
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getCurrentPage')
            ->will($this->returnValue(5));
        $this->config
            ->expects($this->never())
            ->method('getFirstPageLink')
            ->will($this->returnValue(''));
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getPageLink')
            ->will($this->returnValue($page_link));

        $node = $this->view->getPrev();
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(4, $node->getPage());
        $this->assertEquals($this->getLink($page_link, 4), $node->getLink());
    }

    /**
     * @dataProvider getFirstPageLinks
     *
     * @param string|callback $page_link
     * @param string $first_page_link
     */
    public function testGetCurrent($page_link, $first_page_link)
    {
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getCurrentPage')
            ->will($this->returnValue(1));
        $this->config
            ->expects($first_page_link ? $this->atLeastOnce() : $this->once())
            ->method('getFirstPageLink')
            ->will($this->returnValue($first_page_link));
        $this->config
            ->expects($first_page_link ? $this->never() : $this->atLeastOnce())
            ->method('getPageLink')
            ->will($this->returnValue($page_link));

        $node = $this->view->getCurrent();
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(1, $node->getPage());
        if ($first_page_link) {
            $this->assertEquals($first_page_link, $node->getLink());
        } else {
            $this->assertEquals($this->getLink($page_link, 1), $node->getLink());
        }
    }

    /**
     * @dataProvider getPageLinks
     *
     * @param string|callback $page_link
     */
    public function testGetNext($page_link)
    {
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getCurrentPage')
            ->will($this->returnValue(5));
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getTotalPages')
            ->will($this->returnValue(10));
        $this->config
            ->expects($this->never())
            ->method('getFirstPageLink')
            ->will($this->returnValue(''));
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getPageLink')
            ->will($this->returnValue($page_link));

        $node = $this->view->getNext();
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(6, $node->getPage());
        $this->assertEquals($this->getLink($page_link, 6), $node->getLink());
    }

    /**
     * @dataProvider getPageLinks
     *
     * @param string|callback $page_link
     */
    public function testGetLast($page_link)
    {
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getCurrentPage')
            ->will($this->returnValue(5));
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getTotalPages')
            ->will($this->returnValue(10));
        $this->config
            ->expects($this->never())
            ->method('getFirstPageLink')
            ->will($this->returnValue(''));
        $this->config
            ->expects($this->atLeastOnce())
            ->method('getPageLink')
            ->will($this->returnValue($page_link));

        $node = $this->view->getLast();
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(10, $node->getPage());
        $this->assertEquals($this->getLink($page_link, 10), $node->getLink());
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return [
            [
                1,
                1,
                5,
                '%s',
                null,
                new ArrayCollection()
            ],
            [
                2,
                1,
                5,
                '/?page=%s',
                null,
                new ArrayCollection([
                    new Node(1, '/?page=1', true),
                    new Node(2, '/?page=2'),
                ])
            ],
            [
                2,
                2,
                5,
                '/?page=%s',
                null,
                new ArrayCollection([
                    new Node(1, '/?page=1'),
                    new Node(2, '/?page=2', true),
                ])
            ],
            [
                10,
                1,
                5,
                '/?page=%s',
                null,
                new ArrayCollection([
                    new Node(1, '/?page=1', true),
                    new Node(2, '/?page=2'),
                    new Node(3, '/?page=3'),
                    new Node(4, '/?page=4'),
                    new Node(5, '/?page=5'),
                ])
            ],
            [
                10,
                10,
                5,
                '/?page=%s',
                null,
                new ArrayCollection([
                    new Node(6, '/?page=6'),
                    new Node(7, '/?page=7'),
                    new Node(8, '/?page=8'),
                    new Node(9, '/?page=9'),
                    new Node(10, '/?page=10', true),
                ])
            ],
            [
                10,
                5,
                5,
                '/?page=%s',
                null,
                new ArrayCollection([
                    new Node(3, '/?page=3'),
                    new Node(4, '/?page=4'),
                    new Node(5, '/?page=5', true),
                    new Node(6, '/?page=6'),
                    new Node(7, '/?page=7'),
                ])
            ],
            [
                10,
                5,
                4,
                function ($number) {
                    return sprintf('/?page=%s', $number);
                },
                '/',
                new ArrayCollection([
                    new Node(4, '/?page=4'),
                    new Node(5, '/?page=5', true),
                    new Node(6, '/?page=6'),
                    new Node(7, '/?page=7'),
                ])
            ]
        ];
    }

    /**
     * @dataProvider getNodes
     *
     * @param integer $total_pages
     * @param integer $current_page
     * @param integer $max_navigate
     * @param string|\Closure $page_link
     * @param string $first_page_link
     * @param ArrayCollection $list
     */
    public function testGetIterator($total_pages, $current_page, $max_navigate, $page_link, $first_page_link, $list)
    {
        $this->config
            ->expects($this->any())
            ->method('getTotalPages')
            ->will($this->returnValue($total_pages));
        $this->config
            ->expects($this->any())
            ->method('getCurrentPage')
            ->will($this->returnValue($current_page));
        $this->config
            ->expects($this->any())
            ->method('getMaxNavigate')
            ->will($this->returnValue($max_navigate));
        $this->config
            ->expects($this->any())
            ->method('getPageLink')
            ->will($this->returnValue($page_link));
        $this->config
            ->expects($this->any())
            ->method('getFirstPageLink')
            ->will($this->returnValue($first_page_link));
        $this->assertEquals($list, $this->view->getIterator());
    }
}
