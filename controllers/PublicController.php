<?php

/**
 * Public messages controller.
 *
 * @author Embedder <iam@embedder.org>
 */
class PublicController extends Controller
{

    public $layout = '//layouts/column_2';


    public function behaviors()
    {
        return array(
            // add message streaming functionality;
            'EventSourceBehavior' => array(
                'class' => 'im.extensions.event-source-behavior.EventSourceControllerBehavior',
                'modelClassName' => 'ImPublicMessage'
            )
        );
    }


    /**
     * Main page.
     */
    public function actionIndex()
    {
        $this->render('index');
    }


    /**
     * Add new message.
     * @throws CHttpException When not saved.
     */
    public function actionAdd()
    {
        $data = CJavaScript::jsonDecode(file_get_contents('php://input'));
        $message = $data['message'];

        if(strlen($message) > 0)
        {
            $pm = new ImPublicMessage;
            $pm->user_from_id = user()->id;
            $pm->content = $message;
            if($pm->save() === false)
                throw new CHttpException(418);
        }

        $pm = ImPublicMessage::model()->findByPk($pm->id);
        $response = $pm->content;
        echo $response;
    }
}