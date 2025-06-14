<?php
class Poker {
    private $cards = array("2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K", "A");
    private $suits = array("H", "D", "C", "S"); // Hearts ♥, Diamonds ♦, Clubs ♣, Spades ♠
    private $cardDeck = array();
    public function __construct(){
        //echo 123;
        foreach ($this->cards as $item1) {
            foreach ($this->suits as $item2) {
                $this->cardDeck[] = array($item1, $item2);
            }
        }
        shuffle($this->cardDeck);
        $this->start();
        /* echo '<pre>';
        print_r($this->cardDeck);
        echo '</pre>'; */
    }

    private function start($playersCount = 4){
        $this->preflop($playersCount);
    }

    private function preflop($playersCount){
        for($i = 1;$i <= $playersCount; $i++){
            $dealedCards = $this->getPairCard();
            echo "Player $i: <img style=\"border: 1px solid black\" src=\"Assets/cards/$dealedCards[0].svg\" width=\"80\" height=\"120\"> <img style=\"border: 1px solid black\" src=\"Assets/cards/$dealedCards[1].svg\" width=\"80\" height=\"120\"><br>";
        }
        $this->flop();
    }
    private function flop(){
        $flop = array(array_pop($this->cardDeck),array_pop($this->cardDeck),array_pop($this->cardDeck));
        $card_1 = $flop[0][0].$flop[0][1];
        $card_2 = $flop[1][0].$flop[1][1];
        $card_3 = $flop[2][0].$flop[2][1];
        echo "<br><br><br>Flop: <img style=\"border: 1px solid black\" src=\"Assets/cards/$card_1.svg\" width=\"80\" height=\"120\"> <img style=\"border: 1px solid black\" src=\"Assets/cards/$card_2.svg\" width=\"80\" height=\"120\"> <img style=\"border: 1px solid black\" src=\"Assets/cards/$card_3.svg\" width=\"80\" height=\"120\"><br>";
        $this->turn();
    }
    private function turn(){
        $flop = array(array_pop($this->cardDeck));
        $card_4 = $flop[0][0].$flop[0][1];
        echo "<br><br><br>Turn: <img style=\"border: 1px solid black\" src=\"Assets/cards/$card_4.svg\" width=\"80\" height=\"120\"><br>";
        $this->river();
    }
    private function river(){
        $flop = array(array_pop($this->cardDeck));
        $card_5 = $flop[0][0].$flop[0][1];
        echo "<br><br><br>River: <img style=\"border: 1px solid black\" src=\"Assets/cards/$card_5.svg\" width=\"80\" height=\"120\"><br>";
    }
    private function getPairCard(){
        $randomKey = array_rand($this->cardDeck);
        $firstCard = $this->cardDeck[$randomKey];
        unset($this->cardDeck[$randomKey]);
        $this->cardDeck = array_values($this->cardDeck);

        $randomKey = array_rand($this->cardDeck);
        $secondCard = $this->cardDeck[$randomKey];
        unset($this->cardDeck[$randomKey]);
        $this->cardDeck = array_values($this->cardDeck);
        return [$firstCard[0].$firstCard[1], $secondCard[0].$secondCard[1]];
    }
}