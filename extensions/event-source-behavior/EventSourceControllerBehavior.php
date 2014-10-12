<?php

/**
 * Message streaming functionality for public and private mode.
 * 
 * @author Embedder <iam@embedder.org>
 */
class EventSourceControllerBehavior extends CBehavior
{
    /**
     * @var int one poll processing time, sec;
     */
    const POLL_LIFETIME = 15;

    /**
     * @var int stream update time, sec;
     */
    const STREAM_FREQUENCY = 1;

    /**
     * @var string message model name;
     */
    public $modelClassName;


    /**
     * Disable php session blocking.
     * Send EventSource headers.
     * Detect first poll and prepare response (last N messages from all users). Send it.
     * Check new messages every {@link STREAM_FREQUENCY} time. Send response.
     * Restart poll after {@link POLL_LIFETIME}
     * @param int id opponent id, for private im mode;
     */
    public function actionStream($id=null)
    {
        session_write_close();
        $this->eventSourceHeaders();

        $headers = getallheaders();
        $startTime = time();

        while(true)
        {
            $lastEventId = isset($headers['Last-Event-ID']) ? $headers['Last-Event-ID'] : null;
            $modelClassName = $this->modelClassName;
            $publicMode = $id === null;

            if($publicMode)
            {
                $messages = $modelClassName::model()->getMessages($lastEventId);
                $this->eventSourceEcho($messages);
            }
            else
            {
                $opponentIsTyping = $modelClassName::model()->getOpponentIsTyping($id);
                $this->eventSourceEcho($opponentIsTyping, $restartPoll=false);

                $messages = $modelClassName::model()->getMessages($id, $lastEventId);
                if($messages === null && $opponentIsTyping !== null){}
                else
                    $this->eventSourceEcho($messages);
            }

            sleep(self::STREAM_FREQUENCY);

            if((time() - $startTime) > self::POLL_LIFETIME)
                $this->eventSourceRestartPoll();
        }
    }


    /**
     * Echo EventSource response.
     * Stop current poll.
     * Start new immediately.
     * @param string $data EventSource-prepared string
     * @param bool $restartPoll
     */
    public function eventSourceEcho($data='', $restartPoll=true)
    {
        if(strlen($data) > 0)
        {
            echo $data;
            if($restartPoll)
                $this->eventSourceRestartPoll();
        }
        else
            echo "data:{}\n\n"; // firefox freeze issue; object - opera empty data issue;

        ob_flush();
        flush();
    }


    /**
     * Close current poll.
     * Initiate new immediately.
     */
    public function eventSourceRestartPoll()
    {
        die("retry: 0\n\n");
    }


    /**
     * Standart EventSource headers.
     */
    public function eventSourceHeaders()
    {
        header('Content-Type: text/event-stream');
        header('Cache-control: no-cache');
    }
}