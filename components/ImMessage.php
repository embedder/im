<?php

/**
 * Public and private messages common model functionality.
 *
 * @author Embedder <iam@embedder.org>
 */
class ImMessage extends ActiveRecord
{

    /**
     * Humanized day representation for messages day-separator.
     * When previous message day differs from current message day or force mode enabled.
     * @see ImPrivateMessage::buildMessages
     * @see ImPublicMessage::buildMessages
     * @param ImMessage $prevModel
     * @param bool $force Force day-separator, for first (id=1) message, for example.
     * @return null|string Day string representation, if days are different or force enabled. Or null.
     */
    public function getDay($prevModel, $force=false)
    {
        $df = app()->dateFormatter;
        $prevDate = $df->format('yy-MM-dd', $prevModel->create_time);
        $date = $df->format('yy-MM-dd', $this->create_time);
        $daysBack = $this->getDaysBack();

        return ($force || $prevDate <> $date) ? $daysBack : null;
    }


    /**
     * String representation of message day of creation.
     * For example: today, yesterday or 12.12.12 - for old dates.
     * @see getDay
     * @return string Day string representation.
     */
    public function getDaysBack()
    {
        $df = app()->dateFormatter;
        $daysBack = floor((strtotime(date('y-m-d')) - strtotime($df->format('yy-MM-dd', $this->create_time)))/86400);

        switch($daysBack)
        {
            case 0: $daysBack = Yii::t('ImModule.main', 'Today'); break;
            case 1: $daysBack = Yii::t('ImModule.main', 'Yesterday'); break;
            case 2: $daysBack = Yii::t('ImModule.main', 'Ereyesterday'); break;
            default: $daysBack = $df->format('dd MMMM yyy', $this->create_time);
        }

        return $daysBack;
    }


    /**
     * Show author name, when previous author differs.
     * @see ImPrivateMessage::buildMessages
     * @see ImPrivateMessage::getNotificationMessage
     * @see ImPublicMessage::buildMessages
     * @param bool $force Force, for example, when day separator exists (first message today).
     * @param ImMessage $prevModel
     * @return string|null Null if previous author equals to current. Or current author name.
     */
    public function getUserFromName($force=false, $prevModel=null)
    {
        if($prevModel === null)
            return $this->userFrom->getName();
        else
        {
            $prevUserFromDifferent = $prevModel->userFrom->id !== $this->userFrom->id;
            $force |= $prevUserFromDifferent;

            return ($force) ? $this->userFrom->getName() : null;
        }
    }


    /**
     * @return string Humanized time representation in message row.
     */
    public function getHumanTime()
    {
        return app()->dateFormatter->format('HH:mm:ss', $this->create_time);
    }


    public function behaviors()
    {
        return array(
            'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'create_time',
                'updateAttribute' => null,
                'timestampExpression' => new CDbExpression('NOW()'),
            ),
            'EventSourceBehavior' => array(
                'class' => 'im.extensions.event-source-behavior.EventSourceModelBehavior'
            )
        );
    }


    public function rules()
    {
        return array(
            array(  'user_from_id, user_to_id',
                    'numerical',
                    'integerOnly' => true
            ),
            array(  'content',
                    'filter',
                    'filter' => array($obj=new CHtmlPurifier(),'purify')
            )
        );
    }
}