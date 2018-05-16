<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Config;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use PHPUnit\Framework\TestCase;
use Synetic\JanusAB\Config\ABConfig;
use Synetic\JanusAB\Variation\Experiment;
use Synetic\JanusAB\Variation\Variation;

/**
 * @covers \Drupal\janus_ab\Config\ABConfigFactory
 */
class ABConfigFactoryTest extends TestCase {

  /**
   * Test if we can properly create the ABConfig object.
   */
  public function testCreate(): void {
    $config = $this->prophesize(Config::class);
    $experimentArray = [
      [
        'id'         => 'ab-test-1',
        'startDate'  => '01-01-2018',
        'endDate'    => '02-01-2018',
        'variations' => [
          [
            'id'   => '0',
            'name' => 'A',
          ],
          [
            'id'   => '1',
            'name' => 'B',
          ],
        ],
      ],
      [
        'id'         => 'ab-test-2',
        'startDate'  => '03-01-2018',
        'endDate'    => '04-01-2018',
        'variations' => [
          [
            'id'   => '0',
            'name' => 'A',
          ],
          [
            'id'   => '1',
            'name' => 'B',
          ],
          [
            'id'   => '2',
            'name' => 'C',
          ],
        ],
      ],
    ];
    $config->get('experiments')->willReturn($experimentArray);
    $config->get('vendorName')->willReturn('vendor');
    $config->get('siteName')->willReturn('site');
    $config->get('trafficUrl')->willReturn('/url');
    $config->get('successUrl')->willReturn('/surl');
    $config->get('analyticsId')->willReturn('UA-Coolest-id-ever');

    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get('janus_ab.settings')->willReturn($config->reveal());
    $abConfigFactory = new ABConfigFactory($configFactory->reveal());

    $expected = new ABConfig(
      'vendor',
      'site',
      '/url',
      '/surl',
      'UA-Coolest-id-ever',
      [
        new Experiment(
          'ab-test-1',
          new \DateTime('01-01-2018'),
          new \DateTime('02-01-2018'),
          [new Variation('0', 'A'), new Variation('1', 'B')]
        ),
        new Experiment(
          'ab-test-2',
          new \DateTime('03-01-2018'),
          new \DateTime('04-01-2018'),
          [
            new Variation('0', 'A'),
            new Variation('1', 'B'),
            new Variation('2', 'C'),
          ]
        ),
      ]
    );

    self::assertEquals($expected, $abConfigFactory->create());
  }

}
