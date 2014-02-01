<?php
namespace mcfedr\TwitterPushBundle\Service;

use mcfedr\AWSPushBundle\Message\Message;
use mcfedr\AWSPushBundle\Service\Messages;

class TweetPusher
{

    /**
     * @var Messages
     */
    private $messages;

    /**
     * @param Messages $messages
     */
    public function __construct(Messages $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Send a tweet to everyone
     *
     * @param array $tweet
     */
    public function pushTweet($tweet)
    {
        $m = new Message($tweet['text']);
        $this->messages->broadcast($m);
    }
}
