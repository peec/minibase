<?php
namespace app\models;

/**
 * @Entity @Table(name="news")
 */
class News {
	
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	/** @Column(type="string") **/
	protected $title;
	
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getTitle () {
		return $this->title;
	}
	
	public function getId () {
		return $this->id;
	}
	
}