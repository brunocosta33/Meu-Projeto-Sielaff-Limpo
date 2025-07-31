$(function() {


    var names = [];
    $('body').on('change', '.picupload', function(event) {
        var getAttr = $(this).attr('click-type');
        var files = event.target.files;
        
        var output = document.getElementById("media-list");
        var z = 0
        
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                names.push($(this).get(0).files[i].name);
                
                if (file.type.match('image')) {

                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;

                        var div = document.createElement("li");

                        div.innerHTML = "<img src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'/><input type=\"hidden\" name=\"imagens[]\" value=\"" + picFile.result + "\" ><div  class='post-thumb'><div class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $("#media-list").prepend(div);

                    });
                } else {
                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;

                        var div = document.createElement("li");

                        div.innerHTML = "<video src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'></video><div class='post-thumb'><div  class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $("#media-list").prepend(div);

                    });
                }
                picReader.readAsDataURL(file);

            }
            // return array of file name
            console.log(names);
        

    });


    $('body').on('change', '.picupload-mobile', function(event) {
   
        var getAttr = $(this).attr('click-type');
        var files = event.target.files;
        
        var output = document.getElementById("media-list-mobile");
        var z = 0
        
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                names.push($(this).get(0).files[i].name);
                
                if (file.type.match('image')) {

                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;

                        var div = document.createElement("li");

                        div.innerHTML = "<img src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'/><input type=\"hidden\" name=\"imagens-mobile[]\" value=\"" + picFile.result + "\" ><div  class='post-thumb'><div class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $("#media-list-mobile").prepend(div);

                    });
                } else {
                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;

                        var div = document.createElement("li");

                        div.innerHTML = "<video src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'></video><div class='post-thumb'><div  class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $("#media-list-mobile").prepend(div);

                    });
                }
                picReader.readAsDataURL(file);

            }
            // return array of file name
            console.log(names);
        

    });






    $('body').on('change', '.onepicupload', function(event) {
        
        var getAttr = $(this).attr('click-type');
        var files = event.target.files;
        
        var output = document.getElementById("media-list");
        var z = 0
        
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                names.push($(this).get(0).files[i].name);
                
                if (file.type.match('image')) {
                    

                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;
                        if($('ul.clearfix > li').length > 1 ){
                            removeItem = document.getElementById("imgtmp").getAttribute("value");
                            var $promise = $.ajax({
                                url: '/api/backoffice/imageDelete/' + removeItem,
                                type: 'GET',
                                success: function(response, status) {
                                    console.log('AJAX success: ' + response);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown) {
                                    console.log('AJAX error:' + textStatus);
                                }
                            });
                            document.getElementById('imgtmp').innerHTML = '';
                            var div = document.getElementById('imgtmp');
                        }else{
                            var div = document.createElement("li");
                            div.setAttribute("id", "imgtmp");
                        }
                       

                        div.innerHTML = "<img src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'/><input type=\"hidden\" name=\"imagens[]\" value=\"" + picFile.result + "\" ><div  class='post-thumb'><div class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $("#media-list").prepend(div);

                    });
                } else {
                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;
                        if($('ul.clearfix > li').length > 1 ){
                            removeItem = document.getElementById("imgtmp").getAttribute("value");
                            var $promise = $.ajax({
                                url: '/api/backoffice/imageDelete/' + removeItem,
                                type: 'GET',
                                success: function(response, status) {
                                    console.log('AJAX success: ' + response);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown) {
                                    console.log('AJAX error:' + textStatus);
                                }
                            });
                            document.getElementById('imgtmp').innerHTML = '';
                            var div = document.getElementById('imgtmp');
                        }else{
                            var div = document.createElement("li");
                            div.setAttribute("id", "imgtmp");
                        }

                        div.innerHTML = "<video src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'></video><div class='post-thumb'><div  class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $("#media-list").prepend(div);

                    });
                }
                picReader.readAsDataURL(file);

            }
            // return array of file name
            console.log(names);
        

    });

    
    $('body').on('change', '.onepicupload-mobile', function(event) {
        
        var getAttr = $(this).attr('click-type');
        var files = event.target.files;
        
        var output = document.getElementById("media-list-mobile");
        var z = 0
        
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                names.push($(this).get(0).files[i].name);
                
                if (file.type.match('image')) {
                    

                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;
                        if($('ul.clearfix-mobile > li').length > 1 ){
                            removeItem = document.getElementById("imgtmp-mobile").getAttribute("value");
                            var $promise = $.ajax({
                                url: '/api/backoffice/imageDelete/' + removeItem,
                                type: 'GET',
                                success: function(response, status) {
                                    console.log('AJAX success: ' + response);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown) {
                                    console.log('AJAX error:' + textStatus);
                                }
                            });
                            document.getElementById('imgtmp-mobile').innerHTML = '';
                            var div = document.getElementById('imgtmp-mobile');
                        }else{
                            var div = document.createElement("li");
                            div.setAttribute("id", "imgtmp-mobile");
                        }
                       

                        div.innerHTML = "<img src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'/><input type=\"hidden\" name=\"imagens-mobile[]\" value=\"" + picFile.result + "\" ><div  class='post-thumb'><div class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $(".clearfix-mobile").prepend(div);

                    });
                } else {
                    var picReader = new FileReader();
                    picReader.fileName = file.name
                    picReader.addEventListener("load", function(event) {

                        var picFile = event.target;
                        if($('ul.clearfix-mobile > li').length > 1 ){
                            removeItem = document.getElementById("imgtmp-mobile").getAttribute("value");
                            var $promise = $.ajax({
                                url: '/api/backoffice/imageDelete/' + removeItem,
                                type: 'GET',
                                success: function(response, status) {
                                    console.log('AJAX success: ' + response);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown) {
                                    console.log('AJAX error:' + textStatus);
                                }
                            });
                            document.getElementById('imgtmp-mobile').innerHTML = '';
                            var div = document.getElementById('imgtmp-mobile');
                        }else{
                            var div = document.createElement("li");
                            div.setAttribute("id", "imgtmp-mobile");
                        }

                        div.innerHTML = "<video src='" + picFile.result + "'" +
                            "title='" + picFile.name + "'></video><div class='post-thumb'><div  class='inner-post-thumb'><a href='javascript:void(0);' data-id='" + event.target.fileName + "' class='remove-pic'><i class='fa fa-times' aria-hidden='true'></i></a><div></div>";

                        $(".clearfix-mobile").prepend(div);

                    });
                }
                picReader.readAsDataURL(file);

            }
            // return array of file name
            console.log(names);
        

    });

    $('body').on('click', '.star-pic', function() {
        event.preventDefault();
        
        
        jQuery('.star-pic-selected').addClass('star-pic');
        jQuery('.star-pic-selected').removeClass('star-pic-selected');
            
            $(this).addClass('star-pic-selected');
            jQuery(this).removeClass('star-pic');
            
            var starImg = $(this).attr('star-data-id');
            
            var Item = $(this).attr('data-id-item');
  
            var $promise = $.ajax({
                url: '/api/backoffice/imageStar/' + starImg+'/'+Item,
                type: 'GET',
                success: function(response, status) {
                    console.log('AJAX success: ' + response);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log('AJAX error:' + textStatus);
                }
            });
            return false;


            // return array of file name
            // console.log(names);
       
        
    });

    
    $('body').on('click', '.star-pic-mobile', function() {
        event.preventDefault();
        
        
        jQuery('.star-pic-selected-mobile').addClass('star-pic-mobile');
        jQuery('.star-pic-selected-mobile').removeClass('star-pic-selected-mobile');
            
            $(this).addClass('star-pic-selected-mobile');
            jQuery(this).removeClass('star-pic-mobile');
            
            var starImg = $(this).attr('star-data-id');
            
            var Item = $(this).attr('data-id-item');
  
            var $promise = $.ajax({
                url: '/api/backoffice/imageMobileStar/' + starImg+'/'+Item,
                type: 'GET',
                success: function(response, status) {
                    console.log('AJAX success: ' + response);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log('AJAX error:' + textStatus);
                }
            });
            return false;


            // return array of file name
            // console.log(names);
       
        
    });


    $('body').on('click', '.remove-pic-saved', function() {
        event.preventDefault();
        
        if (confirm(window.translations['Are you sure?'])) {

            $(this).parent().parent().parent().remove();
            var removeItem = $(this).attr('data-id');
            var refreshPage = $(this).data('refresh') ?? false;
            var yet = names.indexOf(removeItem);
    
            if (yet != -1) {
                names.splice(yet, 1);
            }

            var $promise = $.ajax({
                url: '/api/backoffice/imageDelete/' + removeItem,
                type: 'GET',
                success: function(response, status) {
                    console.log('AJAX success: ' + response);
                
                    if(response > 0) {
                        jQuery('#'+response).removeClass('star-pic');
                        jQuery('#'+response).addClass('star-pic-selected');
                    }
                    if(refreshPage == "1"){
                        console.log('refresh')
                        location.reload();
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log('AJAX error:' + textStatus);
                }
            });
            return false;


            // return array of file name
            // console.log(names);
        } else {
            
        }
        
    });

    $('body').on('click', '.remove-pic', function() {
        if (confirm(window.translations['Are you sure?'])) {        
            $(this).parent().parent().parent().remove();
            var removeItem = $(this).attr('data-id');
            var yet = names.indexOf(removeItem);
    
            if (yet != -1) {
                names.splice(yet, 1);
            }
            // return array of file name
            console.log(names);
        }        
    });
    
    $('#hint_brand').on('hidden.bs.modal', function(e) {
        names = [];
        z = 0;
    });
});