;
/**
 * Repeatable Meta Group Plugin
 * @author Hasin Hayder (http://hasin.me)
 * @version 1.0
 * @licence GPL
 */
(function ($) {
    "use strict";
    $(document).ready(function () {
        $(".rmg").parents(".postbox").addClass("rmgcontainer");

        hideMetaBoxes();
        showDefaultMetaboxesWithDeps();
        $("#page_template").on("change", function () {
            var pt = $(this).val();
            $(".rmg").each(function () {
                var deps = $(this).data("page-templates");
                hideMetaBoxes();
                if (deps != "" && deps.indexOf(pt) != -1) {
                    $(this).parents(".postbox").show();
                }
            });
        });
    });


    $(".rmg .rmg-addmore").on("click", function (ev) {
        var e = $(this).parents(".rmg").find(".rmg-rb:eq(0)").clone();
        $(e).find("input, textarea, select").attr("id", "").val("");

        $(e).find(".galgalremove").remove();
        $(e).find(".gallery-ph").html("");
        $(e).find(".galgal").val("Add Images to Gallery");

        $(e).insertAfter($(this).parents(".rmg").find(".rmg-rb:last"));
        $(".rmg .rmg-rb").css("border-bottom", "1px solid #eee");
        $(".rmg").find(".rmg-rb:last").css("border", "none");
        $(this).parents(".rmg").find(".rmg-del").show();


        ev.preventDefault();
    });

    $('.rmg').on("click", ".rmg-del", function (e) {
        if ($(this).parents(".rmg").find(".rmg-rb").length > 1) {
            $(this).parents(".rmg-rb").detach();
            $(".rmg").find(".rmg-rb:last").css("border", "none");
        } else {
            $(this).hide();
            $(this).parents(".rmg-rb").find("input, textarea, select").attr("id", "").val("");
        }

        e.preventDefault();

    });
    if ($(".rmg-color").length > 0)
        $(".rmg-color").farbtastic();


    $(".rmg").find(".rmg-rb:last").css("border", "none");


    /**
     * Visibiity Options
     */
    function hideMetaBoxes() {
        $(".rmg").each(function () {
            if ($(this).data("page-templates") != "") {
                $(this).parents(".postbox").hide();
            }
        });
    }

    function showDefaultMetaboxesWithDeps() {
        var pt = $("#page_template").val();
        $(".rmg").each(function () {
            var deps = $(this).data("page-templates");
            if (deps != "" && deps.indexOf(pt) != -1) {
                $(this).parents(".postbox").show();
            }
        });
    }


})(jQuery);