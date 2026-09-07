(function($) {
    $.fn.videobject = function(callerSettings) {
        var settings = $.extend({
            //flash: '<object width="mywidth" height="myheight" type="application/x-shockwave-flash" data="myurl" id="youku_player" style="visibility: visible;"><param name="quality" value="high"><param name="wmode" value="opaque"><param name="allowScriptAccess" value="always"><param name="SCALE" value="exactfit"><param name="flashvars" value="playMovie=true&isAutoPlay=true"></object>',
            flash: '<iframe width="mywidth" height="myheight" src="myurl" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in- picture" allowfullscreen></iframe>',
            id: 'XMTcwMDMxNTA4',
            url: 'youtube',
            width: '500',
            height: '282',
            warning: 'You need Flash Player enabled.'
        }, callerSettings || {});
        var checkFlash = function() {
            //checks if flash is installed/enabled on the browser
            var hasFlash = false;
            try {
                var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
                if (fo) hasFlash = true;
            } catch (e) {
                if (navigator.mimeTypes["application/x-shockwave-flash"] !== undefined) hasFlash = true;
            }
            return hasFlash;
        };

        var showvideo = function (uri) {

            var okFlash = true,
                yk,
                ytb = 'http' + (/^https/.test(location.protocol) ? 's' : '') + '://www.youtube.com/embed/myid?autoplay=1&wmode=opaque';

                if ($('html').hasClass('desktop')) {
                    okFlash = checkFlash();
                    yk = 'https://static.youku.com/v/swf/qplayer.swf?VideoIDS=myid=&isAutoPlay=true&isShowRelatedVideo=false&embedid=-&showAd=0';
                } else {
                    yk = 'https://player.youku.com/embed/myid';
                }

            if (okFlash) {
                // youku or youtube
                if (uri.length == 11) {

                    settings.id = ytb.replace(/myid/, uri);
                } //youtube
                else {
                    settings.id = yk.replace(/myid/, uri);
                }//youku
                return uri == 'youku' ? settings.flash.replace(/myurl/, settings.id).replace(/mywidth/, settings.width).replace(/myheight/, settings.height) : settings.flash.replace(/myurl/, settings.id).replace(/mywidth/, settings.width).replace(/myheight/, settings.height);
            } else {
                return settings.warning;
            }
        };

        return this.empty().html(showvideo(settings.id));
    };
})(jQuery);
