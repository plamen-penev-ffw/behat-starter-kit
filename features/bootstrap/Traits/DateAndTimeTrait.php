<?php

namespace Traits;
use DateTime;
use Exception;
use Traits\JsonTrait;

trait DateAndTimeTrait {

    // Declare time and date variables
    public $generatedDate = null;
    public $generatedTime = null;

    /**
     * Generate a date in provided format for today or X days from now.
     * You can use +5 days or -5 days.
     *
     * The generated date will be stored in the JSON, by default it is named "date" but the name can be provided with one of the step definitions.
     *
     * Example format is Y-m-d which will generate date like this 2017-01-30
     *
     * @When I generate date in :format format for :days days from now and store it in :variable JSON variable
     * @When I generate date in :format format for :days days from now
     * @When I generate date in :format format for today
     * @param $format , $days, $variable
     * @param int $days
     * @param string $variable
     * @throws Exception
     */
    public function iGenerateDateInFormatPlusDays($format ,$days = 0, $variable = "date") {
        $date = new DateTime('now');
        $string = $days." day";
        $date->modify($string);
        $this->iStoreVariableWithValueToTheJsonFile($variable, $date->format($format));
    }

    /**
     * Generate a time in provided format for now or X hours Y minutes from now.
     * You can use +3 hours and +30 minutes
     *
     * The generated time will be stored in the JSON, by default it is named "time" but the name can be provided with one of the step definitions.
     *
     * Example format is H:i:s which will generate time like this 15:30:28
     *
     * @When I generate time in :format format for :hours hours and :minutes minutes from now and store it in :variable JSON variable
     * @When I generate time in :format format for :hours hours and :minutes minutes from now
     * @When I generate time in :format format for :hours hours from now
     * @When I generate time in :format format for :minutes minutes from now
     * @When I generate time in :format format for now
     * @param $format , $hours, $minutes, $variable
     * @param int $hours
     * @param int $minutes
     * @param string $variable
     * @throws Exception
     */
    public function iGenerateTimeInFormatPlusHoursMinutes($format ,$hours = 0, $minutes = 0, $variable = "time") {
        $date = new DateTime('now');
        // Modify the hours
        $string = $hours." hours";
        $date->modify($string);
        // Modify the minutes
        $string = $minutes. " minutes";
        $date->modify($string);
        $this->iStoreVariableWithValueToTheJsonFile($variable, $date->format($format));
    }

    /**
     * Echo the $generatedTime variable
     * @When I see the generated time
     */
    public function iSeeTheGeneratedTime(){
        echo $this->generatedTime;
    }

    /**
     * Echo the $generatedDate variable
     * @When I see the generated date
     */
    public function iSeeTheGeneratedDate(){
        echo $this->generatedDate;
    }

}
