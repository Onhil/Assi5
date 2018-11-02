<?php
/**
  * This file is a part of the code used in IMT2571 Assignment 5.
  *
  * @author Rune Hjelsvold
  * @version 2018
  */

require_once('Club.php');
require_once('Skier.php');
require_once('YearlyDistance.php');
require_once('Affiliation.php');

/**
  * The class for accessing skier logs stored in the XML file
  */  
class XmlSkierLogs
{
    /**
      * @var DOMDocument The XML document holding the club and skier information.
      */  
    protected $doc;
    
    /**
      * @param string $url Name of the skier logs XML file.
      */  
    public function __construct($url)
    {
        $this->doc = new DOMDocument();
        $this->doc->load($url);
        $this->xpath = new DOMXpath($this->doc);
    }
    
    /**
      * The function returns an array of Club objects - one for each
      * club in the XML file passed to the constructor.
      * @return Club[] The array of club objects
      */
    public function getClubs()
    {
        $clubs = array();
        
         $elements = $this->xpath->query('/SkierLogs/Clubs/Club');
        foreach ($elements as $element) {
            $xmlName = $element->getElementsByTagName("Name");
            $xmlCity = $element->getElementsByTagName("City");
            $xmlCounty = $element->getElementsByTagName("County");

            $valueName = $xmlName->item(0)->nodeValue;
            $valueCity = $xmlCity->item(0)->nodeValue;
            $valueCounty = $xmlCounty->item(0)->nodeValue;
            
            $v = new Club($element->getAttribute('id'), $valueName, $valueCity, $valueCounty);
            
            array_push($clubs, $v); // appends new club
        }
        // TODO: Implement the function retrieving club information
        return $clubs;
    }

    /**
      * The function returns an array of Skier objects - one for each
      * Skier in the XML file passed to the constructor. The skier objects
      * contains affiliation histories and logged yearly distances.
      * @return Skier[] The array of skier objects
      */
    public function getSkiers()
    {
        $skiers = array();

        $elements = $this->xpath->query('/SkierLogs/Skiers/Skier');
        foreach ($elements as $element) {
          $xmlFirstName = $element->getElementsByTagName("FirstName");
          $xmlLastName = $element->getElementsByTagName("LastName");
          $xmlYearOfBirth = $element->getElementsByTagName("YearOfBirth");

          $valueFirstName = $xmlFirstName->item(0)->nodeValue;
          $valueLastName = $xmlLastName->item(0)->nodeValue;
          $valueYearOfBirth = $xmlYearOfBirth->item(0)->nodeValue;
          
          
          $v = new Skier($element->getAttribute('userName'), $valueFirstName, $valueLastName, $valueYearOfBirth);
          $seasons = $this->xpath->query('/SkierLogs/Season');
          foreach ($seasons as $season) { // Goes through both seasons
            // Go through all Skiers elements
            foreach ($season->getElementsByTagName("Skiers") as $affiliationElement) { 
              // Goes through all skiers for current year
              foreach ($affiliationElement->getElementsByTagName("Skier") as $skierElement) { 
                if ($skierElement->getAttribute('userName') == $element->getAttribute('userName')) {

                  // Checks wether or not skier is in affiliated with a club
                  if ($affiliationElement->getAttribute('clubId')) {
                    $affiliation = new Affiliation($affiliationElement->getAttribute('clubId'), 
                      $season->getAttribute('fallYear'));
                    $v->addAffiliation($affiliation);
                  }
                      
                  // Goes through each log element
                  foreach($skierElement->getElementsByTagName('Log') as $log) { 
                    $distance = array();
                          
                    // Yearly distance
                    // Goes through each entry in the log
                    foreach ($log->getElementsByTagName('Entry') as $entry) { 
                    $xmlDistance = $entry->getElementsByTagName("Distance");
                    $distance[] = $xmlDistance->item(0)->nodeValue;
                  }
                }
                      
                $yearlyDistance = new YearlyDistance($season->getAttribute('fallYear'), array_sum($distance));
                $v->addYearlyDistance($yearlyDistance); 
              }
            }
          }
        }
          array_push($skiers, $v); // appends new club
        }
        
      
    
        // TODO: Implement the function retrieving skier information,
        //       including affiliation history and logged yearly distances.
        return $skiers;
    }
}

?>