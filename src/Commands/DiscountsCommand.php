<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Commands;

use Illuminate\Console\Command;

class DiscountsCommand extends Command
{
    public $signature = 'discounts';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
