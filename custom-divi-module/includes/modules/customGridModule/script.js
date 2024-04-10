jQuery(document).ready(function ($) {
    
    $(document).ready(function () {
        $("._slider-1").slick({
            infinite: true,
            dots: true,
            slidesToShow: 1,
            slidesToScroll: 1,
        });
    });

    $("._carousel").slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: false,
        dots: true,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                },
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                },
            },
        ],
    });


    $("#ar-tabs-nav li:first-child").addClass("active");
    $(".ar-tab-content").hide();
    $(".ar-tab-content:first").show();

    // Click function
    $("#ar-tabs-nav li").click(function () {
        $("#ar-tabs-nav li").removeClass("active");
        $(this).addClass("active");
        $(".ar-tab-content").hide();

        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
        return false;
    });
});


$(document).ready(function () {
    // Add an event listener to the checkbox for the 'change' event
    $('input[type="radio"]').change(function () {
        // Toggle the 'active' class on the associated label based on the checkbox state
        $("label[for=myCheckbox]").toggleClass("active", this.checked);
    });
});

