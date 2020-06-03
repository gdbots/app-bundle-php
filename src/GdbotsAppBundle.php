<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GdbotsAppBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
