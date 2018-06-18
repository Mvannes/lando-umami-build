<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\Experiment;
use Synetic\JanusAB\Variation\Variation;

/**
 * @covers \Drupal\janus_ab\Routing\JanusRequestClient
 */
class JanusRequestClientTest extends TestCase {

  /**
   * The request handler under test.
   *
   * @var \Drupal\janus_ab\Routing\JanusRequestClient
   */
  private $handler;

  /**
   * Mocked ABConfig for testing purposes.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * Mocked client to avoid sending actual requests in a test.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $client;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->config = $this->prophesize(ABConfigInterface::class);
    $this->client = $this->prophesize(ClientInterface::class);

    $this->handler = new JanusRequestClient(
      $this->config->reveal(),
      $this->client->reveal()
    );
  }

  /**
   * Checks if the request properly returns null when the request fails.
   */
  public function testDoTrafficRequestFailure(): void {
    $this->config->getTrafficUrl()->willReturn('https://google.com/traffic');
    $exception = new ClientException(
      'mess',
      $this->prophesize(RequestInterface::class)->reveal()
    );
    $this->client->request(Argument::cetera())->willThrow($exception);
    self::assertNull(
      $this->handler->doTrafficRequest(
        new Experiment('id', new \DateTime(), new \DateTime(), []),
        new Variation('0', 'A'),
        'id'
      )
    );
  }

  /**
   * Checks if the request properly returns null when the request fails.
   */
  public function testDoSuccessRequestFailure(): void {
    $this->config->getSuccessUrl()->willReturn('https://google.com/success');
    $exception = new ClientException(
      'mess',
      $this->prophesize(RequestInterface::class)->reveal()
    );
    $this->client->request(Argument::cetera())->willThrow($exception);
    self::assertNull(
      $this->handler->doSuccessRequest(
        new Experiment('id', new \DateTime(), new \DateTime(), []),
        new Variation('0', 'A'),
        'id'
      )
    );
  }

  /**
   * Test if the proper request is created based on the given parameters.
   */
  public function testDoTrafficRequest(): void {
    $this->config->getTrafficUrl()->willReturn('/traffic');
    $_SERVER['HTTP_HOST'] = 'localhost';
    $response             = new Response();
    $this->client->request(
      'POST',
      'localhost/traffic',
      [
        'form_params' => [
          'experiment' => 'id',
          'variation'  => '0',
          'userId'     => 'id',
        ],
        'http_errors' => FALSE,
        // Timeout if no response in 2 seconds.
        'timeout'     => 2,
      ]
    )->shouldBeCalled()->willReturn($response);

    self::assertSame(
      $response, $this->handler->doTrafficRequest(
      new Experiment('id', new \DateTime(), new \DateTime(), []),
      new Variation('0', 'A'),
      'id'
    )
    );
  }

  /**
   * Test if the proper request is created based on the given parameters.
   */
  public function testDoSuccessRequest(): void {
    $this->config->getSuccessUrl()->willReturn('/success');
    $_SERVER['HTTP_HOST'] = 'localhost';
    $response             = new Response();
    $this->client->request(
      'POST',
      'localhost/success',
      [
        'form_params' => [
          'experiment' => 'id',
          'variation'  => '0',
          'userId'     => 'id',
        ],
        'http_errors' => FALSE,
        // Timeout if no response in 2 seconds.
        'timeout'     => 2,
      ]
    )->shouldBeCalled()->willReturn($response);

    self::assertSame(
      $response, $this->handler->doSuccessRequest(
      new Experiment('id', new \DateTime(), new \DateTime(), []),
      new Variation('0', 'A'),
      'id'
    )
    );
  }

}
