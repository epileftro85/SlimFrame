<?php
namespace App\Controllers;

use App\Controllers\Controller;
use App\Utils\View;

class DashController extends Controller
{
	public function index()
	{
		// $this->out('dashboard');
		View::make()
            ->with([
                'title' => 'Dashboard - Welcome',
                'csrf' => $this->set_csrf(),
            ])
            ->display('dashboard', 'layout');
	}
}
