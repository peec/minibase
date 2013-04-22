<?php
namespace app\controllers;

use Minibase\Mvc\Controller;

use app\models\News;

class Home extends Controller {
	
	public function index () {
		
		$em = $this->mb->em;
		// Entity manager
		$newsRepository = $em->getRepository('app\models\News');
		$news = $newsRepository->findAll();
		
		return $this->respond("html")
			->view('home.html', array('news' => $news));
	}
	
	public function createNews () {
		
		$news = new News();
		$news->setTitle($_POST['title']);
		$this->mb->em->persist($news);
		
		$this->mb->em->flush();
		return $this->respond("redirect")
			->to($this->call('app/controllers/Home.index')->reverse()->url);
	}
	
}