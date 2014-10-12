<?php
/**
 * Clear message tables.
 */

class ImCleanCommand extends CConsoleCommand
{

    /**
     * Delete all public messages.
     */
    public function actionPublic()
    {
        $deletedRows = ImPublicMessage::model()->deleteAll();
        app()->db->createCommand('ALTER TABLE {{im_public_message}} AUTO_INCREMENT = 1')->execute();

        echo "$deletedRows public messages deleted";
        echo PHP_EOL;
    }


    /**
     * Delete all private messages.
     */
    public function actionPrivate()
    {
        $deletedRows = ImPrivateMessage::model()->deleteAll();
        app()->db->createCommand('ALTER TABLE {{im_private_message}} AUTO_INCREMENT = 1')->execute();

        echo "$deletedRows private messages deleted";
        echo PHP_EOL;
    }
}