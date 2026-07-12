<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('cms:about', function () { $this->info('L2CMS Core — theme-ready starter.'); })->purpose('Show CMS information');
