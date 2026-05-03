<?php

/**
 * Check whether a value is blank.
 *
 * @param mixed $value - value to check
 * 
 * @return bool true if the value is blank
 */
function is_blank($value)
{
  return !isset($value) || trim($value) === '';
}

/**
 * Check whether a value has presence.
 *
 * @param mixed $value - value to check
 * 
 * @return bool true if the value is present
 */
function has_presence($value)
{
  return !is_blank($value);
}

/**
 * Check whether a string is longer than a minimum length.
 *
 * @param string $value - string to check
 * @param int $min - minimum length
 * 
 * @return bool true if the string is longer than the minimum
 */
function has_length_greater_than($value, $min)
{
  $length = strlen($value);
  return $length > $min;
}

/**
 * Check whether a string is shorter than a maximum length.
 *
 * @param string $value - string to check
 * @param int $max - maximum length
 * 
 * @return bool true if the string is shorter than the maximum
 */
function has_length_less_than($value, $max)
{
  $length = strlen($value);
  return $length < $max;
}

/**
 * Check whether a string has an exact length.
 *
 * @param string $value - string to check
 * @param int $exact - exact length
 * 
 * @return bool true if the string has the exact length
 */
function has_length_exactly($value, $exact)
{
  $length = strlen($value);
  return $length == $exact;
}

/**
 * Check whether a string matches length requirements.
 *
 * @param string $value - string to check
 * @param array $options - length options to check
 * 
 * @return bool true if the string matches the length requirements
 */
function has_length($value, $options)
{
  if (isset($options['min']) && !has_length_greater_than($value, $options['min'] - 1)) {
    return false;
  } elseif (isset($options['max']) && !has_length_less_than($value, $options['max'] + 1)) {
    return false;
  } elseif (isset($options['exact']) && !has_length_exactly($value, $options['exact'])) {
    return false;
  } else {
    return true;
  }
}

/**
 * Check whether a value is included in a set.
 *
 * @param mixed $value - value to check
 * @param array $set - set of allowed values
 * 
 * @return bool true if the value is included
 */
function has_inclusion_of($value, $set)
{
  return in_array($value, $set);
}

/**
 * Check whether a value is excluded from a set.
 *
 * @param mixed $value - value to check
 * @param array $set - set of disallowed values
 * 
 * @return bool true if the value is excluded
 */
function has_exclusion_of($value, $set)
{
  return !in_array($value, $set);
}

/**
 * Check whether a string contains a required string.
 *
 * @param string $value - string to check
 * @param string $required_string - required string to find
 * 
 * @return bool true if the required string is found
 */
function has_string($value, $required_string)
{
  return strpos($value, $required_string) !== false;
}

/**
 * Check whether a value has a valid email format.
 *
 * @param string $value - email address to check
 * 
 * @return bool true if the email format is valid
 */
function has_valid_email_format($value)
{
  $email_regex = '/\A[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\Z/i';
  return preg_match($email_regex, $value) === 1;
}

/**
 * Check whether a username is unique.
 *
 * @param string $username - username to check
 * @param string $current_id - current member ID to exclude
 * 
 * @return bool true if the username is unique
 */
function has_unique_username($username, $current_id = "0")
{
  $member = Member::find_by_username($username);
  if ($member === false || $member->id == $current_id) {
    // is unique
    return true;
  } else {
    // not unique
    return false;
  }
}
