<?php

namespace Bolt\Filesystem\Tests\Mock;

use Doctrine\Common\Cache\ArrayCache;

class ArrayCacheMock extends ArrayCache
{
    public $fetchInvoked = 0;
    public $saveInvoked = 0;
    public $deleteInvoked = 0;

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        $this->fetchInvoked++;
        return parent::fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $this->saveInvoked++;
        return parent::save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->deleteInvoked++;
        return parent::delete($id);
    }
}
