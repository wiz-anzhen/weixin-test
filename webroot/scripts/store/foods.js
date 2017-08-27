// hide cart and show food list
var bg = get('bg');
var cart = get('cart');
var mainbody = get('shop_main');
bg.style.height = window.innerHeight + 'px';

var showCart = function (show) {
    if (show) {
        cart.show();
        bg.show();
        get('cart_button').className = 'open';
        mainbody.hide();
    } else {
        cart.hide();
        bg.hide();
        get('cart_button').className = 'closed';
        mainbody.show();
    }
};

document.getElementById('close_cart').onclick = function () {
    showCart(false);
};


showCart(false);

// configure cart
var empty = get('empty');
var totalPriceE = get('total_price');
var buttons = get('buttons');
var notEnough = get('not_enough');

function onLoad() {
    resetFoodNum(iniFoodNum);
}

var setCart = function ()
{
    var $totalFood = get('food_num2');

    if (parseInt($totalFood.innerHTML) == 0)
    {
        empty.show();
        totalPriceE.hide();
        buttons.hide();
    }
    else
    {
        empty.hide();
        totalPriceE.show();
        buttons.show();
        /*notEnough.hide();
        if (get('submitted').style.display != 'block')
        {
            buttons.show();
        }
        else
        {
            buttons.hide();
        }*/
    }
};

// show/hide cart
get('cart_button').onclick = function () {
    setCart();
    if (cart.style.display == 'none') {
        showCart(true);
    } else {
        showCart(false);
    }
    return false;
};

bg.onclick = function () {
    showCart(false);
};

function refreshFoodBox() {
    //var orderId = getRequest('order_id');
    //wbtWebAPI.call('/api/web/web/getOrderDetail', null, {'order_id': orderId}, function (data) {
    //    resetFoodNum(data.data);
    //});
}

function resetFoodNum(data) {
    var arr = data.arr;

    clearAll();

    var totalNum = 0;
    var totalPrice = 0;
    for (var i in arr) {
        var num = parseInt(arr[i]['num']);
        var price = parseFloat(arr[i]['price']);

        setFood(arr[i], num);
        totalNum += num;
        totalPrice += num * price;
    }

    setTotal(totalNum, totalPrice);
    //setTotal(totalNum);

    if (data.checkouted == 1) {
        checkouted();
    }
}



function clearAll() {
    var numS = document.getElementsByClassName('num');
    for (var i = 0; i < numS.length; i++) {
        numS[i].hide();
    }

    var reduceS = document.getElementsByClassName('reduce');
    for (var j = 0; j < reduceS.length; j++) {
        reduceS[j].hide();
    }

    get('cart_food_list').innerHTML = '';
    get('food_num').innerHTML = 0;
    get('food_num2').innerHTML = 0;
    get('money_num1').innerHTML = 0;
    setCart();
}

function changeFood(foodLiE, delta) {
    var totalNum1 = get('food_num');
    var totalMoney = get('money_num1');
    var foodId = foodLiE.getAttribute('rel');
    var foodName = foodLiE.getElementsByTagName('h3')[0].innerHTML;
    var priceStr = foodLiE.getElementsByClassName('price')[0].innerHTML;
    var foodPrice = parseFloat(priceStr.split(',').join(''));
    var num = parseInt(foodLiE.getElementsByClassName('num')[0].innerHTML);

    var params = {
        'wx_user_id': getRequest('wx_user_id'),
        'mp_user_id': getRequest('mp_user_id'),
        'store_id': getRequest('store_id'),
        'product_id': foodId,
        'action': delta
    };

    //wbtWebAPI.call('/api/wx_user/order/changeOrder', null, params, function () {
    wbtWebAPI.call('/api/wx_user/order/changeCart', null, params, function () {
        // 此处 food 参数应以服务器传回数值为准
        var food = {'id': foodId, 'name': foodName, 'price': foodPrice};
        setFood(food, num + delta);

        var $pre = parseFloat(totalMoney.innerHTML.replace(/,/g, ''));
        var $cur = $pre + foodPrice * delta;

        setTotal(parseInt(totalNum1.innerHTML) + delta, $.number($cur,2));
    });
}

function cartAddFood($this) {
    var $id = $this.parentElement.getAttribute('rel');
    var foodLiE = get('food_' + $id);
    if (!foodLiE) foodLiE = get('favorite_food_' + $id);
    if (foodLiE) changeFood(foodLiE, 1);
    return false;
}

function cartDeleteFood($this) {
    var $id = $this.parentElement.getAttribute('rel');
    var foodLiE = get('food_' + $id);
    if (!foodLiE) foodLiE = get('favorite_food_' + $id);
    if (foodLiE) changeFood(foodLiE, -1);
    return false;
}

function addFood() {

    var foodLiE = this.parentElement.parentElement.parentElement;
    changeFood(foodLiE, 1);
    return false;
}

function deleteFood() {
    var foodLiE = this.parentElement.parentElement.parentElement;
    changeFood(foodLiE, -1);
    return false;
}

function setFood(food, num) {
    // food: id name price
    if (num < 0) num = 0;
    var $preS = ['', 'favorite_'];
    for (var $i in $preS) {
        var foodLiE = get($preS[$i] + 'food_' + food['id']);
        if (foodLiE) {
            var numE1 = foodLiE.getElementsByClassName('num');
            var reduceE = foodLiE.getElementsByClassName('reduce');
            numE1[0].innerHTML = $.number(num);
            if (num == 0) {
                numE1[0].hide();
                reduceE[0].hide();
            } else {
                numE1[0].show();
                reduceE[0].show();
            }
        }
    }

    // cart
    var cartLiE = get('cart_' + food['id']);
    if (cartLiE) {
        if (num == 0) {
            cartLiE.parentNode.removeChild(cartLiE);
        } else {
            var numE2 = cartLiE.getElementsByClassName('num');
            numE2[0].innerHTML = $.number(num);
        }
    } else {
        cartLiE = document.createElement('li');
        cartLiE.setAttribute('id', 'cart_' + food['id']);
        cartLiE.setAttribute('rel', food['id']);
        cartLiE.innerHTML = '<div style="width:60%;float:left;"><h3 class="name">' + food['name'] + '</h3>' +
            '<span class="pri">¥<span class="price">' + $.number(food['price'],2) +
            '</span> &times; <span class="num">' + num +
            '</span></span></div><a class="cart_add" onclick="javascript:cartAddFood(this);">+</a><a class="cart_reduce" onclick="javascript:cartDeleteFood(this);">-</a>';
        get('cart_food_list').appendChild(cartLiE);
    }
}

function setTotal(num, money) {
//function setTotal(num) {
    if (num < 0) num = 0;
    if (money < 0) money = 0;
    var totalNum1 = get('food_num');
    var totalNum2 = get('food_num2');
    var totalMoney = get('money_num1');
    var notEnoughMoney = get('money_num2');

    totalNum1.innerHTML = $.number(num);
    totalNum2.innerHTML = $.number(num);
    totalMoney.innerHTML = $.number(money, 2);
//    if (money < minPrice)
//    {
//        notEnoughMoney.innerHTML = minPrice - money;
//    }

    setCart();
}

function getRequest(param) {
    var url = window.location.toString();
    var str = "";
    if (url.indexOf("?") != -1) {
        var ary = url.split("?")[1].split("&");
        for (var i in ary) {
            str = ary[i].split("=")[0];
            if (str == param) {
                return decodeURI(ary[i].split("=")[1]);
            }
        }
    }
    return null;
}

function getWxuserid() {
   var mpUserId =getRequest('mp_user_id');
    wbtWebAPI.call
}

function checkout() {
    var orderId = getRequest('order_id');
    var comment = get('comment').value;
    //wbtWebAPI.call('/api/web/web/checkoutOrder', null, {'order_id': orderId, 'comment':comment}, function (data) {
    //    checkouted();
    //});
}

function checkouted() {
    submit.hide();
    //refresh.hide();
    buttons.hide();
    get('submitted').show();
}