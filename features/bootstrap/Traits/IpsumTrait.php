<?php

namespace Traits;
use joshtronic\LoremIpsum;


trait IpsumTrait {

    /**
     * Generate lorem ipsum text with X number of words.
     *
     * @When I generate lorem ipsum text with :number words
     * @param $number
     * @return string as words by default has array set on false
     */
    public function genereteXnumberOfLoremIpsumWords($number) {
        $lipsum = new LoremIpsum();
        return $lipsum->words($number);
    }

    /**
     * @And I fill in :number words lorem ipsum text into :field field
     * @param $number
     * @param $field
     */
    public function fillInLoremIpsumTextIntoField($number, $field) {
        $this->fillField($field, $this->genereteXnumberOfLoremIpsumWords($number));
    }

    /**
     * Fill in WYSIWYG field with X number of lorem ipsum text.
     *
     * @When I fill in :number words lorem ipsum text into :field wysiwyg field
     * @param $number
     * @param $field
     */
    public function fillInLoremIpsumTextIntoWysiwygField($number, $field) {
        $this->writeElementText($this->genereteXnumberOfLoremIpsumWords($number), $field);
    }

}
