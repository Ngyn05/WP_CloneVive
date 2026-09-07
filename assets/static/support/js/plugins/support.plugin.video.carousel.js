/* eslint-disable */
(function ($) {
  'use strict';
  $.fn.videoCarousel = function (options) {
    // Establish our default settings
    var Carousel = (function () {
      var Carousel = {
        setupVideoThumbnail: function () {
          var $defaultThumbnail = $('.default-thumbnail');
          $defaultThumbnail.each(function (index, el) {
            var vid = $(this).parent().find('.vidplayer').data('vid');
            var imgpath = $(this).find('img').attr('src');
            var alt = $(this).find('img').attr('alt');
            /* Only loas the first one */
            if (index === 0) {
              $(this).videobimg({
                id: vid,
                defaultImagePath: imgpath,
                alt: alt,
              });
            }
          });
        },
        setupVideoPlay: function () {
          var $playAreaContainer = $('.panel-main');
          $playAreaContainer.click(function (event) {
            var $currentVideoPlayer = $(this).find('.vidplayer'),
              $currentPlayIcon = $(this).find('.big-play-icon'),
              $currentDefaultThumbnail = $(this).find('.default-thumbnail'),
              vid = $currentVideoPlayer.data('vid');

            $currentVideoPlayer.videobject({
              id: vid,
              width: $('.videot-panels').width(),
              height: $('.videot-panels').height()
            });

            $currentPlayIcon.hide();
            $currentDefaultThumbnail.hide();
          });
        },
        getFirstVideoArticle: function () {
          var $playerPanel = $('.video-panel-block');
          Carousel.getVideoRelatedArticle($playerPanel.eq(0));
        },
        getVideoRelatedArticle: function (item) {
          var pname = item.data('p-name'),
            tags = item.data('tags');
        },
        setupVideoCarousel: function () {
          function setupNavigationClick() {

            var totalLength = $('.item-outer').length;
            var totalPages = Math.ceil(totalLength / 4);
            var handledItems = 0;

            for (var i = 0; i < totalPages; i++) {
              $('<div></div>').addClass('video-nav-page').appendTo('.section-support-video .video-navigation-carousel');
            }

            $('.item-outer').each(function () {
              var navPage = Math.floor(handledItems / 4);

              $(this).on('click', function (event) {
                event.preventDefault();
                var $playerPanel = $('.video-panel-block');
                // var videoIndex = navPage * 4 + $(this).index()
                var videoIndex = $(this).data('slick-index');

                $('.item-outer').removeClass('selected');
                $(this).addClass('selected');

                $playerPanel.find('.vidplayer').html('');
                $playerPanel.addClass('inactive').removeClass('active');
                $playerPanel.eq(videoIndex).removeClass('inactive').addClass('active');
                $playerPanel.eq(videoIndex).find('.panel-main').click();
              });

              $(this).appendTo('.video-nav-page:eq(' + navPage + ')');
              handledItems++;
            });
          }

          var rtlDirection = false;
          rtlDirection = $('html').attr('dir') === 'rtl';

          setupNavigationClick();

          $('.video-navigation-carousel').slick({
            rtl: rtlDirection,
            infinite: false,
            dots: false,
            speed: 300,
            slidesToShow: 3,
            slidesToScroll: 3,
            responsive: [
              {
                breakpoint: 767,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 2
                },
              },
            ],
          });

        }
      };

      return Carousel;

    }());
    return this.each(function () {
      // We'll get back to this in a moment
      Carousel.setupVideoCarousel();
      Carousel.getFirstVideoArticle();
      Carousel.setupVideoThumbnail();
      Carousel.setupVideoPlay();
    });

  };

}(jQuery));
