<?php
namespace mcfedr\TwitterPushBundle\Service;

use mcfedr\AWSPushBundle\Message\Message;
use mcfedr\AWSPushBundle\Service\Messages;
use mcfedr\AWSPushBundle\Service\Topics;

class TweetPusher
{

    /**
     * @var Messages
     */
    private $messages;

    /**
     * @var Topics
     */
    private $topics;

    /**
     * @var string
     */
    private $topicName;

    /**
     * @param Messages $messages
     * @param Topics $topics
     * @param string $topicName
     */
    public function __construct(Messages $messages, Topics $topics, $topicName)
    {
        $this->messages = $messages;
        $this->topics = $topics;
        $this->topicName = $topicName;
    }

    /**
     * Send a tweet to everyone
     *
     * @param array $tweet
     */
    public function pushTweet($tweet)
    {
        $m = new Message($tweet['text']);
        $custom = [
            'i' => $tweet['id_str']
        ];
        //Check for links in the tweet
        foreach (['urls', 'media'] as $entity) {
            if (isset($tweet['entities']) && isset($tweet['entities'][$entity]) && isset($tweet['entities'][$entity][0])) {
                $custom['u'] = $tweet['entities'][$entity][0]['url'];
                //Remove the link from the text to save space
                $newText = trim(substr($tweet['text'], 0, $tweet['entities'][$entity][0]['indices'][0]) . substr($tweet['text'], $tweet['entities'][$entity][0]['indices'][1]));
                if($newText != '') {
                    $m->setText($newText);
                }
                break;
            }
        }

        $m->setCustom($custom);

        if ($this->topicName) {
            $this->topics->broadcast($m, $this->topicName);
        }
        else {
            $this->messages->broadcast($m);
        }
    }
}
