/* eslint-disable */

function showCommentBox() {
    $(".feedback-block").hide();
    $(".comment-box").addClass('show');
}

function addThumbUp(action) {
    var location = window.location.href.replace(".html", "").split("/");
    var articleId = location[location.length - 1];
    var product = location[location.length - 3];
    var siteId = window.location.pathname.split("/")[1];
    if (action == "yes") {
        $('.thank-you-string').show();
        $('.feedback-block').hide();
        $.ajax({
            url: "/rest/support/" + siteId + "/thumbup/",
            type: "post",
            contentType: "application/json",
            success: function (result) {
                // console.log(result);
            },
            data: JSON.stringify({"article_id": articleId, "product": product})
        });
    } else if (action == "comment") {
        $('.comment-box').hide();
        $('.thank-you-string').show();
        var msg = $("#comment_msg").val();
        $.ajax({
            url: "https://obfs94zt33.execute-api.us-west-2.amazonaws.com/rest/support/" + siteId + "/comment/",
            type: "post",
            contentType: "application/json",
            success: function (result) {
                // console.log(result);
            },
            data: JSON.stringify({"article_id": articleId, "comment": msg, "product": product, "host":VHOST})
        });
    }

}

var subMenuModule = (function () {
    return {
        active: function () {
            $('.support-menu a.nav-link').each(function (index, element) {
                var currentItem = $(this);
                var itemUrl = currentItem.attr('href');
                if (document.location.href.indexOf(itemUrl) > -1) {
                    currentItem.parents('.accordion').addClass('active');
                    currentItem.addClass('active');
                    return false;
                }
            });
            $('.support-menu').addClass('show');
        },
    };
}());

$(document).ready(function () {
    subMenuModule.active();
    // if ($(".top-article-title-block").length > 0) {
    //     var topictitle1 = $(".topictitle1").text();
    //     $(".topictitle1").remove();
    //     $(".top-article-title-block").text(topictitle1);
    // }
    if ($('a.active').length > 0) {
        var subheader = $('li.active').children('div.child-menu__accordion--header').html();
        if (subheader !== undefined) {
            $('.support-second-category').after('<li class="arrow-block"> > </li><li> <a href="' + $('.support-second-category a').attr('href') + '#' + subheader + '">' + subheader + ' </a></li>');
        }
    }
    if ($(window).width() >= 1024) {
        if ($('.ullinks .ullinks').length > 0) {
            $('.related-links').css({
                "-moz-column-count": "2",
                "-moz-column-gap": "10px",
                "-webkit-column-count": "2",
                "-webkit-column-gap": "10px",
                "column-count": "2",
                "column-gap": "10px",
                "width": "100%",
                "margin": "0 auto",
                // "height": h + "px"
            });

            // $('.related-links').find("ul").eq(0).css({

            //     "height": h + "px"
            // });

        } else {
            $('.ullinks').css({
                "-moz-column-count": "2",
                "-moz-column-gap": "10px",
                "-webkit-column-count": "2",
                "-webkit-column-gap": "10px",
                "column-count": "2",
                "column-gap": "10px",
                "width": "100%",
                "margin": "0 auto",
                // "height": h + "px"
            });
        }

        // if ($(".support-content").find(".ullinks").length == 0) {
        //     $(".comment_panel").show();

        //     $.post("../thumb_up.aspx", {
        //         sid: $("#sid").text(),
        //         pid: $("#pid").text(),
        //         aid: $("#aid").text()
        //     }, function(data) {
        //         console.log(data);

        //     });
        // }
    }
    $(window).resize(function () {

        if ($(window).width() >= 1024) {
            if ($('.ullinks .ullinks').length > 0) {
                $('.related-links').css({
                    "-moz-column-count": "2",
                    "-moz-column-gap": "10px",
                    "-webkit-column-count": "2",
                    "-webkit-column-gap": "10px",
                    "column-count": "2",
                    "column-gap": "10px",
                    "width": "100%",
                    "margin": "0 auto"
                });
            } else {
                $('.ullinks').css({
                    "-moz-column-count": "2",
                    "-moz-column-gap": "10px",
                    "-webkit-column-count": "2",
                    "-webkit-column-gap": "10px",
                    "column-count": "2",
                    "column-gap": "10px",
                    "width": "100%",
                    "margin": "0 auto"
                });
            }
        } else {
            if ($('.ullinks .ullinks').length > 0) {
                $('.related-links').css({
                    "-moz-column-count": "1",
                    "-moz-column-gap": "10px",
                    "-webkit-column-count": "1",
                    "-webkit-column-gap": "10px",
                    "column-count": "1",
                    "column-gap": "10px",
                    "width": "100%",
                    "margin": "0 auto"
                });
            } else {
                $('.ullinks').css({
                    "-moz-column-count": "1",
                    "-moz-column-gap": "10px",
                    "-webkit-column-count": "1",
                    "-webkit-column-gap": "10px",
                    "column-count": "1",
                    "column-gap": "10px",
                    "width": "100%",
                    "margin": "0 auto"
                });
            }
        }
    });

    $('.related-links').show();

});

