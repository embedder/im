<?php
/**
 * Cron job, which disable "frozen" typing status.
 */

class ImTypingCommand extends CConsoleCommand
{

    public function actionIndex()
    {
        $criteria = UserOnline::model()->passive()->notTyping()->getDbCriteria();
        $count = UserOnline::model()->count($criteria);
        UserOnline::model()->updateAll(array('is_typing_to' => null), $criteria);

        echo "Frozen status disabled of $count users.";
        echo PHP_EOL;
    }
}