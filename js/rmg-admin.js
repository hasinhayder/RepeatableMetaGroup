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

        hideMetaBoxesForPageTemplates();
        showDefaultMetaboxesWithDeps();

        hideMetaboxesForPostFormats();
        showDefaultMetaboxesForPostFormats();

        $("#page_template").on("change", function () {
            var pt = $(this).val();
            $(".rmg").each(function () {
                var deps = $(this).data("page-templates");
                hideMetaBoxesForPageTemplates();
                if (deps != "" && deps.indexOf(pt) != -1) {
                    $(this).parents(".postbox").show();
                }
            });
        });

        $("#post-formats-select").on("click", "input", function () {
            var pf = $(this).val();
            $(".rmg").each(function () {
                var deps = $(this).data("post-formats");
                hideMetaboxesForPostFormats();
                if (deps != "" && deps.indexOf(pf) != -1) {
                    $(this).parents(".postbox").show();
                }
            });
        });

        if($.fn.farbtastic){
            if ($(".rmg-color").length > 0)
                $(".rmg-color").farbtastic();
        }

        $(".rmg").on("click",".data-fieldtype-checkbox",function(e){
            if($(this).prop("checked")){
                var val = $(this).val();
                var id = $(this).attr("id");
                $("#"+id.replace('c___',"")).val(val);
            }else{
                var id = $(this).attr("id");
                $("#"+id.replace('c___',"")).val(0);
            }
        });

        $(".data-fieldtype-checkbox").each(function(){
            var id = $(this).attr("id");
            var val = $("#"+id.replace('c___',"")).val();
            if(val>0){
                $(this).attr("checked","checked");
            }
        })

    });


    $(".rmg .rmg-addmore").on("click", function (ev) {
        var e = $(this).parents(".rmg").find(".rmg-rb:last").clone();
        $(e).find("input, textarea, select").each(function(){
            var id = $(this).attr("id");
            if(id) {
                var parts = id.split("---");
                var counter = parseInt(parts[1]) + 1;
                var newid = parts[0] + "---" + counter;
                $(this).attr("id", newid);

                if($(this).attr("type")!="checkbox" && $(this).attr("type")!="submit"){
                    $(this).val("");
                }

                if($(this).attr("type")=="checkbox"){
                    $(this).prop("checked",false);
                }
            }

            // radio fix
            $(e).find(".data-fieldtype-hidden").val("");
            $(e).find(".data-fieldtype-radio").each(function(){
                    var counter = $(this).data("counter");
                    var name = $(this).prop("name");
                    var parts = name.split("---");
                    var newname = parts[0]+"---"+(parseInt(counter)+1)+"[]";
                    $(this).prop("name",newname);
            });

        });

        $(e).find(".galgalremove").remove();
        $(e).find(".gallery-ph").html("");
        $(e).find(".galgal").val("Add Images to Gallery");

        $(e).insertAfter($(this).parents(".rmg").find(".rmg-rb:last"));
        $(this).parents(".rmg").find(".rmg-del").show();

        borderFix();


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


    $(".rmg").on("click", ".rmg-up", function (e) {
        var prnt = $(this).parents(".rmg-rb");
        var prev = $(prnt).prev();
        if(prev.hasClass("rmg-rb")){
            if (prev.length == 1) {
                var me = $(prnt).detach();
                $(me).insertBefore(prev);
            }
        }
        borderFix();
        e.preventDefault();
    });

    $(".rmg").on("click", ".rmg-down", function (e) {
        var prnt = $(this).parents(".rmg-rb");
        var next = $(prnt).next();
        if(next.hasClass("rmg-rb")){
            if (next.length == 1) {
                var me = $(prnt).detach();
                $(me).insertAfter(next);
            }
        }
        borderFix();
        e.preventDefault();
    });




    $(".rmg").find(".rmg-rb:last").css("border", "none");

    $(".rmg").on("click",".data-fieldtype-radio",function(){
        console.log("u");
        $(this).nextAll(".data-fieldtype-hidden:first").val($(this).val());
    });

    function borderFix(){
        $(".rmg .rmg-rb").css("border-bottom", "1px solid #eee");
        $(".rmg").find(".rmg-rb:last").css("border", "none");
    }


    /**
     * Visibiity Options
     */
    function hideMetaBoxesForPageTemplates() {
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

    function hideMetaboxesForPostFormats() {
        $(".rmg").each(function () {
            if ($(this).data("post-formats") != "") {
                $(this).parents(".postbox").hide();
            }
        });
    }

    function showDefaultMetaboxesForPostFormats() {
        var pf = $("#post-formats-select input:checked").val();
        $(".rmg").each(function () {
            var deps = $(this).data("post-formats");
            if (deps != "" && deps.indexOf(pf) != -1) {
                $(this).parents(".postbox").show();
            }
        });
    }


})(jQuery);