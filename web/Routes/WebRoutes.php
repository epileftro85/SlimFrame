<?php
namespace Routes;

require_once __DIR__.'/init.php';

get('/', 'HomeController@home');

post('/register', 'AuthController@register');
post('/login', 'AuthController@doLogin');

get('/dashboard', 'DashController@index', ['auth']);

dispatch();