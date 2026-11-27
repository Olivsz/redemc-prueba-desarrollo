<?php

declare(strict_types=1);

use App\Http\HttpKernel;
use App\Http\Request;
use App\Http\Session;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::fromGlobals();

Session::start($request);

HttpKernel::create(getenv())->handle($request)->send();
