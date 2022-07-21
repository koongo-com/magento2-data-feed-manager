<?php
namespace Nostress\Koongo\Model\Io;

interface Listable
{
    const DIR = 'dir';
    const FILE = 'file';

    public function getItems();
}
