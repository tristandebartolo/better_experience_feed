/**
* Little Set Up to design BetterExperienceFeed Setting Block form.
* See the following change record for more information,
**/
(function ($, Drupal, drupalSettings) {
  /**
  * Drupal.behaviors.BetterExperiencefeed attached to form
  */
  Drupal.behaviors.BetterExperiencefeed = {
    attach: function(context, settings) {
      // Trigger the behavior to prevent duplicate content
      $("#block-form", context).once('SetupAppPage').each(this.SetupAppPage);

    },
    /**
    * Set up App
    */
    SetupAppPage: function (idx, column) {
      // Prepare all target
      var type_of_request = $(".better-experience-type-of-request select[id^='edit-settings-type-of-request']"),
      url_of_feed_summary = $(".better-experience-url-options details#edit-settings-url-options"),
      type_of_content = $('.better-experience-type-of-content');
      // If type of request change
      type_of_request.change(function() {
        // If type of request is 0 = article // 1 flux url / 2 Mix
        if (type_of_request.val() == 0) {
          url_of_feed_summary.addClass('better-experience-class-hyde');
          type_of_content.removeClass('better-experience-class-hyde');
        } else if (type_of_request.val() == 1) {
          url_of_feed_summary.removeClass('better-experience-class-hyde');
          type_of_content.addClass('better-experience-class-hyde');
        } else {
          url_of_feed_summary.removeClass('better-experience-class-hyde');
          type_of_content.removeClass('better-experience-class-hyde');
        }
      });
      // Trigger once for first display
      $(type_of_request).trigger('change');

    },
  };
})(jQuery, Drupal, drupalSettings);
  