<?php

/**
 * Private message model.
 *
 * @property int user_from_id
 * @property int user_to_id
 * @property string content
 * @property int is_notified
 * @property int is_viewed
 * @property string create_time
 *  
 * @author Embedder <iam@embedder.org>
 */
class ImPrivateMessage extends ImMessage
{

    /**
     * Find last N models for first poll.
     * Find all new models for all the following polls.
     * @see EventSourceControllerBehavior::actionStream
     * @param int $userFromId Opponent id
     * @param null|int $lastMessageId Last event id received from client
     * @return string Client-prepared response string
     */
    public function getMessages($userFromId, $lastMessageId=null)
    {
        $isFirstPoll = $lastMessageId === null;
        $condition = $isFirstPoll ? '' : 'id>='.$lastMessageId;
        $models = $this->myAndToMeFrom($userFromId)->lastN()->findAll($condition);
        $models = array_reverse($models);

        // message table is still empty;
        $messagesEmpty = count($models) === 0;

        // not the first poll; no new messages; found only last old message;
        $noNewMessages = !$isFirstPoll && count($models) === 1;

        if($messagesEmpty || $noNewMessages)
            return null;

        return $this->buildMessages($models, $forceFirstDay=$isFirstPoll);
    }


    /**
     * Prepare data for private message notification (ballon-preview in screen corner).
     * @see PrivateController::actionNotification
     * @return array Client-prepared response string.
     */
    public function getNotificationMessage()
    {
        $m = array();

        $m['day'] = $this->getDaysBack();
        $m['time'] = $this->getHumanTime();
        $m['userFromName'] = $this->getUserFromName();
        $m['userFromId'] = $this->user_from_id;
        $m['content'] = str_replace("\n", ' ', $this->content);
        $m['url'] = app()->createUrl('/im/private/user', array('id' => $this->user_from_id));

        $m = $this->getEventSourceMessage($m, $this->id);

        return $m;
    }


    /**
     * Opponent typing state streaming in private mode.
     * @see EventSourceControllerBehavior::actionStream
     * @param int $opponentId
     * @return null|string Client-prepared response string.
     */
    public function getOpponentIsTyping($opponentId)
    {
        $opponentTypingStatus = app()->db->createCommand()
            ->select('is_typing_to')
            ->from('{{user_online}}')
            ->where('user_id=:user_id and is_typing_to=:is_typing_to', array(
                ':user_id' => $opponentId,
                ':is_typing_to' => user()->id
                ))
            ->queryRow();

        if(is_array($opponentTypingStatus))
        {
            if($opponentTypingStatus['is_typing_to'] !== null)
                return $this->getEventSourceMessage(array('isTyping' => true));
        }

        return null;
    }


    /**
     * Build client response from models array.
     * @see getMessages 
     * @param array $models
     * @param bool $forceFirstDay Force day separator. 
     * @return string Client-prepared response.
     */
    public function buildMessages($models, $forceFirstDay)
    {
        $m = array();
        $mmm = array();

        if(count($models) === 1)
        {
            $model = array_shift($models);
            $m['day'] = $model->getDaysBack();
            $m['time'] = $model->getHumanTime();
            $m['user-from-name'] = $model->getUserFromName();
            $m['user-from-id'] = $model->user_from_id;
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
                $m['user-from-name'] = $model->getUserFromName($forceUserFromName, $prevModel);
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
            // messages to current user, not viewed yet;
            // see PrivateController::actionUser; see views/_contactList
            'notViewed' => array(
                'condition' => 'is_viewed IS NULL AND user_to_id=' . user()->id
            ),
            // messages to current user, not notified yet (in screen corner balloon);
            // see PrivateController::actionNotifications
            'notNotified' => array(
                'condition' => 'is_notified IS NULL AND user_to_id='.user()->id
            ),
            // models count for first response;
            // see getMessages; see ImPublicMessage::getMessages;
            'lastN' => array(
                'limit' => '30',
                'order' => 'id DESC'
            ),
        );
    }


    /**
     * My messages and messages from user to me scope.
     * @see getMessages
     * @param $id Opponent id.
     * @return $this
     */
    public function myAndToMeFrom($id)
    {
        $criteria = $this->getDbCriteria();
        $criteria->addInCondition('user_from_id', array(user()->id, $id));
        $criteria->addInCondition('user_to_id', array(user()->id, $id));

        return $this;
    }


    /**
     * Messages to me from user.
     * @see PrivateController::actionUser
     * @param $id Opponent id.
     * @return $this
     */
    public function toMeFrom($id)
    {
        $criteria = $this->getDbCriteria();
        $criteria->addCondition('user_from_id='.(int)$id);
        $criteria->addCondition('user_to_id='.user()->id);
        $criteria->order = 'id DESC';

        return $this;
    }


    public function getUrl($route='/im/private')
    {
        return parent::getUrl($route);
    }


    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return '{{im_private_message}}';
    }
}