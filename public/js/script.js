$(document).ready(function () {
    ua = detect.parse(navigator.userAgent);
    var url = window.location.href.split('/');
    var currentRazdel = '';
    for (var i=0; i< url.length; i++) {
        if ('lookbook' == url[i]) {
            currentRazdel = 'lookbook';
        }
        if ('dress-kod' == url[i]) {
            currentRazdel = 'dress-kod';
        }
        if ('legkaya-odezhda' == url[i]) {
            currentRazdel = 'legkaya-odezhda';
        }
        if ('verhnyaya-odezhda' == url[i]) {
            currentRazdel = 'verhnyaya-odezhda';
        }
        if ('stil' == url[i]) {
            currentRazdel = 'stil';
        }
        if ('vechernyaya-i-svadebnaya-moda' == url[i]) {
            currentRazdel = 'vechernyaya-i-svadebnaya-moda';
        }
        if ('obuv-i-aksessuary' == url[i]) {
            currentRazdel = 'obuv-i-aksessuary';
        }
    }

    $('.mainnav').find('.nav.level_0 li').each(function(){
	if ($(this).data('razdel') == currentRazdel) {
		$(this).addClass('active');
	}
    });


    $('#search_str').keydown(function (event) {
        if (ua.browser.family == 'Chrome') {
            /* Сделать адкватную проверку введеных символов для ебаного хрома 
             * который сука имеет баг на определение текущей нажатой клавиши */
        } else {
            var result = /^[\sа-яёa-z0-9-]$/iu.test(event.key);
            if (!result && event.which != 8 && event.which != 32) {
                event.preventDefault();
            }
        }


        if (event.which == 13) {
            var str = $(this).val();
            if (str != '' && str.length > 2) {
                var searchStr = $.trim($(this).val());
                sendSearchRequest(searchStr);
            } else {
                $('#slider1_container').css('display', 'inline-block');
                $('#main_page_block').fadeIn('fast');
                $('#razdel-page-main').fadeIn('fast');
                $('#statya-page-main').fadeIn('fast');
                $('#main_page_search_block').fadeOut('fast');
            }
        }

    });

    /* filters on razdel part start */
    $('label.filter-input-menu-label').on('click', function (e) {
        e.preventDefault();
    });

    $('div.was-link').hover(
        function() {
            set_label_visibility($(this), false);
        }, function() {
            set_label_visibility($(this), true);
        }
    );

    function set_label_visibility($element, enableFlag)
    {
        var $checkBox = $element.find("input");
        var $label = $element.find("label");
        var labelId = $label.attr('id');
        var $styleBlock = $('#custom-dynamic-styles');
        var propertyStr = "#" + labelId + ":after {opacity:0.3}";

        if (enableFlag) {
            $styleBlock.html("");
        } else {
            if (!$checkBox.prop("checked")) {
                $styleBlock.html(propertyStr);
            }
        }
    }

    $('div.was-link').on('click', function(e) {
        var $checkBox = $(this).find("input");
        if ($checkBox.prop("checked")) {
            $checkBox.prop("checked", false);
            set_label_visibility($(this), false);
        } else {
            $checkBox.prop("checked", true);
            set_label_visibility($(this), true);
        }

        var filterUrl = '/' + currentRazdel + '/filters';
        var filtersEnabled = [];
        $('div.was-link').each(function () {
            filtersEnabled.push($(this).attr('data-razdel-filtres'));
        });

        $.ajax({
            type: "POST",
            url: filterUrl,
            data: {
                filter: filtersEnabled
            },

            success: function(result) {
                alert(1);
            },

            error: function(result){
                alert(2);
            }
        });
    });
    /* filters on razdel part end */

    $('#button_search_main').click(function () {
        var str = $('#search_str').val();
        if (str != '' && str.length > 2) {
            sendSearchRequest($.trim($('#search_str').val()));
        } else {
            $('#slider1_container').css('display', 'inline-block');
            $('#main_page_block').fadeIn('fast');
            $('#razdel-page-main').fadeIn('fast');
            $('#statya-page-main').fadeIn('fast');
            $('#main_page_search_block').fadeOut('fast');
        }
    });
    //Check to see if the window is top if not then display button

    var mega_href = window.location.href;
    mega_href = mega_href.substr(7).split('/');
    var projectmark = mega_href[1].substr(0, 8);
    var task_new = mega_href[2];
    var task_info = mega_href[1].substr(0, 5);
    var searach_info = mega_href[1];

    
    $('#scrollup img').mouseover( function(){
	$( this ).animate({opacity: 0.65},100);
	}).mouseout( function(){
		$( this ).animate({opacity: 1},100);
	}).click( function(){
		window.scroll(0 ,0); 
		return false;
    });

    $(window).scroll(function(){
	if ( $(document).scrollTop() > 0 ) {
	        $('#scrollup').fadeIn('fast');
	} else {
		$('#scrollup').fadeOut('fast');
	}
   });
    
   
    /* Load arctile on page scroll */
    if (3 == mega_href.length) {
        var inProgress = false;
        var noArcticles = false;
        var razdelName = mega_href[1];
        
        $(window).scroll(function() {
            var startFrom = $('div.blog_article').length;
            if(
                $(window).scrollTop() + $(window).height() >= $(document).height() - 300 
                && !inProgress
                && !noArcticles
            ) {
            	PageObj.onLoader();
                inProgress = true;
                $.post(
                    '/loadarcticleonscroll',
                    {razdel:razdelName, begin:startFrom},
                    function (res) {
                        if (res.status) {
                            $('#razdel-page-main').append(res.html);
                        } else {
                           noArcticles = true; 
                        }
                        inProgress = false;
                        PageObj.offLoader();
                    },
                    'json'
                );
            }
        });
    } 




    /* Custom effects for AUTOPLAY only */
    var _SlideshowTransitions = {
        $Duration: 1200,
        x: 1,
        $Easing: {
            $Left: $JssorEasing$.$EaseInOutQuart,
            $Opacity: $JssorEasing$.$EaseLinear
        },
        $Opacity: 2,
        $Brother: {
            $Duration: 1200,
            x: -1,
            $Easing: {
                $Left: $JssorEasing$.$EaseInOutQuart,
                $Opacity: $JssorEasing$.$EaseLinear
            },
            $Opacity: 2
        }
    }


    /* To override transition efects to use another efeect */
    /* jssor_slider1.$SetSlideshowTransitions(_SlideshowTransitions); */

    /* Main options for slider*/
    var options = {
        $FillMode: 5, //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
        $SlideEasing: $JssorEasing$.$EaseOutQuint, //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
        $MinDragOffsetToSlide: 20, //[Optional] Minimum drag offset to trigger slide , default value is 20
        $SlideSpacing: 0, //[Optional] Space between each slide in pixels, default value is 0
        $DisplayPieces: 1, //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
        $ParkingPosition: 0, //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
        $PlayOrientation: 1, //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
        $DragOrientation: 1, //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
        $Loop: 2,
        $AutoPlay: true, //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
        $AutoPlayInterval: 3000, //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 30008
        $SlideDuration: 300, //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
        $UISearchMode: 1, //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
        $ArrowKeyNavigation: true, //Allows keyboard (arrow key) navigation or not
        $PauseOnHover: 1,
        $SlideshowOptions: {//[Optional] Options to specify and enable slideshow or not
            $Class: $JssorSlideshowRunner$, //[Required] Class to create instance of slideshow
            $Transitions: _SlideshowTransitions, //[Required] An array of slideshow transitions to play slideshow
            $TransitionsOrder: 1, //[Optional] The way to choose transition to play slide, 1 Sequence, 0 Random
            $ShowLink: true                                    //[Optional] Whether to bring slide link on top of the slider when slideshow is running, default value is false
        }

    };

    /* только для главной страницы */
    if (mega_href.length == 2) {
        var jssor_slider1 = new $JssorSlider$('slider1_container', options);

        function ScaleSlider() {
            var bodyWidth = document.body.clientWidth;
            if (bodyWidth)
                jssor_slider1.$ScaleWidth(Math.min(bodyWidth, bodyWidth * 4));
            else
                window.setTimeout(ScaleSlider, 30);
        }
        ScaleSlider();

        $(window).bind("load", ScaleSlider);
        $(window).bind("resize", ScaleSlider);
        $(window).bind("orientationchange", ScaleSlider);

    }
    /*  $('.showteg').click(function(e){
     e.preventDefault(); 
     var id = $(this).data('id');
     var name = $('#razdel-name-from').text();
     $.post('/jenstvenayamoda.ru/public/razdel/showtegsposts',{teg:id},function(result){
     $('.conteiner').html(result.html);
     $('#razdel-name-to').html(name);
     },'json');
     
     });
     */

    $('.header_li').hover(function () {

        var id = $(this).data('id');
        $('#li-' + id).show();
        $('#li-' + id).animate({height: id + 'px'}, 700);
    }, function () {
        var id = $(this).data('id');
        $('#li-' + id).animate({height: '0px'}, 400);
        $('#li-' + id).hide();

    });
    var flag = 0;
    $('.add_coment_block').click(function () {
        var place = $(this).attr('id');

        if (flag != 1)
        {
            $.post('/jenstvenayamoda.ru/public/razdel/commentform', {id: 0}, function (result) {
                flag = 1;
                $('#' + place).html(result.html);
                $('#comment_add_form').show('normal');

                $('#coment_form').submit(function (e) {
                    e.preventDefault();

                    var flagus = 0;
                    var post_id = $('#post_id_div').val();
                    var from = $('#from_name').val();
                    var email = $('#from_email').val();
                    var number = $('#corect_num').val();
                    var text = $('#from_text').val();

                    if (from == "")
                    {

                        $('#from_name').animate({backgroundColor: '#FFD3D3'}, 400);
                        $('#from_name').animate({backgroundColor: '#fff'}, 1000);
                        flagus = 1;
                    }
                    if (email == "")
                    {
                        $('#from_email').animate({backgroundColor: '#FFD3D3'}, 400);
                        $('#from_email').animate({backgroundColor: '#fff'}, 1000);
                        flagus = 1;
                    }
                    if (number != result.c_number)
                    {
                        $('#corect_num').animate({backgroundColor: '#FFD3D3'}, 400);
                        $('#corect_num').animate({backgroundColor: '#fff'}, 1000);
                        flagus = 1;
                    }
                    if (text == "")
                    {
                        $('#from_text').animate({backgroundColor: '#FFD3D3'}, 400);
                        $('#from_text').animate({backgroundColor: '#fff'}, 1000);
                        flagus = 1;
                    }
                    if (flagus == 0)
                    {
                        // добавляем комент в базу
                        $.post('/jenstvenayamoda.ru/public/razdel/addcomments', $('#coment_form').serialize() + "&postid=" + post_id, function (res) {
                            $('#comment_add_form').hide('normal');
                            $('#' + place).html('<span class="leftmesage1">Ваш комментарий будет опубликован после модерации.<span>');
                            $('#' + place).show('fast');
                        }, 'json');
                    }
                });


            }, 'json');
        }
    });


    $("a.first").fancybox();
    $("a.two").fancybox();
    $("a.eshop").attr('rel', 'gallery').fancybox(
        {
            afterShow: function(obj){
                $('img.fancybox-image').on('mouseover', function () {
                    $(this).css('cursor', 'pointer');
                });
                $('img.fancybox-image').on('click', function () {
                    var url = $(this).attr('src');
                    if ('undefined' != PageObj.urlData[url]) {
                        window.open(PageObj.urlData[url], '_blank');
                    }
                });
                PageObj.addBuyButtonHtml(this.href);
                $('div.buy-button').on('click', function () {
                    var url = $(this).prev().attr('src');
                    if ('undefined' != PageObj.urlData[url]) {
                        window.open(PageObj.urlData[url], '_blank');
                    }
                });
            }
        }
        );
    
    $('.pinteres-post').click(function(e){ //
	PageObj.onLoader();
	
	javascript:void((function(d) {
            var e = d.createElement('script');
            e.setAttribute('type', 'text/javascript');
            e.setAttribute('charset', 'UTF-8');
            e.setAttribute('src', '//assets.pinterest.com/js/pinmarklet.js?r=' + Math.random() * 99999999);
            e.onload = PageObj.offLoader;
	    d.body.appendChild(e)
        })(document));
		
    });

    PageObj.initFancyBoxEvents();

});

var PageObj = {
	urlData: {},
    buttonHtml: '<div class="buy-button btn bnt-lg">Купить</div>',
    addBuyButtonHtml: function (imgUrl) {
        console.log(imgUrl);
        $('div.fancybox-inner').find('img[src="' + imgUrl + '"]').after(PageObj.buttonHtml);
    },

    onLoader: function(){
	$('#overlay').show();
    },
    offLoader: function(){
	$('#overlay').hide();
    },

    initFancyBoxEvents: function () {

        $('a.eshop').each(function () {
            if (
                'undefined' != $(this).attr('href')
                && 'undefined' != $(this).attr('siteurl')
            ) {
                var imgUrl = $(this).attr('href');
                var siteUrl = $(this).attr('siteurl');
                PageObj.urlData[imgUrl] = siteUrl;
            }
        });
    },
}


// Добавить в Избранное
function add_favorite(a) {
    title = document.title;
    url = document.location;
    try {
        // Internet Explorer
        window.external.AddFavorite(url, title);
    }
    catch (e) {
        try {
            // Mozilla
            window.sidebar.addPanel(title, url, "");
        }
        catch (e) {
            // Opera
            if (typeof (opera) == "object") {
                a.rel = "sidebar";
                a.title = title;
                a.url = url;
                return true;
            }
            else {
                // Unknown
                alert('Нажмите Ctrl-D чтобы добавить страницу в закладки');
            }
        }
    }
    return false;
}

/* Send search request and draw + hightlight results */
function sendSearchRequest(searchStr)
{
    $.post('/search', {search: searchStr}, function (res) {
        if (res.status == 1 && res.count > 0) {
            $('#slider1_container').css('display', 'none');
            $('#main_page_block').fadeOut('fast');
            $('#razdel-page-main').fadeOut('fast');
            $('#statya-page-main').fadeOut('fast');
            $('#main_page_search_block').html(res.html);

            if (res.count == 1) {
                var serachHtml = res.count + ' статья';
            } else if (res.count == 2 || res.count == 3 || res.count == 4) {
                var serachHtml = res.count + ' статьи';
            } else {
                var serachHtml = res.count + ' статей';
            }
            $('#search-count').html(serachHtml);

            $('#main_page_search_block').find('div.new_art_title').find('a').each(function () {
                var tempName = $.trim($(this).text());
                var tempNameToLower = tempName.toLowerCase();
                var n = tempNameToLower.search(searchStr);
                var symbol = tempName.charAt(n);
                var isUpperCase = symbol.toUpperCase();
                if (symbol === isUpperCase) {
                    var tempReplace = searchStr.charAt(0).toUpperCase() + searchStr.slice(1);
                    var hightlightName = tempName.replace(tempReplace, '<span class="highlitetext">' + tempReplace + '</span>');
                } else {
                    var hightlightName = tempName.replace(searchStr, '<span class="highlitetext">' + searchStr + '</span>');
                }
                $(this).html(hightlightName);
            });

            $('#main_page_search_block').fadeIn('fast');
        } else {
            $('#slider1_container').css('display', 'inline-block');
            $('#main_page_block').fadeIn('fast');
            $('#razdel-page-main').fadeIn('fast');
            $('#statya-page-main').fadeIn('fast');
            $('#main_page_search_block').fadeOut('fast');
        }
    }, 'json');


}