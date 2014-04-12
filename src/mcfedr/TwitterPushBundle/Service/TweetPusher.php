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
    protected $messages;

    /**
     * @var Topics
     */
    protected $topics;

    /**
     * @var string
     */
    protected $topicName;

    /**
     * @param Messages $messages
     * @param Topics $topics
     * @param string $topicName
     */
    public function __construct(Messages $messages = null, Topics $topics = null, $topicName = null)
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
        $m = $this->getMessageForTweet($tweet);

        if ($this->topicName) {
            $this->topics->broadcast($m, $this->topicName);
        }
        else {
            $this->messages->broadcast($m);
        }
    }

    protected function getMessageForTweet($tweet)
    {
        $m = new Message($tweet['text']);
        $custom = [
            'i' => $tweet['id_str']
        ];
        //Check for links in the tweet
        foreach (['urls', 'media'] as $entity) {
            if (isset($tweet['entities']) && isset($tweet['entities'][$entity]) && isset($tweet['entities'][$entity][0])) {
                $entity = $tweet['entities'][$entity][0];
                $custom['u'] = $entity['url'];
                //Remove the link from the text to save space
                $newText = trim(mb_substr($tweet['text'], 0, $entity['indices'][0], 'utf8') . mb_substr($tweet['text'], $entity['indices'][1], 'utf8'));
                if($newText != '') {
                    $m->setText($newText);
                }
                break;
            }
        }

        $m->setCustom($custom);

        return $m;
    }
}
