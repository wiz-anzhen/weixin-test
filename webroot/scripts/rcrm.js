function submit(order_id) {
    $('#submit_order').addClass('disabled').html('提交中');
    var post_data = {'order_id': order_id};
    var site = location.protocol + '//' + location.host + '/api/wx_user/order/submit';
    $.ajax({
        type: 'post',
        url: site,
        data: post_data,
        datatype: 'json',
        async: true,
        success: afterSubmitOrder
    });
}

function cutCount(order_id, menu_auto_id, menu_count) {
    var post_data = {'order_id': order_id, 'menu_auto_id': menu_auto_id, 'menu_count': menu_count - 1};
    var site = location.protocol + '//' + location.host + '/api/wx_user/order/change_menu_count';
    $.ajax({
        type: 'post',
        url: site,
        data: post_data,
        datatype: 'json',
        async: true,
        success: afterChangeMenuCount
    });
}

function addCount(order_id, menu_auto_id, menu_count) {
    var post_data = {'order_id': order_id, 'menu_auto_id': menu_auto_id, 'menu_count': menu_count + 1};
    var site = location.protocol + '//' + location.host + '/api/wx_user/order/change_menu_count';
    $.ajax({
        type: 'post',
        url: site,
        data: post_data,
        datatype: 'json',
        async: true,
        success: afterChangeMenuCount
    });
}

function afterChangeMenuCount() {
    location.reload();
}

function afterSubmitOrder(data) {
    $("#submit_order_status").css('display', 'block');
    if(data.errno == 0){
        $("#submit_order_status").html("点单提交成功");
        location.reload();
        $(".operation").addClass("disabled");
    }else if(data.errno == 1){
        $("#submit_order_status").addClass("alert-error").html(data.error);
    }
}

$(function(){
    $("#submit-comment").click(function(){
        var order_id = $("#order_id").val();
        var wx_user_id = $("#wx_user_id").val();
        var order_comment = $("#order-comment").val();
        var service_score = $("#service_score").children("i[class='select']").attr("value");
        var env_score = $("#env_score").children("i[class='select']").attr("value");

        var list = $("#order tbody tr");
        var menu_list = new Array();
        var menu;
        for(var i=0; i<list.length; i++){
            var menu_name    = $(list[i]).children('td:first').html();
            var menu_auto_id = $(list[i]).find(".rate").attr("name");
            var menu_score   = $(list[i]).find(".rate").children("i[class='select']").attr("value");

            menu = {"menu_auto_id": menu_auto_id, "menu_name": menu_name, "menu_score": menu_score};
            menu_list[i] = menu;
        }

        var post_data = {"order_id": order_id, "wx_user_id": wx_user_id, "service_score": service_score, "env_score": env_score, "order_comment": order_comment, "comment_list":  menu_list};

        $.ajax({
            type: 'post',
            url: '/api/wx_user/comment/submit' ,
            data: post_data,
            datatype: 'json',
            async: true,
            success: afterSubmitComment
        });
    });
});

function afterSubmitComment(data){
    if(data['errno'] == 0 ){
        $("#submit-comment").hide();
        $("#comment-status").removeClass("alert-error").addClass("alert-success").html('感谢您的点评').show();
    }
    else if(data['errno'] == 1){
        $("#comment-status").removeClass("alert-success").addClass("alert-error").html(data['error']).show();
    }
}

/*
 * 通用打分组件
 * callBack打分后执行的回调
 * this.Index:获取当前选中值
 */
var pRate = function(box,callBack){
    this.Index = null;
    var B = $("#"+box),
        rate = B.children("i"),
        w = rate.width(),
        n = rate.length,
        me = this;
    for(var i=0;i<n;i++){
        rate.eq(i).css({
            'width':w*(i+1),
            'z-index':n-i
        });
    }
    rate.hover(function(){
        var S = B.children("i.select");
        $(this).addClass("hover").siblings().removeClass("hover");
        if($(this).index()>S.index()){
            S.addClass("hover");
        }
    },function(){
        rate.removeClass("hover");
    })
    rate.click(function(){
        rate.removeClass("select hover");
        $(this).addClass("select");
        me.Index = $(this).index() + 1;
        if(callBack){callBack();}
    })
}

var pRate1 = function(box,callBack){
    this.Index = null;
    var B = $("#"+box),
        rate = B.children("i"),
        w = rate.width(),
        n = rate.length,
        me = this;
    for(var i=0;i<n;i++){
        rate.eq(i).css({
            'width':w*(i+1),
            'z-index':n-i
        });
    }
}

$(function(){
    $("#scoreButton").click(function(){
        var nick = $("#nick").val();
        var cardID = $("#nick").attr("cid");
        var post_data = {'nick' : nick, 'cardID' : cardID};

        $.ajax({
            type: 'post',
            url: '/api/fcrm/score/score' ,
            data: post_data,
            datatype: 'json',
            async: true,
            success: afterScoreSubmitComment
        });
    });
});

function afterScoreSubmitComment(data){
    if(data.errno == 1){
        $("#scoreStatus").removeClass("alert-success").addClass("alert-error").html(data.error).show();
    }else if(data.errno == 0){
        $("#scoreStatus").removeClass("alert-error").addClass("alert-success").html("积分获取成功<br/><br/>本次新增:" + data.card_score + "分<br/><br/>总积分:" + data.total_score + "分").show();
    }
}