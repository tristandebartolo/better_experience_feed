<?php

namespace Drupal\better_experience_feed\Utility;

/**
 * Provides generic array sorting helper methods.
 *
 * @ingroup utility
 */
class SortBetterExperienceFeed {

  /**
   * Sorts a structured array by 'date' key (no # prefix).
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative arrays
   *   that optionally include a 'date' key.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortByDateElement($a, $b) {
    // ksm($a);
    return static::sortByKeyDate($a, $b, 'date');
  }

	/**
   * Sorts list item by date.
   *
   * @param array $a
   *   First item for comparison.
   * @param array $b
   *   Second item for comparison.
   * @param string $key
   *   The key to use in the comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortByKeyDate($a, $b, $key) {
    $a_weight = is_array($a) && isset($a[$key]) ? $a[$key] : 0;
    $b_weight = is_array($b) && isset($b[$key]) ? $b[$key] : 0;
    if ($a_weight == $b_weight) {
      return 0;
    }
    return $a_weight < $b_weight ? -1 : 1;
  }

    /**
   * Sorts a structured array by 'title' key (no # prefix).
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative arrays
   *   that optionally include a 'title' key.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortByTitleElement($a, $b) {
    // ksm($a);
    return static::sortByKeyTitle($a, $b, 'title');
  }

	/**
   * Sorts list item by title.
   *
   * @param array $a
   *   First item for comparison.
   * @param array $b
   *   Second item for comparison.
   * @param string $key
   *   The key to use in the comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortByKeyTitle($a, $b, $key) {
    $a_title = is_array($a) && isset($a[$key]) ? $a[$key] : '';
    $b_title = is_array($b) && isset($b[$key]) ? $b[$key] : '';
    return strnatcasecmp($a_title, $b_title);
  }

}