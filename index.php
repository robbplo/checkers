<?php

// Basic autoloader
foreach (glob(__DIR__.'/lib/*.php') as $file) {
    include_once $file;
}

Game::start();
