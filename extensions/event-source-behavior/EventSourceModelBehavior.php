<?php

class EventSourceModelBehavior extends CActiveRecordBehavior
{

    /**
     * @param array $data
     * @return string EventSource-prepared string
     */
    public function getEventSourceMessages($data)
    {
        $messages = '';

        foreach($data as $id => $message)
            $messages .= $this->getEventSourceMessage($message, $id);

        return $messages;
    }


    /**
     * @param array $data
     * @param int $id EventSource message id
     * @return null|string
     */
    public function getEventSourceMessage($data, $id=null)
    {
        if(empty($data))
            return null;

        foreach($data as $prop=>$value)
            if($data[$prop] === null)
                unset($data[$prop]);

        $message = 'data: ' . CJavaScript::jsonEncode($data) . "\n";
        if($id !== null)
            $message .= "id: {$id}\n";

        $message .= "\n";
        return $message;
    }
}