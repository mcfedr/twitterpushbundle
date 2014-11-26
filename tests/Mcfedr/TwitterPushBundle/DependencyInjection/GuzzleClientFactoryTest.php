<?php
/**
 * Created by mcfedr on 26/11/14 09:34
 */

namespace Mcfedr\TwitterPushBundle\DependencyInjection;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuzzleClientFactoryTest extends WebTestCase
{
    public function testGet()
    {
        $client = self::createClient();
        /** @var Client $guzzle */
        $guzzle = $client->getContainer()->get('mcfedr_twitter_push.twitter_stream_client');
        $this->assertInstanceOf('GuzzleHttp\Client', $guzzle);
    }
}
