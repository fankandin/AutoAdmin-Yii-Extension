<?php
/**
 * Interface for classes that describe and operate with DB data type.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
interface AAIField
{
	/**
	 * Tests the options that configured by user. Checks all necessary parameters.
	 */
	public function testOptions();

	/**
	 * Adds necessary options (unnecessary to user if they was not set by him) to improve further operating.
	 */
	public function completeOptions();

	/**
	 * Prints the value of the field in HTML format adapted to CMS interface in read mode.
	 * @return string Generated HTML code.
	 */
	public function printValue();

	/**
	 * Generates a name for the input element - to input and process form data.
	 * @return string Generated name for a form element.
	 */
	public function formInputName();

	/**
	 * Prints the field in HTML form input mode (for CMS add/edit interface).
	 * @param CController &$controller Link to the controller.
	 * @param array $tagOptions Form input tag's parameters.
	 * @return string Generated HTML code.
	 */
	public function formInput(&$controller, $tagOptions=array());

	/**
	 * Loads data passed by the main editing form into the fild.
	 * @param array $formData Passed data from the form.
	 */
	public function loadFromForm($formData);

	/**
	 * Loads SQL query's result to internal values.
	 * @param array $queryRow An associative array of data as result of the classic Yii method queryRow().
	 */
	public function loadFromSql($queryRow);

	/**
	 * Prepares internal value for inserting into SQL DB and returns it.
	 * @return string|\CDbExpression A value ready to insert in DB (or update another one).
	 */
	public function valueForSql();

	/*
	 * Validates value with rules set by user.
	 * It ignores the default and NULL checkings and orients only on not-null form values.
	 * @param mixed A value.
	 * @return bool Whether the value is correct.
	 */
	public function validateValue($value);

}
