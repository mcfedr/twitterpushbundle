<?php
/**
 * Created by mcfedr on 26/11/14 09:23
 */

namespace Mcfedr\TwitterPushBundle\DependencyInjection;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class GuzzleClientFactory
{
    public static function get(array $options, Oauth1 $oauth1)
    {
        $stack = HandlerStack::create();
        $stack->unshift($oauth1);

        $options['handler'] = $stack;
        return new Client($options);
    }
}
