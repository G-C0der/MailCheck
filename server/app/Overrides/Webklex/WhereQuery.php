<?php


namespace App\Overrides\Webklex;


use Webklex\IMAP\Client;
use Webklex\IMAP\Exceptions\GetMessagesFailedException;
use Webklex\IMAP\Support\MessageCollection;

class WhereQuery extends \Webklex\IMAP\Query\WhereQuery
{
    /**
     * Amavis server name from where the "email was isolated message" originally was sent
     * @var string
     */
    private $amavisServerName = "";

    /**
     * Class which represents the desired target format of the message
     * @var null|string
     */
    private $targetEmailType = "";

    /**
     * WhereQuery constructor
     * @param Client $client
     * @param string $amavisServerName
     * @param string $targetEmailType
     * @param string $charset
     */
    public function __construct(Client $client, string $amavisServerName, string $targetEmailType,
                                string $charset = 'UTF-8') {

        // Set the Amavis server
        $this->amavisServerName = $amavisServerName;

        // Set the target email type
        $this->targetEmailType = $targetEmailType;

        // Parent construction
        parent::__construct($client, $charset);
    }

    /**
     * Fetch the current query and return all found messages
     * Override: Create override "App\Overrides\Webklex\Message" object
     * @return MessageCollection
     * @throws GetMessagesFailedException
     */
    public function get() {
        $messages = MessageCollection::make([]);

        try {
            $available_messages = $this->search();
            $available_messages_count = $available_messages->count();

            if ($available_messages_count > 0) {

                $messages->total($available_messages_count);

                $options = config('imap.options');

                if(strtolower($options['fetch_order']) === 'desc'){
                    $available_messages = $available_messages->reverse();
                }

                $query =& $this;

                $available_messages->forPage($this->page, $this->limit)->each(function($msgno, $msglist)
                    use(&$messages, $options, $query) {

                    // New message object
                    $oMessage = new Message($msgno, $msglist, $query->getClient(),
                        $this->amavisServerName, $this->targetEmailType, $query->getFetchOptions(),
                        $query->getFetchBody(), $query->getFetchAttachment(), $query->getFetchFlags());
                    switch ($options['message_key']){
                        case 'number':
                            $message_key = $oMessage->getMessageNo();
                            break;
                        case 'list':
                            $message_key = $msglist;
                            break;
                        default:
                            $message_key = $oMessage->getMessageId();
                            break;

                    }
                    $messages->put($message_key, $oMessage);
                });
            }

            return $messages;
        } catch (\Exception $e) {
            throw new GetMessagesFailedException($e->getMessage());
        }
    }
}
