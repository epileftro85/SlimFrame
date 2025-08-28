<?php
namespace App\Controllers;

use App\Controllers\Controller;

class HomeController extends Controller
{
	public function home()
	{
        $this->redirectIfAuthenticated();
        $child_view = __DIR__ . '/../views/home.php';
        $title = "Home - Wellcome";
        $csrf = $this->set_csrf();
        $javascript = ['/views/js/login.js'];
        include __DIR__ . '/../views/layout.php';
	}
}
