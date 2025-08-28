<?php
namespace App\Controllers;

use App\Controllers\Controller;
use App\Utils\View;

class HomeController extends Controller
{
	public function home()
	{
        $this->redirectIfAuthenticated();

        View::make()
            ->with([
                'title' => 'Home - Welcome',
                'csrf' => $this->set_csrf(),
                'javascript' => ['/js/login.js']
            ])
            ->display('home', 'layout');
	}
}