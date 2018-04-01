$(document).ready(function() {
    
    //Форма авторизации в сайдбаре
    $("#login-form").submit(function(e){ e.preventDefault();
        if($('#login-form input[name="username"]').val()=='')
        {
            $('#login-form input[name="username"]').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#login-form input[name="username"]').animate({backgroundColor:'#fff'}, 1000);
            return;
        }
        if($('#login-form input[name="password"]').val()=='')
        {
            $('#login-form input[name="password"]').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#login-form input[name="password"]').animate({backgroundColor:'#fff'}, 1000);
            return;
        }
        
        var m_data=$(this).serialize();
        $.post('/auth/login',m_data,function(results) {
            if(results.status == 1) 
                window.location = '/profile';
            else if (results.status == 0)
            {
                $('#errors').html(results.message);
            }
            else if (results.status == 2)
            {
                window.location = '/forgot';
            }
                
        },'json');
    });
    
    //Переключение способа регистрации
    $('#showEmail').click(function() {
               $('#reg-input').attr('name','email');
               $('#reg-input').attr('placeholder','Email');
        });

    $('#showPhone').click(function() {
           $('#reg-input').attr('name','phone');
           $('#reg-input').attr('placeholder','Телефон');
    });
    
    //Регистрация пользователя
    $('#register').click(function(e) {
        e.preventDefault();
        if($('#reg-input').attr('name')=='email')
            {
                $('#reg-input').css('background','#fff');
                var email = $('#reg-input').val();
                if(!email.match(/.+@.+\..+/i))
                {
                    $('#reg-input').animate({backgroundColor:'#FFD3D3'}, 400);
                    $('#reg-input').animate({backgroundColor:'#fff'}, 1000);
                    return;
                }
            }
        $.post('/auth/confirmation/',$('#emailForm').serialize(),function(json) {
            if(json.status!=0)
                //window.location.href = '/auth/login';
                {
                    $('.reg-form').slideUp('slow');
                    $('.success-show').html(json.message);
                }
            else
                $('#error').html(json.message);
        },'json');
    });
   
    //
    $("#login-recovery-pass").submit(function(e){ e.preventDefault();
        
        var m_data=$(this).serialize();
        $.post('/auth/changepass',m_data,function(results) {
            if(results.status == 1) 
                window.location = '/profile';
            else
                $('#errors').html(results.message);
            },'json');
    });
    
    //Форма авторизации в основном блоке
    $("#login-forgot").submit(function(e){ e.preventDefault();
        if($('#login-forgot input[name="username"]').val()=='')
        {
            $('#login-forgot input[name="username"]').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#login-forgot input[name="username"]').animate({backgroundColor:'#fff'}, 1000);
            return;
        }
        if($('#login-forgot input[name="password"]').val()=='')
        {
            $('#login-forgot input[name="password"]').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#login-forgot input[name="password"]').animate({backgroundColor:'#fff'}, 1000);
            return;
        }
        
        var m_data=$(this).serialize();
        $.post('/auth/login',m_data,function(results) {
            if(results.status == 1) 
                window.location = '/profile';
            else if (results.status == 0)
            {
                $('#errors').html(results.message);
            }
            else if (results.status == 2)
            {
                window.location = '/forgot';
            }
                
        },'json');
    });
    
    //Форма восстановления пароля
    $("#login_r").submit(function(e){ e.preventDefault();
        var m_data = $(this).serialize();
        $.post('/auth/checkremember',m_data,function(results) {
            if(results.status == 1) {
                $('.container-pass-recovery').slideUp('slow');
                $('#ok-forgot').show();
                 $('.errors-forgot-main').show();
                $('#ok-forgot').html('Вам на почту были отправлены инструкции для создания нового пароля!');  
            }
            else
                $('#errors-forgot').html(results.message);
        },'json');
    });
    
   /* $('a.select-button').click(function() {
       console.log($(this).prev().html());
        
       $(this).prev().trigger('click');
        
    });*/
    
    // добавляет пустую форму для карьеры
    $("#career_add").click(function() 
    {
        
        var id = $(this).attr('name').split('-')[1];
		var new_id = parseInt(id)+1
		var new_name = $(this).attr('name').split('-')[0]+'-'+new_id;
		$(this).attr('name',new_name);
        $.post('/profile/addcareer',{'id':id},function(results){ 

            $('#new_career').append(results.html);
            $('#country-name-'+id).chosen({width: "100%"});
            $('#city-name-'+id).chosen({width: "100%"});
            
        },'json');
        
    });
     // сохранить результаты измененной карьеры
     $("#career").submit(function(e){ e.preventDefault();  
       
            $.post('/profile/tools/?act=career',$(this).serialize(),function(results){ 
                if(results.status == '0') $('#message').css({'color':'red'}); else $('#message').css({'color':'green'}); 
                $('#message').html(results.message); 
            },'json');
       
     });
    // натягиваем селектбоксы chosen()
     
    // $("#category-id").chosen({width: "30%",placeholder_text_multiple:'Выберите категорию'});
     $("#task-lvl").chosen({width: "30%"});
    // $("#tasks-inputs").chosen({width: "100%",display_selected_options:false});
     $('.peoples-lists').chosen({width: "100%"});
     $(".task-friends-list").chosen({disable_search: true,width: "100%"});
     $(".tasks.priority").chosen({disable_search: true,width: "100%"});
     $(".country").chosen({width: "100%"});
     $("#filter-age-from").chosen({width: "50%"});
     $("#filter-age-to").chosen({width: "50%"});
     $(".fcountry").chosen({width: "100%"});
     $(".fcity").chosen({width: "100%"});
    //$(".hcity").chosen({width: "100%"});
     $(".addcountry").chosen({width: "100%"});
     $(".addcity").chosen({width: "100%"});
     $(".hcityc").chosen({width: "100%"});
     $(".sex").chosen({disable_search: true,width: "100%"});
     $(".days").chosen({disable_search: true,width: "100%"});
     $(".mons").chosen({disable_search: true,width: "100%"});
     $(".years").chosen({disable_search: true,width: "100%"});
     $(".menu-sel1").chosen({disable_search: true,width: "100%"});
     $(".menu-sel2").chosen({disable_search: true,width: "100%",placeholder_text_single:'Укажите Ваше имя'});
     $(".list1").chosen({disable_search: true,width: "100%",placeholder_text_multiple:'Выберите язык'});
     $(".list2").chosen({disable_search: true,width: "100%",placeholder_text_multiple:'Выберите специализацию'});
     
     
     $(".hcitycadd").chosen({width: "100%"});
     $('.tems-list').chosen({width:"100%"})
     
     
     
     
     
     // добавляем заявку на добавления в друзья
     $('#add_friend').click(function(){
     var id = $('#add_friend').val();
     if (id)
     {
        $.post('/profile/addfriendrequest',{'freind_id': id},
               function(result)
               {
                 $('#add_friend').html('');  
                 $('#add_friend').append('Заявка отправлена');
                 $('#add_friend').attr('id','friend_set');
               },'json'

           );
     }   
     }
     );
     // принятие предложения дружить из профайла
     $('.button.friends').click(function (){
        var id = $(this).attr('id');
        $.post('/friends/addfriend',{'freind_id': id},
            function(result)
            {
               //alert(result.ok) ;
               document.location.href='';  
            },'json'

        );
         
     })
     
     // принятие предложения дружить из заявок
     $('.friendrequestadd').click(function (e){
         e.preventDefault();
        var id = $(this).attr('id');
        $.post('/friends/addfriend',{'freind_id': id},
            function(result)
            {
               //alert(result.ok) ;
               document.location.href='';  
            },'json'

        );
         
     })
     // удалить заявку на дружбу
     $('.friends.delfriends').click(function (){
        var id = $(this).attr('id');
        $.post('/friends/delfriendrequest',{'freind_id': id},
            function(result)
            {
               //alert(result.ok) ;
               document.location.href=''; 
            },'json'

        );
         
     })
     // удалить заявку на дружбу из раздела заявок
     $('.delfriendsreq').click(function (){
        var id = $(this).attr('id');
        $.post('/friends/delfriendrequest',{'freind_id': id},
            function(result)
            {
               //alert(result.ok) ;
               document.location.href=''; 
            },'json'

        );
         
     })
     // удалить друга
     $('.freinds_del').click(function (){
        var id = $(this).attr('id');
        //alert(id);
        $.post('/friends/delfriend',{'freind_id': id},
            function(result)
            {
               //alert(result.ok) ;
               document.location.href=''; 
            },'json'

        );
         
     })
     
     
     // выводим блок с результатами поиска
     $('#search').click(function(){
       
       var param = $('form input[name=search]').val();
       
       if(param.length == 0)
       {
           var marker = 1;
           var search_data = "";
       }
       else 
       {
           marker = 0;
           search_data = param;
       }
       
        $.post('/search/searchbox',{search_str:search_data,mark:marker},
            function(result)
            {
                $('.search_block').html('');
                $('.search_block').append(result.html);
            },'json'

        );
       $('.search_block').show();
     });
     // обновляем блок с результати поиска 
     $('form input[name=search]').keyup(function(){
        
         var search_data = ($(this).val());
        $.post('/search/searchbox',{search_str:search_data,mark:0},
            function(result)
            {
                $('.search_block').html('');
                $('.search_block').append(result.html);
            },'json'

        );
         $('.search_block').show();
     });
     
     // скрываем выпадающее меню поиска
     $('#search').focusout(function(){
        if (!$('.search_block').is(":hover"))
            $('.search_block').hide();
        
     });
    
    // поиск форма
    $('#search-form1').change(function(){
    if ($('#filter-country').val()==0)
    {
        $('#filter-city').val(0);
        $('.filter-city').hide();
        
    }
    
    if ($('#filter-country').val()!=0)  
    {
        $('.filter-city').show(); 
        var id = "filter-"+$('#filter-country').val()+"-"+$('#filter-city').val();
        change_country_filter(id);   
        
    }  
    
    // изменять фильтр возраста 
        var agef = $('#filter-age-from').val(); // от from
        var aget = $('#filter-age-to').val();  // до  to
        
       if (agef == 0 && aget ==0)
       {
            $('#filter-age-to').html('');
            $('#filter-age-to').append('<option value='+0+' label="До " selected>До </option>');
            for (i=14;i<=80;i++)
            {
                $('#filter-age-to').append('<option value='+i+' label="До '+i+'">До '+i+'</option>');
            }
            $('#filter-age-to').trigger("chosen:updated");
            $('#filter-age-from').html('');
            $('#filter-age-from').append('<option value='+0+' label="От " selected>От </option>');
            for (i=14;i<=80;i++)
            {
                $('#filter-age-from').append('<option value='+i+' label="От '+i+'">От '+i+'</option>');
            }
            $('#filter-age-from').trigger("chosen:updated");
       }
       else
       {
                $('#filter-age-to').html('');
                $('#filter-age-to').append('<option value='+0+' label="До ">До </option>');
                if (agef ==0) agef=14;
                for (i=agef;i<=80;i++)
                {
                    if (aget == i) // selected
                        $('#filter-age-to').append('<option value='+i+' label="До '+i+'" selected>До '+i+'</option>');
                    else
                    $('#filter-age-to').append('<option value='+i+' label="До '+i+'">До '+i+'</option>');
                }
                $('#filter-age-to').trigger("chosen:updated");
                $('#filter-age-from').html('');
                $('#filter-age-from').append('<option value='+0+' label="От ">От </option>');
                if (aget ==0) aget=80;
                for (i=14;i<=aget;i++)
                {
                    if (agef == i) // selected
                        $('#filter-age-from').append('<option value='+i+' label="От '+i+'" selected>От '+i+'</option>');
                    else
                    $('#filter-age-from').append('<option value='+i+' label="От '+i+'">От '+i+'</option>');
                }
                $('#filter-age-from').trigger("chosen:updated");
            
       }
        
  
    
    $.post('/search/searchbox',$('#search-form1').serialize(),function(result){ 
    $('.container.fil').html('');
    $('.container.fil').append(result.html);
    },'json');
   
    })
    
//    // создание задачи предварительные действия
//    $('#task-content').focus(function(){
//        
//        $('.task-name-text').css('height','50px');
//        $('.task-name-text').attr('placeholder','');
//    });
    
    // создание задачи предварительные действия
    $('#task-content').click(function(){
        
        $('.task-name-text').css('height','50px');
        $('.task-name-text').attr('placeholder','');
    });
    
    $('.dotaskclass1-input').click(function(){
        $('.dotaskclass1-input').attr('placeholder','');
    })
    
    $('.dotaskclass1-input').focusout(function(){
        $('.dotaskclass1-input').attr('placeholder','Название задачи');
    })
    
    
    $('#task-price-id').click(function(){
        $('#task-price-id').attr('placeholder','');
    });
    
    $('#task-price-id').focusout(function(){
        $('#task-price-id').attr('placeholder','Вознаграждение');
    });
    
    $('#task-content').click(function(){
        $('#task-content').attr('placeholder','');
    });
    
    $('#task-content').focusout(function(){
        $('#task-content').attr('placeholder','Введите описание задачи');
    });
    
    
    
    
//    // потеря фокуса формы
//    $('#task-content').focusout(function(e){
//     
//        $('#task-content').keypress(function(e){
//	alert(e.keyCode);
//        if(e.keyCode==13){
//		// Enter pressed... do anything here...
//	}
//        if (e.keyCode == 16) {alert("ctr");}
//        if (e.keyCode == 17) {alert("shift");}
//        });
//        
//        
//        if ($('#task-content').val() == "" )
//        {   
//                $('.task-name-text').css('height','16px');
//                $('.task-name-text').attr('placeholder','Свободным текстом напишите название задачи');
//            //  alert(2);
//        }
//        
//        
//    });
    
    
//    $('#testtext').click(function(){
//       $('#testtext').css('height','50px');
//       $('#testtext').attr('placeholder','');
//   }) ;
   
  
  
//    // кейдаун
//    $('#task-content').keyup(function(){
////        $('#task-content').focusout(function(e){
////            e.preventDefault();
////        });
////        $('#task-content').focus();
//        $('.task-name-text').css('height','50px');
//        $('.task-name-text').attr('placeholder','Свободным текстом напишите название задачи');
//       // alert("keyup");
//    });
//    
//    
//     // кейдаун
//    $('#task-content').keydown(function(){
//        //e.preventDefault(); 
////        $('#task-content').focusout(function(e){
////            e.preventDefault();
////        });
////        $('#task-content').focus();
//        $('.task-name-text').css('height','50px');
//        $('.task-name-text').attr('placeholder','Свободным текстом напишите название задачи');
//      //  alert("keydown");
//    });
//  
    
    //Создание задачи
    $("#task-submit").click(function(e){ e.preventDefault();  
       // не пустое название задачи
        if ($('#task-name').val()=="")
        {
            $('#task-name').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#task-name').animate({backgroundColor:'#fff'}, 1000);
           // $('.task-name-text').attr('placeholder','Введите название задачи');
            var mark = 0; // проверка не пройдена
        }
        else var mark = 1;
  
        // цена задачи число
        if  ($.isNumeric($('#task-price-id').val()) && $('#task-price-id').val()> 0)
            var mark2 = 2; 
        else
        {
            $('#task-price-id').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#task-price-id').animate({backgroundColor:'#fff'}, 1000);
           // $('#task-price-id').attr('placeholder','Неверная цена');
           var mark2 = 0;  // проверка не пройдена
        }
       
       // дата не указана
       if ($('#dateinput-id').val() =="")
       {
           // $('#message').css({'color':'red'}); 
           //$('#message').html('');
            $('#dateinput-id').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#dateinput-id').animate({backgroundColor:'#fff'}, 1000);  
           var mark3 = 0; // проверка не пройдена
       }
       else     
          var mark3 = 3;  
       
       if ($('#task-content').val()=="")
        {
            $('#task-content').animate({backgroundColor:'#FFD3D3'}, 400);
            $('#task-content').animate({backgroundColor:'#fff'}, 1000);
           // $('.task-name-text').attr('placeholder','Введите название задачи');
            var mark4 = 0; // проверка не пройдена
        }
        else var mark4 = 4;  
//        if (mark == 0)
//            $('#message').append('Введите название задачи');
//        else if(mark2 == 0)    
//            $('#message').append('Правильно укажите цену задачи');
//        else if (mark3 == 0)
//            $('#message').append('Введите дату окончания');
        
         
        
       if (mark == 1 && mark2 == 2 && mark3 == 3 && mark4 == 4)
       { 
            $.post('/tasks/maketask',$('#task-form-do').serialize(),function(results){ 
            if(results.status != 1) $('#message').css({'color':'red'}); else $('#message').css({'color':'green'}); 
            $('#message').html(results.message);  
            location.reload();
            },'json');
       }
      
   
    });
    
    // показать форму для места действия
    $('#placetaskdo').click(function(e){
        e.preventDefault();
        
    })
    
    $('#taskaddmorea').click(function(e){
        e.preventDefault();
        $('#taskaddmore').show();
        $('#taskaddmore').animate({top:'-20px'}, 300);
        //$('#taskaddmore').animate({top:'0px'}, 1000);
    })
    
    $('#showlistperformers').click(function(e){
        e.preventDefault();
        $('#pers-t').show();
    })
    
//    $('.task-friends-list').change(function(){
//        if ($('.task-friends-list').val() == 0)
//             $('#pers-t').hide(); 
//    })
    // не отправлять форму ЗАДАЧ при ентере
  // $('#task-form').submit(function(e){e.preventDefault()}); 
    // фильтр исполнителей
    $('#task-form-performers').change(function(){
        
//       $.post('/tasks/findtasksresult',$('#task-form').serialize(),function(results){ 
//         $('.task-container-left').html('');
//         $('.task-container-left').append(results.html);   
//       },'json'); 
       
       
    });
    // удалить из верхнего меню исполнителей
    $('.tasks-line-performers').click(function(){
        
        $('#'+$(this).attr('id')).hide();
        $('#li'+$(this).attr('id')).removeAttr('checked');

//        $.post('/tasks/findtasksresult',$('#task-form').serialize(),function(results){ 
//        $('.task-container-left').html('');
//        $('.task-container-left').append(results.html);  
//        },'json'); 
        
          
    });
    // показывает верхнюю строку исполнителей
    $('.category-input-performers').click(function(){
        if ($('#task'+$(this).val()).css('display') == "none")
           $('#task'+$(this).val()).show();
        else
           $('#task'+$(this).val()).hide(); 
        
    });
    
    
    // фильтр задач
    $('#task-form').change(function(){
        
       $.post('/tasks/findtasksresult',$('#task-form').serialize(),function(results){ 
         $('.task-container-left').html('');
         $('.task-container-left').append(results.html);   
       },'json'); 
       
       
    });
    // удалить из верхнего меню задач
    $('.tasks-line').click(function(){
        
        $('#'+$(this).attr('id')).hide();
        $('#li'+$(this).attr('id')).removeAttr('checked');

        $.post('/tasks/findtasksresult',$('#task-form').serialize(),function(results){ 
        $('.task-container-left').html('');
        $('.task-container-left').append(results.html);  
        },'json'); 
        
          
    });
    // показывает верхнюю строку задач
    $('.category-input').click(function(){
        if ($('#task'+$(this).val()).css('display') == "none")
           $('#task'+$(this).val()).show();
        else
           $('#task'+$(this).val()).hide(); 
        
    });
    
    // порядок сортировки в разделе мои задачи
    $('#task-order').click(function(){
        
        $.post('/tasks/mytaskold',{mark:1},function(results){ 
        $('.mytasks-result').html('');
        $('.mytasks-result').append(results.html);   

        },'json'); 
    })
    
    
    // мессадж для друга
    var friend_set = $('form input[name=friend-set]').val();
    if (friend_set != 0)
        show_friends(friend_set);
    // месадж
    $('#select_wrap1').change(function(){
        if ($(this).val() == 1)
            location.href = "/messages/"; 
        if ($(this).val() == 4)
            location.href = "/profile/tools/";
        if ($(this).val() == 6)
            location.href = "/friends";
        
    });
    
    // отправить сообщение
    $('#message-text').click(function(){
        var id = $(this).attr('name').split("-"); 
      location.href = '/messages/addnewpost/?id='+id[1];
    });
    
    
    // отправляем сообщение другу пользователю.
    $('#addmessage').change(function(){
       
    });
    $('#addmessage').submit(function(e){e.preventDefault()}); 
    
     // выводим блок с результатами поиска
     $('#search-user').click(function(){
        
        
        $.post('/messages/showallfriends',{mark:1},
            function(result)
            {
                $('.search_user').html('');
                $('.search_user').append(result.html);
            },'json'

        );
        $('.search_user').show();
     });
     // скрываем выпадающее меню поиска
     $('#search-user').focusout(function(){
        if (!$('.search_user').is(":hover"))
            $('.search_user').hide();
        
     });
     // обновляем блок с результати поиска 
     $('form input[name=to-user]').keyup(function(){
         $('.search_user').show();
         
         var search_data = ($(this).val());
         $.post('/messages/showallfriends',{search_str:search_data,mark:1},
            function(result)
            {
                $('.search_user').html('');
                $('.search_user').append(result.html);
            },'json'

        );
     });
     
     $('#messagesend').click(function(){
         
         $.post('/messages/sendmessage',$('#addmessage').serialize(),
            function(result)
            {
               $('.message').html('');
               $('.message').append('<div class="done-message">Ваше сообщение отправлено</div>');
               setTimeout(function(){
               if (result.stat ==1)    
                   location.href="/messages/addnewpost/";
               else    
                   location.href="/messages/dialog/?id="+result.id;
               }, 1000);
               
            },'json'
         );
     });
     
  
    // показывать окно чата раскрытым снизу
    var height = $('.marker-div').offset();
    if (height)
        $('.dialog-container').animate({
          scrollTop: height['top']*2
        }, 1);
  
    
    
    // подключение и настройка датепиера с временем
    $('#datetimepicker').datetimepicker({
        language: 'ru',
        startDate: new Date(),
        todayBtn: true,
        pick12HourFormat: true,
        pickTime: true,
        
      });
   // $('#datetimepicker').datetimepicker('update', new Date());
    //initialDate:new Date(),
   
   
    
    
    // вкладка создать задачу (скрываем тендер || ответственного)
    $('#tender-t').click(function(){
        $('#friend-id').attr('disabled', true).trigger("chosen:updated")
    });
    $('#person-t').click(function(){
        $('#friend-id').attr('disabled', false).trigger("chosen:updated")
    });
    
     //alert(document.cookie);
     
     // посылка заявки на задачу
    $('.task-from-request').click(function(){
        var text = $('#task-text-request').val();
        if (text != "")
        {    
            $.post('/tasks/addtaskrequest',$('#task-request').serialize(),
            function(result){
                if(result.stat == 1)
                {   
                    $('.task-main-request').hide();
                }
                location.reload();
            } ,'json'
            );
        }
        else
        {
            alert('Заполните поле заявки');
        }
         
    }) ;
    
    // принимаем заявку исполнителя на задачу
    $('.tasks-performers-requests').click(function(){
       
        var param = $(this).attr('id');
        var id = param.split('-');
        
        $.post('/tasks/accepttaskrequest',{performerid :id[2],taskid : id[1]},
            function(result){
                if(result.stat == 1)
                {   
                  
                    $('.task-main-request').hide();
                }
                location.reload();
            } ,'json'
            );
        
        
        
    }); 
    
    
    // переход между моими заявками
    $('.request-change').click(function(e){e.preventDefault();
        var id = $(this).attr('id').split('-');
        
        if (id[1] == 1)
            $.post('/tasks/myrequestsactive',{ mark: 1},
            function(result){
                  
                  $('.task-requests-content').html('');
                  $('.task-requests-content').append(result.html);  
               
            } ,'json'
            );
        if (id[1] == 2)
            $.post('/tasks/myrequestsaccepted',{ mark: 1},
            function(result){
                  $('.task-requests-content').html('');
                  $('.task-requests-content').append(result.html);  
              
                
            } ,'json'
            );
        if (id[1] == 3)
            $.post('/tasks/myrequestsfailed',{ mark: 1},
            function(result){
                  $('.task-requests-content').html('');
                  $('.task-requests-content').append(result.html);  
             
            } ,'json'
            );    
        //alert(id);
        
    });
    
    
    // создаем раздел
    $('#add-department-form').click(function(e){e.preventDefault();
       var text = $('#department-form-id').val();
       if (text != "")
       {
            $.post('/friends/createdepartment',$('#department-form').serialize(),
                 function(result){
                     if (result.status == 1)
                     {
                         $('.message').html('');
                         $('.message').append('Отдел успешно создан');
                         $('#department-form-id').html('');

                     }    
                     else
                     {
                         $('.message').html('');
                         $('.message').append('Отдел не был создан');
                     }


                 } ,'json'
                 );    
             $('.hidden-dep').show();
         }
         else
         {
             $('.message').html('');
             $('.message').append('Заполните название отдела');
         }    
    });
    
   
    
    // показываем окно с выбором сотрудников (для нового отдела)
    $('#add-list-dep').click(function(){
       
       $.post('/friends/addpeopletodep',{},
            function(result){
                if(result.ok == 1)
                {   
                    $('.yuor-people-add-list').show();
                    $('.yuor-people-add-list').html('');
                    $('.yuor-people-add-list').append(result.html);
                    
                   
                }
               
            } ,'json'
            );
      
    
        
    });
  
    
    // штрафы 
    // добавить штраф 1 раз
    
    $('#addpenalty').click(function(e){
        e.preventDefault;
   //     penaltys-list-action
        $('.penaltys-list-action').chosen({width: "100%"});    
        $('.add-more-penaltys').show(); 
        $.post('/tasks/penaltyslist',$('#task-form-do').serialize(),function(result){
            $('#parametrs-list').append(result.html);
            $('.penaltys-list-action').chosen({width: "100%"});
            
        },'json'
                );
        
        // убираем возможность повторного нажатия
        $('#addpenalty').remove();
        $('.tasktext12').append('<a href="#" class="taska1">Штрафы</a>');
        
        
    });
    
    // добавить еще 1 штраф 
    $('.add-more-penaltys').click(function (){
     
        $.post('/tasks/penaltyslistedited',$('#task-form-do').serialize(),function(result){
            $('#parametrs-list').append(result.html);
            $('.penaltys-list-action').chosen({width: "100%"}).change();
           // $('#parametrs-list').append('<div class="deletepenalty">dasdsad</div>');
        },'json'
                );
    });
    
    //delete penalty
    
    
    
    // загрузка аватара
    /////////////////////////////////////////////////////////////////////
    // показать всплывающее меню
    $('#target-img').hover(
        function(){
        $('.modal-upload-img').show();
        $('.modal-upload-img').stop().animate({ top: "-20" }, 500);
        
        },
        function(){
        $('.modal-upload-img').hide();
        $('.modal-upload-img').stop().animate({ top: "0" }, 1);
        
    });
    // показать всплывающее меню
    $('.modal-upload-img').hover(function(){
       $('.modal-upload-img').show();
       $('.modal-upload-img').stop().animate({ top: "-20" }, 500);
        },
       function(){
        $('.modal-upload-img').hide();
        $('.modal-upload-img').stop().animate({ top: "0" }, 1);
    }); 
    
    // показываем окно с выбором файла
    $('#avatar-load1').click(function(e){
        e.preventDefault();
        
        $.post('/profile/adddialogbox',{mark:1},
                function(result){
                     
                    $('.load-avatar').html('');
                    $('.load-avatar').append(result.html);

                } ,'json'
                );
 //       Array: An array containing an x, y coordinate pair in pixel offset 
 //       from the top left corner of the viewport or the name of a possible string value.
         $('.load-avatar').dialog({
            width: 650,
            modal: true,
            close: function( event, ui ) {
                $('.imgareaselect-outer').hide();
                $('.imgareaselect-selection').hide();
                $('.imgareaselect-handle').hide();
                $('.imgareaselect-border1').hide();
                $('.imgareaselect-border2').hide();
                $('.imgareaselect-border3').hide();
                $('.imgareaselect-border4').hide();
                
                },
        buttons: {

                 }
        });
        
    })
 
  
  //показать поле редакутирования статуса 
  $('.self_status_a').click(function(e){
     e.preventDefault();
     $('.self_status_a').hide();
     $('.change-status').show();
     $('.savestatus').show();
     
  });
    
  // сохранить статус если не пустой
  $('#status-change').submit(function(e){
      e.preventDefault();
     $.post('/profile/changestatus',$('#status-change').serialize(),
      function(result){
         location.reload(); 
      }); 
      
  });
  
  
   
    
});
//
//function textkeyon()
//{
//       if ($('#task-content').val() == "")
//       {
//           $('#task-content').css('height','50px');
//           $('#task-content').attr('placeholder','Введите название задачи');
//       }
//}  
//
//function textkeydown()
//{
//      if ($('#task-content').val() == "")
//       {
//           $('#task-content').css('height','50px');
//           $('#task-content').attr('placeholder','Введите название задачи');
//       }
//   
//}
//function focusout()
//{
//     
//   $('#task-content').focusout(function(){ 
//      if ($('#task-content').val() == "")  
//      {
//        $('#task-content').css('height','16px');
//        $('#task-content').attr('placeholder','Введите название задачи');
//      }  
//   });
//}


// удалить 1 штраф
function delpenaltyline(id)
{
    $('#'+id).remove();
   
}
// переходим к выбору миниатюры
function proccedtocrop()
{
    //$('.proccedtocrop').click(function(){
    
       var fullpath = $('.imgsend-name').attr('id'); // полный путь
       var path = fullpath.substr(23); // имя файла
       
       $.post('/profile/changeavatar',{path:fullpath},
                function(result){
                     
                    $('.load-avatar').html('');
                    $('.load-avatar').append(result.html);

                } ,'json'
                );   
 //   });
}
// сохраняем миниатюру
function miniimgsave()
{
    $.post('/profile/imagecropdone',$('#cropimage-done').serialize(),
                function(result){
                     location.reload();
                } ,'json'
                );   
    //alert(img);
}



// переходим в новый созданый раздел
function shownewotdel()
{
   $.post('/friends/lastotdel',{mark:1},
                function(result){
                      if (result.ok == 1)
                      {
                         var id = result.id;
                          // выводим отдел
                         //alert("2par-"+id);
                         return id;
                        
                      }

                } ,'json'
                );   
    
    
   
    
}

// удаляем человека из отдела
function delpeoplefromlist(user_id,curent_otdel)
{
    
    $.post('/friends/delpeoplefromlist',{ user: user_id,otdel:curent_otdel},
                function(result){
                      if (result.ok == 1)
                      {
                         $.post('/friends/peoplelistshow',{ mark: curent_otdel },
                         function(result){
                              $('.people-shows').html('');
                              $('.people-shows').append(result.html);  

                         } ,'json'
                         );  
                      }

                } ,'json'
                );   
    
}

// закрыть окно добавляемых сотрудников
function closedepartments()
{
    $('.main-dep-list').hide();
    $('.yuor-people-add-list').hide();
    
   // alert($('#people-list-id').val());
   // alert("3par"+param);
    // переходит в последний созданый отдел
   // showpeoplespecialrel(param);
    // переходит в текущий  отдел
    showpeoplespecialrel($('#people-list-id').val());
    
}
// добавляет выбранного человека в мой список людей
function addpeopletomydep(param)
{
    var id = $('.otdel-list').attr('id');
            //alert(id);
    if (id)  // выбран конкретный отдел 
        var otdel = id.split("-")[1];
    // добавляем в новый(последний созданый) отдел людей
    if (!id)
    {    
       // var mark = 0;
        var otdel_id = 0;
    }
    else // добавляем людей в  выбранный отдел 
    {
       // var mark = 1;
        var otdel_id = otdel;
    }
    
    $('#dep-request-'+param).attr('onclick','');
    $.post('/friends/addpeopletomydep',{ user_id: param, otdel: otdel_id},
                function(result){
                      if (result.ok == 1)
                      {
                         $('#dep-request-'+param).html(''); 
                         $('#dep-request-'+param).append('Добавлен'); 
                      }

                } ,'json'
                );   
    
}


// удаляем выбранный отдел и всех людей из него
function delotdel(otdel_id)
{
   
     $.post('/friends/delotdel',{otdel:otdel_id},
            function(result){
                if(result.ok == 1)
                {   
                    location.href='/friends/';
                }
               
            } ,'json'
            );
    
    
}


// выводит  показываем окно с выбором сотрудников
function addlistdep(id)
{
    
     $.post('/friends/addpeopletodep',{otdel_id : id},
            function(result){
                if(result.ok == 1)
                {   
                    $('.yuor-people-add-list').show();
                    $('.yuor-people-add-list').html('');
                    $('.yuor-people-add-list').append(result.html);
                    $('.yuor-people-add-list').append('<span class="otdel-list" id="otdel-'+id+'"></span>');
                }
               
            } ,'json'
            );
   
}

// выводит список людей по выбраному из списка разделу
function showpeoplespecial(param)
{
   
  //  if (
  //          == 0)
  //      location.href='friends/crsection';
    $.post('/friends/peoplelistshow',{ mark: param.value },
            function(result){
                  $('.people-shows').html('');
                  $('.people-shows').append(result.html);  
             
            } ,'json'
            );    
}

// обновляем список людей
function showpeoplespecialrel(id)
{
    if (id != 0) // если не новый отдел
    {    
        $.post('/friends/peoplelistshow',{ mark: id },
                function(result){
                      $('.people-shows').html('');
                      $('.people-shows').append(result.html);  

                } ,'json'
                );    
    }
    
}

function change_body_class(param) 
{
     $.cookie('classname',param,{ path: '/'});
     location.reload();
       
}


 // возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}

// устанавливает cookie c именем name и значением value
// options - объект с свойствами cookie (expires, path, domain, secure)
function setCookie(name, value, options) {
  options = options || {};

  var expires = options.expires;

  if (typeof expires == "number" && expires) {
    var d = new Date();
    d.setTime(d.getTime() + expires*1000);
    expires = options.expires = d;
  }
  if (expires && expires.toUTCString) { 
  	options.expires = expires.toUTCString();
  }

  value = encodeURIComponent(value);

  var updatedCookie = name + "=" + value;

  for(var propName in options) {
    updatedCookie += "; " + propName;
    var propValue = options[propName];    
    if (propValue !== true) { 
      updatedCookie += "=" + propValue;
     }
  }

  document.cookie = updatedCookie;
}

// удаляет cookie с именем name
function deleteCookie(name) {
  setCookie(name, "", { expires: -1 })
}

// показываем друзей в поиске для сообщений
    function show_friends(id)
    {
        //alert(id);
       //var param = id.split("-");
      //  $.post('/messages/friendsinfo',{friend_id:id},function(result){
     //       alert(result.name)},'json'
     //   );  +result.name+' '+result.family+
    // var param = $('#fr'+id).attr('id');
    // var par = param.split("fr");
     
    //    if (par != id)
    $('#fr'+id).show();
        //    $('.main-input').prepend('<div class="hiden-friends" id="fr'+id+'">fried</div>');
        
        
        $('.add-message').append('<input type="hidden" name="friends[]" value="'+id+'">');
        $('.search_user').hide();

    }


// выводит список городов для смены в фильтре
    function change_country_filter(obj)
    {
       
        var res = obj.split("-");
        //alert(res);
        if (res[1] != 0)
        {   
            $.post('/profile/sitylist',{'country_cur':res[1],cur_city:res[2]},
            function(result)
            {
                    $('#filter-city').html('');
                    $('#filter-city').append('<option value="0" label="не выбрано">не выбрано</option>');

                    var o = result.data_name;
                    var cur_city = result.cur_city; 
                    for (var key in o) 
                    {
                        if (cur_city != key) 
                            $('#filter-city').append('<option value='+key+' label='+o[key]+'>'+o[key]+'</option>');
                        else
                            $('#filter-city').append('<option value='+key+' selected label='+o[key]+'>'+o[key]+'</option>');

                    }
                    $('#filter-city').trigger("chosen:updated");

                
             },'json'

             );
             
        }
    
    }

// выводит список городов при смене страны
     function change_country (obj){  
            var id = $(obj).val();
            var id_form = ($(obj).attr('id'));
            var res = id_form.split("-");
            
            if (id != 0)
                {    
                    $.post('/profile/sitylist',{'country_cur': id},
                    function(result)
                    {

                    if (result !='')
                        {
                            var res = id_form.split("-");

                            $('#city-name-'+res[2]).chosen().change();
                            $('#div-city-name-'+res[2]).show(); 
                            $('#city-name-'+res[2]).html('');
                            $('#city-name-'+res[2]).append('<option value="0" label="не выбрано">не выбрано</option>');

                            var o = result.data_name
                            for (var key in o) 
                            {
                                $('#city-name-'+res[2]).append('<option value='+key+' label='+o[key]+'>'+o[key]+'</option>');

                            }

                            $('#city-name-'+res[2]).trigger("chosen:updated");
                            $('.rowc-'+res[2]).show(); 
                        }
                    },'json'

                    );
                }
                else
                {
                   $('.rowc-'+res[2]).hide(); 
                   $('#career-info-'+res[2]).hide();
                }
         
    }
    //  выводит список город для текущей страны
    function change_city (obj){   
        
        var id_cur = ($(obj)).attr('id');
        var str = id_cur.split("-");
        var country_id = $('#country_set-'+str[2]).val();
         
        if ($(obj).val() != 0)
        { 
            $('#career-info-'+str[2]).show();
            $.post('/profile/sitylist',{'country_cur': country_id},function(result) {

                if (result !='')
                    {
                       // $('#city-name-'+str[2]).selectbox('detach'); 
                   //     $('#city-name-'+str[2]).chosen().change(); 
                        
                        var o = result.data_name;
                        for (var key in o) 
                        {
                            $('#city-name-'+str[2]).append('<option value='+key+' label='+o[key]+'>'+o[key]+'</option>');
                        }
                       // $('#city-name-'+str[2]).selectbox();
                  //      $('#city-name-'+str[2]).trigger("chosen:updated"); 
                    }
                    
            },'json'

            );
        }
        else
        {
            $('#career-info-'+str[2]).hide();
        }
    }
    // удаляет запись карьеры
    function del_career_form (val){
    
        $('.career-add-'+val).hide();
    
        
    }
   
   
   //Запрос на добавление в друзья
   function add_friend(id)
   {
       alert(id);
   }
   
   function change_lang(lang) 
   {
       $.cookie('lang',lang,{ path: '/'});
       location.reload();
   }

