<?php
namespace App\Controllers;

use App\Controllers\Controller;

class HomeController extends Controller
{
	public function home()
	{
        $this->redirectIfAuthenticated();
        $child_view = ROOT_PATH . 'app/Views/home.php';
        $title = "Home - Wellcome";
        $csrf = $this->set_csrf();
        $javascript = ['/Views/js/login.js'];
        include ROOT_PATH . 'app/Views/layout.php';
	}
}
