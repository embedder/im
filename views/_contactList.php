<?php
/**
 * Chat members menu.
 * 
 * @var int $notViewedPMCount
 * @var array $users
 * @var Controller $this
 */
?>

<div class="contact-list">
    <? $notViewedPMCount = ImPrivateMessage::model()->notViewed()->count() ?>
    <h2><?= Yii::t('ImModule.main', 'Private talks') ?></h2>

    <ul>
        <? $users = array() ?>
        <? $users['online'] = User::model()->areOnline()->notMe()->findAll() ?>
        <? $users['offline'] = User::model()->areOffline()->notMe()->findAll() ?>

        <? foreach($users as $status => $models): ?>
            <? foreach($models as $user): ?>
                <? $notViewedCount = ImPrivateMessage::model()->notViewed()->toMeFrom($user->id)->count() ?>
                <li data-im-user-id="<?= $user->id ?>"
                    data-im-not-viewed-count="<?= $notViewedCount ?>">
                        <a href="<?= $this->createUrl('/im/private/user', array('id' => $user->id)) ?>" 
                           class="<?= $status ?>"><?= $user->getName() ?></a>

                        <span data-im-not-viewed-count-label><?= $notViewedCount ?></span>
                </li>
            <? endforeach ?>
        <? endforeach ?>
    </ul>

    <? if($this->id === 'private'): ?>
        <a class="chat" href="<?= $this->createUrl('/im/public/index') ?>">
            <?= Yii::t('ImModule.main', 'Public talks') ?>
        </a>
    <? endif ?>
</div>