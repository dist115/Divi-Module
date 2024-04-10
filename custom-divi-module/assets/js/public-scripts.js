jQuery(function ($) {
  /**
   * Checks if there is more content to load and sends an AJAX request to load it.
   *
   * @return {void} This function does not return a value.
   */
  function ccdmCheckForMoreContent() {
    var button = $(".cpm_load_more"),
      // Retrieve data attributes from the button
      queryArgs = button.data("args"),
      maxPage = button.data("max-page"),
      currentPage = button.data("current-page");

    showImage = button.data("showimage");
    showExc = button.data("showexc");
    exclenth = button.data("exclenth");
    showMeta = button.data("showmeta");
    showReadMore = button.data("showreadmore");
    showbookbtn = button.data("showbookbtn");

    layout = button.data("layout");
    gridCount = button.data("gridnum");
    taxonomy = button.data("taxonomy");


    // Check if an AJAX request is already in progress to prevent multiple requests
    if (button.data("loading")) {
      return; // Exit the function if a request is already in progress
    }

    // Check if the button is in the viewport
    if (
      button.length > 0 &&
      $(window).scrollTop() + $(window).height() >= button.offset().top
    ) {
      // Set the loading flag to true
      button.data("loading", true);

      $.ajax({
        url: cpmAjax.ajax_url, // AJAX handler
        data: {
          action: "ccdmLazyLoader", // the parameter for admin-ajax.php
          query: queryArgs, // Arguments for the query
          page: currentPage, // Current page to load
          layout: layout,
          gridCount: gridCount,
          showImage: showImage,
          showExc: showExc,
          exclenth: exclenth,
          showMeta: showMeta,
          showReadMore: showReadMore,
          showbookbtn: showbookbtn,
          taxonomy: taxonomy,
        },
        type: "POST",
        beforeSend: function (xhr) {
          // Change the button text to indicate loading
          // button.text("Loading..."); // some type of preloader
        },
        success: function (data) {
          setTimeout(function () {
            $("._slider-1").not('.slick-initialized').slick({
              infinite: true,
              dots: true,
              slidesToShow: 1,
              slidesToScroll: 1,
            });

          }, 500);
          // Increment the current page number
          currentPage++;
          // Insert the loaded content before the button and update its data attribute
          button
            .data("current-page", currentPage)
            .before(data);
          // Remove the button if all pages have been loaded

          if (currentPage == maxPage) {
            button.remove();
          }

        },
        complete: function () {
          // Reset the loading flag when the request is complete
          button.data("loading", false);
        },
      });
    }
  }

  /*
   * Attach scroll event to the window
   */
  $(window).on("scroll", ccdmCheckForMoreContent);
});


