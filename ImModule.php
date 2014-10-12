<?php

/**
 * Public and private instant messages module.
 *
 * @author Embedder <iam@embedder.org>
 */
class ImModule extends WebModule
{

    public function init()
    {
        $this->setImport(array(
            'im.models.*',
            'im.components.*'
        ));
    }
}