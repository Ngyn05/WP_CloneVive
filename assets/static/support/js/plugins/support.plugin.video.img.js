(function($) {
    $.fn.videobimg = function(callerSettings) {
        var $this = this;
        var settings = $.extend({
            defaultImagePath: '/ek-assets/responsive/img/main_thumbnail.png',
            returnImg: '<img src="{0}" alt="{1}">',
            id: '',
            alt: '',
            type: 'ytb' //or yk
        }, callerSettings || {});


        var getImage = function(uri) {
            if (uri.length > 0) {
                var callPath, imgPath = '';
                var yk = 'http://v.youku.com/player/getPlayList/VideoIDS/myid';
                //var yk = 'http://static.youku.com/v/swf/qplayer.swf?VideoIDS=myid=&isAutoPlay=true&isShowRelatedVideo=false&embedid=-&showAd=0';
                var ytb = 'http' + (/^https/.test(location.protocol) ? 's' : '') + '://gdata.youtube.com/feeds/api/videos/myid?v=3&alt=jsonc&callback=?';
                // youku or youtube
                if (uri.length == 11) {
                    settings.type = "ytb";
                    callPath = ytb.replace(/myid/, uri);
                } //youtube
                else {
                    settings.type = "yk";
                    callPath = yk.replace(/myid/, uri);
                }//youku

                //call JSON
                var jqxhr;
                if (settings.type == 'ytb') {
                    //jqxhr = $.getJSON(callPath).done(function(data) {
                        //imgPath = data.data.thumbnail.hqDefault;
                    //}).fail(function() {
                        //imgPath = '';
                    //});
                    imgPath = 'https://i.ytimg.com/vi/' + settings.id + '/hqdefault.jpg';
                } else {
                    //var whatEverOrigin = 'http://whateverorigin.org/get?url=' + encodeURIComponent(callPath) + '&callback=?';
                    // jqxhr = $.getJSON(whatEverOrigin).done(function(data) {
                    //     var toJson = $.parseJSON(data.contents);
                    //     //imgPath = toJson.data[0].logo;
                    //     imgPath = '';
                        
                    // }).fail(function() {
                    //     imgPath = '';
                    // });
                    //imgPath = 'http://events.youku.com/global/api/video-thumb.php?vid=' + encodeURIComponent(uri);
                    imgPath = '';
                }

                //jqxhr.complete(function() {
                    if (imgPath === '') {
                        imgPath = settings.defaultImagePath;
                    }
                    return $this.empty().html( settings.returnImg.replace('{0}', imgPath).replace('{1}', settings.alt) );
                //});
            }
        };

        getImage(settings.id);
    };
})(jQuery);