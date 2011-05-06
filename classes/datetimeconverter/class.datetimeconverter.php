<?php
/**
 *		Date and Time Converter by Elac v0.9.3
 *		elacdude@gmail.com
 *		www.elacdude.com
 *
 *		You are free to use this code free of charge, modify it, and distrubute it,
 *		just leave this comment block at the top of this file.
 *
 *
 *		Changes/Modifications
 *		6/24/08	- Version 0.9.2 released.  Minor additions
 *					- Added "S" support. (th, rd, st, nd.  example: 5th)
 *					- Added a few more abbreviations for units of time in calculate()  (s.  sec. secs. min. mins. m.  and more)
 *					- Added example.php (php examples and usage) and date_time_formats.html (list of supported date/time formats) to the package.
 *		6/25/08	- Version 0.9.3 released.  Bug fixes
 *					- Fixed month subtraction (wrap to previous year) bug
 *					- Fixed month and year "$only_return_the_value=true" bug.  If you calculated by months or years, and set
 * 					  $only_return_the_value=true, it would overwrite the values instead of just returning them.
 * 					- Fixed the "D" (Sun, Mon, Tue) bug.  If you supplied "D" and "d" in the same mask, it would not return the correct output.
 *					- Changed the names of public variables "day", "month", and "year" added "s" at the end for consistency purposes
 * 		11/14/08 - Version 0.9.4 released.  Bug fix
 * 					- Got rid of the _one_dig_num function and used ltrim($num "0") instead
 */





class Date_Time_Converter
{


    /*		PUBLIC VARIABLES		*/


    public $date_time_stamp;		//the date to be calculated in timestamp format
    public $date_time;				//the date to be calculated. ex: 12/30/2008 17:40:00
    public $date_time_mask;			//the php date() style format that $date_time is in.  ex: m/d/Y H:i:s

    public $seconds;
    public $minutes;
    public $hours;
    public $days;
    public $months;
    public $years;
    public $ampm;






    /*		CONSTRUCTOR and DESTRUCTOR */

    /** Constructor.  This is where you supply the date.  Accepts almost any format of
     *   date as long as you supply the correct mask.  DOES accept dates
     * 	without leading zeros (n,j,g,G) as long as they aren't bunched together.
     *   ie: ("1152008", "njY") wont work;   ("1/15/2008", "n/j/2008") will work.
     *   Example: $obj = new Date_Time_Calc('12/30/2008 17:40:00', 'm/d/Y H:i:s'); 	*/
    public function __construct($start_date_time, $mask) {
        $this->_default_date_time_units();				//set date&time units to default values
        $this->date_time = $start_date_time;
        $this->date_time_mask = $mask;

        //convert date to timestamp
        $this->date_time_stamp = $this->_date_to_timestamp($start_date_time, $mask);
    }


    public function __destruct() {
        unset($this->date_time_stamp);
        unset($this->date_time);
        unset($this->date_time_mask);
        unset($this->seconds);
        unset($this->minutes);
        unset($this->hours);
        unset($this->days);
        unset($this->months);
        unset($this->years);
        unset($this->ampm);
    }







    /*		PRIVATE FUNCTIONS		*/

    /** Private Function. Resets date and time unit variables to default
     */
    private function _default_date_time_units() {
        $this->seconds 		= '00';
        $this->minutes		= '00';
        $this->hours		= '12';
        $this->days			= '01';
        $this->months		= '01';
        $this->years		= date("Y");
        $this->ampm			= 'am';
    }


    /** Private Function.  Converts a textual month into a digit.  Accepts almost any
     * 	textual format of a month including abbreviations.
     *   Example: _month_num("jan"); //returns '1'   Example2: _month_num("january", true);  //returns '01'
     */
    private function _month_num($themonth, $return_two_digit=false) {

        switch (strtolower($themonth)) {
            case 'jan':
            case 'jan.';
            case 'january':
                return ($return_two_digit == true ? '01': '1');
                break;
            case 'feb':
            case 'feb.':
            case 'february':
            case 'febuary':
                return ($return_two_digit == true ? '02': '2');
                break;
            case 'mar':
            case 'mar.':
            case 'march':
                return ($return_two_digit == true ? '03': '3');
                break;
            case 'apr':
            case 'apr.':
            case 'april':
                return ($return_two_digit == true ? '04': '4');
                break;
            case 'may':
            case 'may.':
                return ($return_two_digit == true ? '05': '5');
                break;
            case 'jun':
            case 'jun.':
            case 'june':
                return ($return_two_digit == true ? '06': '6');
                break;
            case 'jul':
            case 'jul.':
            case 'july':
                return ($return_two_digit == true ? '07': '7');
                break;
            case 'aug':
            case 'aug.':
            case 'august':
                return ($return_two_digit == true ? '08': '8');
                break;
            case 'sep':
            case 'sep.':
            case 'sept':
            case 'sept.':
            case 'september':
                return ($return_two_digit == true ? '09': '9');
                break;
            case 'oct':
            case 'oct.':
            case 'october':
                return '10';
                break;
            case 'nov':
            case 'nov.':
            case 'november':
                return '11';
                break;
            case 'dec':
            case 'dec.':
            case 'december':
                return '12';
                break;
            default:
                return false;
                break;
        }
    }










    /** Private Function. Converts a date into a timestamp.  Accepts almost any
     * 	format of date as long as you supply the correct mask.  DOES accept dates
     * 	without leading zeros (n,j,g,G) as long as they aren't bunched together.
     *   ie: ("1152008", "njY") wont work;   ("1/15/2008", "n/j/2008") will work
     */
    private function _date_to_timestamp($thedate, $mask) {

        $mask_orig = $mask;
        // define the valid values that we will use to check
        // value => length
        $all = array(
         
        //time
			's' => 'ss',		// Seconds, with leading zeros
			'i' => 'ii',		// Minutes with leading zeros
			'H' => 'HH',		// 24-hour format of an hour with leading zeros
			'h' => 'hh',		// 12-hour format of an hour with leading zeros
			'G' => 'GG',  		// 24-hour format of an hour without leading zeros
			'g' => 'gg',  		// 12-hour format of an hour without leading zeros
			'A' => 'AA',		// Uppercase Ante meridiem and Post meridiem
			'a' => 'aa',		// Lowercase Ante meridiem and Post meridiem
         
        //year
			'y' => 'yy',		// A full numeric representation of a year, 4 digits
			'Y' => 'YYYY', 		// A two digit representation of a year
         
        //month
			'm' => 'mm', 		// A numeric representation of a month with leading zeros.
			'M' => 'MMM',		// A textual representation of a month.  3 letters.  ex: Jan, Feb, Mar, Apr...
			'n' => 'nn',		// Numeric representation of a month, without leading zeros
         
        //days
			'd' => 'dd',		// Day of the month, 2 digits with leading zeros
			'j' => 'jj',		// Day of the month without leading zeros
			'S' => 'SS',		// English ordinal suffix for the day of the month, 2 characters (st, nd, rd, or th. works well with j)
			'D' => 'DDD'		// Textual representation of day of the week (Sun, Mon, Tue, Wed)
         
        );

        // this will give us a mask with full length fields
        $mask = str_replace(array_keys($all), $all, $mask);

        $vals = array();

        //loop through each character of $mask starting at the beginning
        for ($i=0; $i<strlen($mask_orig); $i++) {
            //get the current character
            $thischar = substr($mask_orig, $i, 1);

            //if the character is not in the $all array, skip it
            if (array_key_exists($thischar, $all)) {
                $type = $thischar;
                $chars = $all[$type];

                // get position of the current char
                if(($pos = strpos($mask, $chars)) === false)
                continue;

                // find the value from $thedate
                $val = substr($thedate, $pos, strlen($chars));

                /*		START FIX FOR UNITS WITHOUT LEADING ZEROS		*/
                if ($type == "n" || $type == "j" || $type == "g" || $type == "G") {
                    //if its not numeric, try a shorter digit
                    if (!is_numeric($val)) {
                        $val = substr($thedate, $pos, strlen($chars)-1);
                        $mask = str_replace($chars, $type, $mask);
                    } else {
                        //try numeric value checking
                        switch ($type) {
                            case "n":
                                if ($val > 12 || $val < 1) {  //month must be between 1-12
                                    $val = substr($thedate, $pos, strlen($chars)-1);
                                    $mask = str_replace($chars, $type, $mask);
                                }
                                break;
                            case "j":
                                if ($val > 31 || $val < 1) {  //day must be between 1-31
                                    $val = substr($thedate, $pos, strlen($chars)-1);
                                    $mask = str_replace($chars, $type, $mask);
                                }
                                break;
                            case "g":
                                if ($val > 12 || $val < 1) {  //day must be between 1-12
                                    $val = substr($thedate, $pos, strlen($chars)-1);
                                    $mask = str_replace($chars, $type, $mask);
                                }
                                break;
                            case "G":
                                if ($val > 24 || $val < 1) {  //day must be between 1-24
                                    $val = substr($thedate, $pos, strlen($chars)-1);
                                    $mask = str_replace($chars, $type, $mask);
                                }
                                break;
                        }
                    }
                }

                /*		END FIX FOR UNITS WITHOUT LEADING ZEROS		*/

                //save this value
                $vals[$type] = $val;
            }
        }

        foreach($vals as $type => $val) {

            switch($type) {
                case 's' :
                    $this->seconds = $val;
                    break;
                case 'i' :
                    $this->minutes = $val;
                    break;
                case 'H':
                case 'h':
                    $this->hours = $val;
                    break;
                case 'A':
                case 'a':
                    $this->ampm = $val;
                    break;
                case 'y':
                    $this->years = '20'.$val;
                    break;
                case 'Y':
                    $this->years = $val;
                    break;
                case 'm':
                    $this->months = $val;
                    break;
                case 'M':
                    $this->months = $this->_month_num($val, true);
                    break;
                case 'd':
                    $this->days = $val;
                    break;
                    //ones without leading zeros:
                case 'n':
                    $this->months = $val;
                    break;
                case 'j':
                    $this->days = $val;
                    break;
                case 'g':
                    $this->hours = $val;
                    break;
                case 'G':
                    $this->hours = $val;
                    break;
            }
        }

        if (strtolower($this->ampm) == "pm") {$this->hours = $this->hours + 12;}			//if its pm, add 12 hours

        $make_stamp = adodb_mktime( (int)ltrim($this->hours, "0"), (int)ltrim($this->minutes, "0"),
        (int)ltrim($this->seconds, "0"), (int)ltrim($this->months, "0"),
        (int)ltrim($this->days, "0"), (int)ltrim($this->years, "0"));

        return $make_stamp;

    }










    /**		PUBLIC FUNCTIONS			*/





    /** Sets a new format/mask for the date using the php date() style formatting
     * 	Example: $obj->convert("M j Y H:i:s A");
     */
    public function convert($new_mask, $save=true) {
        $newdate = adodb_date($new_mask, $this->date_time_stamp);
        //if they want to save and apply this new mask to $this->date_time, save it
        if ($save == true) {
            $this->date_time_mask = $new_mask;
            $this->date_time = $newdate;
        }
        return $newdate;
    }






    /** Changes the date to a new one.
     *   Example: $obj->set_date_time('11/20/2005 07:40:00 AM', 'm/d/Y H:i:s A');
     */
    public function set_date_time($start_date_time, $mask) {
        $this->__construct($start_date_time, $mask);
    }







}

?>