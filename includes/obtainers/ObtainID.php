<?php

/**
 * @file
 * Class ObtainID
 */

/**
 * {@inheritdoc}
 */
class ObtainID extends ObtainHtml {

  /**
   * Method for returning the table cell at 3rd row, 1st column.
   *
   * @return text
   *   The string found.
   */
  protected function pluckTable3y1x() {
    $table = $this->queryPath->find("table");
    $text = $this->pluckFromTable($table, 3, 1);

    return $text;
  }

  // ***************** Helpers ***********************************************.

  /**
   * Cleans $text and returns it.
   *
   * @param string $text
   *   Text to clean and return.
   *
   * @return string
   *   The cleaned text.
   */
  public static function cleanString($text) {
    // There are also numeric html special chars, let's change those.
    module_load_include('inc', 'migration_tools', 'includes/migration_tools');
    $text = strongcleanup::decodehtmlentitynumeric($text);

    // We want out titles to be only digits and ascii chars so we can produce
    // clean aliases.
    $text = StringCleanUp::convertNonASCIItoASCII($text);

    // Checking again in case another process rendered it non UTF-8.
    $is_utf8 = mb_check_encoding($text, 'UTF-8');

    if (!$is_utf8) {
      $text = StringCleanUp::fixEncoding($text);
    }

    // Remove some strings that often accompany dates.
    $remove = array(
      'id:',
      'ID',
    );
    // Replace these with nothing.
    $text = str_ireplace($remove, '', $text);
    $remove = array(
      "\n",
    );
    // Replace these with spaces.
    $text = str_ireplace($remove, ' ', $text);
    // Remove multiple spaces.
    $text = preg_replace('/\s{2,}/u', ' ', $text);

    // Remove white space-like things from the ends and decodes html entities.
    $text = StringCleanUp::superTrim($text);

    return $text;
  }


  /**
   * Evaluates $string and if it checks out, returns TRUE.
   *
   * @param string $string
   *   The string to validate.
   *
   * @return bool
   *   TRUE if possibleText can be used as an id.  FALSE if it cant.
   */
  protected function validateString($string) {
    // Run through any evaluations.  If it makes it to the end, it is good.
    // Case race, first to evaluate TRUE aborts the text.
    switch (TRUE) {
      // List any cases below that would cause it to fail validation.
      case empty($string):
      case is_object($string):
      case is_array($string):
        // Too long or too short it is likely not an id.
      case (strlen($string) > 10):
      case (strlen($string) < 2):
        // Should contain a - in middle.
      case (count(explode('-', $string)) != 2):
        // Consider adding a regex to look for ##-## pattern.
        return FALSE;

      // It made it this far, it should be valid.
      default:
        return TRUE;
    }
  }
}
