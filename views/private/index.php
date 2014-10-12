<?php
/**
 * Private messaging page view.
 * 
 * @var PrivateController $this
 * @var User $user Opponent.
 */
?>

<div data-im-container
     data-im-stream-url="<?= $this->createUrl('/im/private/stream', array('id' => $user->id)) ?>"
     data-im-private-user-id="<?= $user->id ?>">

    <? $this->renderPartial('/_contactList') ?>

    <div class="chat">
        <div class="header">
            <h1><?= $user->getName() ?></h1>
            <img src="<?= $user->getUploadUrl('photo', 70, 95, true) ?>" alt="<?= $user->getName() ?>"/>

            <a class="chat-exit" href="<?= app()->homeUrl ?>">
                <?= Yii::t('ImModule.main', 'Leave chat') ?></a>
        </div>

        <div data-im-message-list-container>
            <ul data-im-message-list></ul>
        </div>

        <form method="post" 
              action="<?= $this->createUrl('/im/private/add', array('id' => $user->id)) ?>" 
              data-im-save-my-typing-state-url="<?= $this->createUrl('/im/private/saveMyTypingState', array('opponentId' => $user->id)) ?>">

            <span data-im-is-typing-label><?= Yii::t('ImModule.main', 'Opponent is typing') ?>...</span>
            <div data-im-input-container></div>
            <input type="submit" value="Отправить"/>
        </form>
    </div>
</div>