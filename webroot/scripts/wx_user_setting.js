function saveUserSetting(wxUserID) {
    var nick = $("#nick").val();
    var post_data = {'wx_user_id': wxUserID, 'nick': nick};
    var site = location.protocol + '//' + location.host + '/api/wx_user/account/save_nick';
    $.ajax({
        type: 'post',
        url: site,
        data: post_data,
        datatype: 'json',
        async: true,
        success: afterUserSetting
    });
}

function afterUserSetting(data) {
    $("#submit_status").show();
    if(data.errno == 0)
    {
        $("#submit_status").removeClass("alert-error");
        $("#submit_status").html("称呼设置成功,请点击页面左上角'返回'按钮后进行其它操作。");
        //location.reload();
    }else
    {
        $("#submit_status").addClass("alert-error");
        $("#submit_status").html(data.error);
    }
}