//var devBaseSearchURL = "http://dev-ws.htc.com/htc-search-1.1/query/gsa/search?ie=utf8&oe=utf8&getfields=*&site=default_collection&client=products-us";
var devBaseSearchURL = "//ws.htc.com/htc-search-1.1/query/gsa/search?ie=utf8&oe=utf8&getfields=*&site=default_collection&client=vive-faq-all";
var isLoadClick = false, count = 0;
//get parameter from url



//new search api
var n_count = 0, load_more = true;;
function getSearchData() {

    if (!load_more) return;
    load_more = false;
    var str_res = location.search.substr(1).split("=");
    var str_search = decodeURI(decodeURIComponent(str_res[1].replace(/\+/g, '%20')));
    var current_site = $('body').data('site');
    var words = str_search.split("%20");
    var optionalWords = JSON.stringify(words).replace('"','\"');
    
    $.ajax({
        type: "POST",
        beforeSend: function (request) {
            request.setRequestHeader("X-Algolia-API-Key", "6dfc6709a0cea8e628187b9d7991f32c");
            request.setRequestHeader("X-Algolia-Application-Id", "DAKWH4P7PR");

        },
        url: "https://DAKWH4P7PR-dsn.algolia.net/1/indexes/vive_support_" + current_site + "_Prod/query",
        data: "{ \"optionalWords\":" + optionalWords + ",\"params\": \"query=" + encodeURIComponent(str_search) + "&filters=NOT%20content%3Aunknown&hitsPerPage=10&getRankingInfo=1&page=" + n_count + "\" }",
        processData: false,
        success: function (msg) {

            var resultstr = $(".results-count b").text();
            if (resultstr.indexOf("{0}") >= 0) {
                $(".results-count b").text(resultstr.replace("{0}", msg["nbHits"]));
                $('title').html(resultstr.replace("{0}", msg["nbHits"]));

            }
            $(".results-count b").show();
            load_more = true;
            n_count++;
            //console.log(msg["nbHits"]);
          
            var u_str = str_search.split(" ");
           
            for (var i in msg["hits"]) {
                //console.log(msg["hits"][i]);

                for (var j in u_str) {
                    var u_str_search = u_str[j];
                    if (u_str_search.length == 0) continue;
             
                    var o_str_s = msg["hits"][i]["content"].match(new RegExp(u_str_search, 'gi'));
                    if (!o_str_s) {
                        o_str_s = msg["hits"][i]["hierarchy"]["lvl0"].match(new RegExp(u_str_search, 'gi'));
                    }
                    for (var k in o_str_s) {
                         
                        if (k > 0) continue;
                        var re = new RegExp(o_str_s[k],"gi");
                        msg["hits"][i]["hierarchy"]["lvl0"] = msg["hits"][i]["hierarchy"]["lvl0"].replace(re, "<b>" + o_str_s[k] + "</b>");
                        //msg["hits"][i]["content"] = msg["hits"][i]["content"].substring(0, 86);
                        msg["hits"][i]["content"] = msg["hits"][i]["content"].replace(re, "<b>" + o_str_s[k] + "</b>");
                        
                    }

                }
                var r = '<div class="list-item-block"><a href="' + msg["hits"][i]["url"] + '">' + msg["hits"][i]["hierarchy"]["lvl0"] + '</a><p class="search_result_parahraph">' + msg["hits"][i]["content"] + '</p></div>';
                $('.search-page-container .result_display').append(r);
            }
              
            $('.loader').hide();
         
        }
    });

}
//end


(function ($) {
    $.UrlParam = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]); return null;
    }
    $.getGSAresult = function () {
        if (isLoadClick == false) {
            var total_count, product_name_text = '', content_text = '', result_link = '';;
            var arr_res = {
                "T": product_name_text,
                "S": content_text,
                "U": result_link
            };
        
                    $('.loader').show();
                    var str_res = location.search.substr(1).split("=");
                    var str_search = decodeURI(decodeURIComponent(str_res[1].replace(/\+/g, '%20')));
                    var current_site = $('body').data('site');
                    $.ajax({
                        type: 'GET',
                        url: devBaseSearchURL,
                        data: {
                            q: str_search + '+site:www.vive.com/' + current_site + '/support',
                            num: 10,
                            start: count
                        },
                        dataType: 'json',
                        jsonp: true,
                        async: true,
                        cache: false,
                        beforeSend: function (evt) { },
                        success: function (data) {
                            if (data.GSP.RES && data.GSP.RES.M) {
                                total_count = parseInt(data.GSP.RES.M);
                                /*
								var resultstr = $(".results-count b").text();
                                if (resultstr.indexOf("{0}") >= 0) {
                                    $(".results-count b").text(resultstr.replace("{0}", total_count));
									$('title').html(resultstr.replace("{0}", total_count));

                                }
                                $(".results-count b").show();
                                */
                               // $(".results-count span").text(total_count);
                                if (count < total_count) {
                                    
                                    $.each(data.GSP.RES.R, function (key, value) {
                                        if (total_count <= 1) {
                                            $.each(arr_res, function (check_key, check_v) {
                                                if (key == check_key) { arr_res[check_key] = value;}
                                            });
                                         
                                            if (arr_res["T"] != '' && arr_res["S"] != '' && arr_res["U"] != '') {
                                                //arr_res["T"] = arr_res["T"].replace('<b>...</b>', '');
                                                if (arr_res["T"].charAt(0) == "-") { arr_res["T"] = arr_res["T"].slice(1, arr_res["T"].length); }
                                                arr_res["S"] = arr_res["S"].replace('<br>', '');
                                                arr_res["S"] = arr_res["S"].replace('...', '');
                                              //  $('.search-page-container .result_display').append('<div class="list-item-block"><a href="' + arr_res["U"] + '">' + arr_res["T"] + '</a><p>' + arr_res["S"] + '</p></div>');
                                                arr_res["T"] = ''; arr_res["S"] = ''; arr_res["U"] = '';
                                                }
                                            
                                        } else {
                                            $.each(value, function (key2, value2) {
                                                $.each(arr_res, function (check_key, check_v) {
                                                    if (key2 == check_key) { arr_res[check_key] = value2; }
                                                });
                                             
                                                if (arr_res["T"] != '' && arr_res["S"] != '' && arr_res["U"] != '') {
                                                    if (arr_res["T"].charAt(0) == "-") { arr_res["T"] = arr_res["T"].slice(1, arr_res["T"].length); }
                                                  
                                                    //arr_res["T"] = arr_res["T"].replace('<b>...</b>', '');
                                                    arr_res["S"] = arr_res["S"].replace('<br>', '');
                                                    arr_res["S"] = arr_res["S"].replace('...', '');
                                                 //   $('.search-page-container .result_display').append('<div class="list-item-block"><a href="' + arr_res["U"] + '">' + arr_res["T"] + '</a><p>' + arr_res["S"] + '</p></div>');
                                                    arr_res["T"] = ''; arr_res["S"] = ''; arr_res["U"] = '';
                                                }
                                            });
                                        }
                                    count++;
                                });
                                }
                                
                            }
                            $('.loader').hide();
                            return false;
                        },
                        error: function (data) {
                          
                        }
                    }).done(function (data) {
                        var resultstr = $(".results-count b").text();
                        isLoadClick = false;

                        if (resultstr.indexOf("{0}") >= 0) {
                            $(".results-count b").text(resultstr.replace("{0}", 0));
                            $('title').html(resultstr.replace("{0}", 0));
                            $(".results-count b").show();

                        }
                    });
        }
        
    }
})(jQuery);

$(document).ready(function ($) {
    var current_site = $('body').data('site'), support_path='';
    'use strict';
    //$('#ctl00_howToMainContent_txtWithHint').attr("placeholder", $('input[name="uc-hidden-result-for"]').val());
    if ($(location).attr('hostname') == 'localhost') { support_path = 'vivesupport'; } else { support_path = 'support'; }
    $('.main form').submit(function () {
        //encode setHrefParamsAndReplace and submit once respectively, means it would be encoded twice  on search page
        location.href = '/' + current_site + '/search/?q=' + encodeURIComponent(encodeURIComponent($('#ctl00_howToMainContent_txtWithHint').val()))+'&section=support';
        return false;
    });
    var str_search = $.UrlParam("q");
    if (str_search != '' && str_search != null) {
        var str_res = location.search.substr(1).split("=");
        
        $('#ctl00_howToMainContent_txtWithHint').val(decodeURI(decodeURIComponent(str_res[1].replace(/\+/g, '%20'))));
     //   $.getGSAresult();
        getSearchData();
    }
   
});
$(window).scroll(function () {
    var morePosition = $(document).height() - $(window).height() - $('footer').height();
    var str_search = $.UrlParam("q");
    if (str_search != '' && str_search != null) {
        if ($(window).scrollTop() > morePosition) {
          //  $.getGSAresult();
            getSearchData();
                isLoadClick = true;
            }
    }
    
});
