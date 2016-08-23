<?php

class Deck{

	public $cards = array();
	const CARDS = 52;
	public static $suit_order = array("Club","Diamond","Heart","Spade");  // suit order
	public static $values = array('2','3','4','5','6','7','8','9','10','J','Q','K','A');

	function __construct(){
		
		for ($i=0; $i<self::CARDS; $i++){
			
			switch (intval($i/13)) {
				case 0:
					$this->cards[] = Card::factory(Deck::$suit_order[0], $i);
					continue;
				case 1:
					$this->cards[] = Card::factory(Deck::$suit_order[1], $i);
					continue;
				case 2:
					$this->cards[] = Card::factory(Deck::$suit_order[2], $i);
					continue;
				case 3:
					$this->cards[] = Card::factory(Deck::$suit_order[3], $i);
					continue;
			}

		}

	}

	function dealOne( $type_=null, $value=null ){

		$card = null;

		for( $i=count($this->cards)-1; $i>0; $i-- ){

			if($this->cards[$i]->dealt) 
				continue;

			if( $type_ && $value ){
				
				if( $this->cards[$i]->type_==$type_ && $this->cards[$i]->value==$value )
					$card = $this->cards[$i];
				else
					continue;

			}else
				$card = $this->cards[$i];

			$this->cards[$i]->dealt = true;

		}
		
		return $card;

	}

	function print_(){

		$nd_cards = $d_cards = array();

		foreach ($this->cards as $card) {
			if( $card->dealt )
				$d_cards[] = $card;
			else
				$nd_cards[] = $card;
		}

		if( count($nd_cards) ) {
			echo "Non-dealt cards: \n\n";
			foreach ($nd_cards as $card) {
				$card->print_();
			}
		}else
			echo "\nThere is no Non-dealt cards!\n\n";

		
		if( count($d_cards) ) {
			echo "Dealt cards: \n\n";
			foreach ($nd_cards as $card) {
				$card->print_();
			}
		}else
			echo "\nThere is no Dealt cards!\n\n";

	}

	function shuffle(){

		for( $i=count($this->cards)-1; $i>0; $i-- ){
			$rand = rand(0,$i);
			$this->cards[$rand]->dealt = false;
			$card = $this->cards[$i];
			$this->cards[$i] = $this->cards[$rand];
			$this->cards[$rand] = $card;
		}

	}

}

abstract class Card{
	
	public $index;  // position on deck
	public $dealt = false;
	public $type_;
	public $value;

	function __construct( $index ){
		$this->index = $index;
		$this->value = Deck::$values[$index%13];
	}

	static function factory( $type_, $index ){
		switch ($type_) {
			case "Club":
				return new Club( $index );
			case "Diamond":
				return new Diamond( $index );
			case "Heart":
				return new Heart( $index );
			case "Spade":
				return new Spade( $index );
		}
	}

	function print_type(){
		echo $this->type_."\n";
	}

	function print_( ){
		echo $this->type_." ".$this->value."\n";
	}

}

class Hand {

	public static $sortBy = "value";    // default sort by value
	public $cards = array();
	private $deck = null;   // observer

	public function __construct( $deck ){
		$this->deck = $deck;   // add observer object
	}

	function print_(){
		echo "Cards in hand\n-------------\n";
		foreach ($this->cards as $card) { 
			echo $card->type_." ".$card->value."\n";
		}
		echo "\n";
	}

	function addCard($type_, $value){
		if( !in_array($type_, Deck::$suit_order) )
			exit("\n'$type_' is invalid type.\nIt has to be: Club, Diamond, Heart, or Spade\n\n");

		if( !in_array($value, Deck::$values) )
			exit("\n'$value' is invalid number.\nIt has to be 2 through A\n\n");

		$card = $this->deck->dealOne($type_, $value);

		$this->cards[] = $card;
	}

	function divide( $cards ){

		if (count($cards)===1) {
        	return $cards;
    	}

		$left = $right = array();
		$middle = round(count($cards)/2);

		for ($i = 0; $i < $middle; ++$i) {
	        $left[] = $cards[$i];
	    }

	    for ($i = $middle; $i < count($cards); ++$i) {
	        $right[] = $cards[$i];
	    }
    
    	$left = $this->divide($left);
    	$right = $this->divide($right);
    	return $this->conquer($left, $right);

	}

	function conquer(array $left, array $right) {
	    
	    $result = array();
	    
	    while (count($left) > 0 || count($right) > 0) {
	        if (count($left) > 0 && count($right) > 0) {

	          	$firstLeft = $left[0]->value;
	            $firstRight = $right[0]->value;

	            if(Hand::$sortBy=="suit"){
	          		$firstLeft = array_search($left[0]->type_,Deck::$suit_order);  
	            	$firstRight = array_search($right[0]->type_,Deck::$suit_order);  
	            }

	            if ($firstLeft <= $firstRight) 
	                $result[] = array_shift($left);
	            else 
	                $result[] = array_shift($right);
	            
	        } else if (count($left) > 0)
	            $result[] = array_shift($left);
	        else if (count($right) > 0)
	            $result[] = array_shift($right);

	    }

	    return $result;
	}

	function sortByValue(){

		Hand::$sortBy = "value";

		if(count($this->cards)>1)
			$this->cards = $this->divide($this->cards);

		$this->print_();
	}

	function sortBySuit(){

		Hand::$sortBy = "suit";

		if(count($this->cards)>1)
			$this->cards = $this->divide($this->cards);

		$this->print_();
	}

	function findStraight($start, $end, $sameSuit){

		if( $end-$start==0 )
			return true;

		if( $end - $start != $this->cards[$end]->value - $this->cards[$start]->value )
			return false;

		if( $sameSuit && $this->cards[$end]->type_!=$this->cards[$start]->type_ )
			return false;

		return $this->findStraight($start+1, $end, $sameSuit);

	}

	function hasStraight($len, $sameSuit){

		$found = false;

		for( $i=0; $i<count($this->cards); $i++ ){

			if( !isset($this->cards[$i+$len]) )
				break;

			if( $this->findStraight( $i, $i+$len, $sameSuit ) )
				$found = true;
			else
				continue;
		}

		return $found;
	
	}
}

class Club extends Card {
	public $type_ = "Club";
}

class Diamond extends Card {
	public $type_ = "Diamond";
}

class Heart extends Card {
	public $type_ = "Heart";
}

class Spade extends Card {
	public $type_ = "Spade";
}


/**
* Testing Part
*/
$deck = new Deck();
$deck->print_();

echo "Shuffling:\n";
$deck->shuffle();
$deck->print_();

$hand = new Hand($deck);
$hand->addCard("Heart", 2);
$hand->addCard("Club", 4);
$hand->addCard("Diamond", 3);
$hand->addCard("Diamond", 4);
$hand->addCard("Diamond", 5);
$hand->print_();
echo "Cards added.\n\n";

echo "[Sort by Suit]\n";
$hand->sortBySuit();

echo "[Sort by Value]\n";
$hand->sortByValue();

echo "Has 2 straight?\n";
if($hand->hasStraight(2, false)) echo "True\n\n";
else echo "False\n\n";

?>