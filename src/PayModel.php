<?php

namespace MyApp;

class PayModel{

	/**
     * Unit - day/month 
     * @var string
     */
	private $unit;

	/**
     * Increment count for the unit 
     * @var int
     */
	private $count;

	public function __construct($unit,$count){
		
		$this->unit = $unit;
		$this->count = $count;

	}
	  
	/**
     * Get the unit value.
     *
     * @return string 
     */
	public function getUnit(){
		
		return $this->unit;
	
	}
	
	/**
     * Get the count value.
     *
     * @return int 
     */
	public function getCount(){
		
		return $this->count;
	
	}   

}