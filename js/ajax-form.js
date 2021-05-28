(function ($, Drupal) {
  Drupal.behaviors.ajaxForm = {
    attach: function (context, settings) {
      $('.cancel-delete-product', context).click(function (event) {
        event.preventDefault();
        var id = $(this).attr('id').replace("hide-delete-dialog-", "");
        $('#delete-dialog-container-' + id).addClass('visually-hidden');
        $('input[name="' + id + '"].show-delete-dialog').fadeIn();
      });
      $('.show-delete-dialog', context).click(function (event) {
        event.preventDefault();
        $(this).fadeOut(function () {
          $('#delete-dialog-container-' + $(this).attr('id')).removeClass('visually-hidden');
        });
      });
    }
  }
})(jQuery, Drupal);
