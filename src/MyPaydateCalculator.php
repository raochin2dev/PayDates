<?php

namespace MyApp;

use MyApp\PayModel;

class MyPaydateCalculator implements PaydateCalculatorInterface {

	/**
	 * Pay Models
	 * @var array
	 */
	private $models = ['MONTHLY', 'BIWEEKLY', 'WEEKLY'];

	/**
	 * Holidays
	 * @var array
	 */
	private $holidays;

	/**
	 * Employee pay model
	 * @var PayModel
	 */
	private $payModel;

	/**
	 * This function takes a paydate model and a first paydate and generates the next $number_of_paydates paydates.
	 *
	 * @param string $paydateModel The paydate model, one of the items in the spec
	 * @param string $paydateOne First paydate as a string in Y-m-d format, different from the second
	 * @param int $numberOfPaydates The number of paydates to generate
	 *
	 * @return array the next paydates (from today) as strings in Y-m-d format
	 */
	public function calculateNextPaydates($paydateModel, $paydateOne, $numberOfPaydates) {
		$this->validateArgs($paydateModel, $paydateOne, $numberOfPaydates);

		$this->setPayModel($paydateModel);

		$nextPayDates = array();
		$currentPayDate = $paydateOne;

		for ($i = 0; $i < $numberOfPaydates; $i++) {
			$currentPayDate = $this->increaseDate($currentPayDate, $this->payModel->getCount(), $this->payModel->getUnit());
			$currentPayDate = $this->setToValidPayDate($currentPayDate);
			$nextPayDates[] = $currentPayDate;
		}

		return $nextPayDates;
	}

	/**
	 * This function determines whether a given date in Y-m-d format is a holiday.
	 *
	 * @param string $date A date as a string formatted as Y-m-d
	 *
	 * @return boolean whether or not the given date is on a holiday
	 */
	public function isHoliday($date) {
		$this->holidays = ['01-01-2014', '20-01-2014', '16-02-2014', '17-02-2014', '26-05-2014', '04-07-2014', '01-09-2014', '13-10-2014', '11-11-2014', '27-11-2014', '25-12-2014', '01-01-2015', '19-01-2015', '16-02-2015', '25-05-2015', '03-07-2015', '07-09-2015', '12-10-2015', '11-11-2015', '26-11-2015', '25-12-2015'];

		return in_array($this->convertDate($date, 'd-m-Y'), $this->holidays);
	}

	/**
	 * This function determines whether a given date in Y-m-d format is on a weekend.
	 *
	 * @param string $date A date as a string formatted as Y-m-d
	 *
	 * @return boolean whether or not the given date is on a weekend
	 */
	public function isWeekend($date) {
		$dayNo = date('N', strtotime($date));

		return ($dayNo > 5);
	}

	/**
	 * This function determines whether a given date in Y-m-d format is a valid paydate according to specification rules.
	 *
	 * @param string $date A date as a string formatted as Y-m-d
	 *
	 * @return boolean whether or not the given date is a valid paydate
	 */
	public function isValidPaydate($date) {
		return (!$this->isHoliday($date) && !$this->isWeekend($date));
	}

	/**
	 * This function increases a given date in Y-m-d format by $count $units
	 *
	 * @param string $date A date as a string formatted as Y-m-d
	 * @param integer $count The amount of units to increment
	 * @param string $unit adjustment unit
	 *
	 * @return string the calculated day's date as a string in Y-m-d format
	 */
	public function increaseDate($date, $count, $unit = 'day') {
		return date('Y-m-d', strtotime("+" . $count . " " . $unit, strtotime($date)));
	}

	/**
	 * This function decreases a given date in Y-m-d format by $count $units
	 *
	 * @param string $date A date as a string formatted as Y-m-d
	 * @param integer $count The amount of units to decrement
	 * @param string $unit adjustment unit
	 *
	 * @return string the calculated day's date as a string in Y-m-d format
	 */
	public function decreaseDate($date, $count, $unit = 'day') {
		return date('Y-m-d', strtotime("-" . $count . " " . $unit, strtotime($date)));
	}

	/**
	 * This function validates the arguments provided for paydate calculations
	 *
	 * @param string $paydateModel The paydate model, one of the items in the spec
	 * @param string $paydateOne First paydate as a string in Y-m-d format, different from the second
	 * @param int $numberOfPaydates The number of paydates to generate
	 *
	 */
	public function validateArgs(...$args) {
		if (!in_array($args[0], $this->models)) {
			echo "\nError: Invalid Pay Model! Should be one of the '" . implode("','", $this->models) . "' \n\n";
			exit;
		}

		$d = $this->convertDate($args[1], 'Y-m-d');
		if (!($d == $args[1])) {
			echo "\nError: Invalid date !\n\n";
			exit;
		}

		if (!is_numeric($args[2]) || (int) $args[2] < 0) {
			echo "\nError: Invalid count !\n\n";
			exit;
		}
	}

	/**
	 * This function set the pay model
	 *
	 * @param string $model The paydate model, one of the items in the spec
	 *
	 */
	public function setPayModel($model) {
		switch ($model) {
		case 'WEEKLY':
			$this->payModel = new PayModel('day', 7);
			break;
		case 'BIWEEKLY':
			$this->payModel = new PayModel('day', 14);
			break;
		case 'MONTHLY':
			$this->payModel = new PayModel('month', 1);
			break;
		}
	}

	/**
	 * This function sets the valid pay date adjusting for holidays & weekends
	 *
	 * @param string $currentPayDate Current pay date in Y-m-d format
	 *
	 * @return string Adjusted valid date as a string in Y-m-d format
	 */
	public function setToValidPayDate($currentPayDate) {

		/* Loop until a valid pay date found */
		while (!$this->isValidPaydate($currentPayDate)) {
			if ($this->isHoliday($currentPayDate)) {
				$dayNo = date('N', strtotime($currentPayDate));

				/* Special case if Monday is a holiday then the current day would be adjusted to Friday */
				if ($dayNo == 1) {
					$currentPayDate = $this->decreaseDate($currentPayDate, 3, 'day');
				} elseif ($dayNo == 7) {
					$currentPayDate = $this->decreaseDate($currentPayDate, 2, 'day');
				} else {
					$currentPayDate = $this->decreaseDate($currentPayDate, 1, 'day');
				}
			}

			if (!$this->isHoliday($currentPayDate) && $this->isWeekend($currentPayDate)) {
				$currentPayDate = $this->increaseDate($currentPayDate, 1, 'day');
			}
		}

		return $currentPayDate;
	}

	/**
	 * This function sets the valid pay date adjusting for holidays & weekends
	 *
	 * @param string $date Date given in any format
	 * @param string $format The format which date is to be converted
	 *
	 * @return string date Converted date to the given format
	 */
	public function convertDate($date, $format) {
		return date($format, strtotime($date));
	}
}
