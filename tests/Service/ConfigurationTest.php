<?php
/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace AnimeDb\Bundle\PaginationBundle\Tests\Service;

use AnimeDb\Bundle\PaginationBundle\Service\Configuration;
use AnimeDb\Bundle\PaginationBundle\Service\View;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    protected $config;

    protected function setUp()
    {
        $this->config = new Configuration(150, 33);
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return [
            [10, 1],
            [150, 33],
        ];
    }

    /**
     * @dataProvider getConfigs
     *
     * @param int $total_pages
     * @param int $current_page
     */
    public function testConstruct($total_pages, $current_page)
    {
        $config = new Configuration($total_pages, $current_page);
        $this->assertEquals($total_pages, $config->getTotalPages());
        $this->assertEquals($current_page, $config->getCurrentPage());
    }

    /**
     * @dataProvider getConfigs
     *
     * @param int $total_pages
     * @param int $current_page
     */
    public function testCreate($total_pages, $current_page)
    {
        $config = Configuration::create($total_pages, $current_page);
        $this->assertEquals($total_pages, $config->getTotalPages());
        $this->assertEquals($current_page, $config->getCurrentPage());
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return [
            [
                150,
                10,
                'getTotalPages',
                'setTotalPages',
            ],
            [
                33,
                1,
                'getCurrentPage',
                'setCurrentPage',
            ],
            [
                Configuration::DEFAULT_LIST_LENGTH,
                Configuration::DEFAULT_LIST_LENGTH + 5,
                'getMaxNavigate',
                'setMaxNavigate',
            ],
            [
                Configuration::DEFAULT_PAGE_LINK,
                'page_%s.html',
                'getPageLink',
                'setPageLink',
            ],
            [
                Configuration::DEFAULT_PAGE_LINK,
                function ($number) {
                    return 'page_'.$number.'.html';
                },
                'getPageLink',
                'setPageLink',
            ],
            [
                '',
                '/index.html',
                'getFirstPageLink',
                'setFirstPageLink',
            ],
        ];
    }

    /**
     * @dataProvider getMethods
     *
     * @param mixed $default
     * @param mixed $new
     * @param string $getter
     * @param string $setter
     */
    public function testSetGet($default, $new, $getter, $setter)
    {
        $this->assertEquals($default, call_user_func([$this->config, $getter]));
        $this->assertEquals($this->config, call_user_func([$this->config, $setter], $new));
        $this->assertEquals($new, call_user_func([$this->config, $getter]));
    }

    public function testGetView()
    {
        $view = $this->config->getView();
        $this->assertInstanceOf(View::class, $view);

        // test lazy load
        $this->config->setPageLink('?p=%s');
        $this->assertEquals($view, $this->config->getView());
    }
}
