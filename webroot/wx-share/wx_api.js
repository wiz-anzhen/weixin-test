
    function getStrFromTxtDom(selector) {
        var url = jQuery('#txt-' + selector)
                .html()
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>');
        return jQuery.trim(url);
    }

    function getStrFromTxtDomAndDecode(selector) {
        return 'http://' + window.location.host + '/mp/redirect?url=' + encodeURIComponent(getStrFromTxtDom(selector));
    }



    function viewSource() {
      
        jQuery.ajax({
            url: '/mp/appmsg/show-ajax' + location.search, //location.href
            async: false,
            type: 'POST',
            timeout: 2000,
            data: {url: getStrFromTxtDom('sourceurl')},
            complete: function() {
                location.href = getStrFromTxtDomAndDecode('sourceurl');
            }
        });
        return false;
    }
    ;
    function report(link, fakeid, action_type) {
        var parse_link = parseUrl(link);
        if (parse_link == null)
        {
            return;
        }
        var query_obj = parseParams(parse_link['query_str']);
        query_obj['action_type'] = action_type;
        query_obj['uin'] = fakeid;
        var report_url = '/mp/appmsg/show?' + jQuery.param(query_obj);
        jQuery.ajax({
            url: report_url,
            type: 'POST',
            timeout: 2000
        })
    }
    ;

    function share_scene(link, scene_type) {
        var parse_link = parseUrl(link);
        if (parse_link == null)
        {
            return link;
        }
        var query_obj = parseParams(parse_link['query_str']);
        query_obj['scene'] = scene_type;
        var share_url = 'http://' + parse_link['domain'] + parse_link['path'] + '?' + jQuery.param(query_obj) + (parse_link['sharp'] ? parse_link['sharp'] : '');
        return share_url;
    }
    ;

    (function() {
        var onBridgeReady = function() {
            var
                    appId = '',
                    imgUrl = window.picUrl,
                    link = window.baseUrl,
                    title = htmlDecode(window.shareTitle),
                    desc = htmlDecode(window.shareDesc),
                    fakeid = "",
                    desc = desc || link;

            if ("1" == "0") {
                WeixinJSBridge.call("hideOptionMenu");
            }

            jQuery("#post-user").click(function() {
                WeixinJSBridge.invoke('profile', {'username': 'gh_dab5c199ae98', 'scene': '57'});
            })

            // 发送给好友; 
            WeixinJSBridge.on('menu:share:appmessage', function(argv) {

                WeixinJSBridge.invoke('sendAppMessage', {
                    "appid": appId,
                    "img_url": imgUrl,
                    "img_width": "640",
                    "img_height": "640",
                    "link": share_scene(link, 1),
                    "desc": desc,
                    "title": title
                }, function(res) {
                    report(link, fakeid, 1);
                    $.get(window.baseUrl+"/count.php?t=share&c="+window.customId);//统计分享
                });
            });
            // 分享到朋友圈;
            WeixinJSBridge.on('menu:share:timeline', function(argv) {
                report(link, fakeid, 2);
                $.get(window.baseUrl+"/count.php?t=share&c="+window.customId);//统计分享
                WeixinJSBridge.invoke('shareTimeline', {
                    "img_url": imgUrl,
                    "img_width": "640",
                    "img_height": "640",
                    "link": share_scene(link, 2),
                    "desc": desc,
                    "title": desc
                }, function(res) {                  
                });

            });

            // 分享到微博;
            var weiboContent = '';
            WeixinJSBridge.on('menu:share:weibo', function(argv) {

                WeixinJSBridge.invoke('shareWeibo', {
                    "content": title + share_scene(link, 3),
                    "url": share_scene(link, 3)
                }, function(res) {
                    report(link, fakeid, 3);
                });
            });

            // 分享到Facebook
            WeixinJSBridge.on('menu:share:facebook', function(argv) {
                report(link, fakeid, 4);
                WeixinJSBridge.invoke('shareFB', {
                    "img_url": imgUrl,
                    "img_width": "640",
                    "img_height": "640",
                    "link": share_scene(link, 4),
                    "desc": desc,
                    "title": title
                }, function(res) {
                });
            });

            // 隐藏右上角的选项菜单入口;
            //WeixinJSBridge.call('hideOptionMenu');
        };
        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    })();

 

    function nbspDecode(str) {
        if (str == undefined)
        {
            return "";
        }
        var nbsp = "&nbsp;";
        var replaceFlag = "<nbsp>";
        var matchList = str.match(/(&nbsp;){1,}/g);
        if (matchList) {
            var replacedStr = str.replace(/(&nbsp;){1,}/g, replaceFlag);

            for (var idx = 0; idx < matchList.length; idx++) {
                var tmpNbsp = matchList[idx];
                tmpNbsp = tmpNbsp.replace(nbsp, " ");
                replacedStr = replacedStr.replace(replaceFlag, tmpNbsp);
            }
            return replacedStr;
        } else {
            return str;
        }
    }

    var title = $("#activity-name").html();
    title = nbspDecode(title);
    $("#activity-name").html(title);
