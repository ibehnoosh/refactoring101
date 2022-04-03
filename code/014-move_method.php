<?php
/**
 * We change the Price class to no longer be abstract, and remove the getPriceCode()
 * abstract method.
 *
 * In this step we "Move Method" getCharge() from Movie to Price and call it from
 * the old getCharge() left behind in the Movie class.
 * 
 * Keep in mind, this is just a step toward an end.  The intent is not to leave
 * the method in its current state in Price.
 * 
 */

namespace Refactoring014;

class Customer {
    protected string $name;
    protected array $rentals;
    
    public function __construct($name) {
        $this->name = $name;
    }
    
    public function addRental(Rental $rental) {
        $this->rentals[] = $rental;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function statementText(): string {
        $result = "Rental Record for " . $this->getName() . "\n";
        
        foreach ($this->rentals as $rental) {
            $result .= "\t" . $rental->movie->getTitle() . "\t" . $rental->getCharge() . "\n";
        }

        // add footer lines
        $result .= "Amount owed is " . $this->getTotalCharge() . "\n";
        $result .= "You earned " . $this->getTotalFrequentRenterPoints() . " frequent renter points";
        
        return $result;
    }

    public function statementHtml(): string {
        $result = "<h1>Rental Record for <em>" . $this->getName() . "</em></h1>\n";

        foreach ($this->rentals as $rental) {
            $result .= $rental->movie->getTitle() . " " . $rental->getCharge() . "<br />\n";
        }

        // add footer lines
        $result .= "<p>Amount owed is <em>" . $this->getTotalCharge() . "</em></p>\n";
        $result .= "<p>You earned <em>" . $this->getTotalFrequentRenterPoints() . "</em> frequent renter points</p>";

        return $result;
    }
    
    public function getTotalFrequentRenterPoints(): int {
        $result = 0;
        
        foreach ($this->rentals as $rental) {
            $result += $rental->getFrequentRenterPoints();
        }
        
        return $result;
    }
    
    public function getTotalCharge(): float {
        $result = 0;
        
        foreach ($this->rentals as $rental) {
            $result += $rental->getCharge();
        }
        
        return $result;
    }
}

class Movie {
    const CHILDREN = 2;
    const REGULAR = 0;
    const NEW_RELEASE = 1;
    
    protected string $title;
    protected Price $price;
    
    public function __construct($title, $priceCode) {
        $this->title = $title;
        $this->setPrice($priceCode);
    }
    
    public function getPriceCode(): int {
        return $this->price->getPriceCode();
    }
    
    public function setPrice($priceCode) {

        switch ($priceCode) {
            case self::REGULAR:
                $this->price = new RegularPrice();
                break;

            case self::CHILDREN:
                $this->price = new ChildrenPrice();
                break;

            case self::NEW_RELEASE:
                $this->price = new NewReleasePrice();
                break;

            default:
                throw new Exception('Incorrect Price Code.');
                break;
        }
    }
    
    public function getTitle(): string {
        return $this->title;
    }

    public function getCharge($daysRented): float {
        return $this->price->getCharge($daysRented);
    }

    public function getFrequentRenterPoints($daysRented): int {
        $result = 0;

        // add bonus for a two-day new release rental
        if (($this->getPriceCode() == self::NEW_RELEASE) && ($daysRented > 1)) {
            $result += 2;
        } else {
            $result += 1;
        }

        return $result;
    }
}

class Rental {
    public Movie $movie;
    protected int $daysRented;
    
    public function __construct(Movie $movie, $daysRented) {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }
    
    public function getDaysRented(): int {
        return $this->daysRented;
    }
    
    public function getMovie(): Movie {
        return $this->movie;
    }

    public function getCharge(): float {
        return $this->movie->getCharge($this->getDaysRented());
    }

    public function getFrequentRenterPoints(): int {
        return $this->movie->getFrequentRenterPoints($this->getDaysRented());
    }
}

class Price {
    public function getCharge($daysRented): float {
        $result = 0;

        switch ($this->getPriceCode()) {
            case Movie::REGULAR:
                $result += 2;
                if ($daysRented > 2) {
                    $result += ($daysRented - 2) * 1.5;
                }
                break;

            case Movie::NEW_RELEASE:
                $result += $daysRented * 3;
                break;

            case Movie::CHILDREN:
                $result += 1.5;
                if ($daysRented > 3) {
                    $result += ($daysRented - 3) * 1.5;
                }
                break;
        }

        return $result;
    }
}

class ChildrenPrice extends Price {
    public function getPriceCode(): int {
        return Movie::CHILDREN;
    }
}

class NewReleasePrice extends Price {
    public function getPriceCode(): int {
        return Movie::NEW_RELEASE;
    }
}

class RegularPrice extends Price {
    public function getPriceCode(): int {
        return Movie::REGULAR;
    }
}

// define customer
$customer = new Customer('Adam Culp');

// choose movie to be rented, define rental, add it to the customer
$movie = new Movie('Gladiator', 0);
$rental = new Rental($movie, 1);
$customer->addRental($rental);

// choose 2nd movie to be rented, define rental, add it to the customer
$movie = new Movie('Spiderman', 1);
$rental = new Rental($movie, 2);
$customer->addRental($rental);

// print the statement
echo $customer->statementText();
echo $customer->statementHtml();
