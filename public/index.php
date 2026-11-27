<?php

declare(strict_types=1);

use App\CompositionRoot;
use App\Http\Request;
use App\Http\Session;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::fromGlobals();

Session::start($request);

CompositionRoot::httpKernel(getenv())->handle($request)->send();
