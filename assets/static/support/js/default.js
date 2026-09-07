var defaultPage = (function() {
    'use strict';

    var defaultPage = {
        init: function() {
            /*
             * Video carousel
             */
            var videoBlock = $('.videot-panels');
            if (videoBlock.length > 0) {
                videoBlock.videoCarousel();
            }
        }
    };

    return defaultPage;

}());

$(window).load(function() {
    'use strict';
    defaultPage.init();
});
