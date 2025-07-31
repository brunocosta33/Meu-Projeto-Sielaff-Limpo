/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('users-table', require('./components/UsersTable.vue').default);
Vue.component('roles-table', require('./components/RolesTable.vue').default);
Vue.component('permissions-table', require('./components/PermissionsTable.vue').default);
Vue.component('products-table', require('./components/productsTable.vue').default);



$(document).ready(function() {
    $.ajaxSetup({

        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $(this).toggleClass('active');

        
 if (window.matchMedia("(max-width: 768px)").matches) {
    if ($('#sidebarCollapse').hasClass('active')) {
        $('#img_logo').removeClass('visibile').addClass('invisible');
    } else {
        $('#img_logo').removeClass('invisible').addClass('visible');
    }
}
else{
 if ($('#sidebarCollapse').hasClass('active')) {
$('#img_logo').removeClass('invisible').addClass('visibile');
} else {
$('#img_logo').removeClass('visibile').addClass('invisible');
}
}
    });

    $('div.alert').not('.alert-important').delay(3000).fadeOut(350);

    $('#flash-overlay-modal').modal();

    $('#change-password-trigger').on('click', function(event) {
       
        event.preventDefault();
        $.ajax({
            url: '/profile/change-password',
            type: 'GET',
            dataType: 'HTML',
            success: function(data, textStatus, jqXHR){
                $.showModal({
                    title: 'Alterar Password',
                    body: $(data).find('#change-password').html(),
                    onCreate: function (modal) {
                        // create event handler for form submit and handle values
                        $(modal.element).on("click", "button[type='submit']", function (event) {
                            event.preventDefault()
                            var form = $(modal.element).find("form").serialize();

                            $.ajax({
                                url: '/profile/change-password/save',
                                type: 'POST',
                                dataType: 'json',
                                data: form,
                                success: function(data, textStatus, jqXHR){
                                    modal.hide();
                                    window.location.reload();
                                },
                                error: function(jqXHR, textStatus, errorThrown){
                                    var errors = jqXHR.responseJSON;

                                    $.each(errors, function(key, value){ 
                                        $('#'+key).addClass('is-invalid').parent().append($('<div/>', {class: 'invalid-feedback'}).text(value[0]));
                                    });
                                }
                            });
                        });
                    }
                });
            },
            error: function(jqXHR, textStatus, errorThrown){

            }
        });
        
    });
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

window.app = new Vue({
    el: '#app',
    methods: {
        slug(){
            var slug = $('.slugify').val().toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text

            $('#slug').val(slug);
        }
    }
});
