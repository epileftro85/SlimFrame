<?php
namespace App\Controllers;

use App\Controllers\Controller;

class DashController extends Controller
{
	public function index()
	{
		$this->out('dashboard');
	}
}
