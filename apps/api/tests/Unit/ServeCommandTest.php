<?php

use Illuminate\Foundation\Console\ServeCommand;
use Tests\TestCase;

uses(TestCase::class);

it('passes Windows\' temp folder settings to `php artisan serve`, so local photo uploads work', function () {
    expect(ServeCommand::$passthroughVariables)->toContain('TEMP', 'TMP');
});
