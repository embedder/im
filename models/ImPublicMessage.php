<?php

/**
 * Public message model.
 *
 * @property int user_from_id
 * @property int user_to_id
 * @property string content
 * @property string create_time
 * 
 * @author Embedder <iam@embedder.org>
 */
class ImPublicMessage extends ImMessage
{

    /**
     * Find last N models for first poll.
     * Find all new models for all the following polls.
     * @param $lastMessageId Last event id received from client.
     * @return string Client-prepared response string
     */
    public function getMessages($lastMessageId=null)
    {
        // detect first poll by last event id presence;
        $isFirstPoll = $lastMessageId === null;

        if($isFirstPoll)
        {
            $models = $this->lastN()->findAll();
            $models = array_reverse($models);
        }
        else
            // find all new and last old model (for calculating date between them (name also));
            $models = $this->findAll('id>='.$lastMessageId);

        // message table is still empty;
        if(count($models) === 0)
            return null;

        // not the first poll; no new messages; found only last old message;
        if(!$isFirstPoll && count($models) === 1)
            return null;

        return $this->buildMessages($models, $isFirstPoll);
    }


    /**
     * Build client response from models array.
     * @see getMessages
     * @param array $models
     * @param bool $forceFirstDay Force first day separator.
     * @return string Client-prepared response.
     */
    public function buildMessages($models, $forceFirstDay=false)
    {
        $m = array();
        $mmm = array();

        // only one message exists while first poll;
        if(count($models) === 1)
        {
            $model = array_shift($models);
            $m['day'] = $model->getDaysBack();
            $m['time'] = $model->getHumanTime();
            $m['userFromName'] = $model->getUserFromName();
            $m['content'] = str_replace("\n", ' ', $model->content);
            $mmm[$model->id] = $m;
        }
        else
        {
            for($i=1; $i<count($models); $i++)
            {
                $model = $models[$i];
                $prevModel = $models[$i-1];

                $m['day'] = $model->getDay($prevModel, $forceFirstDay && $i===1);
                $m['time'] = $model->getHumanTime();
                $forceUserFromName = $m['day'] !== null;
                $m['userFromName'] = $model->getUserFromName($forceUserFromName, $prevModel);
                $m['content'] = str_replace("\n", ' ', $model->content);

                $mmm[$model->id] = $m;
            }
        }

        $mmm = $this->getEventSourceMessages($mmm);

        return $mmm;
    }


    public function relations()
    {
        return array(
            'userFrom' => array(self::BELONGS_TO, 'User', 'user_from_id'), // message author;
            'userTo' => array(self::BELONGS_TO, 'User', 'user_to_id'), // message receiver;
        );
    }


    public function scopes()
    {
        return array(
            // count of models for first response;
            // see getMessages; see ImPublicMessage::getMessages;
            'lastN' => array(
                'limit' => '30',
                'order' => 'id DESC'
            )
        );
    }


    /**
     * @param string $route
     * @return string
     */
    public function getUrl($route='/im/public')
    {
        return parent::getUrl($route);
    }


    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return '{{im_public_message}}';
    }
}