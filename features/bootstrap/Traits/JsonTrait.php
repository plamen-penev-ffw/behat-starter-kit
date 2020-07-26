<?php

namespace Traits;

use Exception;



trait JsonTrait {

    // Defining variables for JSON usage
    private $json = array();

    /**
     * This function will create the JSON file if it's not existing already.
     */
    public function createJsonFileIfNotExisting(){
        if(isset($this->params["json_file_path"])){
            fopen($this->params["json_file_path"], "a+");
        }
    }

    /**
     * Clear the json file
     *
     * @When I clear the json file
     */
    public function iClearTheJsonFile() {
        unset($this->json);
        $this->json = array();
        file_put_contents($this->params["json_file_path"], json_encode($this->json, JSON_PRETTY_PRINT));
    }

    /**
     * Store a variable with specific value to the json file
     *
     * @param $variable
     * @param $value
     */
    public function iStoreVariableWithValueToTheJsonFile($variable, $value) {
        $temp_json_array = json_decode(file_get_contents($this->params["json_file_path"]), TRUE);
        $temp_json_array[$variable] = $value;
        $this->json = $temp_json_array;
        file_put_contents($this->params["json_file_path"], json_encode($this->json,JSON_PRETTY_PRINT));
    }



    /**
     * Return the value of specific JSON variable.
     *
     * @param $variable
     * @return mixed
     * @throws Exception
     */
    public function iGetTheVariableFroMJsonFile($variable) {
        if(isset($this->params["json_file_path"])) {
            $decoded_json = json_decode(file_get_contents($this->params["json_file_path"]), TRUE);
        } else
            throw new Exception("No JSON file found. Have you set it?");

        if (isset($decoded_json[$variable])) {
            return $decoded_json[$variable];
        }
        else
            throw new Exception("Variable with name $variable was not found in the json file. Are you sure you have set it?");
    }

    /**
     * Return the value of specific JSON variable or null
     * To be used when you don't need to break the execution if the variable is not set.
     * Example: When you want to use the same step with custom or stored variable.
     *
     * @param $variable
     * @return mixed
     * @throws Exception
     */
    public function iGetTheVariableFroMJsonFile2($variable) {
        if(isset($this->params["json_file_path"])) {
            $decoded_json = json_decode(file_get_contents($this->params["json_file_path"]), TRUE);
        } else
            throw new Exception("No JSON file found. Have you set it?");

        if (isset($decoded_json[$variable])) {
            return $decoded_json[$variable];
        }
        else {
            return null;
        }
    }

}
