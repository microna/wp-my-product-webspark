/**
 * WP My Product Webspark - Main JavaScript File
 */
jQuery(document).ready(function ($) {
  var mediaUploader;

  $("#upload_image_button").on("click", function (e) {
    e.preventDefault();
    if (mediaUploader) {
      mediaUploader.open();
      return;
    }

    mediaUploader = wp.media({
      title: "Select or Upload Product Image",
      button: {
        text: "Use this image",
      },
      library: {
        type: "image",
      },
      multiple: false,
    });

    mediaUploader.on("select", function () {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      $("#product_image_id").val(attachment.id);
      $("#product_image_preview").attr("src", attachment.url).show();
      $("#remove_image_button").show();
    });
    mediaUploader.open();
  });

  $("#remove_image_button").on("click", function (e) {
    e.preventDefault();
    $("#product_image_id").val("");
    $("#product_image_preview").attr("src", "").hide();
    $(this).hide();
  });

  $(".delete-product-button").on("click", function (e) {
    e.preventDefault();

    var productId = $(this).data("product-id");
    var productName = $(this).data("product-name");

    if (
      confirm(
        'Are you sure you want to delete the product "' +
          productName +
          '"? This action cannot be undone.'
      )
    ) {
      $.ajax({
        type: "POST",
        url: wpmyprodData.ajaxUrl,
        data: {
          action: "wpmyprod_delete_product",
          security: wpmyprodData.security,
          product_id: productId,
        },
        success: function (response) {
          if (response.success) {
            window.location.reload();
          } else {
            alert(response.data.message || "Error deleting product");
          }
        },
        error: function () {
          alert("An error occurred. Please try again.");
        },
      });
    }
  });

  $("#wpmyprod-add-product-form").on("submit", function (e) {
    var productName = $("#product_name").val();
    var productPrice = $("#product_price").val();
    var productQuantity = $("#product_quantity").val();

    var isValid = true;

    $(".wpmyprod-error").remove();

    if (!productName) {
      $("#product_name").after(
        '<span class="wpmyprod-error">Please enter a product name</span>'
      );
      isValid = false;
    }

    if (!productPrice || isNaN(productPrice) || productPrice <= 0) {
      $("#product_price").after(
        '<span class="wpmyprod-error">Please enter a valid price</span>'
      );
      isValid = false;
    }

    if (!productQuantity || isNaN(productQuantity) || productQuantity < 0) {
      $("#product_quantity").after(
        '<span class="wpmyprod-error">Please enter a valid quantity</span>'
      );
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
    }
  });
});
