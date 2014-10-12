<?php
/**
 * Public messaging page view.
 * 
 * @var PublicController $this
 */
?>

<div data-im-container
     data-im-stream-url="<?= $this->createAbsoluteUrl('/im/public/stream') ?>">

    <? $this->renderPartial('/_contactList') ?>

    <div class="chat">
        <div class="header">
            <h1><?= Yii::t('ImModule.main', 'Public talks') ?></h1>
            <img src="/img/chat-team.png" alt="<?= Yii::t('ImModule.main', 'Team') ?>"/>

            <a class="chat-exit" href="<?= app()->homeUrl ?>">
                <?= Yii::t('ImModule.main', 'Leave chat') ?></a>
        </div>

        <div data-im-message-list-container>
            <ul data-im-message-list></ul>
        </div>

        <form method="post" 
              action="<?= $this->createUrl('/im/public/add') ?>">
    
            <div data-im-input-container></div>
            <input type="submit" value="<?= Yii::t('ImModule.main', 'Send') ?>"/>
        </form>
    </div>
</div>