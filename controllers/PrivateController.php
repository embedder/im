<?php

/**
 * Private messages controller.
 *
 * @author Embedder <iam@embedder.org>
 */
class PrivateController extends Controller
{

    /** 
     * @var int private message check time, sec;
     */
    const NOTIFICATIONS_FREQUENCY = 5;

    /**
     * @var int private notifications max count;
     */
    const NOTIFICATIONS_MAX_COUNT = 3;


    public $layout = '//layouts/column_2';


    public function behaviors()
    {
        return array(
            // add message streaming functionality;
            'EventSourceBehavior' => array(
                'class' => 'im.extensions.event-source-behavior.EventSourceControllerBehavior',
                'modelClassName' => 'ImPrivateMessage'
            )
        );
    }


    /**
     * Opponent im page.
     * @param int opponent id.
     */
    public function actionUser($id)
    {
        $opponent = $this->loadModel($id, 'User');
        $myOnline = UserOnline::model()->findByAttributes(array('user_id' => user()->id));
        $myOnline->is_typing_to = null;
        $myOnline->update(array('is_typing_to'));

        $criteria = ImPrivateMessage::model()->toMeFrom($id)->notViewed()->getDbCriteria();
        ImPrivateMessage::model()->updateAll(array(
            'is_viewed' => 1,
            'is_notified' => 1
        ), $criteria);

        $this->render('index', array('user' => $opponent));
    }


    /**
     * Add new message.
     * @param int $id Message receiver.
     * @throws CHttpException When not saved.
     */
    public function actionAdd($id)
    {
        $data = CJavaScript::jsonDecode(file_get_contents('php://input'));
        $message = $data['message'];

        $pm = new ImPrivateMessage;
        $pm->user_from_id = user()->id;
        $pm->user_to_id = $id;
        $pm->content = $message;
        if($pm->save() === false)
            throw new CHttpException(418);

        // reset typing state after submit immediately;
        $myOnline = UserOnline::model()->findByAttributes(array('user_id' => user()->id));
        $myOnline->is_typing_to = null;
        $myOnline->update(array('is_typing_to'));

        // Timestamp creation fix.
        $pm = ImPrivateMessage::model()->findByPk($pm->id);
        $response = $pm->content;

        echo $response;
    }


    /**
     * Check new not notified messages.
     * Per 1 balloon-notification each poll.
     * @param null $exceptId
     * @return string
     */
    public function actionNotifications($exceptId=null)
    {
        session_write_close();
        $this->eventSourceHeaders();

        $response = '';

        $startTime = time();
        while(true)
        {
            $count = ImPrivateMessage::model()->notNotified()->count();
            if($count > 0 && $count < self::NOTIFICATIONS_MAX_COUNT)
            {
                $model = ImPrivateMessage::model()->notNotified()->find();
                if($exceptId !== null && $model->user_from_id == (int)$exceptId)
                {
                    $model->is_viewed = 1;
                    $model->is_notified = 1;
                    $model->update(array('is_notified', 'is_viewed'));
                }
                else
                {
                    $model->is_notified = 1;
                    $model->update(array('is_notified'));
                    $response = $model->getNotificationMessage();
                }
            }

            $this->eventSourceEcho($response);
            sleep(self::NOTIFICATIONS_FREQUENCY);

            if((time() - $startTime) > EventSourceControllerBehavior::POLL_LIFETIME)
                $this->eventSourceRestartPoll();
        }

        return $response;
    }


    /**
     * Save current user typing start/stop events.
     * @param int $opponentId opponents id;
     */
    public function actionSaveMyTypingState($opponentId)
    {
        $newState = file_get_contents('php://input');
        $isAllowed = in_array($newState, array('start', 'stop'));

        if($isAllowed)
        {
            $userOnline = UserOnline::model()->findByAttributes(array('user_id' => user()->id));

            switch($newState)
            {
                case 'start':
                    $userOnline->is_typing_to = $opponentId;
                    break;
                case 'stop':
                    $userOnline->is_typing_to = null;
                    break;
            }

            $userOnline->last_active_time = date("Y-m-d H:i:s");
            $userOnline->update(array('is_typing_to', 'last_active_time'));
        }
    }
}